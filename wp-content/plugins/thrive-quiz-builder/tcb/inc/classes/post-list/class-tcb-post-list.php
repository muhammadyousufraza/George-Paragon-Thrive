<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

defined( 'THRIVE_WRAPPER_CLASS' ) || define( 'THRIVE_WRAPPER_CLASS', 'thrv_wrapper' );
defined( 'TCB_POST_LIST_CLASS' ) || define( 'TCB_POST_LIST_CLASS', 'tcb-post-list' );

/* class that applies to ALL shortcodes ( the post-list shortcodes AND the TTB shortcodes ) */
defined( 'TCB_SHORTCODE_CLASS' ) || define( 'TCB_SHORTCODE_CLASS', 'tcb-shortcode' );

/* class that applies ONLY to the post-list shortcodes */
defined( 'TCB_DO_NOT_RENDER_POST_LIST' ) || define( 'TCB_DO_NOT_RENDER_POST_LIST', 'do-not-render-post-list' );

defined( 'TCB_RENDERING_SYMBOL' ) || define( 'TCB_RENDERING_SYMBOL', 'tcb-symbol-render' );

defined( 'TCB_POST_WRAPPER_CLASS' ) || define( 'TCB_POST_WRAPPER_CLASS', 'post-wrapper' );
defined( 'TCB_POST_LIST_LOCALIZE' ) || define( 'TCB_POST_LIST_LOCALIZE', 'tcb_post_list_localize' );

/* constants for the sub-element identifiers */
defined( 'TCB_POST_AUTHOR_IDENTIFIER' ) || define( 'TCB_POST_AUTHOR_IDENTIFIER', 'tcb-post-author' );
defined( 'TCB_POST_AUTHOR_BIO_IDENTIFIER' ) || define( 'TCB_POST_AUTHOR_BIO_IDENTIFIER', 'tcb-post-author-bio' );
defined( 'TCB_POST_AUTHOR_PICTURE_IDENTIFIER' ) || define( 'TCB_POST_AUTHOR_PICTURE_IDENTIFIER', 'tcb-post-author-picture' );
defined( 'TCB_POST_CATEGORIES_IDENTIFIER' ) || define( 'TCB_POST_CATEGORIES_IDENTIFIER', 'tcb-post-categories' );
defined( 'TCB_POST_COMMENTS_NUMBER_IDENTIFIER' ) || define( 'TCB_POST_COMMENTS_NUMBER_IDENTIFIER', 'tcb-post-comments-number' );
defined( 'TCB_POST_DATE' ) || define( 'TCB_POST_DATE', 'tcb-post-date' );
defined( 'TCB_POST_TAGS_IDENTIFIER' ) || define( 'TCB_POST_TAGS_IDENTIFIER', 'tcb-post-tags' );
defined( 'TCB_POST_THUMBNAIL_IDENTIFIER' ) || define( 'TCB_POST_THUMBNAIL_IDENTIFIER', 'tcb-post-thumbnail' );
defined( 'TCB_POST_TITLE_IDENTIFIER' ) || define( 'TCB_POST_TITLE_IDENTIFIER', 'tcb-post-title' );

/**
 * Class TCB_Post_List
 */
class TCB_Post_List {

	protected $css;
	protected $attr;
	protected $query;
	protected $article;
	protected $article_attr;
	protected $in_editor_render;

	/**
	 * TCB_Post_List constructor.
	 *
	 * @param array  $attr
	 * @param string $article
	 */
	public function __construct( $attr = [], $article = '' ) {
		$this->attr = array_merge( static::default_args(), $attr );

		$this->attr['element-name'] = $this->is_featured() ? __( 'Featured Content List', 'thrive-cb' ) : __( 'Post List', 'thrive-cb' );

		$this->article_attr = static::post_shortcode_data( $attr );
		$this->article      = unescape_invalid_shortcodes( $article ?: '' );

		$this->css = empty( $attr['css'] ) ? substr( uniqid( 'tve-u-', true ), 0, 17 ) : $attr['css'];

		$this->in_editor_render = TCB_Utils::in_editor_render( true );

		/* if the query attribute is not empty, store it */
		if ( ! empty( $this->attr['query'] ) ) {
			$this->init_query( $this->attr['query'] );
		}

		$this->hooks();
	}

	private function hooks() {
		add_filter( 'post_class', [ $this, 'article_class' ] );
		add_filter( 'tcb_post_attributes', [ $this, 'tcb_post_attributes' ], 10, 2 );
		/* Add the dynamic filters in the query vars for Blog List templates / cases where
		    the dynamic filters were not already declared */
	}

	/**
	 * Render a custom post list
	 *
	 * @return string
	 */
	public function render() {
		global $post;
		/* save a reference to the current global $post so we can restore it at the end */
		$current_post = $post;
		/*before we bring any posts, the total post count is 0*/
		$this->attr['total_post_count'] = 0;

		$posts_per_page   = (int) $this->query['posts_per_page'];
		$has_sticky_query = ! empty( $this->query['sticky'] );

		if ( $has_sticky_query ) {
			/*if sticky posts exist, prepare the query args, save the posts and increase the total post count*/
			$query_args_sticky = static::prepare_wp_query_args_sticky( $this->query );

			$sticky_query = new WP_Query( $query_args_sticky );
			$sticky_posts = $sticky_query->posts;

			$this->attr['total_sticky_count'] = $sticky_query->found_posts;
		} else {
			/*there are no sticky posts*/
			$sticky_posts                     = [];
			$this->attr['total_sticky_count'] = 0;
		}

		/*number of sticky posts from the current page*/
		$number_of_sticky_posts = count( $sticky_posts );

		if ( $number_of_sticky_posts > 0 ) {
			/*compute the number of 'normal' posts that needs to be added on a page with sticky posts*/
			$this->query['posts_per_page'] = $posts_per_page - $number_of_sticky_posts;
		} else if ( $number_of_sticky_posts === 0 && $has_sticky_query ) {
			/*the offset of 'normal' posts*/
			$this->query['offset'] = $posts_per_page * ( $this->query['paged'] - 1 ) + 1;
		}

		$post_query = static::prepare_wp_query_args( $this->query );

		$post_query = apply_filters( 'tcb_post_list_query_args', $post_query, $this );

		/* if pagination is active, we can't have an offset because they're not compatible */
		if ( $this->attr['pagination-type'] !== 'none' ) {
			$post_query['offset'] = 0;
		}

		$post_query = TCB_Post_List_Filter::filter( $post_query );

		$query = new WP_Query( $post_query );

		if ( ! empty( $post_query['dynamic_filter'] ) ) {
			$this->attr['dynamic_filter'] = json_encode( $post_query['dynamic_filter'] );

			unset( $post_query['dynamic_filter'] );
		} else {
			unset( $this->attr['dynamic_filter'] );
		}

		if ( $number_of_sticky_posts === $posts_per_page ) {
			//do nothing
			$all_posts = $sticky_posts;
		} else {
			$all_posts = array_merge( $sticky_posts, $query->posts );
		}

		$this->attr['total_post_count'] = $query->found_posts;
		$this->attr['posts_per_page']   = $posts_per_page;
		$this->query['posts_per_page']  = $posts_per_page;

		/* always start the paging at 1 since it's ajax-based */
		$post_query['paged'] = 1;

		$GLOBALS[ TCB_POST_LIST_LOCALIZE ][] = array(
			'identifier' => '[data-css="' . $this->css . '"]',
			'template'   => $this->css,
			'content'    => $this->article,
			'attr'       => $this->attr,
			'query'      => $this->query,
			'posts'      => array_map( function ( $post ) {
				return $post->ID;
			}, $all_posts ),
		);

		$class   = $this->class_attr( $this->attr );
		$content = '';

		if ( empty( $all_posts ) ) {
			/* even if there are no posts, we still display the template because in case we modify the query we will have something to sync */
			$content = $this->article_content();

			/* hide everything inside */
			$class .= ' empty-list';
		} else {
			foreach ( $all_posts as $post ) {
				$content .= $this->article_content();
			}
		}
		/* text to display for no posts */
		$this->attr['no_posts_text'] = isset( $this->query['no_posts_text'] ) ? $this->query['no_posts_text'] : '';

		$post = $current_post;

		/**
		 * This is first called in the Post Content shortcode ( @see TCB_Post_List_Content::get_content() ) inside the post list loop.
		 * It must be called again at this point in order to restore the post data to what it was before the custom loop
		 */
		setup_postdata( $post );

		if ( is_editor_page_raw( true ) ) {
			$shared_styles = '';
		} else {
			/* add shared styles only once for the whole post list and only in front */
			$shared_styles = tve_get_shared_styles( $content );

			/* don't add anything if it's just an empty wrapper */
			$stripped = strip_tags( $shared_styles );
			if ( empty( $stripped ) ) {
				$shared_styles = '';
			}
		}

		$content = static::prepare_carousel( $content, $this->attr );

		/* render the shared styles just before the post list wrapper */
		$content = $shared_styles . TCB_Post_List_Shortcodes::before_wrap(
				array(
					'content' => $content,
					'tag'     => 'div',
					'id'      => empty( $this->attr['id'] ) ? '' : $this->attr['id'],
					'class'   => $class,
				), $this->attr );

		return $content;
	}

	/**
	 * Parse post list shortcode data and make sure we have some defaults in case of empty data
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	public static function post_shortcode_data( $attr = [] ) {
		$data = [];

		if ( empty( $attr ) || ! is_array( $attr ) ) {
			return $data;
		}

		$article_data_prefix = 'article-';

		foreach ( $attr as $k => $v ) {
			if ( strpos( $k, $article_data_prefix ) === 0 ) {
				$data[ str_replace( $article_data_prefix, '', $k ) ] = $v;
			}
		}

		/* remove this attribute because we're not using it anymore */
		unset( $data['permalink'] );

		return $data;
	}

	/**
	 * Post list classes to be displayed depending on the attributes and screen
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public function class_attr( $attr = [] ) {
		/* tve-content-list identifies lists with dynamic content */
		$classes = [ TCB_POST_LIST_CLASS, 'tve-content-list' ];

		if ( $this->in_editor_render ) {
			$classes[] = 'tcb-compact-element';
			/*If the Post List is a Featured List, add the classes that will remove the duplicate and drag options*/
			if ( $this->is_featured() ) {
				$classes[] = 'tve_no_drag';
				$classes[] = 'tve_no_duplicate';
			}
		}

		if ( isset( $attr['type'] ) && $attr['type'] === 'masonry' ) {
			$classes[] = 'tve_post_grid_masonry';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Render article content.
	 *
	 * @return mixed|string
	 */
	public function article_content() {
		$article_attr = apply_filters( 'tcb_post_attributes', $this->article_attr, get_post() );

		$post_id = get_the_ID();

		$article_attr['data-id'] = $post_id;

		$content = empty( $this->article ) ? tcb_template( 'elements/post-list-article.php', null, true ) : $this->article;

		if ( static::has_read_more( $content ) ) {
			add_filter( 'the_content_more_link', [ 'TCB_Post_List_Content', 'the_content_more_link_filter' ] );
		}

		$content = TCB_Post_List_Shortcodes::do_shortcode( $content );

		if ( static::has_read_more( $content ) ) {
			remove_filter( 'the_content_more_link', [ 'TCB_Post_List_Content', 'the_content_more_link_filter' ] );
		}

		/* only in preview + front, add a div containing a link that covers the article */
		if ( ! $this->in_editor_render && isset( $this->attr['disabled-links'] ) && (int) $this->attr['disabled-links'] === 1 ) {
			$permalink = get_permalink( $post_id );

			/**
			 * Add an anchor inside the article cover so the post has link options on right click
			 * The <a> needs text for SEO reasons, but the text needs to be hidden ( this is done from CSS )
			 */
			$article_link = TCB_Utils::wrap_content( get_the_title(), 'a', '', 'tcb-article-cover-link', [ 'href' => $permalink ] );

			$article_cover = TCB_Utils::wrap_content( $article_link, 'div', '', 'tve-article-cover' );

			$content .= $article_cover;
		}

		return TCB_Post_List_Shortcodes::before_wrap( array(
			'content' => apply_filters( 'tcb_post_list_article_content', $content ),
			'tag'     => 'article',
			'id'      => 'post-' . $post_id,
			'class'   => static::post_class(),
			'attr'    => $article_attr + [ 'data-selector' => '.' . TCB_POST_WRAPPER_CLASS ],
		), $this->article_attr );
	}

	/**
	 * Check if the current content of the article already has a read more button/link
	 *
	 * @param $content
	 *
	 * @return bool
	 */
	public static function has_read_more( $content = '' ) {
		return stripos( $content, 'continue reading' ) !== false || stripos( $content, 'read-more' ) !== false || stripos( $content, 'read more' ) !== false;
	}

	/**
	 * Return an array with concentrated post information
	 *
	 * @param $order
	 *
	 * @return array
	 */
	public static function post_info( $order = 0 ) {
		$id = get_the_ID();

		if ( empty( $id ) ) {
			$post = [];
		} else {
			$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

			$post = array(
				'tcb_post_categories'       => do_shortcode( '[tcb_post_categories link=1]' ),
				'tcb_post_tags'             => do_shortcode( '[tcb_post_tags link=1]' ),
				/* localize the timestamp of the post's published date - in the editor, this is used by Moment JS. ! Do not wrap this in anything ! */
				'tcb_post_published_date'   => get_the_time( $date_format ),
				/* localize the timestamp of this post's modified date. Same as above, please don't wrap this */
				'tcb_post_modified_date'    => get_the_modified_date( $date_format ),
				'tcb_post_title'            => TCB_Post_List_Shortcodes::the_title(),
				'tcb_post_comments_number'  => TCB_Post_List_Shortcodes::comments_number(),
				'tcb_post_featured_image'   => TCB_Post_List_Shortcodes::post_thumbnail( [
					'type-url' => 'post_url',
					'size'     => 'full',
					'title'    => 'post_title',
				] ),
				'featured_image_sizes_data' => TCB_Post_List_Featured_Image::get_sizes( $id ),
				'tcb_post_author_picture'   => TCB_Post_List_Shortcodes::author_picture(),
				'tcb_post_author_bio'       => TCB_Post_List_Shortcodes::author_bio(),
				'tcb_post_author_name'      => do_shortcode( '[tcb_post_author_name link=1]' ),
				'tcb_post_content'          => TCB_Post_List_Shortcodes::the_content( [
					'size'      => 'content',
					'read_more' => '',
				] ),
				'tcb_post_type'             => get_post_type(),
				'className'                 => static::post_class(),
				'ID'                        => $id,
				'order'                     => empty( $order ) ? $id : $order,
				'tcb_post_excerpt'          => TCB_Post_List_Shortcodes::the_content( [
					'size'      => 'excerpt',
					'read_more' => '',
				] ),
				'tcb_post_words'            => TCB_Post_List_Shortcodes::the_content( [
					'size'      => 'words',
					'read_more' => '',
					'words'     => 500,
				] ),
				'author_picture'            => TCB_Post_List_Author_Image::author_avatar(),
				'tcb_post_author_role'      => TCB_Post_List_Shortcodes::author_role(),
				'tcb_post_the_permalink'    => get_permalink( $id ),
				'tcb_post_archive_link'     => static::get_post_type_archive_link( $id ),
				'tcb_post_author_link'      => static::get_author_posts_url( $id ),
				'tcb_post_date_link'        => static::get_day_link(),
				'tcb_post_comments_link'    => static::comments_link( $id ),
				'tcb_post_dynamic_css'      => TCB_Post_List_Shortcodes::tcb_get_article_dynamic_variables( $id ),
			);

			$custom_fields      = static::get_post_custom_fields( $id );
			$custom_field_types = [ 'data', 'link', 'image', 'number', 'countdown', 'audio', 'video', 'color' ];

			foreach ( $custom_field_types as $field ) {
				$post[ 'tcb_post_custom_fields_' . $field ] = $custom_fields[ $field ];
			}
		}

		/**
		 * filter post info that we're going to localize. maybe add some more data.
		 *
		 * @param array $post
		 * @param int   $id
		 *
		 * @return array
		 */
		return apply_filters( 'tcb_post_list_post_info', $post, $id );
	}

	/**
	 * Callback to add the post class to the article.
	 *
	 * @return string
	 */
	public static function post_class() {
		return implode( ' ', get_post_class() );
	}

	/**
	 * Add attributes to the post wrapper
	 *
	 * @param array   $attributes
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	public static function tcb_post_attributes( $attributes, $post ) {
		if ( TCB_Editor()->is_inner_frame() && $post ) {
			$attributes['data-id'] = $post->ID;
		}

		return $attributes;
	}

	/**
	 * Add custom classes to the article wrapper
	 *
	 * @param array $post_class
	 *
	 * @return array
	 */
	public function article_class( $post_class = [] ) {
		$post_class[] = TCB_POST_WRAPPER_CLASS;
		$post_class[] = THRIVE_WRAPPER_CLASS;
		$post_class[] = 'thrive-animated-item';

		return $post_class;
	}

	/**
	 * Localize the post list in the main frame and in the inner frame.
	 */
	public static function wp_print_footer_scripts() {
		/* when we're on the frontend, localize these for infinite load / load more pagination */
		if ( ! TCB_Editor()->is_inner_frame() && ! is_editor_page_raw() ) {
			foreach ( $GLOBALS[ TCB_POST_LIST_LOCALIZE ] as $post_list ) {
// phpcs:disable
				echo TCB_Utils::wrap_content(
					str_replace( [ '[', ']' ], array( '{({', '})}' ), $post_list['content'] ),
					'script',
					'',
					'tcb-post-list-template',
					[
						'type'            => 'text/template',
						'data-identifier' => $post_list['template'],
					]
				);
				//phpcs:enable
			}

			$posts_localize = [];

			if ( ! empty( $GLOBALS[ TCB_POST_LIST_LOCALIZE ] ) ) {
				/* remove the post content before localizing the posts */
				$posts_localize = array_map(
					function ( $item ) {
						unset( $item['content'] );

						return $item;
					}, $GLOBALS[ TCB_POST_LIST_LOCALIZE ] );
			}

			echo TCB_Utils::wrap_content( "var tcb_current_post_lists=JSON.parse('" . addslashes( json_encode( $posts_localize ) ) . "'); var tcb_post_lists=tcb_post_lists?[...tcb_post_lists,...tcb_current_post_lists]:tcb_current_post_lists;", 'script', '', '', [ 'type' => 'text/javascript' ] ); // phpcs:ignore
		}
	}

	/**
	 * Prepare wp_query arguments
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function prepare_wp_query_args( $args = [] ) {
		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		/* allow only our query params to be used */
		$query_args = array_intersect_key( $args, [
			'post_type'            => '',
			's'                    => '',
			'year'                 => '',
			'monthnum'             => '',
			'day'                  => '',
			'numberposts'          => '',
			'orderby'              => '',
			'order'                => '',
			'offset'               => '',
			'posts_per_page'       => '',
			'tag__in'              => '',
			'category__in'         => '',
			'author__in'           => '',
			'paged'                => '',
			'tag'                  => '',
			'page'                 => '',
			'pagename'             => '',
			'author_name'          => '',
			'category_name'        => '',
			'exclude_current_post' => '',
			'dynamic_filter'       => '',
			'post__not_in'         => '',
		] );

		/* nothing for now */
		$defaults = [];

		$args = array_merge( $defaults, $args );

		if ( TCB_Utils::is_rest() && isset( $args['queried_object'] ) ) {
			$queried_object = (object) $args['queried_object'];
		} else {
			$queried_object = get_queried_object();
		}

		/* in a related query, we get info from the current post or archive type and we display posts based on that */
		if ( isset( $args['filter'] ) && $args['filter'] === 'related' ) {

			/* on author page we get posts from the same author */
			if ( ! empty( $queried_object->data->ID ) ) {

				/* get posts from the same author we're on */
				$query_args['author__in'] = [ $queried_object->data->ID ];

			} elseif ( ! empty( $queried_object->ID ) ) {

				/* on singular page we get the terms that the user asked and we get posts based on that */
				$query_args['tax_query'] = [ 'relation' => 'OR' ];
				/* get posts that have at least one taxonomy term as the post we're on */

				if ( ! empty( $args['related'] ) && is_array( $args['related'] ) ) {

					foreach ( $args['related'] as $taxonomy ) {

						switch ( $taxonomy ) {

							case 'author':
								$query_args['author'] = $queried_object->post_author;
								break;

							case 'post_format':
								$format = get_post_format( $queried_object->ID );

								if ( $format ) {
									$query_args['tax_query'][] = [
										'taxonomy' => 'post_format',
										'field'    => 'slug',
										'terms'    => [ 'post-format-' . $format ],
									];
								} else {
									/*
									 * If the post format is not set, the post is actually a standard post.
									 * In order to take all the standard posts, we query the db for all the posts that are not included in the other post formats.
									 * So, first we get the post formats supported by this theme, and then we generate a taxonomy query that filters them out.
									 */
									$post_formats = TCB_Utils::get_supported_post_formats();

									if ( ! empty( $post_formats ) ) {
										$terms = [];

										foreach ( $post_formats as $post_format ) {
											$terms[] = 'post-format-' . $post_format;
										}

										$query_args['tax_query'][] = [
											'taxonomy' => 'post_format',
											'field'    => 'slug',
											'terms'    => $terms,
											'operator' => 'NOT IN',
										];
									}
								}
								break;

							default:
								$post_terms = wp_get_post_terms( $queried_object->ID, $taxonomy, [ 'fields' => 'ids' ] );
								if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
									$query_args['tax_query'][] = array(
										'taxonomy' => $taxonomy,
										'field'    => 'term_id',
										'terms'    => array_map( function ( $term ) {
											return $term instanceof WP_Term ? $term->term_id : $term;
										}, $post_terms ),
									);
								}
						}
					}
				}

				$query_args['post_type'] = get_post_type( $queried_object->ID );

			} elseif ( ! empty( $queried_object->taxonomy ) ) {

				/* on taxonomy page: tag, category... we display posts from the same taxonomy. */
				/* get posts based on the taxonomy we're on */
				$query_args['tax_query'] = [
					[
						'taxonomy' => $queried_object->taxonomy,
						'field'    => 'id',
						'terms'    => $queried_object->term_id,
					],
				];

				$taxonomy                = get_taxonomy( $queried_object->taxonomy );
				$query_args['post_type'] = $taxonomy->object_type;
			}

			/* if we have a custom query, we check the rules and display posts based on that */
		} elseif ( isset( $args['rules'] ) && is_array( $args['rules'] ) ) {

			$query_args['tax_query']      = [];
			$query_args['author__in']     = [];
			$query_args['author__not_in'] = [];
			$query_args['post__in']       = [];

			/* we always want to exclude sticky posts */
			$query_args['post__not_in'] = array_merge( get_option( 'sticky_posts', [] ), isset( $query_args['post__not_in'] ) ? $query_args['post__not_in'] : [] );

			foreach ( $args['rules'] as $rule ) {

				if ( empty( $rule['terms'] ) ) {
					continue;
				}

				if ( ! isset( $rule['taxonomy'] ) ) {
					$rule['taxonomy'] = 'post';
				}

				if ( 'author' === $rule['taxonomy'] ) {
					/* operator can be IN or NOT IN */
					$arg                = 'author__' . strtolower( str_replace( ' ', '_', $rule['operator'] ) );
					$query_args[ $arg ] = array_values( array_merge( $query_args[ $arg ], $rule['terms'] ) );
				} elseif ( post_type_exists( $rule['taxonomy'] ) ) {
					$arg = 'IN' === $rule['operator'] ? 'post__in' : 'post__not_in';
					/* for posts type, we don't use tax_query, we use include and exclude */
					$query_args[ $arg ] = array_values( array_merge( $query_args[ $arg ], $rule['terms'] ) );
				} else {
					/* SUPP-8749 Exclude the sub-categories when the operator is AND because the query will return no posts*/
					if ( $rule['operator'] === 'AND' ) {
						$rule['include_children'] = false;
					}

					$query_args['tax_query'][] = $rule;
				}
			}
		}

		/* inherit was added just so it will work with attachments also  */
		$query_args['post_status'] = [ 'publish', 'inherit' ];

		/* the human mind will read much easier indexes that start from 1 and not from 0 */
		if ( isset( $query_args['offset'] ) && $query_args['offset'] > 0 ) {
			$query_args['offset'] = (int) $query_args['offset'] - 1;
		}

		/* exclude current post when on singular */
		if ( ! empty( $queried_object->ID ) && ! empty( $query_args['exclude_current_post'] ) ) {
			$query_args['post__not_in'][] = $queried_object->ID;
		}

		/*The sticky posts need to be excluded from the normal posts*/
		if ( ! empty( $args['sticky'] ) ) {
			foreach ( $args['sticky'] as $sticky_rule ) {
				if ( 'author' === $sticky_rule['taxonomy'] ) {
					/* operator can be IN or NOT IN */
					$query_args['author__not_in'] = array_values( array_merge( $query_args['author__not_in'], $sticky_rule['terms'] ) );
				} elseif ( post_type_exists( $sticky_rule['taxonomy'] ) ) {
					/* for posts type, we don't use tax_query, we use include and exclude */
					$query_args['post__not_in'] = array_values( array_merge( $query_args['post__not_in'], $sticky_rule['terms'] ) );
				} else {
					$sticky_rule['operator']   = 'NOT IN';
					$query_args['tax_query'][] = $sticky_rule;
				}
			}
		}

		/**
		 * Filter the excluded posts
		 *
		 * @param array - ids of excluded posts
		 *
		 * @return array - modified array of excluded post ids
		 */
		$query_args['post__not_in'] = apply_filters( 'tcb_post_list_excluded_post_ids', empty( $query_args['post__not_in'] ) ? [] : $query_args['post__not_in'] );

		return $query_args;
	}

	/**
	 * Prepare the wp_query arguments for sticky posts(posts that are always displayed at the top of the Post List)
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function prepare_wp_query_args_sticky( $args = [] ) {
		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		/* allow only our query params to be used */
		$query_args = array_intersect_key( $args, [
			'post_type'      => '',
			'offset'         => '',
			'posts_per_page' => '',
			'paged'          => '',
		] );

		/* check if we're in a rest request or not */
		$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;

		if ( $is_rest && isset( $args['queried_object'] ) ) {
			$queried_object = (object) $args['queried_object'];
		} else {
			$queried_object = get_queried_object();
		}

		if ( isset( $args['rules'] ) && is_array( $args['rules'] ) ) {

			$query_args['tax_query']  = [];
			$query_args['author__in'] = [];
			$query_args['post__in']   = [];

			if ( ! empty( $args['sticky'] ) ) {
				/*if sticky posts exist, build the $quety_args based on the sticky rules */
				foreach ( $args['sticky'] as $rule_sticky ) {

					if ( empty( $rule_sticky['terms'] ) ) {
						continue;
					}
					/*initially, the operator is ALWAYS, but it should be IN in order to include the posts*/
					$rule_sticky['operator'] = str_replace( 'ALWAYS ', '', $rule_sticky['operator'] );
					/*the posts are added differently dependyng on the rule type(author, post type and taxonomy)*/
					if ( 'author' === $rule_sticky['taxonomy'] ) {
						/* operator is always IN*/
						$query_args['author__in'] = array_values( array_merge( $query_args['author__in'], $rule_sticky['terms'] ) );
					} elseif ( post_type_exists( $rule_sticky['taxonomy'] ) ) {
						/* for posts type, we don't use tax_query, we use include and exclude */
						$query_args['post__in'] = array_values( array_merge( $query_args['post__in'], $rule_sticky['terms'] ) );
					} else {
						$query_args['tax_query'][] = $rule_sticky;
					}
				}
			}
		}

		/* inherit was added just so it will work with attachments also  */
		$query_args['post_status'] = [ 'publish', 'inherit' ];

		/* the human mind will read much easier indexes that start from 1 and not from 0 */
		if ( isset( $query_args['offset'] ) && $query_args['offset'] > 0 ) {
			$query_args['offset'] = (int) $query_args['offset'] - 1;
		}

		/* exclude current post when on singular */
		if ( ! empty( $queried_object->ID ) && ! empty( $query_args['exclude_current_post'] ) ) {
			$query_args['post__not_in'][] = $queried_object->ID;
		}

		return $query_args;
	}

	/**
	 * Return the Post List specific elements label
	 *
	 * @return string
	 */
	public static function elements_group_label() {
		return __( 'Article Components', 'thrive-cb' );
	}

	/**
	 * Register REST Routes for the Post List
	 */
	public static function rest_api_init() {
		require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-rest.php';
	}

	/**
	 * Default args
	 *
	 * @return array
	 */
	public static function default_args() {
		return array(
			'query'              => str_replace( '"', '\'', json_encode( static::get_default_query() ) ),
			'type'               => 'grid',
			'columns-d'          => 3,
			'columns-t'          => 2,
			'columns-m'          => 1,
			'vertical-space-d'   => 30,
			'horizontal-space-d' => 30,
			'ct'                 => 'post_list--1',
			'ct-name'            => 'Default Post List',
			'tcb-elem-type'      => 'post_list',
			'pagination-type'    => 'none',
			'pages_near_current' => '2',
			'dynamic_filter'     => str_replace( '"', '\'', json_encode( static::get_default_filters() ) ),
		);
	}

	/**
	 * Get the default query of the post list.
	 *
	 * @return array
	 */
	public static function get_default_query() {
		return array(
			'filter'               => 'custom',
			'related'              => [],
			'post_type'            => 'post',
			'orderby'              => 'date',
			'order'                => 'DESC',
			'posts_per_page'       => '6',
			'offset'               => '1',
			'no_posts_text'        => 'There are no posts to display.',
			'exclude_current_post' => [ '1' ],
			'rules'                => [],
			'dynamic_filter'       => static::get_default_filters(),
		);
	}

	/**
	 * Get the default filters for the Post List, used with Post List Filter element
	 *
	 * @return string[]
	 */
	public static function get_default_filters() {
		return [
			'category' => 'category',
			'tag'      => 'tag',
			'author'   => 'author',
			'search'   => 'search',
		];
	}

	/**
	 * Check if a post list is rendering right now.
	 *
	 * @return bool
	 */
	public static function is_outside_post_list_render() {
		return empty( $GLOBALS[ TCB_DO_NOT_RENDER_POST_LIST ] );
	}

	/**
	 * Mark that we started rendering a post list. This is done in order to avoid situations like rendering another post list inside an existing post list.
	 */
	public static function enter_post_list_render() {
		$GLOBALS[ TCB_DO_NOT_RENDER_POST_LIST ] = true;
	}

	/**
	 * Mark that we finished rendering a post list.
	 */
	public static function exit_post_list_render() {
		$GLOBALS[ TCB_DO_NOT_RENDER_POST_LIST ] = false;
	}

	/**
	 * Post type archive link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_post_type_archive_link( $id = 0 ) {
		$link = get_post_type_archive_link( get_post_type( $id ) );
		if ( empty( $link ) ) {
			$link = '#';
		}

		return $link;
	}

	/**
	 * Author archive link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_author_posts_url( $id = 0 ) {
		$post = get_post( $id );

		if ( $post !== null ) {
			$link = get_author_posts_url( $post->post_author );
		}

		if ( empty( $link ) ) {
			$link = '#';
		}

		return rtrim( $link, '/' );
	}

	/**
	 * Date link
	 *
	 *
	 * @return string
	 */
	public static function get_day_link() {
		$link = get_day_link( get_the_date( 'Y' ), get_the_date( 'm' ), get_the_date( 'd' ) );
		if ( empty( $link ) ) {
			$link = '#';
		}

		return rtrim( $link, '/' );
	}

	/**
	 * Comments link.
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function comments_link( $id = 0 ) {
		$post = get_post( $id );

		return get_permalink( $post ) . '#comments';
	}

	/**
	 * Init the Query
	 *
	 * @param $attr_query
	 */
	public function init_query( $attr_query ) {
		/* replace single quotes with double quotes */
		$decoded_string = str_replace( "'", '"', html_entity_decode( $attr_query, ENT_QUOTES ) );

		/* replace newlines and tabs */
		$decoded_string = preg_replace( '/[\r\n]+/', ' ', $decoded_string );

		$query = json_decode( $decoded_string, true );

		$this->query = array_merge(
		/* default values for query */
			[ 'paged' => 1 ],
			is_array( $query ) ? $query : []
		);

		/* this will be localized for each page */
		unset( $this->query['queried_object'] );

		/* If the Post List has a Featured List attached  we parse all Posts Lists from the page */
		if ( ! empty( $this->attr['featured-list'] ) ) {
			$feature_list_identifier = '[data-css="' . $this->attr['featured-list'] . '"]';

			foreach ( $GLOBALS[ TCB_POST_LIST_LOCALIZE ] as $post_list ) {
				/* If we find a pair of Post List and Featured List we add the posts from Featured List as excluded posts from Post List */
				if ( $post_list['identifier'] === $feature_list_identifier ) {
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
	 * Checks if the Post List is Featured
	 *
	 * @return bool
	 */
	public function is_featured() {
		return ! empty( $this->attr['class'] ) && strpos( $this->attr['class'], 'tcb-featured-list' ) !== false;
	}

	/**
	 * Replaces element type with post_list if the type is post_list_featured
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public static function featured_type_replace( $type ) {
		if ( $type === 'post_list_featured' ) {
			$type = 'post_list';
		}

		return $type;
	}

	/**
	 * Replaces element tag with post_list_featured if the type is post_list_featured
	 *
	 * @param $tag
	 * @param $type
	 *
	 * @return string
	 */
	public static function post_list_tag_replace( $tag, $type ) {
		if ( $type === 'post_list_featured' ) {
			$tag = $type;
		}

		return $tag;
	}

	/**
	 * Get and filter the custom fields of the post
	 *
	 * @param $id
	 *
	 * @return array
	 */

	public static function get_post_custom_fields( $id ) {
		$custom_keys = array_filter( (array) get_post_custom_keys( $id ), function ( $meta ) {
			return is_protected_meta( $meta ) === false;
		} );

		$acf_data = tcb_custom_fields_api()->get_all_external_postlist_fields( $id );
		$result   = [ 'data' => [], 'link' => [] ];
		$custom   = get_post_custom( $id );

		//Set Text and Links
		foreach ( $custom_keys as $val ) {
			$result[ filter_var( $custom[ $val ][0], FILTER_VALIDATE_URL ) ? 'link' : 'data' ][] = array( 'key' => $val, 'value' => get_post_meta( $id, $val, true ) );
		}

		if ( ! empty( $acf_data['text'] ) ) {
			foreach ( $acf_data['text'] as $val ) {
				$result['data'][] = [ 'key' => $val['name'], 'value' => $val['value'], 'label' => $val['label'] ];
			}
		}
		if ( ! empty( $acf_data['link'] ) ) {
			foreach ( $acf_data['link'] as $val ) {
				$result['link'][] = [ 'key' => $val['name'], 'value' => $val['value'], 'label' => $val['label'] ];
			}
		}

		$result['color'] = ! empty( $acf_data['color'] ) ? tcb_custom_fields_api()->prepare_custom_fields_colors( 0, $acf_data['color'] ) : [];

		//Format the links to be ready to go
		$items = [];
		if ( ! empty( $result['link'] ) ) {
			foreach ( $result['link'] as $key => $val ) {
				$items[ $val['key'] ] = array(
					'name'  => $val['key'],
					'label' => empty( $val['label'] ) ? $val['key'] : $val['label'],
					'url'   => '[tcb_post_custom_field]',
					'show'  => true,
				);
			}
			$result['link'] = $items;
		}

		$extraCustomFields = [ 'image', 'number', 'countdown', 'audio', 'video' ];
		//Set values for other cf types
		foreach ( $extraCustomFields as $field ) {
			$result[ $field ] = [];
			if ( ! empty( $acf_data[ $field ] ) ) {
				foreach ( $acf_data[ $field ] as $val ) {
					$result[ $field ][ $val['name'] ] = $val;
				}
			}
		}

		return $result;
	}

	/**
	 * Get the specific value of an attribute
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_attr( $key = '' ) {
		if ( ! empty( $key ) && isset( $this->attr[ $key ] ) ) {
			return $this->attr[ $key ];
		}

		return $this->attr;
	}

	/**
	 * Dataset attributes that have to be displayed on the frontend because they're used there ( normally, 'data-' is removed on front, so this acts as a whitelist )
	 *
	 * @var array
	 */
	public static $front_attr = [
		'tcb-events',
		'css',
		'masonry',
		'type',
		'hide', //used for TOC headings
		'pagination-type',
		'no_posts_text',
		'layout',
		'total_post_count',
		'total_sticky_count',
		'pages_near_current',
		'disabled-links',
		'featured-list',
		'template-id',
		'styled-scrollbar',
		'dynamic_filter',
	];

	/**
	 * Dataset attributes that don't have to persist anywhere ( the data is used only during construct() )
	 *
	 * @var array
	 */
	public static $ignored_attr = [
		'article-tcb-events',
		'article-class',
		'article-permalink',
	];

	public static function prepare_carousel( $content, &$attr ) {
		/* Re-wrap the content in the carousel wrapper & add the carousel settings attribute */
		if ( $attr['type'] === 'carousel' && isset( $attr['carousel-settings'] ) ) {
			$carousel_setting['data-carousel-settings'] = $attr['carousel-settings'];
			$content                                    = TCB_Utils::wrap_content( $content, 'div', '', 'tcb-carousel-container', $carousel_setting );

			unset( $attr['carousel-settings'] );

			if ( ! is_editor_page_raw( true ) ) {
				/* no need inside the editor */
				if ( empty( $attr['style'] ) ) {
					$attr['style'] = '';
				}

				/* add styles for cwv elements */
				foreach ( [ 'm', 't', 'd' ] as $media ) {
					if ( ! empty( $attr["cwv-height-$media"] ) ) {
						$attr['style'] .= "--tcb-cwv-height-$media:" . $attr["cwv-height-$media"] . 'px;';
					}

					if ( ! empty( $attr["cwv-slides-$media"] ) ) {
						$attr['style'] .= "--tcb-cwv-slides-$media:" . $attr["cwv-slides-$media"] . ';';
					}
				}
			}
		}

		return $content;
	}
}
