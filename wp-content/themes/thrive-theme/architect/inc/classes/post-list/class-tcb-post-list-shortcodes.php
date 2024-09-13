<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_Shortcodes
 */
class TCB_Post_List_Shortcodes {

	private static $_instance = null;

	private $execution_stack = [];

	public static $dynamic_shortcodes = [
		'tcb_post_content'               => 'the_content',
		'tcb_post_title'                 => 'the_title',
		'tcb_post_featured_image'        => 'post_thumbnail',
		'tcb_post_author_picture'        => 'author_picture',
		'tcb_post_list'                  => 'post_list',
		'tcb_post_published_date'        => 'post_date',
		'tcb_post_tags'                  => 'the_tags',
		'tcb_post_categories'            => 'the_category',
		'tcb_post_author_name'           => 'the_author',
		'tcb_post_author_bio'            => 'author_bio',
		'tcb_post_comments_number'       => 'comments_number',
		'tcb_post_author_role'           => 'author_role',
		'tcb_featured_image_url'         => 'the_post_thumbnail_url',
		'tcb_author_image_url'           => 'author_image_url',
		'tcb_user_image_url'             => 'user_image_url',
		'tcb_the_id'                     => 'the_id',
		'tcb_post_list_dynamic_style'    => 'tcb_post_list_dynamic_style',
		'tcb_pagination'                 => 'pagination',
		'tcb_post_custom_field'          => 'custom_field',
		'tcb_post_custom_external'       => 'externals',
		'tcb_video_cover_featured_image' => 'video_cover_featured_image',
		'thrive_author_url'              => 'author_link_shortcode',
	];

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );

		add_action( 'wp_print_footer_scripts', [ 'TCB_Post_List', 'wp_print_footer_scripts' ] );

		add_filter( 'tcb_content_allowed_shortcodes', [ $this, 'tcb_content_allowed_shortcodes' ] );

		add_filter( 'tcb_inline_shortcodes', [ $this, 'tcb_inline_shortcodes' ], 11 );

		add_filter( 'aioseo_conflicting_shortcodes', [ $this, 'remove_shortcode_conflicts' ] );
	}

	/**
	 * Dont allow AllinOne SEO to do shortcodes while getting description for meta tags
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function remove_shortcode_conflicts( $shortcodes ) {
		return array_merge( $shortcodes, array_keys( TCB_Post_List_Shortcodes::$dynamic_shortcodes ) );
	}

	/**
	 * Add more shortcodes to the existing array of inline shortcodes.
	 *
	 * Add some inline shortcodes that are available on the Pagination Label element, and hidden elsewhere.
	 * Also add inline shortcodes available only in Post List edit mode.
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function tcb_inline_shortcodes( $shortcodes ) {
		$shortcodes = array_merge_recursive( TCB_Utils::get_pagination_inline_shortcodes(), $shortcodes );
		$shortcodes = array_merge_recursive( TCB_Utils::get_post_list_inline_shortcodes(), $shortcodes );

		return $shortcodes;

	}

	/**
	 * Return shortcode execution stack
	 *
	 * @return array
	 */
	public function get_execution_stack() {
		return $this->execution_stack;
	}

	/**
	 * We need to add our shortcodes to this array in order for them to be processed in the editor.
	 * If we're on the frontend, we don't have to do this.
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function tcb_content_allowed_shortcodes( $shortcodes ) {
		if ( is_editor_page_raw( true ) ) {
			$shortcodes = array_merge( $shortcodes, array_keys( TCB_Post_List_Shortcodes::$dynamic_shortcodes ) );
			$shortcodes = array_merge( $shortcodes, [ 'tcb_postlist_custom_image' ] );
		}

		return $shortcodes;
	}

	/**
	 * TCB_Post_List_Shortcodes instance
	 *
	 * @return null|TCB_Post_List_Shortcodes
	 */
	public static function instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * Add all shortcodes and their callbacks
	 */
	public function init() {
		foreach ( static::$dynamic_shortcodes as $shortcode => $func ) {
			add_shortcode(
				$shortcode,
				function ( $attr, $content, $tag ) {
					$func   = TCB_Post_List_Shortcodes::$dynamic_shortcodes[ $tag ];
					$output = '';

					if ( method_exists( __CLASS__, $func ) ) {
						$attr = TCB_Post_List_Shortcodes::parse_attr( $attr, $tag );

						TCB_Post_List_Shortcodes()->execution_stack[] = [
							'shortcode' => $tag,
							'attr'      => $attr,
						];

						$output = TCB_Post_List_Shortcodes::$func( $attr, $content, $tag );

						/**
						 * Filter applied in case there are other implementations for this shortcode tag.
						 * !!! Note: when adding a filter for this, the first param has to be $output ( since it's what the filter returns ).
						 *
						 * Usage example - adding a filter for the [tcb_post_title] shortcode:
						 *
						 * add_filter( 'tcb_render_shortcode_tcb_post_title', function ( $output, $attr, $content ) {
						 *   ||| process $output, use $attr for logic, etc |||
						 *   return $output;
						 *  }, 10, 3 )
						 *
						 * @param string $output
						 * @param array  $attr
						 * @param string $content - content between the shortcode opening and closing tags ( like [tag] content [/tag] )
						 */
						$output = apply_filters( 'tcb_render_shortcode_' . $tag, $output, $attr, $content );

						/**
						 * If a static link is detected in config, we need to wrap $output in that link
						 * ::is_inline() check seems to address backwards compatibility issues from the time where not all post list shortcodes were inline texts.
						 */
						if ( TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
							$output = TVD_Global_Shortcodes::maybe_link_wrap( $output, $attr );
						}

						array_pop( TCB_Post_List_Shortcodes()->execution_stack );
					}

					return $output;
				}
			);
		}

		$GLOBALS[ TCB_POST_LIST_LOCALIZE ] = [];
	}

	/**
	 * @param array $wrap_args
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function before_wrap( $wrap_args = [], $attr = [] ) {

		/* attributes that have to be present also on front */
		$front_attr = TCB_Post_List::$front_attr;

		/* attributes that were used only for initializing stuff during construct(), we don't need these anymore */
		$ignored_attr = TCB_Post_List::$ignored_attr;

		$wrap_args = array_merge(
			array(
				'content' => '',
				'tag'     => 'div',
				'id'      => '',
				'class'   => '',
				'attr'    => [],
			),
			$wrap_args
		);
		/* extra classes that are sent through data attr */
		$wrap_args['class'] .= ' ' . ( strpos( $wrap_args['class'], THRIVE_WRAPPER_CLASS ) === false ? THRIVE_WRAPPER_CLASS : '' ) . ( empty( $attr['class'] ) ? '' : ' ' . $attr['class'] );

		if ( ! empty( $attr['style'] ) ) {
			$wrap_args['attr']['style'] = $attr['style'];
			unset( $attr['style'] );
		}

		/* attributes that come directly from the shortcode */
		foreach ( $attr as $key => $value ) {
			if (
				! in_array( $key, $ignored_attr, true ) && /* if this attribute is 'ignored', don't do anything */
				(
					TCB_Utils::in_editor_render( true ) || /* in the editor, always add the attributes */
					in_array( $key, $front_attr, true ) /* if this attr has to be added on the frontend, add it */
				)
			) {
				$wrap_args['attr'][ 'data-' . $key ] = $value;
				unset( $wrap_args['attr'][ $key ] );
			}
		}

		/* during ajax we can't render shortcodes, so we add the shortcode tag and class so we can fix them in JS */
		if ( wp_doing_ajax() ) {
			$last_shortcode = end( TCB_Post_List_Shortcodes()->execution_stack );

			$wrap_args['attr']['data-shortcode'] = $last_shortcode['shortcode'];

			$wrap_args['class'] .= ' ' . TCB_SHORTCODE_CLASS;
		}

		return call_user_func_array( [ 'TCB_Utils', 'wrap_content' ], $wrap_args );
	}

	/**
	 * Render the post list element.
	 *
	 * @param array  $attr
	 * @param string $article_content
	 *
	 * @return string
	 */
	public static function post_list( $attr = [], $article_content = '' ) {
		/* don't render a post list inside another post list ( in 'the_content' shortcode ). */
		if ( TCB_Post_List::is_outside_post_list_render() ) {
			TCB_Post_List::enter_post_list_render();

			$post_list = new TCB_Post_List( $attr, $article_content );

			$content = $post_list->render();

			/* parse the animations that are inside the post list */
			tve_parse_events( $content );

			TCB_Post_List::exit_post_list_render();
		} else {
			/* if the flag is not empty, it means we're already inside a post list shortcode */
			$content = '';
		}

		return $content;
	}

	/**
	 * Shortcode callback for the pagination element.
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function pagination( $attr = [], $content = '' ) {
		$pagination = '';

		/* only render if we're outside the post list render */
		if ( TCB_Post_List::is_outside_post_list_render() ) {
			/* if no type is set, use the 'no pagination' type by default */
			$type = empty( $attr['data-type'] ) ? TCB_Pagination::NONE : $attr['data-type'];

			/* compatibility with the old load more setting */
			if ( $type === 'button' ) {
				$type = TCB_Pagination::LOAD_MORE;
			}

			/* call the render function for this pagination shortcode */
			$pagination = tcb_pagination( $type, $attr )->render( $content );
		}

		return $pagination;
	}

	/**
	 * The content shortcode.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function the_content( $attr = [] ) {
		return TCB_Post_List_Content::get_content( $attr );
	}

	/**
	 * Callback for the Post Author Bio shortcode.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function author_bio( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/author-bio.php', $attr, true );

		/**
		 * Filter the author bio content.
		 *
		 * @param string $content
		 * @param array  $attr shortcode attributes
		 */
		$content = apply_filters( 'tcb_author_bio', $content, $attr );

		if ( ! static::is_inline( $attr ) ) {
			$tag     = empty( $attr['tag'] ) ? 'div' : $attr['tag'];
			$classes = [ TCB_POST_AUTHOR_BIO_IDENTIFIER, TCB_SHORTCODE_CLASS ];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		$content = nl2br( $content );

		return $content;
	}

	/**
	 * Callback for the custom fields shortcode.
	 *
	 * @param $attr
	 *
	 * @param $replacement
	 *
	 * @return string
	 */
	public static function custom_field( $attr = [], $replacement = '' ) {

		$content = get_post_meta( get_the_ID(), $attr['id'], true );
		$content = isset( $content ) ? $content : $replacement;

		$external_data = tcb_custom_fields_api()->get_all_external_postlist_fields( get_the_ID() );

		if ( empty( $content ) ) {
			$type = empty( $attr['do_not_wrap'] ) ? 'text' : 'link';
			if ( ! empty( $external_data ) && ! empty( $external_data[ $type ] ) && ! empty( $external_data[ $type ][ $attr['id'] ] ) ) {
				$content = $external_data[ $type ][ $attr['id'] ]['value'];
			} else {
				$content = $replacement;
			}
		}

		if ( is_editor_page_raw( true ) && empty( $content ) ) {
			return '[Custom Fields] ' . $attr['id'];
		}

		if ( empty( $attr['do_not_wrap'] ) ) {
			$content = sprintf( '<span>%s</span>', $content );
		}

		return $content;
	}

	/**
	 * Callback for the external custom fields shortcode.
	 *
	 * @param $attr
	 *
	 * @param $replacement
	 *
	 * @param $is_text
	 *
	 * @return string
	 */
	public static function externals( $attr = [] ) {

		if ( ! isset( $attr['data-field-type'] ) ) {
			return '';
		}
		//Safety net when we are outside postlist and a cf wants to be rendered in relation to page
		if ( TCB_Post_List::is_outside_post_list_render() && $attr['data-field-type'] !== 'background' ) {
			$shortcode_data = array_map(
				function ( $k, $v ) {
					return $k . "=" . htmlspecialchars( $v ) . "";
				},
				array_keys( $attr ), $attr
			);

			return '[' . 'tcb_post_custom_external' . ' ' . implode( ' ', $shortcode_data ) . ']';
		}

		$attr['in_postlist'] = 1;
		$type                = 'tcb_post_custom_fields_' . ( $attr['data-field-type'] === 'background' ? 'image' : $attr['data-field-type'] );

		$post_id = get_the_ID();

		if ( empty( $post_id ) ) {
			$content = [];
		} else {
			$custom_fields = TCB_Post_List::get_post_custom_fields( $post_id );

			$custom_field_types = [ 'data', 'link', 'image', 'number', 'countdown', 'audio', 'video' ];
			foreach ( $custom_field_types as $field ) {
				$content[ 'tcb_post_custom_fields_' . $field ] = $custom_fields[ $field ];
			}
		}

		if ( empty( $content ) || empty( $content[ $type ] ) || empty( $content[ $type ][ $attr['data-id'] ] ) ) {
			$content = [];
		} else {
			$content = $content[ $type ][ $attr['data-id'] ];
		}

		$content = tcb_custom_fields_api()->render_custom_fields( $attr, $content );

		return $content;
	}

	/**
	 * Callback for the Post Author Name shortcode.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_author( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/author-name.php', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$classes = [ TCB_POST_AUTHOR_IDENTIFIER, TCB_SHORTCODE_CLASS ];

			$tag = empty( $attr['tag'] ) ? 'div' : $attr['tag'];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	/**
	 * Author Picture.
	 * Only used for backwards compatibility purposes, the author image element now inherits the Image element and is saved statically.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function author_picture( $attr = [] ) {
		return tcb_template( 'post-list-sub-elements/author-image.php', $attr, true );
	}

	/**
	 * Callback for the Post Categories shortcode.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_category( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/post-categories.php', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$tag     = empty( $attr['tag'] ) ? 'span' : $attr['tag'];
			$classes = [ TCB_POST_CATEGORIES_IDENTIFIER, TCB_SHORTCODE_CLASS ];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	/**
	 * Callback for the Post Comments Number shortcode.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function comments_number( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/comments-number.php', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$tag     = empty( $attr['tag'] ) ? 'div' : $attr['tag'];
			$classes = [ TCB_POST_COMMENTS_NUMBER_IDENTIFIER, TCB_SHORTCODE_CLASS ];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	/**
	 * The post_date shortcode callback.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function post_date( $attr = [] ) {
		/* make sure there is a default set for everything */
		$attr = array_merge(
			array(
				'type' => 'published',
				'tag'  => 'p',
			),
			$attr );

		/* check the old format value for backwards compatibility */
		if ( empty( $attr['format'] ) ) {
			/* if 'date-format' is empty, it means this is an old shortcode, so we add the 'time-format' too */
			if ( empty( $attr['date-format'] ) ) {
				$attr['date-format'] = get_option( 'date_format' );
				$attr['time-format'] = get_option( 'time_format' );
			} else {
				/* if nothing is set for 'time-format', use the empty default */
				if ( empty( $attr['time-format'] ) ) {
					$attr['time-format'] = '';
				}
			}
		} else {
			/* if there's something in the old 'format' value, add it all to 'date-format' and leave time-format empty */
			$attr['date-format'] = $attr['format'];
			$attr['time-format'] = '';

			/* unset it since we don't need it anymore */
			unset( $attr['format'] );
		}

		$format = $attr['date-format'] . ' ' . $attr['time-format'];

		$attr['date'] = $attr['type'] === 'published' ? get_the_time( $format ) : get_the_modified_date( $format );

		$content = tcb_template( 'post-list-sub-elements/post-date.php', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$classes = [ TCB_POST_DATE, TCB_SHORTCODE_CLASS ];

			if ( $attr['tag'] === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( array(
				'content' => TCB_Utils::wrap_content( $content, 'time' ),
				'tag'     => $attr['tag'],
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	/**
	 * Post featured image
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function post_thumbnail( $attr = [] ) {
		if ( has_post_thumbnail() ) {
			$image = static::shortcode_function_content( 'the_post_thumbnail', [ $attr['size'] ] );
		} else if (
			TCB_Editor()->is_inner_frame() ||
			TCB_Utils::is_rest() ||
			( ! empty( $attr['type-display'] ) && $attr['type-display'] === 'default_image' )
		) {
			$image = TCB_Utils::wrap_content( '', 'img', '', '', array( 'src' => TCB_Post_List_Featured_Image::get_default_url(), 'loading' => 'lazy' ) );
		} else {
			/* if we're not in the editor or the default display option is not selected, then don't display anything */
			$image = '';
		}

		/* add the post url only when the post url option is selected */
		$url_attr = $attr['type-url'] === 'post_url' ?
			array(
				'href' => get_permalink(),
			) : [];

		$attr['post_id'] = get_the_ID();
		$image_id        = get_post_thumbnail_id( $attr['post_id'] );

		if ( ! empty( $attr['title'] ) && $attr['title'] === 'gallery_title' ) {
			$title_attr = array(
				'title' => get_the_title( $image_id ),
			);
		} else {
			$title_attr = array(
				'title' => get_the_title(),
			);
		}
		$classes = [ TCB_POST_THUMBNAIL_IDENTIFIER, TCB_SHORTCODE_CLASS ];
		/* add the responsive classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}
		$classes = implode( ' ', $classes );

		return static::before_wrap( array(
			'content' => $image,
			'tag'     => 'a',
			'class'   => $classes,
			'attr'    => array_merge( $url_attr, $title_attr ),
		), $attr );
	}

	/**
	 * Post title shortcode.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function the_title( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/post-title.php', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$tag     = empty( $attr['tag'] ) ? 'h2' : $attr['tag'];
			$classes = [ TCB_POST_TITLE_IDENTIFIER, TCB_SHORTCODE_CLASS ];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	/**
	 * Post tags shortcode callback.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_tags( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/post-tags.php', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$tag     = empty( $attr['tag'] ) ? 'div' : $attr['tag'];
			$classes = [ TCB_POST_TAGS_IDENTIFIER, TCB_SHORTCODE_CLASS ];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			/* add an extra class so we can completely hide the container div on the frontend */
			if ( empty( $content ) ) {
				$classes[] = ' no-tags';
			}

			$content = static::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	/**
	 * Author role inline shortcode ( this one is not an element! )
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function author_role( $attr = [] ) {
		$content = tcb_template( 'post-list-sub-elements/author-role', $attr, true );

		if ( ! static::is_inline( $attr ) ) {
			$content = static::before_wrap( [
				'content' => $content,
				'tag'     => 'div',
				'class'   => '',
			], $attr );
		}

		return $content;
	}

	/**
	 * Return true if the element with the given attributes is an inline shortcode.
	 *
	 * @param [] $attr
	 *
	 * @return bool
	 */
	public static function is_inline( $attr = [] ) {
		return ! empty( $attr['inline'] ) && (int) $attr['inline'] === 1;
	}

	/**
	 * the_permalink
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function the_permalink( $attr = [] ) {
		return static::shortcode_function_content( 'the_permalink' );
	}

	/**
	 * Call do_shortcode() on the dynamic style from the saved content and wrap it in a <style> tag.
	 *
	 * @param array  $attr
	 * @param string $dynamic_style
	 *
	 * @return string
	 */
	public static function tcb_post_list_dynamic_style( $attr = [], $dynamic_style = '' ) {
		$style_css = do_shortcode( $dynamic_style );

		$style_css .= static::tcb_get_article_dynamic_variables( get_the_ID() );

		return TCB_Utils::wrap_content( $style_css, 'style', '', 'tcb-post-list-dynamic-style', [ 'type' => 'text/css' ] );
	}

	/**
	 * Returns article dynamic variables
	 *
	 * Contains the article ID as node for the variables
	 *
	 * @param int $article_id
	 *
	 * @return string
	 */
	public static function tcb_get_article_dynamic_variables( $article_id ) {
		$style_css      = '';
		$post_list_vars = apply_filters( 'tcb_get_post_list_variables', '', $article_id );
		if ( ! empty( $post_list_vars ) ) {
			$style_css .= sprintf( 'article#post-%d{%s}', $article_id, $post_list_vars );
		}

		return $style_css;
	}

	/**
	 * Return the post ID.
	 *
	 * @return string
	 */
	public static function the_id() {
		return get_the_ID();
	}

	/**
	 * Return the featured image url.
	 *
	 * @param array  $data
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function the_post_thumbnail_url( $data, $content, $tag ) {

		$size = empty( $data['size'] ) ? 'full' : $data['size'];

		if ( has_post_thumbnail() ) {
			$image_url = static::shortcode_function_content( 'the_post_thumbnail_url', [ $size ] );
		} else {
			$image_url = TCB_Post_List_Featured_Image::get_default_url();
		}
		/* if we're in the editor, append a dynamic flag at the end so we can recognize that the URL is dynamic in the editor */
		if ( TCB_Utils::in_editor_render( true ) ) {
			$image_url = add_query_arg( [
				'dynamic_featured' => 1,
				'size'             => $size,
			], $image_url );
		}

		return $image_url;
	}

	public static function video_cover_featured_image() {
		if ( has_post_thumbnail() ) {
			$image_url = static::shortcode_function_content( 'the_post_thumbnail_url' );
		} else {
			$image_url = TCB_Post_List_Featured_Image::get_default_url();
		}

		return TCB_Utils::wrap_content( '', 'img', '', 'tcb-video-cover-image', [ 'src' => $image_url ] );
	}

	/**
	 * Author image url
	 * We are calling this from the theme also
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function author_image_url( $attr = [], $content = '', $tag = 'tcb_author_image_url' ) {
		/**
		 * When we're outside the post list, skip rendering, but only if the shortcode is in the CSS.
		 * [backwards compat only] This can also be a shortcode inside a HTML tag ( in an img src ), and it renders in do_shortcodes_in_html_tags() which is called before do_shortcode
		 */
		if ( TCB_Post_List::is_outside_post_list_render() && ! TCB_Utils::is_processing_custom_css() ) {
			return '[' . $tag . ']';
		}

		return TCB_Post_List_Author_Image::author_avatar();
	}

	/**
	 * User image url
	 * We are calling this from the theme also
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function user_image_url( $attr = [], $content = '', $tag = 'tcb_user_image_url' ) {

		return tcb_dynamic_user_image_instance( get_current_user_id() )->user_avatar();
	}

	/**
	 * Return the content of the shortcode function
	 *
	 * @param string $func
	 * @param array  $args
	 *
	 * @return string
	 */
	public static function shortcode_function_content( $func, $args = [] ) {
		ob_start();

		is_callable( $func ) && call_user_func_array( $func, $args );

		return ob_get_clean();
	}

	/**
	 * There are some cases when one do_shortcode is not enough
	 *
	 * @param      $content
	 * @param bool $ignore_html
	 *
	 * @return mixed|string
	 */
	public static function do_shortcode( $content, $ignore_html = false ) {
		$content = do_shortcode( $content, $ignore_html );

		/* in some cases, when this shortcode is in a attribute, it might not be replaced, so we do it manually */
		$content = str_replace( '[tcb_post_the_permalink]', static::the_permalink(), $content );

		$content = static::check_dynamic_links( $content );

		return $content;
	}

	/**
	 * Parse shortcode attributes before getting to the shortcode function
	 *
	 * @param $attr
	 * @param $tag
	 *
	 * @return array
	 */
	public static function parse_attr( $attr, $tag ) {
		if ( ! is_array( $attr ) ) {
			$attr = [];
		}

		/* set default values if available */
		$attr = array_merge( static::default_attr( $tag ), $attr );

		/* escape attributes and decode [ and ] -> mostly used for json_encode */
		$attr = array_map( function ( $v ) {
			$v = esc_attr( $v );

			return str_replace( [ '|{|', '|}|' ], [ '[', ']' ], $v );
		}, $attr );

		return $attr;
	}

	/**
	 * Default values for some shortcodes
	 *
	 * @param $tag
	 *
	 * @return array|mixed
	 */
	private static function default_attr( $tag ) {
		$default = [
			'tcb_post_featured_image' => [
				'type-url'     => 'post_url',
				'type-display' => 'default_image',
				'css'          => '',
				'size'         => 'full',
			],
		];

		return isset( $default[ $tag ] ) ? $default[ $tag ] : [];
	}

	/**
	 * For the dynamic links just replace the "shortcode" with the url so it wont mess with froala and element style
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public static function check_dynamic_links( $content ) {
		$post_id = get_the_ID();

		$content = str_replace(
			[
				'[tcb_post_the_permalink]',
				'[tcb_post_archive_link]',
				'[tcb_post_author_link]',
				'[tcb_post_date_link]',
				'[tcb_post_comments_link]',
			],
			[
				get_permalink( $post_id ),
				TCB_Post_List::get_post_type_archive_link( $post_id ),
				TCB_Post_List::get_author_posts_url( $post_id ),
				TCB_Post_List::get_day_link(),
				TCB_Post_List::comments_link( $post_id ),
			],
			$content );
		if ( ! is_editor_page_raw( true ) ) {
			$content = self::replace_postlist_dynamic_link( $content );
		}

		return $content;
	}

	private static function replace_postlist_dynamic_link( $content ) {

		while ( preg_match( '(\[thrive_postlist_custom_fields_shortcode_url id=(.*?)\])', $content, $aux ) ) {
			$replacement = static::custom_field( [
				'id'          => $aux[1],
				'do_not_wrap' => true,
			] );

			if ( filter_var( $replacement, FILTER_VALIDATE_URL ) ) {
				$content = preg_replace( '(\[thrive_postlist_custom_fields_shortcode_url id=.*?\])', $replacement, $content, 1 );
			} else {
				$content = preg_replace( '(href="\[thrive_postlist_custom_fields_shortcode_url id=.*?")', 'href', $content, 1 ); //Used to completely remove the href if it is not a link
			}
		}

		return $content;
	}

	/**
	 * Add the author link shortcode shortcode.
	 */
	public function author_link_shortcode( $attr, $content, $tag ) {
		$key = $attr['id'] ?? '';

		/* we shouldn't render the shortcode in the editor */
		if ( TCB_Utils::in_editor_render( true ) ) {
			$link = empty( $key ) ? '#' : "[$tag id='$key']";
		} else {
			global $post;

			$links = (array) get_the_author_meta( 'thrive_social_urls', tve_get_post_author( $post ) );

			$link = empty( $links[ $key ] ) ? '' : $links[ $key ];
		}

		return $link;
	}
}

/**
 * Return TCB_Post_List_Shortcodes instance
 *
 * @return null|TCB_Post_List_Shortcodes
 */
function tcb_post_list_shortcodes() {
	return TCB_Post_List_Shortcodes::instance();
}

new TCB_Post_List_Shortcodes();
