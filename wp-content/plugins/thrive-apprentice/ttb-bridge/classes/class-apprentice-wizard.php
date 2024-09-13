<?php

namespace TVA\TTB;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Apprentice_Wizard extends \Thrive_Wizard {
	const KEY = 'tva-wizard';
	/**
	 * Stores all active wizard steps
	 *
	 * @var array
	 */
	public static $active_steps
		= [
			'logo',
			'color',
			'header',
			'footer',
			'homepage',
			'course',
			'module',
			'lesson',
			'menu',
			\TVA_Course_Completed::WIZARD_ID,
			\TVA_Assessment::WIZARD_ID,
		];

	/**
	 * Get an object to be used on previews/editing screens based on $content_type. If nothing found, it will return TA demo data
	 *
	 * @param string  $content_type
	 * @param int     $content_id
	 * @param boolean $prefer_demo_content Whether or not to prefer demo content vs regular content
	 *
	 * @return \WP_Post|\WP_Term|null
	 */
	public static function get_object_or_demo_content( $content_type, $content_id = 0, $prefer_demo_content = false ) {
		switch ( $content_type ) {
			case \TVA_Const::NO_ACCESS_POST:
				$post = null;

				if ( ! empty( $content_id ) ) {
					$post = get_post( $content_id );
				}
				if ( empty( $post ) ) {
					$posts = get_posts( \Thrive_Utils::filter_default_get_posts_args( [
						'numberposts' => 1,
						'post_type'   => [ 'post', 'page' ],
						'status'      => [ 'publish', 'draft' ],
						'orderby'     => 'ID',
						'order'       => 'DESC',
						'exclude'     => [],
						'meta_query'  => \Thrive_Utils::meta_query_no_landing_pages(),
					] ) );

					$post = isset( $posts[0] ) ? $posts[0] : new \WP_Post( new \stdClass() );

				}

				return $post;
			case \TVA_Course_Completed::POST_TYPE:
			case \TVA_Const::ASSESSMENT_POST_TYPE:
			case \TVA_Const::LESSON_POST_TYPE:
			case \TVA_Const::MODULE_POST_TYPE:
			case \TVA_Const::NO_ACCESS:
			case 'video_lesson':
			case 'audio_lesson':
				$post = null;

				if ( ! empty( $content_id ) ) {
					$post = get_post( $content_id );
				}

				/**
				 * This if also covers the case if the content is recently deleted
				 * Ex: We load a module with a specific ID from cache but that module has been deleted recently
				 */
				if ( empty( $post ) ) {
					$meta_lesson_type = 'text';
					if ( $content_type === 'video_lesson' || $content_type === 'audio_lesson' ) {
						$meta_lesson_type = str_replace( '_lesson', '', $content_type );
						$content_type     = \TVA_Const::LESSON_POST_TYPE;
					}
					$args = [
						'numberposts' => 1,
						'post_type'   => $content_type === \TVA_Const::NO_ACCESS ? \TVA_Const::LESSON_POST_TYPE : $content_type,
						'status'      => [ 'publish', 'draft' ],
						'orderby'     => 'ID',
						'order'       => $prefer_demo_content ? 'ASC' : 'DESC',
					];
					if ( $prefer_demo_content ) {
						$args['meta_query']['demo_content'] = [
							'key'   => 'tva_is_demo',
							'value' => '1',
						];
					} else {
						$args['meta_query']['demo_content'] = [
							'key'     => 'tva_is_demo',
							'compare' => 'NOT EXISTS',
						];
					}

					if ( $content_type === \TVA_Const::LESSON_POST_TYPE ) {
						/* make sure the correct format is used for audio and video lessons */
						$args['meta_query'][] = [
							'key'   => 'tva_lesson_type',
							'value' => $meta_lesson_type,
						];
					}
					$posts = get_posts( $args );
					if ( empty( $posts ) && ! $prefer_demo_content ) {
						/* no regular posts found, redo the query for demo content.. */
						$args['meta_query']['demo_content'] = [
							'key'   => 'tva_is_demo',
							'value' => '1',
						];
						$args['order']                      = 'ASC';
						$posts                              = get_posts( $args );
					}

					$post = isset( $posts[0] ) ? $posts[0] : new \WP_Post( new \stdClass() ); // todo wizard default post ?
				}

				return $post;

			case \TVA_Const::COURSE_TAXONOMY:
				$wp_term = null;

				if ( ! empty( $content_id ) ) {

					/**
					 * $content_id is a the course overview post ID
					 * We need to get the course ID the course overview post is linked to
					 */
					$term_query = new \WP_Term_Query( [
						'meta_key'     => 'tva_overview_post_id',
						'meta_value'   => $content_id,
						'meta_compare' => '=',
						'number'       => '1',
					] );

					$terms = $term_query->get_terms();
					if ( ! empty( $terms ) && is_array( $terms ) ) {
						$wp_term = $terms[0];
					}
				}

				if ( empty( $wp_term ) ) {
					/* if demo_content is needed, do not try to load user-created courses */
					if ( ! $prefer_demo_content ) {
						$courses = \TVA_Course_V2::get_items( [ 'limit' => 1, 'status' => 'publish' ] );
					}
					if ( empty( $courses ) ) {
						$courses = \TVA_Course_V2::get_items( [ 'status' => 'private', 'limit' => 1 ] );
					}
					$course = isset( $courses[0] ) ? $courses[0] : new \TVA_Course_V2( [] ); // todo wizard default course ??

					$wp_term = $course->get_wp_term();
				}

				return $wp_term;
		}

		return null;
	}

	/**
	 * Get a URL of the first item based on $content_type. If nothing found, it will use TA demo data
	 *
	 * @param string  $content_type
	 * @param int     $content_id
	 * @param boolean $prefer_demo_content Whether or not to prefer demo content vs regular content
	 *
	 * @return int
	 */
	public static function get_post_or_demo_content_url( $content_type, $content_id = 0, $prefer_demo_content = false ) {
		switch ( $content_type ) {
			case \TVA_Const::LESSON_POST_TYPE:
			case \TVA_Const::MODULE_POST_TYPE:
			case \TVA_Const::COURSE_TAXONOMY:
			case \TVA_Const::NO_ACCESS:
			case \TVA_Const::NO_ACCESS_POST:
			case \TVA_Course_Completed::POST_TYPE:
			case \TVA_Const::ASSESSMENT_POST_TYPE:
			case 'video_lesson':
			case 'audio_lesson':
				$object = static::get_object_or_demo_content( $content_type, $content_id, $prefer_demo_content );
				$url    = '';
				if ( $object instanceof \WP_Post ) {
					$url = get_permalink( $object );
				} elseif ( $object instanceof \WP_Term ) {
					$url = get_term_link( $object );
				}
				break;
			case 'school':
				$url = tva_get_settings_manager()->factory( 'index_page' )->get_link();
				break;
			default:
				$url = '';
				break;
		}

		if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {
			/**
			 * For demo content we need to inject the tva_skin_id in inner frame URL
			 */
			$url = add_query_arg( [
				'tva_skin_id' => $_REQUEST['tva_skin_id'],
			], $url );
		}

		return $url;
	}

	/**
	 * Get full the wizard data for a skin
	 *
	 * @param int|string $skin_id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function get_data( $skin_id ) {
		$skin        = Main::skin( $skin_id );
		$wizard_data = $skin->get_meta( 'ttb_wizard', [] );
		if ( ! is_array( $wizard_data ) || empty( $wizard_data ) ) {
			$wizard_data = [];
		}

		/* make sure the "done" steps are always valid */
		if ( isset( $wizard_data['done'] ) ) {
			$wizard_data['done'] = array_filter( $wizard_data['done'] );
		}

		/**
		 * Preload these templates to allow faster wizard navigation
		 */
		$lesson_templates_request = static::get_templates( 'lesson' );
		if ( ! is_wp_error( $lesson_templates_request ) ) {
			$lesson_templates = array_filter( $lesson_templates_request, static function ( $template ) {
				return $template['default'];
			} );

			/**
			 * Filter a list of templates by their format
			 *
			 * @param string $template_format
			 *
			 * @return array
			 */
			$filter_templates = static function ( $template_format ) use ( $lesson_templates ) {
				return array_values( array_filter(
					$lesson_templates,
					static function ( $template ) use ( $template_format ) {
						return $template['format'] === $template_format;
					}
				) );
			};

			$templates = [
				'lesson'                         => $filter_templates( 'standard' ),
				'video_lesson'                   => $filter_templates( 'video' ),
				'audio_lesson'                   => $filter_templates( 'audio' ),
				'module'                         => static::get_templates( 'module', [ 'skin_id' => $skin_id ] ),
				\TVA_Course_Completed::WIZARD_ID => static::get_templates( \TVA_Course_Completed::WIZARD_ID, [ 'skin_id' => $skin_id ] ),
				\TVA_Assessment::WIZARD_ID       => static::get_templates( \TVA_Assessment::WIZARD_ID, [ 'skin_id' => $skin_id ] ),
			'course'                         => static::get_templates( 'course', [ 'skin_id' => $skin_id ] ),
			'school'                         => static::get_templates( 'school', [ 'skin_id' => $skin_id ] ),
		];

			/**
			 * Fill in a default template ID so that the preview is loaded directly in the wizard, without loading a placeholder page
			 */
			foreach ( $templates as $type => $template_list ) {
				if ( ! empty( $template_list ) ) {
					/* synchronize wizard selected IDs with the currently selected default templates - by default take the first template */
					$default_template_id = $template_list[0]['id'];

					/* default templates for different formats */
					$default_format = 'standard';
					if ( $type === 'video_lesson' || $type === 'audio_lesson' ) {
						/* for audio/video lessons, the default template needs to be matched to the correct format */
						$default_format = str_replace( '_lesson', '', $default_format );
					}

					/* get the ID of the default template */
					$wizard_data['settings'][ $type ]['template_id'] = array_reduce( $template_list, static function ( $current_id, $template ) use ( $default_format ) {
						if ( $template['default'] && $default_format === $template['format'] ) {
							$current_id = $template['id'];
						}

						return $current_id;
					}, $default_template_id );
				}
			}
		} else {
			$wizard_data['settings']['templates_error'] = $lesson_templates_request;
		}

		/* try to detect the current sidebar used for lesson templates if nothing is set in the wizard */
		if ( empty( $wizard_data['settings']['sidebar']['template_id'] ) ) {
			$default_lesson = Main::requested_skin()->get_default_template( 'lesson' );
			$sections       = $default_lesson->sections;
			if ( ! empty( $sections['sidebar']['id'] ) ) {
				$wizard_data['settings']['sidebar'] = [
					'template_id' => $sections['sidebar']['id'],
					'source'      => 'local',
				];
			}
		}


		$wizard_data['activeIndex'] = isset( $wizard_data['activeIndex'] ) ? (int) $wizard_data['activeIndex'] : - 1;

		/* logo is a general setting */
		$wizard_data['settings']['logo'] = Main::get_logo();

		$wizard_data['share_color'] = (int) ( \Thrive_Theme::is_active() && ! empty( tva_get_settings_manager()->factory( 'share_ttb_color' )->get_value() ) );

		return $wizard_data;
	}

	/**
	 * Save wizard data for a skin
	 *
	 * @param int   $skin_id
	 * @param array $wizard_data
	 */
	public static function save_data( $skin_id, array $wizard_data ) {
		$skin = Main::skin( $skin_id );
		$skin->set_meta( 'ttb_wizard', $wizard_data );
	}

	/**
	 * Get the URLs for the main wizard steps
	 *
	 * @return string[]
	 */
	public static function get_urls() {
		return [
			'lesson'                         => static::get_post_or_demo_content_url( \TVA_Const::LESSON_POST_TYPE ),
			'video_lesson'                   => static::get_post_or_demo_content_url( 'video_lesson' ),
			'audio_lesson'                   => static::get_post_or_demo_content_url( 'audio_lesson' ),
			'module'                         => static::get_post_or_demo_content_url( \TVA_Const::MODULE_POST_TYPE ),
			\TVA_Course_Completed::WIZARD_ID => static::get_post_or_demo_content_url( \TVA_Course_Completed::POST_TYPE ),
			\TVA_Assessment::WIZARD_ID       => static::get_post_or_demo_content_url( \TVA_Const::ASSESSMENT_POST_TYPE ),
			'course'                         => static::get_post_or_demo_content_url( \TVA_Const::COURSE_TAXONOMY ),
			'school'                         => static::get_post_or_demo_content_url( 'school' ),
		];
	}

	/**
	 * Localize main admin data (data available for all wizards)
	 *
	 * @return array
	 */
	public static function localize_admin() {
		return [
			'structure'     => include \TVA_Const::plugin_path( 'ttb-bridge/wizard/structure.php' ),
			'suggest_pages' => static::autocomplete_pages(),
		];
	}

	public function init_frontend() {
		parent::init_frontend();

		if ( $this->is_template_preview() ) {
			add_action( 'theme_template_before_render', function ( $template ) {
				$step = $this->request( 'step' );
				if ( in_array( $step, [ \TVA_Assessment::WIZARD_ID, \TVA_Course_Completed::WIZARD_ID, 'module' ] ) || strpos( $step, 'lesson' ) !== false ) {
					/* make sure the default sidebar ID is correctly assigned to the template */
					$default_sidebar_id = thrive_skin()->get_default_data( 'apprentice_sidebar_id' );
					if ( $default_sidebar_id ) {
						/* replace the sidebar in the corresponding template */
						static::replace_section_in_templates( [ $template->ID ], $default_sidebar_id );
					}
				}
			} );
		}
	}


	/**
	 * Whether or not the current request relates to the wizard (either REST API wizard, either frontend preview)
	 *
	 * @return bool
	 */
	public static function is_during_preview() {
		if ( defined( 'TA_WIZARD_REQUEST' ) && TA_WIZARD_REQUEST ) {
			return true;
		}

		return static::is_frontend() || ( ! empty( $_REQUEST['tva_skin_id'] ) && \TCB_Utils::is_rest() );
	}

	/**
	 * Get the current skin being configured
	 *
	 * @return int
	 */
	public static function requested_skin_id() {
		if ( ! static::is_during_preview() ) {
			return 0;
		}

		return (int) ( empty( $_REQUEST['tva_skin_id'] ) ? Main::get_default_skin_id() : $_REQUEST['tva_skin_id'] );
	}

	/**
	 * Get the currently requested / previewed skin
	 *
	 * @return Skin
	 */
	public static function requested_skin() {
		return Main::skin( static::requested_skin_id() );
	}

	/**
	 * Replace some dynamic elements in a piece of content while previewing / saving wizard data
	 *
	 * Currently used for: logo
	 *
	 * @param \WP_Post $post
	 */
	public static function replace_data_in_preview_content( $post ) {
		if ( static::is_during_preview() || ( ! empty( $_REQUEST['tva_skin_id'] ) && is_editor_page_raw( true ) ) ) {
			$logo_id = Main::get_logo_id();
			if ( $logo_id !== null ) {
				$replaced = preg_replace_callback( '#\[tcb_logo(.+?)data-id-d=(["\'])(\d*)(["\'])#', static function ( $matches ) use ( $logo_id ) {
					return '[tcb_logo' . $matches[1] . 'data-id-d=' . $matches[2] . $logo_id . $matches[4];
				}, $post->post_content );

				/* also try the variant in which there's no data-id-d attribute */
				if ( $replaced === $post->post_content ) {
					$replaced = str_replace( '[tcb_logo', "[tcb_logo data-id-d='{$logo_id}'", $post->post_content );
				}
				$post->post_content = $replaced;
			}
		}
	}

	/**
	 * Wrapper over get_template function
	 *
	 * @param string         $type
	 * @param array          $args
	 * @param boolean|string $format if not false, it will be used as a filter for the lesson format
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function get_templates( $type, $args = [], $format = false ) {
		$templates = parent::get_templates( $type, $args );

		if ( is_wp_error( $templates ) ) {
			return $templates;
		}
		if ( ! empty( $format ) ) {
			$templates = array_filter( $templates, static function ( $template ) use ( $format ) {
				return $template['format'] === $format;
			} );
		}

		return is_array( $templates ) ? array_values( $templates ) : [];
	}

	/**
	 * Get a filter array for a template type
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_template_filters_for_type( $type ) {
		switch ( $type ) {
			case 'school':
				$filters = [
					'primary'   => THRIVE_HOMEPAGE_TEMPLATE,
					'secondary' => \TVA_Const::COURSE_POST_TYPE,
				];
				break;
			case 'course':
				$filters = [
					'primary'   => THRIVE_ARCHIVE_TEMPLATE,
					'secondary' => \TVA_Const::COURSE_TAXONOMY,
				];
				break;
			case 'module':
				$filters = [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => \TVA_Const::MODULE_POST_TYPE,
				];
				break;
			case \TVA_Course_Completed::WIZARD_ID:
				$filters = [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => \TVA_Course_Completed::POST_TYPE,
				];
				break;
			case \TVA_Assessment::WIZARD_ID:
				$filters = [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => \TVA_Const::ASSESSMENT_POST_TYPE,
				];
				break;
			case 'lesson':
				$filters = [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => \TVA_Const::LESSON_POST_TYPE,
				];
				break;
			default:
				$filters = parent::get_template_filters_for_type( $type );
				break;
		}

		return $filters;
	}

	/**
	 * Get section templates for a step in the wizard
	 *
	 * @param string $type
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function get_section_templates( $type ) {
		/* to make sure the correct apprentice skin is used */
		static::setup_default_lesson_template();
		/* cloud templates */
		try {
			$api             = \Thrive_Theme_Cloud_Api_Factory::build( 'sections' );
			$cloud_templates = [];
			foreach ( $api->get_items( [ 'type' => $type ] ) as $id => $section ) {
				$section['post_title'] = $section['name'];
				$section['source']     = 'cloud';
				$cloud_templates []    = $section;
			}
		} catch ( \Exception $e ) {
			$cloud_templates = [];
		}

		$local_templates = array_map(
			static function ( $section ) {
				$section['post_title'] = $section['name'];
				$section['source']     = 'local';

				return $section;
			},
			Main::requested_skin()->get_sections( [
				'meta_key'   => 'type',
				'meta_value' => $type,
			] )
		);

		$sort = static function ( $a, $b ) {
			return strnatcasecmp( $a['post_title'], $b['post_title'] );
		};
		usort( $local_templates, $sort );
		usort( $cloud_templates, $sort );

		return array_merge( $local_templates, $cloud_templates );
	}

	/**
	 * Setup global query vars and the default lesson template so that the correct template is loaded during the REST API request
	 */
	public static function setup_default_lesson_template() {
		/* make sure the global wp query correctly picks up the current preview ID */
		$default_lesson = \TVA\TTB\Apprentice_Wizard::get_object_or_demo_content( \TVA_Const::LESSON_POST_TYPE );
		\Thrive_Utils::set_query_vars( [
			\TVA_Const::LESSON_POST_TYPE => $default_lesson->post_name,
			'post_type'                  => \TVA_Const::LESSON_POST_TYPE,
			'name'                       => $default_lesson->post_name,
		] );

		/* get the first lesson template and consider that as the default */
		$lesson_template = \TVA\TTB\Main::requested_skin()->get_default_template( 'lesson' );
		if ( ! empty( $lesson_template ) ) {
			add_filter( 'thrive_template_default_id', static function () use ( $lesson_template ) {
				return $lesson_template->ID;
			} );
		}
		thrive_template();

		/**
		 * Make sure global objects ( course, lesson etc ) are set for this request
		 */
		do_action( 'tva_set_objects' );
	}

	/**
	 * Replace a section in all the templates received as parameter
	 *
	 * @param Skin_Template[]|string[]|int[] $templates
	 * @param \Thrive_Section|string|int     $section
	 */
	public static function replace_section_in_templates( $templates, $section ) {

		if ( ! ( $section instanceof \Thrive_Section ) ) {
			$section = new \Thrive_Section( $section );
		}

		$section_type = $section->type();
		foreach ( $templates as $template ) {
			if ( is_numeric( $template ) ) {
				$template = new Skin_Template( $template );
			}

			$sections            = $template->sections;
			$needs_style_replace = empty( $sections[ $section_type ]['id'] );

			$sections[ $section_type ] = [ 'id' => $section->ID ];
			$template->set_meta( 'sections', $sections );
			if ( $section_type === 'sidebar' ) {
				$sidebar_type = @json_decode( $template->get_meta( 'sidebar-type' ) ?: '' ) ?: [];
				if ( ! is_array( $sidebar_type ) ) {
					$sidebar_type = [];
				}
				if ( empty( $sidebar_type['desktop'] ) || $sidebar_type['desktop'] !== 'off-screen' ) {
					/* make sure the template has an off-screen sidebar, as it was previewed in the wizard */
					$sidebar_type['desktop'] = 'off-screen';
					$template->set_meta( 'sidebar-type', json_encode( $sidebar_type ) );
				}
			}

			/* remove any possible section styles ( if section was unlinked before, we need to remove those styles, as they will conflict with the linked section ) */
			if ( $needs_style_replace ) {
				$template->remove_section_styles( $section_type );
			}
		}
	}
}
