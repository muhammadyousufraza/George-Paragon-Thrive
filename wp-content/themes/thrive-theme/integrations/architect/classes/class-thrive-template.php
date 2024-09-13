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
 * Class Thrive_Template
 *
 * @property int    $default
 * @property int    $layout
 * @property string $structure
 * @property array  $comments
 * @property array  $style
 * @property array  $sections
 * @property array  icons
 * @property string $format
 * @property string $tag
 * @property string $primary_template
 * @property string $secondary_template
 * @property string $variable_template
 */
class Thrive_Template {

	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Use the shortcuts for post meta setters and getters
	 */
	use Thrive_Post_Meta;

	use Thrive_Belongs_To_Skin;

	/**
	 * the $post object of the template
	 *
	 * @var array|null|stdClass|WP_Post
	 */
	public $post;

	/**
	 * template id
	 *
	 * @var int
	 */
	public $ID;

	/**
	 * Content of the template
	 *
	 * @var null
	 */
	private $_html;

	/**
	 * Meta fields used by the template and their default values
	 *
	 * @var array
	 */
	public static $meta_fields
		= [
			'default'                 => 0,
			'layout'                  => 0,
			'layout_data'             => [],
			'structure'               => '',
			'sidebar-type'            => '',
			'sticky-sidebar'          => [],
			'off-screen-sidebar'      => '',
			'tve_globals'             => [
				'js_sdk'            => [],
				'fb_comment_admins' => '',
			],
			'comments'                => [
				'labels' => [],
				'icons'  => [],
			],
			'sections'                => [],
			'icons'                   => [],
			'style'                   => Thrive_Css_Helper::DEFAULT_STYLE_ARRAY,
			'format'                  => THRIVE_STANDARD_POST_FORMAT,
			'tag'                     => '',
			'no_search_results'       => '',
			THRIVE_PRIMARY_TEMPLATE   => '',
			THRIVE_SECONDARY_TEMPLATE => '',
			THRIVE_VARIABLE_TEMPLATE  => '',
		];

	/**
	 * 'You get what you give'
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		/* either we're looking for a field from the post */
		if ( isset( $this->post, $name ) ) {
			$value = $this->post->$name;
		} else {
			/* or we're looking for a meta field */
			$value = $this->meta( $name );
		}

		return $value;
	}

	/**
	 * Template name
	 *
	 * @return string
	 */
	public function __toString() {

		$primary_template   = $this->get_primary();
		$secondary_template = $this->get_secondary();

		switch ( $primary_template ) {
			case THRIVE_SINGULAR_TEMPLATE:
				$string = empty( $secondary_template ) ? THRIVE_POST_TEMPLATE : $secondary_template;
				break;

			case THRIVE_ARCHIVE_TEMPLATE:
				$string = empty( $secondary_template ) ? THRIVE_CATEGORY_TEMPLATE : $secondary_template;
				break;

			case THRIVE_HOMEPAGE_TEMPLATE:
				$string = THRIVE_BLOG_TEMPLATE;
				break;

			default:
				$string = $primary_template;
		}

		return ucfirst( $string );
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value ) {
		if ( strpos( $name, 'meta_' ) !== false ) {
			$this->set_meta( str_replace( 'meta_', '', $name ), $value );
		} else {
			$this->$name = $value;
		}
	}

	/**
	 * Isset method
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return isset( $this->$name );
	}

	/**
	 * Thrive_Template constructor.
	 *
	 * @param int $id template id
	 */
	public function __construct( $id = 0 ) {

		/* on the editor page, we load the current template and the query string will make sure to display everything nice */
		if ( empty( $id ) ) {
			if ( Thrive_Utils::is_theme_template() ) {
				$id = (int) get_the_ID();
			} elseif ( Thrive_Utils::is_preview() ) {
				$id = Thrive_Utils::inner_frame_id();
			} else {
				$id = Thrive_Utils::is_inner_frame() ? Thrive_Utils::inner_frame_id() : 0;
			}
		}

		/* we can't read template id on the admin page */
		if ( empty( $id ) && is_admin() ) {
			$this->ID = 0;
		} else {
			$this->post = empty( $id ) ? $this->get() : get_post( $id );

			if ( $this->post !== null ) {
				$this->ID = $this->post->ID;
			}
		}
	}

	/**
	 * Render Template for the current page
	 */
	public function render() {

		/**
		 * Action fired right before rendering any part of the content
		 *
		 * @param Thrive_Template $instance
		 */
		do_action( 'theme_template_before_render', $this );

		/* html tag, head and open the body tag */
		$this->header();

		do_action( 'theme_after_body_open' );

		do_action( 'wp_body_open' );

		/* template content */
		echo $this->template();

		do_action( 'theme_before_body_close' );

		/* call wp_footer and close body and html tag */
		$this->footer();
	}

	/**
	 * Render theme header
	 */
	public function header() {
		if ( Thrive_Theme::is_active() ) {
			get_header();
		} else {
			include apply_filters( 'thrive_theme_header_path', THEME_PATH . '/header.php', $this );
		}
	}

	/**
	 * Render theme footer
	 */
	public function footer() {
		if ( Thrive_Theme::is_active() ) {
			get_footer();
		} else {
			include apply_filters( 'thrive_theme_footer_path', THEME_PATH . '/footer.php', $this );
		}
	}

	/**
	 * Template content;
	 */
	public function template() {

		/**
		 * Allow other plugins to manipulate the content
		 *
		 * @param string $this ->_html
		 * @param        $this
		 */
		$this->_html = apply_filters( 'thrive_theme_template_content', $this->_html, $this );

		if ( empty( $this->_html ) ) {

			if ( is_singular() && apply_filters( 'thrive_theme_do_the_post', true ) ) {
				/* we never run the_post action, where a usual theme would do this before rendering their page.
				 * calling this before displaying the template so we have all the data in place.
				 */
				the_post();
			}

			/* action done before starting to render the template */
			do_action( 'before_theme_builder_template_render', $this->ID );

			$progress  = $this->render_progress_bar();
			$header    = $this->render_theme_hf_section( THRIVE_HEADER_SECTION );
			$footer    = $this->render_theme_hf_section( THRIVE_FOOTER_SECTION );
			$structure = $this->structure();

			/* add .tcb-style-wrap class to the #wrapper div instead of <body> */
			$wrapper_class = [
				Thrive_Utils::is_inner_frame() ? THRIVE_WRAPPER_CLASS : '',
				'tcb-style-wrap',
			];
			/* wrap all content so we know what to save */
			$this->_html = TCB_Utils::wrap_content( $progress . $header . $structure . $footer, 'div', 'wrapper', $wrapper_class );

			/* action called after the template has been rendered */
			do_action( 'after_theme_builder_template_render', $this->ID );
		}

		/* run do shortcode one more time just in case some more shortcodes have been added after the first run of the function */

		return do_shortcode( $this->_html );
	}

	/**
	 * Render html for progress bar
	 *
	 * @return string
	 */
	public function render_progress_bar() {
		$content = '';

		/* We do not show the progress bar in our iframes - branding, wizard ... */
		if ( ! Thrive_Utils::is_iframe() && $this->is_singular() ) {
			$attr    = [
				'max'           => 100,
				'value'         => 0,
				'data-selector' => '.thrive-progress-bar',
			];
			$class   = [ 'thrive-progress-bar' ];
			$globals = $this->meta( 'tve_globals' );

			if ( ! empty( $globals['progress_bar'] ) ) {
				$attr['data-position'] = $globals['progress_bar'];
			}

			if ( is_editor_page_raw() ) {
				$class[]       = 'show';
				$attr['value'] = 100;
			}

			if ( ! empty( $attr['data-position'] ) || is_editor_page_raw( true ) ) {
				/* on front, add the element only if it's enabled */
				$content = TCB_Utils::wrap_content( '', 'progress', 'thrive-progress-bar', $class, $attr );
			}
		}

		return $content;
	}

	/**
	 * Return the structure of the current template
	 *
	 * @param bool $do_shortcodes
	 *
	 * @return mixed|string
	 */
	private function structure( $do_shortcodes = true ) {
		/**
		 * Allows dynamically hiding page content (structure)
		 *
		 * @param boolean $render whether or not to render it
		 */
		if ( apply_filters( 'thrive_template_render_structure', true ) === false ) {
			return '';
		}

		$structure = $this->meta( 'structure' );

		if ( empty( $structure ) ) {
			$structure = Thrive_Utils::return_part( '/inc/templates/default/layout.php', [], false );
		}

		if ( $do_shortcodes ) {
			$structure = do_shortcode( $structure );

		}

		/**
		 * Allows filtering the structure part of a template (top_section + content + bottom section)
		 *
		 * @param string          $structure
		 * @param Thrive_Template $this
		 *
		 * @return string filtered html
		 */
		return apply_filters( 'thrive_template_structure', $structure, $this );
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	public function render_theme_hf_section( $type ) {
		/* accepted symbol sections */
		if ( ! in_array( $type, [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ], true ) ) {
			return '';
		}
		/**
		 * Allows dynamically hiding the header / footer on a page
		 *
		 * @param boolean $render whether or not to render it
		 */
		if ( apply_filters( "thrive_template_render_{$type}", true ) === true ) {
			$hf_data = $this->get_section( $type );

			/**
			 * Allows dynamic render of a different header / footer
			 *
			 * @param array  $hf_data header / footer data
			 * @param string $type    section type
			 *
			 * @return string
			 */
			$hf_data = apply_filters( 'thrive_hf_section', $hf_data, $type );

			$content = $this->get_hf_section_instance( $type, $hf_data )->render();
		} else {
			$content = '';
		}

		/**
		 * Allows dynamically placing content before the section
		 *
		 * @param string $content html content to add
		 */
		$before = apply_filters( "thrive_template_{$type}_before", '' );

		/**
		 * Allows others to modify header/footer content
		 *
		 * @param string $content
		 */
		$content = apply_filters( "thrive_template_{$type}_content", $content );

		/**
		 * Allows dynamically placing content after the section
		 *
		 * @param string $content html content to add
		 */
		$after = apply_filters( "thrive_template_{$type}_after", '' );

		return $before . $content . $after;
	}

	/**
	 * Return an instance for a header / footer section
	 *
	 * @param string $type
	 * @param array  $hf_data
	 *
	 * @return Thrive_HF_Section
	 */
	public function get_hf_section_instance( $type, $hf_data = [] ) {
		$section = empty( $hf_data ) ? $this->get_section( $type ) : $hf_data;

		return new Thrive_HF_Section( $section['id'], $type, $section, $this->ID );
	}

	/**
	 * Return template title
	 *
	 * @return string
	 */
	public function title() {
		return $this->post_title;
	}

	/**
	 * Get template post meta field
	 *
	 * @param $key
	 * @param $use_default_if_empty
	 *
	 * @return mixed
	 */
	public function meta( $key, $use_default_if_empty = false ) {
		if ( empty( $this->meta[ $key ] ) ) {
			$this->meta[ $key ] = get_post_meta( $this->ID, $key, true );
		}

		if ( $use_default_if_empty && isset( static::$meta_fields[ $key ] ) && $this->meta[ $key ] === false ) {
			$this->meta[ $key ] = static::$meta_fields[ $key ];
		}

		return $this->meta[ $key ];
	}

	/**
	 * Decide what template are we going to use for displaying the current page we're on.
	 *
	 * @return WP_Post|null
	 */
	protected function get() {

		$template_meta = Thrive_Utils::localize_url();

		$is_singular = is_singular();

		$args = [
			'posts_per_page' => 1,
			'post_type'      => THRIVE_TEMPLATE,
			'tax_query'      => [ thrive_skin()->build_skin_query_params() ],
			'order'          => 'ASC',
			'orderby'        => 'ID',
			'meta_query'     => [
				[
					'key'   => 'default',
					'value' => '1',
				],
				[
					'key'   => THRIVE_PRIMARY_TEMPLATE,
					'value' => $template_meta[ THRIVE_PRIMARY_TEMPLATE ],
				],
				[
					'key'   => THRIVE_SECONDARY_TEMPLATE,
					'value' => $template_meta[ THRIVE_SECONDARY_TEMPLATE ],
				],
				[
					'key'   => THRIVE_VARIABLE_TEMPLATE,
					'value' => $is_singular ? '' : $template_meta[ THRIVE_VARIABLE_TEMPLATE ],
				],
			],
		];

		if ( $is_singular ) {

			$templates = Thrive_Utils::get_page_custom_templates();

			if ( empty( $templates ) ) {

				if ( $template_meta[ THRIVE_SECONDARY_TEMPLATE ] === THRIVE_POST_TEMPLATE ) {

					$format = get_post_format();

					if ( empty( $format ) ) {
						/* default post format is the standard one */
						$format = THRIVE_STANDARD_POST_FORMAT;
					}

					$args['meta_query'][] = [
						'key'   => 'format',
						'value' => $format,
					];
				}
				/**
				 * Filter the get_posts() arguments for singular templates.
				 * This is useful when we need a custom template format for a custom post type
				 *
				 * @param array           $args     arguments array to filter
				 * @param Thrive_Template $template template instance
				 */
				$args = apply_filters( 'thrive_template_singular_args', $args, $this );

				/* at this moment we're looking for a very specific template, the one for a post type and with a specific post format. let's hope we're lucky. */
				$templates = get_posts( $args );

				if ( empty( $templates ) ) {
					$args['meta_query'] = array_filter( $args['meta_query'], static function ( $arg ) {
						return $arg['key'] !== 'format';
					} );
				}
			}
		} else {
			$templates = get_posts( $args );

			/* each search can return either empty or just the result that we're looking for, because there's only one perfect match */
			if ( empty( $templates ) ) {
				/* in case we don't find the very specific template with id, we remove it and we search for a template specified just by type */
				$args['meta_query'][3]['value'] = '';

				$templates = get_posts( $args );

				if ( empty( $templates ) ) {
					/* this will return the most general archive of that type template */
					$args['meta_query'][2]['value'] = '';

					if ( $template_meta[ THRIVE_PRIMARY_TEMPLATE ] !== THRIVE_ARCHIVE_TEMPLATE ) {
						/* search for templates only other than default archive.
						 * use case: for a post type (like product) - we might want to redirect taxonomy archives to post type archive and not to the general archive
						 */
						$templates = get_posts( $args );
					}
				}
			}
		}

		$templates = apply_filters( 'thrive_theme_default_templates', $templates, $args, $template_meta );

		/* if we didn't find any templates, try to get something more general */
		if ( empty( $templates ) ) {
			/* if this is a homepage, set primary to singular */
			if ( $template_meta[ THRIVE_PRIMARY_TEMPLATE ] === THRIVE_HOMEPAGE_TEMPLATE ) {
				$args['meta_query'][1]['value'] = THRIVE_SINGULAR_TEMPLATE;
			} elseif ( $is_singular ) {
				/* if this is singular, get the post template */
				$args['meta_query'][2]['value'] = THRIVE_POST_TEMPLATE;
			} else {
				/* if this is an archive, set primary to archive */
				$args['meta_query'][1]['value'] = THRIVE_ARCHIVE_TEMPLATE;
			}

			$templates = get_posts( $args );
		}

		/* We keep this just to be very very very sure */

		return empty( $templates ) ? null : array_pop( $templates );
	}

	/**
	 * Return template url depending on the type and id. If there is content saved in the cookie, load the URL for it instead.
	 *
	 * @param $get_content_from_cookie
	 *
	 * @return string
	 */
	public function url( $get_content_from_cookie = false ) {
		$primary_template   = $this->get_primary();
		$secondary_template = $this->get_secondary();
		$variable_template  = $this->get_variable();

		/* if the $get_content_from_cookie flag is active, get the content meta from cookies */
		if ( $get_content_from_cookie ) {
			/* overwrite the templates with the templates stored in the cookie */
			list( $primary_template, $secondary_template, $variable_template ) = thrive_content_switch()->get_existing_data( $this );
		}

		/**
		 * Filter template url from outside, maybe for custom posts
		 *
		 * @param Thrive_Template $this
		 * @param String          $primary_template
		 * @param String          $secondary_template
		 * @param String          $variable_template
		 */
		$url = apply_filters( 'thrive_theme_template_url', '', $this, $primary_template, $secondary_template, $variable_template );

		if ( empty( $url ) ) {

			switch ( $primary_template ) {
				case THRIVE_HOMEPAGE_TEMPLATE:
					if ( $secondary_template === THRIVE_BLOG_TEMPLATE ) {
						$post_type = THRIVE_POST_TEMPLATE;
						/* if 'show on front' is set to 'page' but we don't have a specific page for blog, we display demo content */
						if ( get_option( 'show_on_front' ) === 'page' && empty( get_option( 'page_for_posts' ) ) ) {
							$post_type = Thrive_Demo_Content::POST_TYPE;
						}

						$url = get_post_type_archive_link( $post_type );
					} else {
						$url = get_home_url();
					}

					break;

				case THRIVE_ARCHIVE_TEMPLATE:
					/* Check if secondary is a custom post type. We should load the list for that specific post type in that case */
					if ( post_type_exists( $secondary_template ) && array_key_exists( $secondary_template, Thrive_Utils::get_content_types() ) ) {
						$posts = get_posts( [
							'post_type'   => $secondary_template,
							'numberposts' => 1,
							'post_status' => 'publish',
						] );

						/* If we have posts we can show the post type archive page */
						$url = count( $posts ) ? get_post_type_archive_link( $secondary_template ) : Thrive_Demo_Content::url();
					} else {
						switch ( $secondary_template ) {
							case 'author':
								if ( empty( $variable_template ) ) {
									$users = get_users(
										[
											'orderby' => 'id',
											'order'   => 'ASC',
											'number'  => 1,
											'echo'    => false,
											'html'    => false,
										]
									);

									$variable_template = empty( $users ) ? 1 : $users[0]->ID;
								}

								$url = get_author_posts_url( $variable_template );
								break;

							case 'date':
								/* for Date, if we have a stored $variable_template, load it as an URL */
								$url = empty( $variable_template ) ? get_year_link( gmdate( 'Y' ) ) : $variable_template;
								break;

							case 'category':
							case 'post_tag':
							default:
								$secondary_template = empty( $secondary_template ) ? 'category' : $secondary_template;

								if ( empty( $variable_template ) ) {

									$all = get_terms(
										[
											'taxonomy'   => $secondary_template,
											'hide_empty' => false,
											'orderby'    => 'count',
											'order'      => 'DESC',
											'number'     => 1,
										]
									);

									$variable_template = empty( $all ) || is_wp_error( $all ) ? 0 : $all[0]->term_id;
								}

								$term = get_term_by( 'id', $variable_template, $secondary_template );

								$url = get_term_link( $term );
						}
					}
					break;

				case THRIVE_SEARCH_TEMPLATE:
					$search_term = empty( $variable_template ) ? 'a' : $variable_template;
					$url         = add_query_arg( [
						's'                => $search_term,
						'tcb_sf_post_type' => 'post',
					], home_url( '/' ) );
					break;

				case THRIVE_ERROR404_TEMPLATE:
					/* generate random link so we can fake a 404 page */
					$permalink_structure = get_option( 'permalink_structure' );

					/* When the permalink setting it's plain we need a different kind of url params*/
					$url_params = empty( $permalink_structure ) ? '?p=-1' : '/' . md5( time() );

					$url = site_url() . $url_params;
					break;

				case THRIVE_SINGULAR_TEMPLATE:
					$from_tar = Thrive_Utils::from_tar();
					if ( empty( $variable_template ) || $from_tar || get_post_status( $variable_template ) !== 'publish' || tve_post_is_landing_page( $variable_template ) ) {
						$args = [
							'posts_per_page' => 1,
							'post_status'    => [ 'draft', 'publish' ],
							'exclude'        => [
								get_option( 'page_on_front', 0 ),
								get_option( 'page_for_posts', 0 ),
								Thrive_Defaults::get_default_post_id( 'blog' ),
							],
						];

						if ( ! empty( $secondary_template ) ) {
							/* If the user comes from tar we need to take into account the post type of the source post */
							$args['post_type'] = $from_tar ? get_post_type( $from_tar ) : $secondary_template;

							/* filter out the landing pages with a meta_query  */
							/* double array is needed for meta_query */
							$args['meta_query'] = Thrive_Utils::meta_query_no_landing_pages();
						}

						/* if this is a post template, make sure that we load posts only for the current format */
						if ( ( empty( $secondary_template ) || $secondary_template === THRIVE_POST_TEMPLATE ) && ! $from_tar ) {
							$args['tax_query'] = Thrive_Utils::get_post_format_tax_query( $this->meta( 'format' ) );
						}

						/* When the user is redirected from tar to the template we need to load the post from which the redirect was made in the first place */
						if ( $from_tar ) {
							$args['include'] = [ $from_tar ];
						}

						/**
						 * Filter the arguments before getting the content ( page / post ) to show inside the template
						 */
						$args = Thrive_Utils::filter_default_get_posts_args( $args, 'template_iframe' );

						$posts = get_posts( $args );

						if ( empty( $posts ) ) {
							/* if there are no posts, fetch a demo content post */
							$posts = get_posts( [
								'order'          => 'ASC',
								'posts_per_page' => 1,
								'post_status'    => 'publish',
								'post_type'      => Thrive_Demo_Content::POST_TYPE,
							] );

							/* if there are no generated demo content posts, generate one */
							if ( empty( $posts ) ) {
								$one_post = Thrive_Demo_Content::get_one( $args );

								if ( $one_post !== null ) {
									$id = $one_post->ID;
								}
							}
						}

						if ( empty( $id ) ) {
							if ( empty( $posts ) ) {
								$id = '';
							} else {
								/* the post that is displayed by default is the latest post */
								$id = $posts[0]->ID;
							}
						}

						$variable_template = $id;
					}

					$url = get_permalink( $variable_template );
					break;

				default:
					$url = '';
			}
		}

		return empty( $url ) || is_wp_error( $url ) ? site_url() : $url;
	}

	/**
	 * Update template data
	 *
	 * @param $data
	 */
	public function update( $data ) {

		if ( ! empty( $data['style']['fonts'] ) && is_array( $data['style']['fonts'] ) ) {
			/* make sure fonts are optimized before save */
			$data['style']['fonts'] = TCB_Utils::merge_google_fonts( $data['style']['fonts'] );
		}

		/* get all the default meta keys we have */
		foreach ( static::$meta_fields as $meta_key => $default_value ) {
			/* if we pass one of those keys */
			if ( isset( $data[ $meta_key ] ) ) {
				/* and finally update the meta field */
				$this->set_meta( $meta_key, $data[ $meta_key ] );
			}
		}

		if ( $this->is_default() ) {
			thrive_skin()->generate_style_file();
		}
	}

	/**
	 * Reset template to it's default structure without any custom css
	 */
	public function reset() {
		foreach ( static::$meta_fields as $meta_key => $default_value ) {
			switch ( $meta_key ) {
				case THRIVE_PRIMARY_TEMPLATE:
				case THRIVE_SECONDARY_TEMPLATE:
				case THRIVE_VARIABLE_TEMPLATE:
				case 'format':
				case 'default':
				case 'tag':
				case 'layout':
					/* we don't reset/change those meta fields, they should remain the same as when created */
					break;
				case THRIVE_HEADER_SECTION:
				case THRIVE_FOOTER_SECTION:
					$default_value = call_user_func_array( [ thrive_skin(), 'get_default_' . $meta_key ], [] );

					$this->set_meta( $meta_key, $default_value );
					break;
				default:
					$this->set_meta( $meta_key, $default_value );
					break;
			}
		}

		/* reset the thumbnail */
		TCB_Utils::save_thumbnail_data( $this->ID, [] );

		if ( $this->is_default() ) {
			thrive_skin()->generate_style_file();
		}
	}


	/**
	 * Return template style
	 *
	 * @param bool $wrap
	 *
	 * @return string
	 */
	public function style( $wrap = true ) {
		/* include fonts only if not already included from TAr */
		$include_fonts = ! tcb_should_print_unified_styles();

		/**
		 * The filter allows dynamically modifying the CSS style before outputting / saving it to the template file
		 *
		 * @param string $style full <style> node
		 * @param int    $id    current template id
		 *
		 * @return string filtered style
		 */
		return apply_filters( 'thrive_template_style',
			thrive_css_helper( $this )
				->generate_style( false, false, $include_fonts )
				->maybe_wrap( $wrap ? 'thrive-template' : false ),
			$this->ID
		);
	}

	/**
	 * Get the active global colors of the current template, along with the colors used in the current template's header and footer.
	 *
	 * @param array $all_global_colors
	 *
	 * @return array
	 */
	public function get_global_colors( $all_global_colors ) {
		$template_global_colors = [];
		$template_css           = '';

		/* get the css from the templates and stringify it (we only need to find potential color prefixes in it) */
		$style = $this->get_meta( 'style' );

		$template_css .= json_encode( $style );

		/* get the header and footer id assigned to the template */
		$header_id = $this->get_meta( THRIVE_HEADER_SECTION );
		$footer_id = $this->get_meta( THRIVE_FOOTER_SECTION );

		/* concatenate the template css to the styles of the header and footer that belong to the template */
		$template_css .= get_post_meta( $header_id, 'tve_custom_css', true );
		$template_css .= get_post_meta( $footer_id, 'tve_custom_css', true );

		/* if a global color is used in the theme templates or in their headers and footers, add it to the array */
		foreach ( $all_global_colors as $color ) {
			if ( $color['active'] === 1 && strpos( $template_css, 'var(--tcb-color-' . $color['id'] ) !== false ) {
				$template_global_colors[ $color['id'] ] = $color;
			}
		}

		return $template_global_colors;
	}

	/**
	 * Render template dynamic css.
	 *
	 * @return string
	 */
	public function dynamic_style() {
		return thrive_css_helper( $this )
			->generate_dynamic_style()
			->maybe_wrap( 'thrive-dynamic' );
	}

	/**
	 * Copy data from another template to the current one
	 *
	 * @param $template_id
	 */
	public function copy_data_from( $template_id ) {
		if ( ! empty( $template_id ) && $this->ID !== (int) $template_id ) {
			$copy_from = new Thrive_Template( $template_id );

			$old_body_class     = $copy_from->body_class( false, 'string', true );
			$current_body_class = $this->body_class( false, 'string', true );

			foreach ( static::$meta_fields as $field => $default ) {
				if ( in_array( $field, [ 'default', 'format', 'tag', THRIVE_PRIMARY_TEMPLATE, THRIVE_SECONDARY_TEMPLATE, THRIVE_VARIABLE_TEMPLATE ] ) ) {
					continue;
				}

				$value = $copy_from->meta( $field );

				/* replace old body class with the new one */
				$value = json_decode( str_replace( $old_body_class, $current_body_class, json_encode( $value ) ), true );

				$this->set_meta( $field, $value );
			}

			$thumbnail = $copy_from->thumbnail();

			if ( ! empty( $thumbnail ) ) {
				$this->copy_thumbnail( $template_id, $copy_from->thumbnail() );
			}

			/**
			 * Trigger an action after we finished copying the data
			 *
			 * @param Thrive_Template $this
			 * @param Thrive_Template $copy_from
			 */
			do_action( 'thrive_theme_template_copied_data', $this, $copy_from );
		}
	}

	/**
	 * Copy thumbnail data to this template - manually copy the file, change the file name to fit the new template ID, and change the URL.
	 *
	 * @param $template_id_to_copy
	 * @param $thumb_data
	 */
	public function copy_thumbnail( $template_id_to_copy, $thumb_data ) {
		$copy_from_file = TCB_Utils::get_uploads_path( THEME_UPLOADS_PREVIEW_SUB_DIR . '/' . $template_id_to_copy . '.png' );

		/* copy the file and rename using the new ID */
		copy( $copy_from_file, str_replace( basename( $copy_from_file ), $this->ID . '.png', $copy_from_file ) );

		$thumb_data['url'] = str_replace( basename( $thumb_data['url'] ), $this->ID . '.png', $thumb_data['url'] );

		TCB_Utils::save_thumbnail_data( $this->ID, $thumb_data );
	}

	/**
	 * Get icon svg saved from template
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_icon_svg( $name ) {
		$icons = $this->meta( 'icons', true );

		return empty( $icons[ $name ] ) ? Thrive_Utils::load_default_icons( $name ) : $icons[ $name ];
	}

	/**
	 * Get comments data from meta
	 *
	 * @return array|mixed
	 */
	public function comments_meta() {
		return $this->meta( 'comments', true );
	}

	/**
	 *  Return body class based on what template we're editing
	 *
	 * @param bool   $full
	 * @param string $return_type
	 * @param bool   $with_dot
	 *
	 * @return array|string
	 */
	public function body_class( $full = false, $return_type = 'array', $with_dot = true ) {
		$body_class = [];

		/**
		 * Whether or not theme classes should be set on body tag
		 *
		 * @param boolean allow
		 */
		if ( apply_filters( 'thrive_theme_allow_body_class', true ) ) {
			$class_array = [];

			$class_array[] = 'tve-theme-' . $this->ID;

			if ( $full ) {
				$layout_class = thrive_layout()->body_class();
				if ( ! empty( $layout_class ) ) {
					$class_array[] = $layout_class;
				}

				if ( thrive_post()->should_hide_dynamic_featured_images() ) {
					$class_array[] = 'hide-dynamic-content';
				}

				if ( thrive_post()->should_hide_inline_post_titles() ) {
					$class_array[] = 'hide-inline-post-titles';
				}

				$class_array = array_merge( $class_array, thrive_prev_next()->prev_next_classes() );
			}

			if ( $return_type === 'string' ) {
				$glue = empty( $with_dot ) ? ' ' : '.';

				$body_class = trim( $glue . implode( $glue, $class_array ) );
			} else {
				$body_class = $class_array;
			}
		}

		return $body_class;
	}

	/**
	 * @param null $meta_fields
	 *
	 * get template data that will be used for export
	 *
	 * @return array
	 */
	public function export( $meta_fields = null ) {

		$data = [
			'ID'          => $this->ID,
			'post_title'  => $this->post_title,
			'post_type'   => THRIVE_TEMPLATE,
			'meta_input'  => [],
			'post_status' => 'publish',
		];

		if ( ! is_array( $meta_fields ) ) {
			$meta_fields = static::$meta_fields;
		}
		foreach ( $meta_fields as $field => $value ) {
			$data['meta_input'][ $field ] = $this->meta( $field );
		}

		$data['variable_label'] = $this->get_variable_label();

		return apply_filters( 'theme_template_export', $data );
	}

	/**
	 * For templates that need intensive queries to get the label, calculate it here.
	 * Example: custom author archive template. Instead of localizing all users, just get the author assigned to the template.
	 * Same with taxonomy terms
	 *
	 * @return string
	 */
	public function get_variable_label() {
		$label = '';

		if ( $this->is_archive() && $this->get_variable() ) {
			switch ( true ) {
				case THRIVE_AUTHOR_ARCHIVE_TEMPLATE === $this->secondary_template:
					$author = get_user_by( 'ID', (int) $this->get_variable() );
					if ( $author ) {
						$label = $author->display_name;
					}
					break;
				case taxonomy_exists( $this->secondary_template ):
					$term = get_term( (int) $this->get_variable() );
					if ( $term ) {
						$label = $term->name;
					}
					break;
				default:
					break;
			}
		}

		return $label;
	}

	/**
	 * Return the default values for a template
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function default_values( $args = [] ) {
		$default_meta = static::$meta_fields;

		if ( empty( $args['meta_input'] ) ) {
			$args['meta_input'] = $default_meta;
		} else {
			$args['meta_input'] = array_merge( $default_meta, $args['meta_input'] );
		}
		$default_header_id = thrive_skin()->get_default_data( THRIVE_HEADER_SECTION );
		$default_footer_id = thrive_skin()->get_default_data( THRIVE_FOOTER_SECTION );
		if ( $default_header_id ) {
			$args['meta_input']['sections'][ THRIVE_HEADER_SECTION ] = [
				'id'                   => $default_header_id,
				'content'              => '',
				'inherit_content_size' => 1,
			];
		}
		if ( $default_footer_id ) {
			$args['meta_input']['sections'][ THRIVE_FOOTER_SECTION ] = [
				'id'                   => $default_footer_id,
				'content'              => '',
				'inherit_content_size' => 1,
			];
		}

		$args = array_merge( [
			'post_title'  => '',
			'post_type'   => THRIVE_TEMPLATE,
			'post_status' => 'publish',
		], $args );

		return apply_filters( 'thrive_template_default_values', $args );
	}

	/**
	 * Edit url for the current template
	 *
	 * @return string
	 */
	public function edit_url() {
		return tcb_get_editor_url( $this->ID );
	}

	/**
	 * Preview url for the current template
	 *
	 * @return string
	 */
	public function preview_url() {
		return add_query_arg(
			[
				THRIVE_PREVIEW_FLAG => 'true',
				THRIVE_THEME_FLAG   => $this->ID,
			],
			$this->url()
		);
	}

	/**
	 * @return array|mixed
	 */
	public function thumbnail() {
		$thumb = TCB_Utils::get_thumbnail_data_from_id( $this->ID );
		if ( ! empty( $thumb['url'] ) ) {
			$thumb['url'] = Thrive_Utils::ensure_https( $thumb['url'] );
		}

		return $thumb;
	}

	/**
	 * Localize templates for dashboard display
	 *
	 * @param int $skin_id
	 *
	 * @return array
	 */
	public static function localize_all( $skin_id = 0 ) {

		$skin = thrive_skin( $skin_id );

		$templates = [];

		$posts = get_posts(
			[
				'post_type'      => THRIVE_TEMPLATE,
				'posts_per_page' => - 1,
				'tax_query'      => [ $skin->build_skin_query_params() ],
			]
		);

		foreach ( $posts as $post ) {

			$template = new static( $post->ID );

			$template->set_cached_skin_id( $skin->ID );

			$data = $template->export();

			/* too much data that we don't need */
			unset( $data['meta_input']['structure'], $data['meta_input']['sections'], $data['meta_input']['style'], $data['meta_input']['icons'] );

			$data['layout']      = get_the_title( $template->get_layout() );
			$data['edit_url']    = tcb_get_editor_url( $post->ID );
			$data['preview_url'] = $template->preview_url();
			$data['thumbnail']   = $template->thumbnail();

			$templates[] = $data;
		}

		/**
		 * Filter templates before localize
		 *
		 * @param array $templates
		 *
		 * @return array
		 */
		return apply_filters( 'thrive_theme_templates_localize', $templates );
	}

	/**
	 * Check if a template is a singular template
	 *
	 * @return bool
	 */
	public function is_singular() {
		return $this->get_primary() === THRIVE_SINGULAR_TEMPLATE || ( $this->is_home() && $this->get_secondary() === THRIVE_PAGE_TEMPLATE );
	}

	/**
	 * Check if the current template is a 404 one
	 *
	 * @return bool
	 */
	public function is404() {
		return $this->get_primary() === THRIVE_ERROR404_TEMPLATE;
	}

	/**
	 * Check if current template is an archive one
	 *
	 * @return bool
	 */
	public function is_archive() {
		return $this->get_primary() === THRIVE_ARCHIVE_TEMPLATE;
	}

	/**
	 * Check if current template is an homepage
	 *
	 * @return bool
	 */
	public function is_home() {
		return $this->get_primary() === THRIVE_HOMEPAGE_TEMPLATE;
	}

	/**
	 * Check if the current template is for blog
	 *
	 * @return bool
	 */
	public function is_blog() {
		return $this->is_home() && $this->get_secondary() === THRIVE_BLOG_TEMPLATE;
	}

	/**
	 * Check if current template is a search one
	 *
	 * @return bool
	 */
	public function is_search() {
		return $this->get_primary() === THRIVE_SEARCH_TEMPLATE;
	}


	/**
	 * Get the primary meta attribute for this template
	 *
	 * @return mixed
	 */
	public function get_primary() {
		return $this->meta( THRIVE_PRIMARY_TEMPLATE );
	}

	/**
	 * Get the secondary meta attribute for this template
	 *
	 * @return mixed
	 */
	public function get_secondary() {
		return $this->meta( THRIVE_SECONDARY_TEMPLATE );
	}

	/**
	 * Get the variable meta attribute for this template
	 *
	 * @return mixed
	 */
	public function get_variable() {
		return $this->meta( THRIVE_VARIABLE_TEMPLATE );
	}

	/**
	 * Check if a template is the default one
	 *
	 * @return bool
	 */
	public function is_default() {
		return ! empty( $this->meta( 'default' ) );
	}

	/**
	 * It does what it says.
	 */
	public static function register_post_type() {
		register_post_type( THRIVE_TEMPLATE, [
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => Thrive_Theme_Product::has_access(),
			'query_var'           => false,
			'description'         => 'Thrive Template',
			'rewrite'             => false,
			'labels'              => [
				'name' => 'Thrive Template',
			],
			'_edit_link'          => 'post.php?post=%d',
			'show_in_rest'        => true,
		] );
	}

	/**
	 * Return the file with the specific layout for the editor
	 *
	 * @return string|null
	 */
	public function editor_layout() {
		$primary_template = $this->get_primary();

		$file = locate_template( $primary_template . '.php' );

		if ( ! is_file( $file ) ) {
			$file = null;
		}

		return $file;
	}

	/**
	 * Return current layout applied on the template
	 *
	 * @return mixed
	 */
	public function get_layout() {
		return $this->meta( 'layout' );
	}

	/**
	 * Does exactly what it says
	 *
	 * @param $type
	 *
	 * @return int|mixed
	 */
	public function get_section( $type = '' ) {
		$sections = $this->meta( 'sections' );

		return array_merge( [
			'id'      => 0,
			'content' => '',
			'type'    => $type,
		], empty( $sections[ $type ] ) ? [] : $sections[ $type ] );
	}

	/**
	 * Get an array with all the sections for this template.
	 *
	 * @param string $output - if we want objects or just ids
	 *
	 * @return array
	 */
	public function get_sections( $output = 'objects' ) {
		$sections          = [];
		$template_sections = $this->meta( 'sections' );

		if ( ! empty( $template_sections ) ) {
			foreach ( $template_sections as $type => $section ) {
				if ( ! empty( $section['id'] ) && ! in_array( $type, [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ] ) ) {
					if ( $output === 'ids' ) {
						$sections[] = (int) $section['id'];
					} else {
						$sections[] = new Thrive_Section( (int) $section['id'] );
					}
				}
			}
		}

		return $sections;
	}

	/**
	 * Generate an image preview for the template and return the url
	 *
	 * @return array
	 * @throws Exception
	 */
	public function create_preview() {
		$args = [
			'action'      => 'template_preview',
			/* crop the width to 300 through resizing */
			'crop_width'  => 300,
			/* crop the height to 300px ( but only after the resize )*/
			'crop_height' => 300,
		];

		return Thrive_Utils::create_preview( $this->ID, $args );
	}

	/**
	 * Get all the templates of the same type
	 *
	 * @param bool $only_default
	 *
	 * @return array|int[]|WP_Post[]
	 */
	public function get_similar_templates( $only_default = false ) {
		$args = [
			'post_type'    => THRIVE_TEMPLATE,
			'tax_query'    => [ thrive_skin()->build_skin_query_params() ],
			'order'        => 'ASC',
			'numberposts'  => - 1,
			'post__not_in' => [ $this->ID ],
			'meta_query'   => [
				[
					'key'   => THRIVE_PRIMARY_TEMPLATE,
					'value' => $this->get_primary(),
				],
				[
					'key'   => THRIVE_SECONDARY_TEMPLATE,
					'value' => $this->get_secondary(),
				],
				[
					'key'   => 'format',
					'value' => $this->meta( 'format' ),
				],
			],
		];

		/* Handle also the case when the template is not singular */
		if ( ! $this->is_singular() ) {
			$args['meta_query'][] = [
				'key'   => THRIVE_VARIABLE_TEMPLATE,
				'value' => $this->get_variable(),
			];
		}

		if ( $only_default ) {
			$args['meta_query'][] = [
				'key'   => 'default',
				'value' => 1,
			];
		}

		$templates = get_posts( $args );

		return empty( $templates ) ? [] : $templates;
	}

	/**
	 * loop through each section and get CSS imports for each of them
	 *
	 * @return array of @import statements
	 */
	public function get_css_imports() {
		/* template + sections */
		$imports = thrive_css_helper( $this )->get_css_imports();

		/* header / footer */
		foreach ( [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ] as $type ) {
			$id = $this->get_section( $type )['id'];
			if ( ! empty( $id ) && Thrive_HF_Section::is_valid( $id ) ) {
				$hf_section = new Thrive_HF_Section( $id, $type );

				$imports = array_merge( $imports, $hf_section->get_css_imports() );
			}
		}

		return $imports;
	}

	/**
	 * The location of the sidebar in this template. If no value is set for the template, get it from the layout
	 *
	 * @return bool
	 */
	public function has_sidebar_on_left() {
		$layout_data = $this->get_meta( 'layout_data' );

		if ( isset( $layout_data['sidebar_on_left'] ) ) {
			$has_sidebar_on_left = ! empty( $layout_data['sidebar_on_left'] );
		} else {
			$has_sidebar_on_left = thrive_layout()->has_sidebar_on_left();
		}

		return $has_sidebar_on_left;
	}

	/**
	 * The visibility of the sidebar in this template. If no value is set for the template, get it from the layout
	 *
	 * @return bool
	 */
	public function is_sidebar_visible() {
		$layout_data = $this->get_meta( 'layout_data' );

		if ( isset( $layout_data['hide_sidebar'] ) ) {
			$is_sidebar_visible = empty( $layout_data['hide_sidebar'] );
		} else {
			$is_sidebar_visible = thrive_layout()->is_sidebar_visible();
		}

		return $is_sidebar_visible;
	}

	/**
	 * Strip CSS from the template's css string. Also saves resulting CSS
	 *
	 * @param callable $callback function used to filter unwanted CSS. Gets called with one array parameter with keys `selector` and `css_text`
	 *
	 * @return $this
	 */
	public function filter_css( $callback ) {

		$style = $this->style;
		if ( ! empty( $style['css'] ) ) {
			foreach ( $style['css'] as $media => & $css ) {
				$css = Thrive_Css_Helper::build_string_from_rules( array_filter( Thrive_Css_Helper::get_rules_from_string( $css ), $callback ) );
			}
			unset( $css ); // makes sure this is not accidentally used after the foreach loop
		}

		$this->set_meta( 'style', $style );

		return $this;
	}

	/**
	 * Computes the no search result content
	 *
	 * @return string
	 */
	public function get_no_search_results_content() {
		$content = '';

		if ( $this->is_search() ) {
			$content = $this->get_meta( 'no_search_results' );

			if ( empty( $content ) ) {
				$content = Thrive_Utils::return_part( '/integrations/architect/views/backbone/theme-editor/no-results-default.php' );
			}

			if ( is_editor_page_raw( true ) ) {
				$content = str_replace( 'main-no-results', 'main-no-results tcb-permanently-hidden', $content );
			}
		}

		return $content;
	}

	/**
	 * Check sections from the template in case they have a blog query saved
	 *
	 * @return array
	 */
	public function get_list_query() {
		$list_query = [];

		if ( ! $this->is_singular() ) {
			$sections = $this->meta( 'sections' );
			if ( is_array( $sections ) ) {
				foreach ( $sections as $type => $data ) {
					if ( in_array( $type, [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ], true ) ) {
						continue;
					}

					$section = new Thrive_Section( $data['id'], array_merge( $data, [ 'type' => $type ] ) );
					$query   = $section->get_list_query();

					if ( ! empty( $query ) ) {
						$list_query = $query;
						break;
					}
				}
			}
		}

		return $list_query;
	}

	/**
	 * Check a specific meta key if it's stored in any of the sections
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function get_meta_from_sections( $key ) {
		$sections = $this->meta( 'sections' );

		$value = null;
		if ( is_array( $sections ) ) {
			foreach ( $sections as $type => $data ) {
				$section = new Thrive_Section( $data['id'], array_merge( $data, [ 'type' => $type ] ) );

				$section_value = $section->get_meta( $key );
				if ( ! empty( $section_value ) ) {
					$value = $section_value;
					break;
				}
			}
		}

		return $value;
	}

	/**
	 * Check template and sections for custom scripts to enqueue
	 */
	public function enqueue_global_scripts() {
		if ( function_exists( 'tve_check_post_for_scripts_to_enqueue' ) ) {
			tve_check_post_for_scripts_to_enqueue( $this->ID );

			foreach ( $this->get_sections( 'ids' ) as $section_id ) {
				tve_check_post_for_scripts_to_enqueue( $section_id );
			}
		}
	}

	/**
	 * Get sidebar visibility options for the current template and the logged in user
	 *
	 * @return array|mixed
	 */
	public function get_user_sidebar_visibility() {
		$visibility = [];

		if ( is_user_logged_in() ) {
			$visibility = get_user_option( 'sidebar_visibility', get_current_user_id() );
		}

		return empty( $visibility[ $this->ID ] ) ? [] : $visibility[ $this->ID ];
	}

	/**
	 * Check templates and sections if they have meta tags to add in head
	 */
	public function check_for_meta_tags() {
		tve_load_meta_tags( $this->ID );

		foreach ( $this->get_sections( 'ids' ) as $section_id ) {
			tve_load_meta_tags( $section_id );
		}
	}

	/**
	 * If the header/footer was set inside the wizard, make sure it's applied on this template
	 */
	public function assign_default_hf_from_wizard() {
		$wizard_data = thrive_skin()->get_meta( 'ttb_wizard' );

		if ( ! empty( $wizard_data['done'] ) ) {
			foreach ( [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ] as $hf_key ) {
				if ( in_array( $hf_key, $wizard_data['done'] ) ) {
					$this->set_header_footer( $hf_key, thrive_skin()->get_default_data( $hf_key ) );
				}
			}
		}
	}

	/**
	 * Set header or footer on the template
	 *
	 * @param string $type The type of the symbol
	 * @param int    $id   The id of the header or footer
	 */
	public function set_header_footer( $type, $id ) {

		/* If we don't have an id, we will try to take the default one from the skin defaults */
		if ( empty( $id ) ) {
			$id = thrive_skin()->get_default_data( $type );
		}

		/* We are setting the header / footer only if we have a valid id */
		if ( ! empty( $id ) ) {
			$sections                = $this->get_meta( 'sections' );
			$sections[ $type ]['id'] = $id;
			/* remove all static CSS for header / footer ( in the case where this was unlinked before ) */
			$this->filter_css( function ( $item ) use ( $type ) {
				return strpos( $item['selector'], ".tve-theme-{$this->ID} .thrv_{$type}" ) === false;
			} );

			$this->update( [
				'sections' => $sections,
			] );
		}
	}

	/**
	 * Make a template default
	 */
	public function make_default() {
		/* Take other templates of the same type and "remove" the default status */
		$posts = $this->get_similar_templates();
		foreach ( $posts as $post ) {
			update_post_meta( $post->ID, 'default', 0 );
		}

		/* Set the template as default */
		$this->meta_default = 1;

		/* make sure the template has the status 'publish' - this solves an issue with selecting a cloud template from the wizard ( which is a draft by default ) */
		wp_update_post( [
			'ID'          => $this->ID,
			'post_status' => 'publish',
		] );

		thrive_skin()->generate_style_file();
	}
}

if ( ! function_exists( 'thrive_template' ) ) {
	/**
	 * Return Thrive_Template instance
	 *
	 * @param int id template id
	 *
	 * @return Thrive_Template
	 */
	function thrive_template( $id = 0 ) {
		if ( $id === 0 ) {
			/**
			 * Allows dynamically modifying the template being rendered
			 *
			 * @param int $id id of the template
			 *
			 * @return int
			 */
			$id = (int) apply_filters( 'thrive_template_default_id', $id );
		}

		return Thrive_Template::instance_with_id( $id );
	}
}
