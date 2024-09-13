<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

defined( 'THRIVE_SHORTCODE_CLASS' ) || define( 'THRIVE_SHORTCODE_CLASS', 'thrive-shortcode' );

/**
 * Class Thrive_Shortcodes
 */
class Thrive_Shortcodes {

	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	private $execution_stack = [];
	private $editing_context;

	public static $dynamic_shortcodes
		= [
			'thrive_widget_area'                     => 'dynamic_sidebar',
			'thrive_comments'                        => 'comments_section',
			'thrive_blog_list'                       => 'blog',
			'thrive_the_id'                          => 'the_id',
			'thrive_the_permalink'                   => 'the_permalink',
			'thrive_post_class'                      => 'post_class',
			'thrive_breadcrumbs'                     => 'breadcrumbs',
			'thrive_menu'                            => 'menu',
			'thrive_comments_username'               => 'comments_username',
			'thrive_last_modified'                   => 'post_modified_date',
			'thrive_author_follow_urls'              => 'author_social_url',
			'thrive_archive_link'                    => 'get_post_type_archive_link',
			'thrive_author_link'                     => 'get_author_posts_url',
			'thrive_date_link'                       => 'get_day_link',
			'thrive_dynamic_list'                    => 'dynamic_list',
			'thrive_symbol'                          => 'get_symbol',
			'thrive_calendar_widget'                 => 'get_calendar',
			'thrive_template_section'                => 'theme_section',
			'thrive_comments_link'                   => 'comments_link',
			'thrive_main_container'                  => 'main_container',
			'thrive_template_content'                => 'template_content',
			'thrive_author_box'                      => 'author_box',
			'thrive_dynamic_video'                   => 'dynamic_video',
			'thrive_dynamic_audio'                   => 'dynamic_audio',
			'thrive_taxonomy_term_description'       => 'taxonomy_term_description',
			'thrive_search_inline_shortcode'         => 'render_search_shortcode',
			'thrive_archive_name'                    => 'archive_name',
			'thrive_archive_description'             => 'archive_description',
			'thrive_archive_parent_name'             => 'archive_parent_name',
			'tcb_post_next_link'                     => 'next_link',
			'tcb_post_prev_link'                     => 'previous_link',
			'thrive_next_title_inline_shortcode'     => 'render_next_title',
			'thrive_previous_title_inline_shortcode' => 'render_previous_title',
			'thrive_prev_column'                     => 'render_previous_column',
			'thrive_next_column'                     => 'render_next_column',
			'thrive_remaining_time'                  => 'render_remaining_time',
			'thrive_word_count'                      => 'render_word_count',
			'thrive_post_excerpt'                    => 'render_post_excerpt',
		];

	private static $post_list_shortcodes
		= [
			'tcb_post_title'          => 'the_title',
			'tcb_post_featured_image' => 'post_thumbnail',
			'tcb_featured_image_url'  => 'the_post_thumbnail_url',
			'tcb_author_image_url'    => 'author_image_url',
		];

	/**
	 * All thrive shortcodes should ( hopefully ) start with one of these keywords:
	 *
	 * @var array
	 */
	public static $thrive_shortcode_prefixes
		= [
			'thrive_',
			'thrv_',
			'tcb_',
			'tar_',
			'tve_',
		];

	/**
	 * Default args used when wrapping content
	 */
	const DEFAULT_WRAP_ARGS
		= [
			'content' => '',
			'tag'     => 'div',
			'id'      => '',
			'class'   => [],
			'attr'    => [],
		];

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * @return mixed
	 */
	public function get_editing_context() {
		return $this->editing_context;
	}

	/**
	 * @param string $context
	 * @param array  $args
	 */
	public function set_editing_context( $context, $args = [] ) {
		$this->editing_context = empty( $context )
			? null
			: [
				'name' => $context,
				'args' => $args,
			];
	}

	/**
	 * Return execution stack
	 *
	 * @return array
	 */
	public function get_execution_stack() {
		return $this->execution_stack;
	}

	/**
	 * Add all shortcodes and their callbacks
	 */
	public function init() {
		add_filter( 'tcb_inline_shortcodes', [ $this, 'inline_shortcodes' ] );

		foreach ( static::$dynamic_shortcodes as $shortcode => $func ) {
			add_shortcode(
				$shortcode,
				function ( $attr, $content, $tag ) {
					$func    = static::$dynamic_shortcodes[ $tag ];
					$display = '';

					if ( method_exists( __CLASS__, $func ) ) {
						$attr = static::parse_attr( $attr, $tag );

						thrive_shortcodes()->execution_stack[] = [
							'shortcode' => $tag,
							'attr'      => $attr,
						];

						$display = static::$func( $attr, $content, $tag );

						array_pop( thrive_shortcodes()->execution_stack );
					}

					return $display;
				}
			);
		}
		/* overwrite the implementation of the post list shortcodes */
		foreach ( static::$post_list_shortcodes as $shortcode => $func ) {
			add_filter( 'tcb_render_shortcode_' . $shortcode, [ __CLASS__, $func ], 10, 3 );
		}

		/* do not render non-thrive shortcodes in the editor; instead, we keep the tags + attr/content as we find them */
		add_filter( 'pre_do_shortcode_tag', function ( $output, $tag, $attr, $m ) {

			/*
			 * IF the theme is not active, we might need to render the shortcodes - they might belong to a 3rd party theme and used in TEMPLATE, not in content
			 * So the checks here make sure that
			 * (1) TTB is not active
			 * and
			 * (2) user is editing a content ( not a template ). The TAR editor will make sure that the shortcodes from the content are not rendered in editor pages
			 */
			if ( ! Thrive_Theme::is_active() && ! isset( $_GET[ THRIVE_THEME_FLAG ] ) && TCB_Utils::in_editor_render( true ) ) {
				return $output;
			}


			/**
			 * Decide if a shortcode can be rendered inside the editor or not.
			 * By default, it should be not because at save it will be saved as static html and won't be displayed correctly on frontend after.
			 *
			 * @param {Boolean} $should_render_shortcode
			 * @param {String} $shortcode_tag
			 *
			 * @return {Boolean}
			 */
			$should_render_shortcode = apply_filters( 'thrive_theme_should_render_shortcode', Thrive_Utils::is_thrive_shortcode( $tag ) || ! TCB_Utils::in_editor_render( true ), $tag );

			if ( ! empty( $m[0] ) && ! $should_render_shortcode ) {
				$output = $m[0];
			}

			return $output;
		}, 10, 4 );

		/**
		 * Add extra info for theme inline shortcodes
		 */
		add_filter( 'tcb_post_list_post_info', function ( $post, $id ) {
			$post['thrive_post_excerpt'] = static::render_post_excerpt();
			$post['tcb_post_prev_link']  = static::previous_link();
			$post['tcb_post_next_link']  = static::next_link();

			return $post;
		}, 10, 2 );
	}

	/**
	 * Adds Thrive Theme Inline Shortcodes
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function inline_shortcodes( $shortcodes = [] ) {
		$inline_shortcodes = [];

		$theme_shortcodes = [
			/*Search Result Page */
			'search_term'    => [
				'name'      => __( 'Search Term', 'thrive-theme' ),
				'shortcode' => 'thrive_search_inline_shortcode',
				'fn'        => 'render_search_shortcode',
				'group'     => 'Search Result Page',
			],
			'search_results' => [
				'name'      => __( 'Number of results', 'thrive-theme' ),
				'shortcode' => 'thrive_search_inline_shortcode',
				'fn'        => 'render_search_shortcode',
				'group'     => 'Search Result Page',
			],
		];

		if ( Thrive_Prev_Next::show() ) {
			$theme_shortcodes = array_merge( $theme_shortcodes, [
				'post_excerpt'   => [
					'name'      => __( 'Post excerpt', 'thrive-theme' ),
					'shortcode' => 'thrive_post_excerpt',
					'fn'        => 'render_post_excerpt',
					'group'     => 'Content',
					'input'     => [
						'hide' => [
							'type'  => 'checkbox',
							'label' => __( 'Hide if excerpt is not set', 'thrive-theme' ),
							'value' => false,
						],
					],
				],
				'next_title'     => [
					'name'      => __( 'Title of the next piece of content', 'thrive-theme' ),
					'shortcode' => 'thrive_next_title_inline_shortcode',
					'fn'        => 'render_next_title',
					'group'     => 'Content',
					'input'     => [
						'default' => [
							'type'  => 'input',
							'label' => __( 'Default Value', 'thrive-theme' ),
							'value' => '',
						],
						'link'    => [
							'type'  => 'checkbox',
							'label' => __( 'Link to content', 'thrive-theme' ),
							'value' => true,
						],
					],
				],
				'previous_title' => [
					'name'      => __( 'Title of the previous piece of content', 'thrive-theme' ),
					'shortcode' => 'thrive_previous_title_inline_shortcode',
					'fn'        => 'render_previous_title',
					'group'     => 'Content',
					'input'     => [
						'default' => [
							'type'  => 'input',
							'label' => __( 'Default Value', 'thrive-theme' ),
							'value' => '',
						],
						'link'    => [
							'type'  => 'checkbox',
							'label' => __( 'Link to content', 'thrive-theme' ),
							'value' => true,
						],
					],
				],
			] );
		}

		if ( Thrive_Architect_Utils::show_progress_bar() ) {
			$theme_shortcodes['remaining_time'] = [
				'name'      => __( 'Reading time remaining (in minutes)', 'thrive-theme' ),
				'shortcode' => 'thrive_remaining_time',
				'group'     => 'Content',
			];
			$theme_shortcodes['word_count']     = [
				'name'      => __( 'Word count', 'thrive-theme' ),
				'shortcode' => 'thrive_word_count',
				'group'     => 'Content',
			];
		}
		foreach ( $theme_shortcodes as $key => $data ) {
			$shortcode = [
				'name'        => $data['name'],
				'option'      => $data['name'],
				'value'       => $data['shortcode'],
				'extra_param' => $key,
				'input'       => empty( $data['input'] ) ? [] : $data['input'],
			];

			if ( isset( $data['fn'] ) && method_exists( $this, $data['fn'] ) ) {
				$real_data = call_user_func( [ $this, $data['fn'] ], [ 'id' => $key ] );
			} else {
				$real_data = '';
			}

			$shortcode['input']['id'] = [
				'extra_options' => [],
				'real_data'     => [
					$key => $real_data,
				],
				'type'          => 'hidden',
				'value'         => $key,
			];

			$inline_shortcodes[ $data['group'] ][] = $shortcode;
		}

		return array_merge_recursive( $inline_shortcodes, $shortcodes );
	}

	/**
	 * Renders the search term shortcodes
	 *
	 * @param array $args
	 *
	 * @return int|string
	 */
	public function render_search_shortcode( $args = [] ) {
		$return = '';

		if ( thrive_template()->is_search() && ! empty( $args['id'] ) ) {

			switch ( $args['id'] ) {
				case 'search_results':
					global $wp_query;
					$return = $wp_query->found_posts;
					break;
				case 'search_term':
				default:
					$return = get_search_query();
			}
		}

		return $return;
	}

	/**
	 * Parse shortcode attributes before getting to the shortcode function
	 *
	 * @param array  $attr
	 * @param string $tag
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
		$attr = array_map( static function ( $v ) {

			if ( ! is_array( $v ) ) {
				$v = esc_attr( $v );
				$v = str_replace( [ '|{|', '|}|', '__SHORTCODE_SINGLE_QUOTE__' ], [ '[', ']', "'" ], $v );
			}

			return $v;
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
			'thrive_blog_list'    => Thrive_Post_List::get_blog_default_args(),
			'thrive_dynamic_list' => Thrive_Theme_List::default_list_attributes(),
		];

		return isset( $default[ $tag ] ) ? $default[ $tag ] : [];
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

		$wrap_args = array_merge( static::DEFAULT_WRAP_ARGS, $wrap_args );

		if ( is_string( $wrap_args['class'] ) ) {
			$wrap_args['class'] = explode( ' ', $wrap_args['class'] );
		}

		if ( ! empty( $attr['class'] ) ) {

			if ( is_string( $attr['class'] ) ) {
				$attr['class'] = explode( ' ', $attr['class'] );
			}

			$wrap_args['class'] = array_map( 'trim', array_merge( $wrap_args['class'], $attr['class'] ) );
		}

		if ( ! in_array( THRIVE_WRAPPER_CLASS, $wrap_args['class'], true ) ) {
			$wrap_args['class'] [] = THRIVE_WRAPPER_CLASS;
		}

		if ( ! empty( $attr['style'] ) ) {
			$wrap_args['attr']['style'] = $attr['style'];
			unset( $attr['style'] );
		}

		/* attributes that come directly from the shortcode */
		foreach ( $attr as $key => $value ) {
			if (
				! in_array( $key, $ignored_attr, true ) && /* if this attribute is 'ignored', don't do anything */
				(
					is_editor_page_raw( true ) || /* in the editor, always add the attributes */
					Thrive_Utils::during_ajax() ||
					in_array( $key, $front_attr, true ) /* if this attr has to be added on the frontend, add it */
				)
			) {
				$wrap_args['attr'][ 'data-' . $key ] = is_array( $value ) ? implode( ' ', $value ) : $value;
				unset( $wrap_args['attr'][ $key ] );
			}
		}

		/* during ajax we can't render shortcodes, so we add the shortcode tag and class so we can fix them in JS */
		if ( Thrive_Utils::during_ajax() ) {
			$execution_stack = thrive_shortcodes()->execution_stack;

			if ( ! empty( $execution_stack ) ) {
				$wrap_args['attr']['data-shortcode'] = end( $execution_stack )['shortcode'];
			}

			$wrap_args['class'][] = TCB_SHORTCODE_CLASS;
			$wrap_args['class'][] = THRIVE_SHORTCODE_CLASS;
		}

		return call_user_func_array( [ TCB_Utils::class, 'wrap_content' ], $wrap_args );
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
		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Symbol shortcode
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function get_symbol( $data = [] ) {
		$html = TCB_Symbol_Template::symbol_render_shortcode( $data );

		/**
		 * Make sure that shortcodes are rendered inside the symbol
		 */
		if ( ! is_editor_page_raw( true ) || Thrive_Utils::during_ajax() ) {
			$html = do_shortcode( $html );
		}

		$attr = [
			'data-shortcode' => 'thrive_symbol',
			'data-id'        => $data['id'],
		];

		$class = THRIVE_WRAPPER_CLASS . " thrv_symbol tve-draggable tve-droppable thrv_symbol_{$data['id']} " . THRIVE_SHORTCODE_CLASS;
		if ( ! empty( $data['class'] ) ) {
			$class = $class . ' ' . $data['class'];
		}

		return TCB_Utils::wrap_content( $html, 'div', '', $class, $attr );
	}

	/**
	 * Sidebar shortcode
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function dynamic_sidebar( $attr = [] ) {

		if ( is_active_sidebar( $attr['id'] ) ) {
			$content = static::shortcode_function_content( 'dynamic_sidebar', [ $attr['id'] ] );
			$content = static::before_wrap(
				[
					'content' => $content,
					'tag'     => 'div',
					'class'   => 'widget-area',
				], $attr );
		} else {
			$content = '';
		}

		return $content;
	}

	/**
	 * Comments template
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function comments_section( $data = [] ) {
		$data = array_merge(
			[
				'ct'        => 'thrive_comments',
				'ct-name'   => __( 'Default', 'thrive-theme' ),
				'shortcode' => 'thrive_comments',
			], $data );

		return thrive_theme_comments()->get_comments_template( apply_filters( 'thrive_theme_comments_attributes', $data ) );
	}

	/**
	 * Post title shortcode.
	 *
	 * @param       $output
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function the_title( $output, $attr = [] ) {
		$classes = [ TCB_POST_TITLE_IDENTIFIER, TCB_SHORTCODE_CLASS ];

		$is_inside_post_list = static::is_inside_shortcode( [ 'tcb_post_list', 'thrive_blog_list' ] );

		/* if we're not inside a post list, check if we should hide this element from the page ( by returning nothing or by adding classes to hide it ) */
		if ( ! $is_inside_post_list && ! thrive_post()->is_element_visible( 'post_title', $classes ) ) {
			return '';
		}

		$content = tcb_template( 'post-list-sub-elements/post-title.php', $attr, true );

		//todo this can be deleted as soon as we replace the old article shortcodes from the default templates
		if ( ! TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
			if ( empty( $attr['tag'] ) ) {
				/* inside post lists and on archive pages we show h2 tag, on singular page we use h1 */
				if ( $is_inside_post_list ) {
					$tag = 'h2';
				} else {
					$tag = is_singular() ? 'h1' : 'h2';
				}
			} else {
				$tag = $attr['tag'];
			}

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = static::before_wrap( [
				'content' => $content,
				'tag'     => $tag,
				'class'   => $classes,
			], $attr );
		}

		return $content;
	}

	/**
	 * Post featured image
	 *
	 * @param       $output
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function post_thumbnail( $output, $attr = [] ) {
		$classes = [ TCB_POST_THUMBNAIL_IDENTIFIER, TCB_SHORTCODE_CLASS ];

		/* if this is not inside a post list, check if we should hide this element from the page ( by returning nothing or by adding classes to hide it ) */
		if ( ! static::is_inside_shortcode( [
				'tcb_post_list',
				'thrive_blog_list',
			] ) && ! thrive_post()->is_element_visible( 'featured_image', $classes ) ) {
			return '';
		}

		if ( has_post_thumbnail() ) {
			$image = static::shortcode_function_content( 'the_post_thumbnail', [ $attr['size'] ] );
		} else {
			/* if we're not in the editor and the display option is not selected, then don't display */
			$image = Thrive_Utils::is_inner_frame() || ( ! empty( $attr['type-display'] ) && $attr['type-display'] === 'default_image' ) ? Thrive_Featured_Image::get_default( $attr['size'] ) : '';
		}

		/* add the post url only when the post url option is selected */
		$url_attr = $attr['type-url'] === 'post_url' ? [ 'href' => get_permalink() ] : [];

		$attr['post_id'] = get_the_ID();
		$image_id        = get_post_thumbnail_id( $attr['post_id'] );

		if ( ! empty( $attr['title'] ) && $attr['title'] === 'gallery_title' ) {
			$title_attr = [ 'title' => get_the_title( $image_id ) ];
		} else {
			$title_attr = [ 'title' => get_the_title() ];
		}

		/**
		 * Filter image HTML for each post in post list
		 *
		 * @param $image     string html string
		 * @param $post_id   int post id
		 * @param $post_type string post type
		 */
		$image = apply_filters( 'thrive_theme_post_thumbnail', $image, get_the_ID(), get_post_type() );

		return static::before_wrap( [
			'content' => $image,
			'tag'     => 'a',
			'class'   => $classes,
			'attr'    => array_merge( $url_attr, $title_attr ),
		], $attr );
	}

	/**
	 * Post list for archive page or content for single entry
	 *
	 * @param $attr
	 * @param $article_content
	 *
	 * @return string
	 */
	public static function blog( $attr = [], $article_content = '' ) {
		$blog_list = new Thrive_Post_List( $attr, $article_content );
		$content   = $blog_list->render();

		if ( thrive_template()->is_search() && is_editor_page_raw( true ) ) {
			$content .= thrive_template()->get_no_search_results_content();
		}

		return $content;
	}

	/**
	 * the_ID
	 *
	 * @return string
	 */
	public static function the_id() {
		return get_the_ID();
	}

	/**
	 * Render the 'the_permalink' shortcode.
	 *
	 * @return string
	 */
	public static function the_permalink() {
		/* in the editor, '{tcb_post_url}' is treated as the default empty value, so we return that */
		if ( TCB_Utils::in_editor_render() ) {
			$link = '{tcb_post_url}';
		} else {
			if ( in_the_loop() ) {
				$link = static::shortcode_function_content( 'the_permalink' );
			} else {
				/* if we're not in the loop, return the current url */
				$prefix = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) ? 'https' : 'http';
				$link   = $prefix . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}
		}

		return $link;
	}

	/**
	 * Render menu
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function menu() {

		ob_start();

		include TVE_TCB_ROOT_PATH . '/inc/views/elements/menu.php';

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Breadcrumbs
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function breadcrumbs( $attr = [] ) {
		return is_404() ? '' : Thrive_Breadcrumbs::render( $attr );
	}

	/**
	 * Return the dynamic list
	 *
	 * @param array   $attr
	 * @param Boolean $use_demo_content
	 *
	 * @return string
	 */
	public static function dynamic_list( $attr = [], $use_demo_content = false ) {

		if ( empty( $use_demo_content ) ) {
			$use_demo_content = Thrive_Demo_Content::on_demo_content_page();
		}

		$list = new Thrive_Theme_List( $attr, $use_demo_content );

		return $list->render();
	}

	/**
	 * Return icon html based on the name.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function get_icon_by_name( $name ) {
		$icon = '';

		if ( ! empty( $GLOBALS['symbol_id'] ) ) {
			/* Get the icons from the header / footer meta if the shortcode is executed inside a header or a footer */
			$icons = get_post_meta( $GLOBALS['symbol_id'], 'icons', true );
			$icon  = empty( $icons[ $name ] ) ? '' : $icons[ $name ];
		} else {
			$context = thrive_shortcodes()->get_editing_context();

			/* if we're in a section context, look for the icon inside the section meta, else look for it inside the template meta */
			if ( $context !== null && $context['name'] === 'section' ) {
				$section = $context['args']['instance'];

				if ( $section instanceof Thrive_Section ) {
					$icon = $section->get_icon( $name );
				}
			}

			if ( empty( $icon ) ) {
				$icon = thrive_template()->get_icon_svg( $name );
			}
		}

		return $icon;
	}

	/**
	 * Render calendar widget
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function get_calendar( $data = [] ) {
		$calendar = get_calendar( true, false );

		return static::before_wrap( [
			'content' => $calendar,
			'class'   => 'thrive-calendar-widget',
		], $data );

	}

	/**
	 * Return featured image url
	 *
	 * @param string $image_url
	 * @param array  $data
	 *
	 * @return string
	 */
	public static function the_post_thumbnail_url( $image_url = '', $data = [] ) {
		$size = empty( $data['size'] ) ? 'full' : $data['size'];

		if ( has_post_thumbnail() ) {
			$image_url = static::shortcode_function_content( 'the_post_thumbnail_url', [ $size ] );
		} else {
			$image_url = Thrive_Featured_Image::get_default_url( $size );
		}

		/* if we're in the editor, append a dynamic flag at the end so we can recognize that the URL is dynamic in the editor */
		if ( TCB_Utils::in_editor_render( true ) ) {
			$image_url = add_query_arg( [
				'dynamic_featured' => 1,
				'size'             => $size,
			], $image_url );
		}

		/**
		 * In context of post list filter thumb url for each post
		 *
		 * @param $image_url string
		 * @param $id        int post id
		 * @param $post_type string post type
		 */
		return apply_filters( 'thrive_theme_post_thumbnail_url', $image_url, get_the_ID(), get_post_type() );
	}

	/**
	 * Author image url
	 *
	 * @return string
	 */
	public static function author_image_url() {
		return TCB_Post_List_Author_Image::author_avatar();
	}

	/**
	 * Comments link.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function comments_link( $attr = [] ) {

		$link = '';
		if ( ! empty( $attr['list'] ) ) {
			/**
			 * Inside post-lists we need the link to redirect to specific post and then scroll to comments
			 * */
			global $post;
			$link = get_permalink( $post );
		}

		return $link . '#comments';
	}

	/**
	 * Show the username of the logged in user inside the comments form
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function comments_username( $attr ) {
		$current_user = wp_get_current_user();

		return $current_user !== null && $current_user->exists() ? $current_user->display_name : '';
	}

	/**
	 * Post last modified date.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function post_modified_date( $attr = [] ) {
		$content = static::shortcode_function_content( 'the_modified_date' );

		$content = static::before_wrap( [
			'content' => $content,
			'tag'     => 'div',
			'class'   => 'last-modified-date',
		], $attr );

		return $content;
	}

	/**
	 * Return the social url for the current author and for the given key
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function author_social_url( $attr = [], $content = '', $tag = '' ) {
		$key = isset( $attr['url'] ) ? $attr['url'] : '';

		/* we shouldn't render the shortcode in the editor */
		if ( TCB_Utils::in_editor_render( true ) ) {
			$link = empty( $key ) ? '#' : "[$tag url='$key']";
		} else {
			global $post;
			if ( ! empty( $post ) ) {
				$links = (array) get_the_author_meta( THRIVE_SOCIAL_OPTION_NAME, $post->post_author );
			}

			$link = empty( $links[ $key ] ) ? '#' : $links[ $key ];
		}

		return $link;
	}

	/**
	 * Post type archive link
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function get_post_type_archive_link( $attr = [] ) {

		$link = get_post_type_archive_link( get_post_type() );

		if ( empty( $link ) ) {
			$link = '#';
		}

		return $link;
	}

	/**
	 * Author archive link
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function get_author_posts_url( $attr = [] ) {
		global $post;
		$link = get_author_posts_url( $post->post_author );

		if ( empty( $link ) ) {
			$link = '#';
		}

		return $link;
	}

	/**
	 * Date link
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function get_day_link( $attr = [] ) {
		$is_inline = is_array( $attr ) && in_array( 'inline', $attr, true );

		if ( $is_inline && Thrive_Utils::is_inner_frame() ) {
			return '[thrive_date_link inline]';
		}

		$link = get_day_link( get_the_date( 'Y' ), get_the_date( 'm' ), get_the_date( 'd' ) );

		if ( empty( $link ) ) {
			$link = '#';
		}

		return $link;
	}

	/**
	 * Render theme section
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function theme_section( $attr = [] ) {
		$section_data = thrive_template()->get_section( $attr['type'] );

		$section = new Thrive_Section( $section_data['id'], $section_data );

		return $section->render();
	}

	/**
	 * Shortcode that wraps the content of the author box.
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function author_box( $attr = [], $content = '' ) {
		$classes = [ 'thrive_author_box', THRIVE_WRAPPER_CLASS, 'wrapper-shortcode', 'tcb-compact-element' ];

		/* check if we should hide this element from the page ( by returning nothing or by adding classes to hide it ) */
		if ( ! wp_doing_ajax() && ! thrive_post()->is_element_visible( 'author_box', $classes ) ) {
			return '';
		}

		$attr = array_merge( [
			'data-ct'        => 'thrive_author_box',
			'data-ct-name'   => __( 'About the Author', 'thrive-theme' ),
			'data-shortcode' => 'thrive_author_box',
		], $attr );

		/* we have to decode the encoded '[' and ']'s that are inside html tags ( this is done for check_dynamic_links() ) */
		$content = unescape_invalid_shortcodes( $content );

		$content = do_shortcode( $content );

		$content = TCB_Post_List_Shortcodes::check_dynamic_links( $content );

		return TCB_Utils::wrap_content( $content, 'div', '', $classes, $attr );
	}

	/**
	 * Columns section
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public static function main_container( $attr = [], $content = '' ) {

		$class = [ 'main-container' ];

		if ( thrive_template()->has_sidebar_on_left() ) {
			$class[] = 'flip-sections';
		}

		if ( Thrive_Utils::is_inner_frame() ) {
			$attr['selector'] = '.main-container';
			$attr['section']  = 'main';
		}

		/* Here we put the extra html, like the columns separator */
		$extra_html = '';

		/* First we need to get the data of the Sidebar */
		$sidebar_data       = thrive_template()->get_section( 'sidebar' );
		$sidebar_section    = new Thrive_Section( $sidebar_data['id'], $sidebar_data );
		$is_sidebar_visible = Thrive_Utils::get_section_visibility( 'sidebar' );

		$content_data = thrive_template()->get_section( 'content' );

		if ( ! $is_sidebar_visible ) {
			if ( ! empty( $content_data['attr']['class'] ) ) {
				$content_data['attr']['class'] .= ' content-full-width';
			} else {
				$content_data['attr']['class'] = 'content-full-width';
			}
		}

		$content_section = new Thrive_Section( $content_data['id'], $content_data );

		$html_content = $content_section->render();

		if ( ( $is_sidebar_visible || is_editor_page_raw( true ) ) && ! Thrive_Utils::during_ajax() ) {
			/* if we render a sidebar, it means we also have a separator between it and the content */
			$extra_html .= '<div class="main-columns-separator"></div>';
		}

		$html_content .= $sidebar_section->render();
		$html_content .= $extra_html;

		return static::before_wrap( [
			'content' => $html_content,
			'class'   => $class,
		], $attr );
	}

	/**
	 * Columns section
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public static function template_content( $attr = [], $content = '' ) {

		$bg_attr = [];
		$class   = [];

		if ( Thrive_Utils::is_inner_frame() ) {
			$bg_attr = [ 'data-selector' => '.main-content-background' ];

			$attr['data-element-name']  = __( 'Layout Container', 'thrive-theme' );
			$attr['data-tcb-elem-type'] = 'template-content';
			$attr['data-selector']      = '#content';

			$class[] = 'tcb-selector-no_highlight';
		}

		$content .= TCB_Utils::wrap_content( '', 'div', '', 'main-content-background', $bg_attr );

		return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', 'content', $class, $attr );
	}

	/**
	 * Render the dynamic video shortcode from post.
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public static function dynamic_video( $attr, $content = '' ) {

		/**
		 * Allow other plugins to modify the dynamic video shortcode output
		 * Used in TA: if the dynamic video is inserted inside a protected post, we hide the element
		 *
		 * @param string $dynamic_video_content
		 */
		return apply_filters( 'thrive_theme_dynamic_video', Thrive_Video_Post_Format_Main::render( $attr, $content ) );
	}

	/**
	 * Render dynamic video from post
	 *
	 * @return string
	 */
	public static function dynamic_audio() {
		/* always render the placeholder inside the editor */
		if ( Thrive_Utils::is_inner_frame() ) {
			$content = Thrive_Utils::return_part( '/inc/templates/parts/dynamic-audio-placeholder.php' );
		} else {
			/* render the actual video iframe on the front */
			$content = Thrive_Audio_Post_Format_Main::render();
		}

		/**
		 * Allow other plugins to modify the dynamic audio shortcode output
		 *  Used in TA: if the dynamic video is inserted inside a protected post, we hide the element
		 *
		 * @param string $content
		 */
		return apply_filters( 'thrive_theme_dynamic_audio', $content );
	}

	/**
	 * Check if at a certain point we're running code inside a shortcode
	 *
	 * @return bool
	 */
	public static function is_inside_shortcode( $shortcodes ) {
		$inside_shortcode = false;

		if ( ! is_array( $shortcodes ) ) {
			$shortcodes = [ $shortcodes ];
		}

		foreach ( TCB_Post_List_Shortcodes()->get_execution_stack() as $s ) {

			if ( in_array( $s['shortcode'], $shortcodes ) ) {
				$inside_shortcode = true;
				break;
			}
		}

		if ( ! $inside_shortcode ) {
			foreach ( thrive_shortcodes()->get_execution_stack() as $s ) {
				if ( in_array( $s['shortcode'], $shortcodes ) ) {
					$inside_shortcode = true;
					break;
				}
			}
		}

		return $inside_shortcode;
	}

	/**
	 * Taxonomy term description
	 *
	 * @return string
	 */
	public static function taxonomy_term_description( $attr = [] ) {
		$description = get_the_archive_description();
		$class       = [ 'thrive-taxonomy-term-description', THRIVE_WRAPPER_CLASS, THRIVE_SHORTCODE_CLASS ];

		if ( empty( $description ) ) {
			/* If we are inside inner frame we are showing a default message */
			if ( Thrive_Utils::is_inner_frame() ) {
				$description_html = static::before_wrap( [
					'content' => Thrive_Utils::return_part( '/inc/templates/parts/default-term-description.php' ),
					'tag'     => 'div',
					'class'   => $class,
				], $attr );
			} else {
				$description_html = '';
			}
		} else {
			$description_html = static::before_wrap( [
				'content' => $description,
				'tag'     => 'div',
				'class'   => $class,
			], $attr );
		}

		return $description_html;
	}

	/**
	 * Render archive name shortcode content
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function archive_name( $attr = [] ) {
		global $wp_query;

		if ( is_search() ) {
			$title = $wp_query->query['s'];
		} elseif ( $wp_query->queried_object !== null ) {
			if ( is_author() ) {
				$title = empty( $wp_query->queried_object->data->display_name ) ? '' : $wp_query->queried_object->data->display_name;
			} else {
				$title = $wp_query->queried_object->name;
			}
		} else {
			$title = '';
		}

		return static::before_wrap( [
			'content' => $title,
			'tag'     => 'span',
			'class'   => [ 'thrive-archive-name', THRIVE_WRAPPER_CLASS, THRIVE_SHORTCODE_CLASS ],
		], $attr );
	}

	/**
	 * Render archive description shortcode content
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function archive_description( $attr = [] ) {
		$term        = get_term( get_queried_object_id() );
		$description = empty( $term ) || is_wp_error( $term ) ? '' : $term->description;
		$class       = [ 'thrive-archive-description', THRIVE_WRAPPER_CLASS, THRIVE_SHORTCODE_CLASS ];

		if ( empty( $description ) ) {
			/* If we are inside inner frame we are showing a default message */
			if ( Thrive_Utils::is_inner_frame() ) {
				$content = __( "The archive description field doesn't exist for the current taxonomy so no content will be displayed", 'thrive-theme' );
			} else {
				$content = '';
			}
		} else {
			$content = $description;
		}

		return static::before_wrap( [
			'content' => $content,
			'tag'     => 'span',
			'class'   => $class,
		], $attr );
	}

	/**
	 * Render archive parent name shortcode content
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function archive_parent_name( $attr = [] ) {
		$class       = [ 'thrive-archive-parent-name', THRIVE_WRAPPER_CLASS, THRIVE_SHORTCODE_CLASS ];
		$term        = get_term( get_queried_object_id() );
		$parent_name = '';

		if ( $term && ! is_wp_error( $term ) && $term->parent ) {
			$parent      = get_term( $term->parent );
			$parent_name = empty( $parent ) || is_wp_error( $parent ) ? '' : $parent->name;
		}

		if ( empty( $parent_name ) && Thrive_Utils::is_inner_frame() ) {
			/* If we are inside inner frame we are showing a default message */
			$parent_name = __( "The archive parent doesn't exist for the current archive so no content will be displayed", 'thrive-theme' );
		};

		return static::before_wrap( [
			'content' => $parent_name,
			'tag'     => 'span',
			'class'   => $class,
		], $attr );
	}

	/**
	 * Return post previous link
	 *
	 * @param array $attr
	 *
	 * @return bool|false|string|WP_Error
	 */
	public static function previous_link( $attr = [] ) {
		return thrive_prev_next()->render_adjacent_link( $attr );
	}


	/**
	 * Return post next link
	 *
	 * @param array $attr
	 *
	 * @return false|string
	 */
	public static function next_link( $attr = [] ) {
		return thrive_prev_next()->render_adjacent_link( $attr, false );
	}

	/**
	 * Return the next post title
	 *
	 * @return string
	 */
	public static function render_next_title( $attr ) {
		return thrive_prev_next()->render_adjacent_title( $attr, false );
	}

	/**
	 * Return the previous post title
	 *
	 * @return string
	 */
	public static function render_previous_title( $attr ) {
		return thrive_prev_next()->render_adjacent_title( $attr );
	}

	/**
	 * Return post excerpt
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render_post_excerpt( $attr = [] ) {
		$is_inline = ! isset( $attr['inline'] ) || ! empty( $attr['inline'] );

		if ( $is_inline ) {
			/* use a generic priority just so we can use it to remove the hook after */
			add_filter( 'excerpt_more', '__return_empty_string', 3141592 );
		}

		if ( empty( $attr['hide'] ) || has_excerpt() ) {
			$excerpt = TCB_Post_List_Shortcodes::shortcode_function_content( 'the_excerpt' );
		} else {
			$excerpt = '';
		}

		if ( $is_inline ) {
			$excerpt = wp_strip_all_tags( $excerpt );

			remove_filter( 'excerpt_more', '__return_empty_string', 3141592 );
		}

		return $excerpt;
	}

	/**
	 * Shortcode for previous column
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function render_previous_column( $attr = [], $content = '' ) {
		$attr['data-shortcode'] = 'thrive_prev_column';

		return thrive_prev_next()->render_adjacent_column( $content, $attr );
	}

	/**
	 * Shortcode for next column
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function render_next_column( $attr = [], $content = '' ) {
		$attr['data-shortcode'] = 'thrive_next_column';

		return thrive_prev_next()->render_adjacent_column( $content, $attr, false );
	}
}

/**
 * Return Thrive_Shortcodes instance
 *
 * @return null|Thrive_Shortcodes
 */
function thrive_shortcodes() {
	return Thrive_Shortcodes::instance();
}

new Thrive_Shortcodes();
