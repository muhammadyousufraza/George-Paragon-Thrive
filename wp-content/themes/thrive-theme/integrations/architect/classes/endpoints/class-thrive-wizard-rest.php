<?php

use TCB\SavedLandingPages\Saved_Lp;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Wizard_Rest
 */
class Thrive_Wizard_Rest {

	public static $version = 1;
	public static $route   = '/wizard';

	public static function register_routes() {
		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/templates', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'fetch_templates' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/templates/(?P<id>.+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'fetch_template' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/url/(?P<step>.+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'fetch_url' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'save_wizard' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route, [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'restart_wizard' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/pages', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'suggest_pages' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );
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
				$section_rest = new Thrive_Section_REST();
				$request->set_param( 'type', $type );
				/* remove "blank" templates for now */

				$response = $section_rest->get_cloud_sections( $request );
				if ( ! is_wp_error( $response ) ) {
					$cloud_templates = array_values( array_filter( $response->get_data()['data'], static function ( $template
					) {
						return strpos( $template['post_title'], 'Blank' ) !== 0;
					} ) );
				} else {
					return new WP_Error( 'tcb_api_error', $response->get_error_message() );
				}


				/* get also the local data */
				$local_templates = Thrive_Wizard::get_local_hf( $type );

				$data = array_map( static function ( $template ) {
					$template['source'] = isset( $template['from_cloud'] ) ? 'cloud' : 'local';

					return $template;
				}, array_merge( $cloud_templates, $local_templates ) );

				break;
			case 'homepage':
				$templates = tve_get_cloud_templates( [
					'home'          => '1',
					'skin_tag'      => thrive_skin()->get_tag(),
					'theme_element' => 'homepage',
				] );

				if ( ! empty( $templates['error'] ) ) {
					return new WP_Error( 'tcb_api_error', $templates['error'] );
				}
				/* fetch a list of Landing Pages that have been marked as homepages */
				$data = array_values( array_map( static function ( $template, $id ) {
					$template_uid = isset( $template['uid'] ) ? $template['uid'] : '';
					$template_key = isset( $template['key'] ) ? $template['key'] : $id;

					return [
						'id'         => $template_key,
						'uid'        => $template_uid, // this is actually the unique ID from cloud
						'post_title' => $template['name'],
						'thumb'      => isset( $template['preview_image'] ) ? $template['preview_image'] : [],
					];
				}, $templates, array_keys( $templates ) ) );

				if ( class_exists( Saved_Lp::class, false ) ) {
					$saved_lps = Saved_Lp::localize();

					foreach ( $saved_lps as $lp ) {
						if ( ! empty( $lp['thumbnail'] ) ) {
							list( $width, $height ) = @getimagesize( $lp['thumbnail'] );
						}

						$lp_data = [
							'id'           => 'user-saved-template-' . $lp['id'],
							'post_title'   => $lp['name'],
							'thumb'        => [
								'url' => $lp['thumbnail'],
								'w'   => isset( $width ) ? $width : 500,
								'h'   => isset( $height ) ? $height : 500,
							],
							'is_from_pack' => (int) get_post_meta( $lp['id'], 'tve_kit_imported', true ),
						];

						/**
						 * if we don't have a preview image or sizes are not set, use the default one
						 */
						if ( empty( $lp['thumbnail'] ) || empty( $width ) || empty( $height ) ) {
							$lp_data['thumb'] = TCB_Utils::get_placeholder_data();
						}

						$data[] = $lp_data;


					}
				}

				break;
			case 'post':
			case 'blog':
			case 'page':
				$data = Thrive_Wizard::get_templates( $type );
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
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function route_permission() {
		return Thrive_Theme_Product::has_access();
	}

	/**
	 * Renders a single template instance and returns the output.
	 * Used initially for headers / footers
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function fetch_template( $request ) {
		$id     = $request['id'];
		$type   = $request->get_param( 'type' );
		$source = $request->get_param( 'source' );

		$data = [
			'id'   => $id,
			'html' => '',
		];

		switch ( $type ) {
			case THRIVE_HEADER_SECTION:
			case THRIVE_FOOTER_SECTION:
				/* get the HTML / CSS for a cloud template */
				$content = thrive_wizard()->get_hf_preview_content( $type, $id, $source );

				$data['id']   = $id;
				$data['html'] = ( new Thrive_HF_Section( 0, $type, [ 'content' => $content ] ) )->render();
				break;
			case THRIVE_POST_TEMPLATE:
			case THRIVE_PAGE_TEMPLATE:
			case THRIVE_BLOG_TEMPLATE:
				if ( $source === 'cloud' ) {
					try {
						$template = thrive_wizard()->get_template_by_tag( $id );

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
				break;

		}

		/**
		 * Maybe fetch another template based on the type
		 *
		 * @param array  $data
		 * @param string $type
		 * @param int    $id
		 * @param string $source
		 */
		$data = apply_filters( 'thrive_theme_wizard_fetch_template', $data, $type, $id, $source );

		return new WP_REST_Response( [
			'success' => 1,
			'data'    => $data,
		] );
	}

	/**
	 * Saves wizard data
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response $response
	 * @throws
	 *
	 */
	public static function save_wizard( $request ) {
		$wizard = json_decode( $request->get_body(), true );
		$step   = $request->get_param( 'step' );

		$response = [
			'success' => true,
		];

		if ( $wizard && $step ) {
			/* get it here and use it when needed */

			$template_id = isset( $wizard['settings'][ $step ]['template_id'] ) ? $wizard['settings'][ $step ]['template_id'] : 0;
			$settings    = $wizard['settings'][ $step ];

			/* step -> step being saved */
			switch ( $step ) {
				case 'logo':
					if ( ! empty( $settings['dark_id'] ) ) {
						/* forward request to branding controller */
						$request->set_method( 'PUT' );
						$request->set_param( 'id', 0 );
						$request->set_param( 'attachment_id', $settings['dark_id'] );
						$result = TCB_Logo_REST::update( $request );

						/* next, "light" logo */
						if ( $result->get_status() === 200 ) {
							$request->set_param( 'id', 1 );
							$request->set_param( 'attachment_id', $settings['light_id'] );
							$result = TCB_Logo_REST::update( $request );

							/* also, update the URL of the logo to point to the homepage, if nothing is already set */
							if ( ! Thrive_Branding::get_logo_url() ) {
								Thrive_Branding::set_logo_url( home_url() );
							}
						}

						if ( $result->get_status() !== 200 ) {
							$response['success'] = false;
							$response['message'] = $result->get_data();
						}
					}
					break;
				case 'color':
					/**
					 * Store the main skin color selected by the user, if any
					 */
					$saved = false;
					if ( ! empty( $settings['save_data'] ) ) {
						/* forward the request to the skin_variables route */
						$request->set_body_params( $settings['save_data'] );

						if ( thrive_palettes()->has_palettes() ) {
							$result = Thrive_Palette_Rest::update( $request );
						} else {
							$skin_controller = new Thrive_Skins_Rest();

							$result = $skin_controller->skin_variables( $request );
						}

						if ( $result instanceof WP_Error ) {
							$response['success'] = false;
							$response['message'] = $result->get_error_message();
						} else {
							$saved = true;
						}
					}

					$wizard['settings'][ $step ] = [
						'saved' => $saved,
						// we don't need to actually save anything here. Just store a flag that this has been saved
					];
					break;
				case 'header':
				case 'footer':
					$source    = isset( $wizard['settings'][ $step ]['source'] ) ? $wizard['settings'][ $step ]['source'] : 'cloud';
					$symbol_id = static::save_header_footer( $step, $template_id, $source );
					if ( is_wp_error( $symbol_id ) ) {
						$response['success'] = false;
						$response['message'] = $symbol_id->get_error_message();
					} else {
						$response['id']          = $symbol_id;
						$response['preview_url'] = get_permalink( $symbol_id );
					}
					break;
				case 'homepage':
					if ( $settings['type'] === 'blog' ) {
						/* directly update the WP option */
						update_option( 'show_on_front', 'posts' );
					} else {
						$is_landing_page = $settings['type'] === 'template';
						$page_id         = $is_landing_page ? Thrive_Defaults::get_default_post_id( 'homepage', 'Generated Homepage', 'page', true ) : (int) $settings['page_id'];

						if ( $is_landing_page ) {
							if ( strpos( $template_id, 'user-saved-template' ) !== false ) {
								/* apply a TCB saved LP on this page */
								$lp_id = (int) str_replace( 'user-saved-template-', '', $template_id );
								tcb_landing_page( $page_id )->change_template( $template_id, $lp_id );
							} else if ( method_exists( TCB_Landing_Page::class, 'apply_cloud_template' ) ) {/* apply landing page template on homepage */
								$should_update_homepage = true;

								$global_homepage_options = Thrive_Utils::get_homepage_options();

								/* check if this template is not already active, because in that scenario we should not update the homepage */
								if (
									! empty( $global_homepage_options['is_landing_page'] ) &&
									$global_homepage_options['is_landing_page'] === $template_id
								) {
									$should_update_homepage = false;
								}

								if ( $should_update_homepage ) {
									$uid = isset( $settings['uid'] ) ? $settings['uid'] : ''; // real cloud ID for this template
									/* append header and footer before save */
									TCB_Landing_Page::apply_cloud_template( $page_id, $template_id, $uid );

									update_post_meta( $page_id, '_tve_header', thrive_skin()->get_default_data( THRIVE_HEADER_SECTION ) );
									update_post_meta( $page_id, '_tve_footer', thrive_skin()->get_default_data( THRIVE_FOOTER_SECTION ) );
									delete_post_meta( $page_id, 'tve_disable_theme_dependency' );
								}
							}
							$response['id']          = $page_id;
							$response['preview_url'] = get_permalink( $page_id );
						}

						update_option( 'show_on_front', 'page' );
						update_option( 'page_on_front', $page_id );

						$page_for_posts_id = get_option( 'page_for_posts' );
						$page_for_posts    = get_post( $page_for_posts_id );

						if ( empty( $page_for_posts_id ) || ! $page_for_posts || $page_for_posts->post_status !== 'publish' ) {
							/* set a default "Posts" page */
							update_option( 'page_for_posts', Thrive_Defaults::get_default_post_id( 'blog', 'Blog', 'page', true ) );
						}
					}
					/* set a global flag in the DB to signal that we completed the homepage step at least once on a skin */
					update_option( THRIVE_THEME_HOMEPAGE_SET_FROM_WIZARD, 1 );
					break;
				case 'post':
				case 'blog':
				case 'page':
					/* save Single Post default template */
					$template = new Thrive_Template( $wizard['settings'][ $step ]['template_id'] );
					$template->make_default();

					$response['id']          = $template->ID;
					$response['preview_url'] = $template->preview_url();
					break;
				case 'menu':
					try {
						$wizard['settings'][ $step ] = static::replace_menu_in_sections( $wizard['settings'][ $step ] );
						thrive_skin()->set_default_data( 'header_menu', $wizard['settings'][ $step ]['header'] > 0 ? $wizard['settings'][ $step ]['header'] : 0 );
						thrive_skin()->set_default_data( 'footer_menu', $wizard['settings'][ $step ]['footer'] > 0 ? $wizard['settings'][ $step ]['footer'] : 0 );
						$response['success'] = true;
					} catch ( Exception $ex ) {
						$response = [
							'success' => false,
							'message' => $ex->getMessage(),
						];
					}
					break;
				default:
					break;
			}

			if ( ! empty( $wizard['settings'][ $step ]['template_id'] ) ) {
				/* in case we had a template that was downloaded but not "activated" */
				wp_update_post( [
					'ID'          => $wizard['settings'][ $step ]['template_id'],
					'post_status' => 'publish',
				] );

				thrive_skin()->generate_style_file();
			}

			/**
			 * Change wizard response when saving
			 * Also, give the possibility to add other steps to the wizard with specific functionality
			 *
			 * @param array  $response The response obtained up to this point
			 * @param array  $wizard   Wizard data
			 * @param string $step     The step that we are saving
			 */
			$response = apply_filters( 'thrive_theme_wizard_save_response', $response, $wizard, $step );
		}

		if ( ! empty( $response['success'] ) ) {
			/* store next step */
			$wizard['active']      = $request->get_param( 'next' );
			$wizard['activeIndex'] = $request->get_param( 'nextIndex' );
			thrive_skin()->set_meta( 'ttb_wizard', $wizard );
		}

		/* always refresh these */
		$localized_data = Thrive_Wizard::localize_admin();
		unset( $localized_data['suggest_pages'] );

		return new WP_REST_Response( $response + $localized_data );
	}

	/**
	 * Restart the current wizard
	 *
	 * @param WP_REST_Request $request
	 */
	public static function restart_wizard( $request ) {
		thrive_skin()->set_meta( 'ttb_wizard', [] );

		return new WP_REST_Response( [] );
	}

	/**
	 * Suggest a list of pages
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function suggest_pages( $request ) {

		return new WP_REST_Response( Thrive_Wizard::autocomplete_pages( [
			's'           => $request->get_param( 'search' ),
			'numberposts' => $request->get_param( 'limit' ) ?: 5,
			'offset'      => $request->get_param( 'offset' ) ?: 0,
		] ) );
	}

	/**
	 * Replace default menu ids in all the headers/footers from the templates
	 *
	 * @param array $default_ids
	 *
	 * @return array sanitized array with IDs
	 *
	 * @throws Exception
	 *
	 */
	public static function replace_menu_in_sections( $default_ids = [] ) {
		$default_ids = array_map( 'intval', $default_ids );

		$filtered = array_filter( $default_ids, static function ( $id ) {
			return $id > 0;
		} );

		if ( empty( $filtered ) ) {
			return $default_ids; // nothing more to do here
		}
		$processed = [];
		foreach ( thrive_skin()->get_templates( 'object' ) as $template ) {
			/** @var Thrive_Template $template */

			foreach ( $filtered as $section_type => $default_menu_id ) {
				$instance = $template->get_hf_section_instance( $section_type );
				if ( ! $instance->is_dynamic() || ( $instance->is_dynamic() && empty( $processed[ $instance->ID ] ) ) ) {
					$section_id = $instance->replace_menu( $default_menu_id );
					if ( ! empty( $section_id ) ) {
						$processed[ $section_id ] = 1;
					}
				}
			}
		}

		return $default_ids;
	}

	/**
	 * Fetch url for a specific wizard step
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function fetch_url( $request ) {

		$step = $request->get_param( 'step' );

		switch ( $step ) {
			case 'blog':
				$page_for_posts = Thrive_Defaults::get_default_post_id( 'blog', 'Blog', 'page', true );
				update_option( 'page_for_posts', $page_for_posts );
				$url = get_permalink( $page_for_posts );
				break;

			case 'demo-blog':
				Thrive_Demo_Content::generate();
				$url = get_post_type_archive_link( Thrive_Demo_Content::POST_TYPE );
				break;

			default:
				$url = '';
		}

		return new WP_REST_Response( $url );
	}

	/**
	 * Saves and processes data related the header / footer step for the wizard
	 *
	 * @param string     $step             wizard step (header / footer)
	 * @param string|int $template_id      id of template to use
	 * @param string     $source           source of the template (local / cloud)
	 * @param bool       $update_blog_page whether or not to also update the default WP blog page with the new header / footer
	 *
	 * @return int|WP_Error saved symbol's ID or WP_Error object in case of error
	 */
	protected static function save_header_footer( $step, $template_id, $source = 'cloud', $update_blog_page = true ) {
		$id = thrive_skin()->get_default_data( $step );

		/* go through all templates and set the same header / footer */
		if ( $source === 'cloud' ) {
			$symbol_id = Thrive_HF_Section::populate_from_cloud_template(
				$template_id,
				$step,
				'Default ' . ucfirst( $step ) . ' for ' . thrive_skin()->term->name,
				get_post( $id )
			);
		} else {
			$symbol = get_posts( [
				'include'   => [ $template_id ],
				'post_type' => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			] );

			$symbol_id = empty( $symbol ) ? new WP_Error( 'symbol_not_found', __( 'Template could not be found', 'thrive-theme' ) ) : $template_id;
		}

		if ( ! is_wp_error( $symbol_id ) ) {
			/* make sure this is the default H/F section for the skin */
			thrive_skin()->set_default_data( $step, $symbol_id );

			/* update all templates */
			foreach ( thrive_skin()->get_templates( 'object' ) as $template ) {
				/** @var Thrive_Template $template */
				$template->set_header_footer( $step, $symbol_id );
			}

			/* if page_on_front -> also set header / footer in there */
			if ( $update_blog_page && get_option( 'show_on_front' ) === 'page' ) {
				$page_id = get_option( 'page_on_front' );

				if ( $page_id ) {
					update_post_meta( $page_id, "_tve_{$step}", $symbol_id );
				}
			}
		}

		return $symbol_id;
	}
}
