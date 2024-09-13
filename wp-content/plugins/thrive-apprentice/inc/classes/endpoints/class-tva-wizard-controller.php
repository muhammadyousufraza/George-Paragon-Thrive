<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Thrive_Wizard_Rest' ) ) {
	/**
	 * Fix for plesk environments
	 * Theme classes not loaded at all while a site is cloned from Plesk panel
	 */
	$architect_path = defined( 'ARCHITECT_INTEGRATION_PATH' ) ? ARCHITECT_INTEGRATION_PATH : \TVA_Const::plugin_path( 'builder' ) . '/integrations/architect';
	require $architect_path . '/classes/endpoints/class-thrive-wizard-rest.php';
}

class TVA_Wizard_Controller extends Thrive_Wizard_Rest {
	protected static $namespace = 'tva/v1';
	public static    $route     = '/wizard';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_item' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'tva_skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'save_wizard' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'tva_skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'step'        => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'restart_wizard' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'tva_skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/templates', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'fetch_templates' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'tva_skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'type'        => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/templates/(?P<id>.+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'fetch_template' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'tva_skin_id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
		] );
	}

	/**
	 * Ugh, this needs to be static because we have it as static in the parent class... ugh :(
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_item( WP_REST_Request $request ) {
		$skin = \TVA\TTB\Main::skin( $request->get_param( 'tva_skin_id' ) );

		if ( ! $skin || is_wp_error( $skin ) ) {
			return new WP_Error( 'invalid_skin_id', 'Skin ID is invalid', [ 'status' => 422 ] );
		}

		try {
			$wizard = \TVA\TTB\Apprentice_Wizard::get_data( (string) $skin->ID );
		} catch ( Exception $e ) {
			$wizard = [];
		}
		$share_ttb_color = (int) ( \Thrive_Theme::is_active() && ! empty( tva_get_settings_manager()->factory( 'share_ttb_color' )->get_value() ) );
		$master_hsl      = $share_ttb_color ? thrive_palettes()->get_master_hsl() : \TVA\TTB\tva_palettes()->get_master_hsl();
		$data            = [
			'wizard_data' => $wizard,
			'colors'      => [
				'hsla' => $master_hsl ? tve_prepare_hsla_code( $master_hsl ) : '',
			],
			'urls'        => \TVA\TTB\Apprentice_Wizard::get_urls(),
		];

		$get_templates_for = $request->get_param( 'step_templates' );
		if ( $get_templates_for ) {
			$data['step_templates'] = static::get_hf_templates( $get_templates_for );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Saves wizard data
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error wizard data for skin
	 */
	public static function save_wizard( $request ) {
		$data    = json_decode( $request->get_body(), true );
		$step    = $request->get_param( 'step' );
		$skin_id = $request->get_param( 'tva_skin_id' );
		$skin    = \TVA\TTB\Main::skin( $skin_id );

		if ( empty( $data['settings'][ $step ] ) ) {
			$data['settings'][ $step ] = [];
		}
		$step_settings = &$data['settings'][ $step ];
		$template_id   = isset( $step_settings['template_id'] ) ? $step_settings['template_id'] : 0;

		switch ( $step ) {
			case 'logo':
				if ( ! empty( $step_settings[1] ) ) {
					/* forward request to branding controller */
					$request->set_method( 'PUT' );
					$request->set_param( 'id', $step_settings[1]['id'] );
					$request->set_param( 'attachment_id', $step_settings[1]['attachment_id'] );
					$result = TCB_Logo_REST::update( $request );

					/* next, "light" logo */
					if ( $result->get_status() === 200 && ! empty ( $step_settings[2] ) ) {
						$request->set_param( 'id', $step_settings[2]['id'] );
						$request->set_param( 'attachment_id', $step_settings[2]['attachment_id'] );
						$result = TCB_Logo_REST::update( $request );
					}

					if ( $result->get_status() !== 200 ) {
						$response['success'] = false;
						$response['message'] = $result->get_data();
					}
				}
				break;
			case 'color':
				$data = ! empty( $step_settings['save_data'] ) ? $step_settings['save_data'] : [];
				if ( ! empty( $data ) && ! empty( $data['hsl_code'] ) && is_array( $data['hsl_code'] ) ) {
					\TVA\TTB\tva_palettes()->update_master_hsl( $data['hsl_code'] );
				}
				break;
			case 'header':
			case 'footer':
				$source    = isset( $step_settings['source'] ) ? $step_settings['source'] : 'cloud';
				$symbol_id = static::save_header_footer( $step, $template_id, $source, false );
				/* when saving a header / footer, setup the apprentice skin context - maybe it will be needed later on */
				update_post_meta( $symbol_id, 'thrive_scope', 'tva' );
				if ( is_wp_error( $symbol_id ) ) {
					return new WP_Error( 'cannot_save', 'Could not save symbol: ' . $symbol_id->get_error_message(), [ 'status' => 500 ] );
				}
				break;
			case 'school':
			case 'course':
			case 'module':
			case 'lesson':
			case 'video_lesson':
			case 'audio_lesson':
			case TVA_Course_Completed::WIZARD_ID:
			case TVA_Assessment::WIZARD_ID:
				$needs_template_update = true;
				\TVA\TTB\thrive_apprentice_template( $template_id )->make_default();
				break;
			case 'menu':
				try {
					$step_settings = static::replace_menu_in_sections( $step_settings );
					thrive_skin()->set_default_data( 'header_menu', ! empty( $step_settings['header'] ) ? $step_settings['header'] : 0 );
					thrive_skin()->set_default_data( 'footer_menu', ! empty( $step_settings['footer'] ) ? $step_settings['footer'] : 0 );
				} catch ( Exception $ex ) {
					return new WP_Error( 'cannot_save', 'Could not save menu: ' . $ex->getMessage(), [ 'status' => 500 ] );
				}
				break;
			case 'sidebar':
				\TVA\TTB\Apprentice_Wizard::setup_default_lesson_template();
				try {
					$templates = array_merge(
						\TVA\TTB\Main::requested_skin()->get_templates_by_type( THRIVE_SINGULAR_TEMPLATE, TVA_Const::LESSON_POST_TYPE, null, [ 'default' => 1 ] ),
						\TVA\TTB\Main::requested_skin()->get_templates_by_type( THRIVE_SINGULAR_TEMPLATE, TVA_Const::MODULE_POST_TYPE, null, [ 'default' => 1 ] ),
						\TVA\TTB\Main::requested_skin()->get_templates_by_type( THRIVE_SINGULAR_TEMPLATE, TVA_Course_Completed::POST_TYPE, null, [ 'default' => 1 ] ),
						\TVA\TTB\Main::requested_skin()->get_templates_by_type( THRIVE_SINGULAR_TEMPLATE, TVA_Const::NO_ACCESS, null, [ 'default' => 1 ] )
					);

					$source            = isset( $step_settings['source'] ) ? $step_settings['source'] : 'cloud';
					$local_template_id = $template_id;

					if ( $source === 'cloud' ) {
						/* search for an existing saved sidebar */
						$post_title = 'Default sidebar for ' . \TVA\TTB\Main::requested_skin()->name;
						$saved_post = get_page_by_title( $post_title, OBJECT, THRIVE_SECTION );
						if ( empty( $saved_post ) ) {
							$post_id    = wp_insert_post( [
								'post_title'  => $post_title,
								'post_type'   => THRIVE_SECTION,
								'post_status' => 'publish',
							] );
							$saved_post = get_post( $post_id );
						}

						add_filter( 'thrive_theme_section_download', static function ( $response, $temp_section ) use ( $templates, $saved_post, &$local_template_id ) {
							$cloud_thumb = $temp_section->get_meta( 'cloud_thumbnail' );

							$section_meta = [];

							foreach ( Thrive_Section::$meta_fields as $meta_field => $default ) {
								if ( $meta_field === 'skin_tag' ) {
									$meta_value = \TVA\TTB\Main::requested_skin()->tag;
								} else {
									$meta_value = $temp_section->get_meta( $meta_field ) ?: $default;
								}
								$section_meta[ $meta_field ] = $meta_value;
								update_post_meta( $saved_post->ID, $meta_field, $meta_value );
							}

							/** @var Thrive_Section $temp_section */
							$new_section = new Thrive_Section( $saved_post->ID, $section_meta );
							$new_section->replace_data_ids( $temp_section->get_meta( 'cloud_id_hash' ), $new_section->ID );
							/* also replace the placeholders, all selectors contain `static::$css_export_placeholder` at this point */
							$new_section->replace_data_ids( Thrive_Transfer_Section::$css_export_placeholder, $new_section->selector() );

							/* We need to also set the thumbnail for the section when it's imported */
							if ( ! empty( $cloud_thumb ) ) {
								Thrive_Transfer_Utils::save_thumbnail( $cloud_thumb, $new_section->ID );
							}

							wp_set_object_terms( $new_section->ID, \TVA\TTB\Main::requested_skin_id(), SKIN_TAXONOMY );
							$local_template_id = $new_section->ID;

							\TVA\TTB\Apprentice_Wizard::replace_section_in_templates( $templates, $new_section );

						}, 10, 2 );

						Thrive_Theme_Cloud_Api_Factory::build( 'sections' )->download_item( $template_id, '', [ 'original_css' => true ] );

					} else {
						\TVA\TTB\Apprentice_Wizard::replace_section_in_templates( $templates, $template_id );
					}
					thrive_skin()->set_default_data( 'apprentice_sidebar_id', $local_template_id );
				} catch ( Exception $e ) {
					return new WP_Error( 'could_not_download', 'Could not download section: ' . $e->getMessage(), [ 'status' => 500 ] );
				}
				break;
			default:
				return new WP_Error( 'invalid_step', 'Invalid step', [ 'status' => 422 ] );
		}

		// these should not be persisted here. they are saved in other places (skin term meta)
		unset(
			$data['settings']['logo'],
			$data['settings']['color']
		);

		$next = $request->get_param( 'next' );
		/* Fetch any available templates at this point, to avoid an extra request */
		if ( $next === 'header' || $next === 'footer' ) {
			$request->set_param( 'step_templates', $next );
		}
		$data['active']      = $request->get_param( 'next' );
		$data['activeIndex'] = $request->get_param( 'nextIndex' );
		$data['done']        = $request->get_param( 'done' );

		\TVA\TTB\Apprentice_Wizard::save_data( $skin_id, $data );

		$response = static::get_item( $request );
		if ( isset( $needs_template_update ) ) {
			$response_data         = $response->get_data();
			$response_data['skin'] = $skin;
			$response->set_data( $response_data );
		}

		return $response;
	}

	public static function get_hf_templates( $type ) {
		$section_rest = new Thrive_Section_REST();
		$request      = new WP_REST_Request();
		$request->set_param( 'type', $type );
		$request->set_param( 'filters', [
			'scope' => 'tva',
		] );

		/**
		 * ugly ugly ugly
		 *
		 * @see class-tcb-cloud-template-element-abstract.php line 90
		 */
		$_REQUEST['type'] = $type;
		$sections_request = $section_rest->get_cloud_sections( $request );
		if ( is_wp_error( $sections_request ) ) {
			return $sections_request;
		}
		/* remove "blank" templates for now */
		$cloud_templates = array_values( array_filter( $sections_request->get_data()['data'], static function ( $template
		) {
			return strpos( $template['post_title'], 'Blank' ) !== 0;
		} ) );

		/* get also the local data */
		$local_templates = \TVA\TTB\Apprentice_Wizard::get_local_hf( $type );

		return array_map( static function ( $template ) {
			$template['source'] = isset( $template['from_cloud'] ) ? 'cloud' : 'local';

			return $template;
		}, array_merge( $cloud_templates, $local_templates ) );
	}

	/**
	 * Get dynamic list by type
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response|boolean
	 * @throws Exception
	 */
	public static function fetch_templates( $request ) {
		$type = $request->get_param( 'type' );
		$data = [];

		switch ( $type ) {
			case THRIVE_HEADER_SECTION:
			case THRIVE_FOOTER_SECTION:
				$data = static::get_hf_templates( $type );
				break;
			case 'school':
			case 'course':
			case 'module':
			case TVA_Course_Completed::WIZARD_ID:
			case TVA_Assessment::WIZARD_ID:
				$data = \TVA\TTB\Apprentice_Wizard::get_templates( $type );
				break;
			case 'lesson':
				$data = \TVA\TTB\Apprentice_Wizard::get_templates( $type, [ 'scope' => 'tva' ], 'standard' );
				break;
			case 'video_lesson':
				$data = \TVA\TTB\Apprentice_Wizard::get_templates( $type, [ 'scope' => 'tva' ], 'video' );
				break;
			case 'audio_lesson':
				$data = \TVA\TTB\Apprentice_Wizard::get_templates( $type, [ 'scope' => 'tva' ], 'audio' );
				break;
			case 'sidebar':
				$data = \TVA\TTB\Apprentice_Wizard::get_section_templates( 'sidebar' );
				break;
			default:
				break;
		}

		/**
		 * Change wizard templates for each step if necessary
		 *
		 * @param array           $data    The templates specific for each step
		 * @param WP_REST_Request $request Rest request
		 */
		$data = apply_filters( 'thrive_theme_wizard_templates', $data, $request );

		if ( is_wp_error( $data ) ) {
			return new WP_Error( 'tcb_api_error', $data->get_error_message() );
		}
		return new WP_REST_Response( [
			'success' => 1,
			'data'    => $data,
		] );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @return bool
	 */
	public static function route_permission() {
		$result = TVA_Product::has_access();

		/* we might find a better place for this ? */
		if ( $result && ! defined( 'TA_WIZARD_REQUEST' ) ) {
			define( 'TA_WIZARD_REQUEST', true );
		}

		return $result;
	}

	/**
	 * Restart the current wizard
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function restart_wizard( $request ) {
		\TVA\TTB\Apprentice_Wizard::save_data( $request->get_param( 'tva_skin_id' ), [] );

		return new WP_REST_Response( [] );
	}

	/**
	 * Renders a single template instance and returns the output.
	 * Used initially for headers / footers
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function fetch_template( $request ) {
		$id     = $request->get_param( 'id' );
		$type   = $request->get_param( 'type' );
		$source = $request->get_param( 'source' );

		$data = [
			'id'   => $id,
			'html' => '',
		];

		switch ( $type ) {
			case 'module':
			case TVA_Course_Completed::WIZARD_ID:
			case TVA_Assessment::WIZARD_ID:
			case 'lesson':
			case 'video_lesson':
			case 'audio_lesson':
			case 'course':
			case 'school':
				if ( $source === 'cloud' ) {
					try {
						$template = \TVA\TTB\tva_wizard()->get_template_by_tag( $id );

						/* make sure that the default wizard HF ( if it's set ) is assigned to the template before it's loaded in preview */
						$template->assign_default_hf_from_wizard();

						$data['id'] = $template ? $template->ID : 0;
					} catch ( \Exception $ex ) {
						$data = [
							'id'   => 0,
							'html' => '',
						];
					}
				}

				return rest_ensure_response( $data );

			case 'sidebar':
				\TVA\TTB\Apprentice_Wizard::setup_default_lesson_template();
				if ( $source === 'cloud' ) {
					try {
						$data     = Thrive_Theme_Cloud_Api_Factory::build( 'sections' )->download_item( $id );
						$response = [
							'id'   => $id,
							'html' => '<style class="ttd-wizard-sidebar-style">' . $data['style'] . '</style>' . $data['content'],
						];
					} catch ( Exception $e ) {
						$response = new WP_Error( 'cant_download', 'Cannot download cloud template', [ 'status' => 500 ] );
					}
				} else {
					/* local template -> render local template */
					$section = new Thrive_Section( $id );

					$response = [
						'id'   => $id,
						'html' => '<style class="ttd-wizard-sidebar-style">' . $section->style() . '</style>' . $section->render(),
					];
				}

				return rest_ensure_response( $response );

			default:
				return parent::fetch_template( $request );
		}
	}
}
