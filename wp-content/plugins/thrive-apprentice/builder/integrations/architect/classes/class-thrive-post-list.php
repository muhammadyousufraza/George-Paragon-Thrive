<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Post_List' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list.php';
}

/**
 * Class Thrive_Post_List
 */
class Thrive_Post_List extends TCB_Post_List {

	/**
	 * query vars that can be modified in the list
	 */
	const ALLOWED_LIST_QUERY_VARS = [
		'posts_per_page',
		'orderby',
		'order',
		'author__in',
		'author__not_in',
		'post__in',
		'post__not_in',
		'offset',
		/* be careful, this one should never be kept for archives! (unless it's the one applied by the post list filter) for an explanation @see list_pre_get_posts */
		'tax_query',
	];

	/**
	 * @param array  $attr
	 * @param string $article
	 */
	public function __construct( $attr = [], $article = '' ) {
		/**
		 * @see thrive_theme_template_copied_data()
		 */
		if ( ! empty( $attr['reset_query'] ) || is_search() ) {
			unset( $attr['reset_query'], $attr['query'] );
		}

		parent::__construct( $attr, $article );
	}

	/**
	 *Init the Query
	 *
	 * @param $attr_query
	 */
	public function init_query( $attr_query ) {
		global $wp_query;

		/* default query values */
		$this->query = [
			'paged' => empty( $wp_query->query['paged'] ) ? 1 : $wp_query->query['paged'],
		];

		try {
			/* replace single quotes with double quotes */
			$decoded_string = str_replace( "'", '"', html_entity_decode( $attr_query, ENT_QUOTES ) );

			/* replace newlines and tabs */
			$decoded_string = preg_replace( '/[\r\n]+/', ' ', $decoded_string );

			$query = json_decode( $decoded_string, true );

			$query = array_merge( static::get_default_query(), $query );

			$this->query = array_merge( $this->query, $query );
		} catch ( Exception $e ) {
			/* something went wrong, so we leave the default values */
		}

		if ( ! empty( $this->attr['featured-list'] ) ) {
			$feature_list_identifier = '[data-css="' . $this->attr['featured-list'] . '"]';

			foreach ( $GLOBALS[ TCB_POST_LIST_LOCALIZE ] as $post_list ) {
				if ( $post_list['identifier'] === $feature_list_identifier ) {
					/* If we find a pair of Blog List - Featured List we add the posts from the Featured List as excluded posts from the Blog List */
					$excluded_posts = $wp_query->get( 'post__not_in' );

					if ( is_array( $post_list['posts'] ) ) {
						$excluded_posts = array_merge( $excluded_posts, $post_list['posts'] );
					}

					$wp_query->set( 'post__not_in', $excluded_posts );

					if ( ! isset( $this->query['rules'] ) ) {
						$this->query['rules'] = [];
					}

					$post_types = isset( $this->query['post_type'] ) ? $this->query['post_type'] : 'post';

					/* when there's more than one post type, add a rule for each post type */
					if ( is_array( $post_types ) ) {
						foreach ( $post_types as $post_type ) {
							$this->query['rules'][] = [
								'taxonomy' => $post_type,
								'terms'    => $post_list['posts'],
								'operator' => 'NOT IN',
							];
						}
					} else {
						$this->query['rules'][] = [
							'taxonomy' => $post_types,
							'terms'    => $post_list['posts'],
							'operator' => 'NOT IN',
						];
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public static function get_blog_default_args() {
		return [
			'type'            => 'list',
			'pagination-type' => 'numeric',
			'columns-d'       => 1,
			'posts_per_page'  => get_option( 'posts_per_page' ),
		];
	}

	/**
	 * Render blog list
	 *
	 * @return string
	 */
	public function render() {
		/* use this flag to prevent rendering a post list inside another post list ( in 'the_content' shortcode ). */
		static::enter_post_list_render();

		$content = '';

		global $wp_query;

		$wp_query->set( 'posts_per_page', $this->attr['posts_per_page'] );

		/**
		 * Make changes before the query from render blog list
		 */
		do_action( 'theme_before_render_blog_list' );

		/* refresh the list of posts after we set the 'posts_per_page' */
		$wp_query->get_posts();

		/* change this from the default 'Post List' to the current name */
		$this->attr['element-name'] = __( thrive_template() . ' List', 'thrive-theme' );

		/* add the total post count for this query - we need this for pagination */
		$this->attr['total_post_count'] = $wp_query->found_posts;

		$posts_per_page = $wp_query->get( 'posts_per_page' );

		$wp_query->query['posts_per_page'] = $posts_per_page;

		$wp_query->query['paged'] = empty( $wp_query->query['paged'] ) ? 1 : $wp_query->query['paged'];

		/* this is set on the normal post list for the cloud template stuff, so we unset it for now */
		unset( $this->attr['tcb-elem-type'] );

		$query = Thrive_Utils::get_query_vars();
		if ( ! empty( $this->query ) ) {
			/* SUPP-14883 - We need to read the order from instance so it won be overwritten by default settings */
			if ( ! empty( $this->query['orderby'] ) ) {
				$query['orderby'] = $this->query['orderby'];
			}

			if ( ! empty( $this->query['order'] ) ) {
				$query['order'] = $this->query['order'];
			}

			$query = array_merge( $this->query, $query );
		}

		$GLOBALS[ TCB_POST_LIST_LOCALIZE ][] = [
			'identifier' => THRIVE_BLOG_LIST_IDENTIFIER,
			'template'   => THRIVE_BLOG_LIST_IDENTIFIER,
			'content'    => $this->article,
			'attr'       => $this->attr,
			'query'      => $query,
		];

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$content .= $this->article_content();
			}
		} else {
			$content = static::no_posts_text();
		}

		/**
		 * Make changes after the query from render blog list
		 */
		do_action( 'theme_after_render_blog_list' );

		if ( Thrive_Utils::is_inner_frame() || TCB_Utils::in_editor_render( true ) ) {
			$this->attr['query'] = str_replace( '"', "'", json_encode( $query ) );
		}

		$content = static::prepare_carousel( $content, $this->attr );

		$content = Thrive_Shortcodes::before_wrap( [
			'content' => $content,
			'tag'     => 'div',
			'id'      => 'main',
			'class'   => $this->get_class_attr( $this->attr ),
			'attr'    => static::blog_attr(),
		], $this->attr );

		static::exit_post_list_render();

		return $content;
	}

	/**
	 * Return post list attributes that will be added to the element
	 *
	 * @return array
	 */
	public static function blog_attr() {
		$attr = [ 'role' => 'main' ];

		if ( Thrive_Utils::is_inner_frame() || TCB_Utils::in_editor_render( true ) ) {
			$attr['data-tcb-elem-type'] = 'blog_list';
			$attr['data-selector']      = THRIVE_BLOG_LIST_IDENTIFIER;
		}

		return $attr;
	}

	/**
	 * Post list classes to be displayed depending on the attributes and screen.
	 * Overrides the parent class.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public function get_class_attr( $attr = [] ) {
		/* tve-content-list identifies lists with dynamic content */
		$classes = [ 'thrive-blog-list', TCB_POST_LIST_CLASS, 'tve-content-list' ];

		if ( $this->in_editor_render ) {
			$classes[] = 'tcb-compact-element';
			$classes[] = 'tcb-selector-no_save';
			$classes[] = 'tcb-selector-no_clone';
			$classes[] = 'tcb-selector-no_delete';
		}

		if ( isset( $attr['type'] ) && $attr['type'] === 'masonry' ) {
			$classes[] = 'tve_post_grid_masonry';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Text to display when there is nothing to display
	 *
	 * @return string
	 */
	public static function no_posts_text() {

		if ( is_home() && current_user_can( 'publish_posts' ) ) {
			$text = sprintf(
				'<p class="no-posts">' . __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'thrive-theme' ) . '</p>',
				esc_url( admin_url( 'post-new.php' ) )
			);
		} elseif ( is_search() ) {
			$text = thrive_template()->get_no_search_results_content();
		} else {
			$text = '<p class="no-posts">' . __( "It seems we can't find what you're looking for. Perhaps searching can help.", 'thrive-theme' ) . '</p>' . get_search_form( false );
		}

		return $text;
	}

	/**
	 * Disable the Related Posts button from the query builder in certain cases.
	 *
	 * @return string
	 */
	public static function disable_query_builder_related_posts() {
		$button_attribute_string = '';

		if ( Thrive_Utils::is_theme_template() ) {
			$has_related_posts = in_array( thrive_template()->get_primary(), [
				THRIVE_SINGULAR_TEMPLATE,
				THRIVE_ARCHIVE_TEMPLATE,
			], true );

			if ( ! $has_related_posts ) {
				$button_attribute_string = 'disabled';
			}
		}

		return $button_attribute_string;
	}

	/**
	 * Change the text from Related Posts to something else, if we're on an archive template.
	 *
	 * @param $text
	 *
	 * @return string|void
	 */
	public static function query_builder_related_posts_text( $text ) {

		if ( Thrive_Utils::is_theme_template() && ! thrive_template()->is_singular() ) {
			$text = __( 'Posts from the same archive type will be displayed.', 'thrive-theme' );
		}

		return $text;
	}

	/**
	 * Do not show the 'exclude current post' checkbox on list templates.
	 *
	 * @param boolean $show
	 *
	 * @return string|void
	 */
	public static function query_builder_show_exclude_current_post( $show ) {
		if ( Thrive_Utils::is_theme_template() && ! thrive_template()->is_singular() ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Add infinite scroll to the list of pagination types.
	 *
	 * @param $pagination_types
	 *
	 * @return mixed
	 */
	public static function add_pagination_types( $pagination_types ) {
		return array_merge( $pagination_types, [ 'infinite_scroll' ] );
	}

	/**
	 * When copying a template that contains an archive list, we have to manually reset the existing query ( exp: we don't want category rules inside a search list ).
	 * In order to do that, we set a 'reset_query' flag here, and when we encounter it inside the Blog List constructor, we reset the query attribute.
	 *
	 * @param \Thrive_Template $destination_template
	 * @param \Thrive_Template $source_template
	 */
	public static function thrive_theme_template_copied_data( $destination_template, $source_template ) {
		$source_sections      = $source_template->meta( 'sections' );
		$destination_sections = $destination_template->meta( 'sections' );
		if ( isset( $destination_sections['content'] ) ) {
			$destination_sections['content']['content'] = str_replace( '[thrive_blog_list', '[thrive_blog_list reset_query=1', $source_sections['content']['content'] );
		}

		$destination_template->set_meta( 'sections', $destination_sections );
	}

	/**
	 * Filter posts for blog / archives / CPT lists
	 *
	 * @param WP_Query $query
	 */
	public static function list_pre_get_posts( $query ) {
		$is_archive = $query->is_archive() || $query->is_post_type_archive();

		/* run this only when we're editing the global query on a blog / archive / CPT list page */
		if ( $query->is_main_query() && ( $query->is_home() || $is_archive ) && apply_filters( 'thrive_theme_should_filter_pre_get_posts', true, $query ) ) {
			$list_query = thrive_template()->get_list_query();
			if ( ! empty( $list_query ) && is_array( $list_query ) ) {

				if ( empty( $list_query['post__not_in'] ) ) {
					$list_query['post__not_in'] = [];
				}

				/* we should not overwrite the post__not_in from the original query */
				if ( ! empty( $query->get( 'post__not_in' ) ) ) {
					$list_query['post__not_in'] = array_merge( $list_query['post__not_in'], $query->get( 'post__not_in' ) );
				}

				$list_query = static::prepare_wp_query_args( $list_query );

				if ( $is_archive ) {
					/**
					 * Since the actual taxonomy query depends on the current archive, we shouldn't apply the saved tax_query for archives
					 *
					 * Explanation:
					 * For instance, saving the 'All archives' TTB template will save a tax_query for the 'Uncategorized' category (which is loaded for 'All archives').
					 * If we apply that tax_query for another category archive, it will load the results for 'Uncategorized', which is not something we want.
					 */
					unset( $list_query['tax_query'] );
					/**
					 * An important thing is to remove the 'tax_query' before calling TCB_Post_List_Filter::filter(),
					 * because it will add a 'tax_query' of its own, which we want to apply to the wp_query
					 */
				}

				if ( class_exists( 'TCB_Post_List_Filter', false ) ) {
					$list_query = TCB_Post_List_Filter::filter( $list_query );
				}

				$allowed_list_query_vars = static::ALLOWED_LIST_QUERY_VARS;

				/* Make sure that the relation is OR, so we can have multiple terms in the tax_query */
				if ( ! empty( $list_query['tax_query'] ) ) {
					$list_query['tax_query']['relation'] = 'OR';
				}

				foreach ( $list_query as $query_var => $value ) {
					if ( ! empty( $value ) && in_array( $query_var, $allowed_list_query_vars, true ) ) {
						$query->set( $query_var, $value );
					}
				}
			}
		}
	}
}
