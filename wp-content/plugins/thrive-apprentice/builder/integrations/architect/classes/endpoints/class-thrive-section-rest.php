<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use TCB\ConditionalDisplay\PostTypes\Conditional_Display_Group;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Section_Rest
 */
class Thrive_Section_Rest {

	public static $namespace = TTB_REST_NAMESPACE;
	public static $route     = '/sections';

	public static function register_routes() {

		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'create' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'read' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'delete' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/cloud', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_cloud_sections' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/cloud' . '/(?P<id>[\w]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'download_section' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/(?P<id>[\d]+)' . '/preview', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'preview' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );
	}

	/**
	 * Download a section from the cloud
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error
	 */
	public static function download_section( $request ) {
		$id         = $request->get_param( 'id' );
		$query_vars = $request->get_param( 'query_vars' );
		$version    = $request->get_param( 'version' );
		/**
		 * We need to know on which template we are because we need the template body class afterwards at css section replace
		 */
		thrive_template( $request->get_param( 'template_id' ) );

		/* add this filter to ensure that the shortcode functions know that we're in the editor */
		add_filter( 'tcb_in_editor_render', '__return_true' );

		do_action( 'thrive_theme_section_before_download' );

		Thrive_Utils::set_query_vars( $query_vars );

		try {
			$data = Thrive_Theme_Cloud_Api_Factory::build( 'sections' )->download_item( $id, $version );
		} catch ( Exception $e ) {
			$data = new WP_Error( 'tcb_download_err', $e->getMessage() );
		}

		return $data;
	}

	/**
	 * Get sections list from cloud based on section type
	 * Also handles header and footer section templates
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_cloud_sections( $request ) {
		$section_type = $request->get_param( 'type' );

		/**
		 * We need to know on which template we are because we need to show different sections for list and singular and sort hf based on template
		 */
		thrive_template( $request->get_param( 'template_id' ) );

		switch ( $section_type ) {
			case THRIVE_HEADER_SECTION:
			case THRIVE_FOOTER_SECTION:
				/**
				 * Allow filtering request parameters for headers / footer cloud templates
				 *
				 * @param array request parameters
				 */
				$params   = apply_filters( 'thrive_theme_hf_params', [
					'ttb_skin'              => thrive_skin()->get_tag(),
					'included_cloud_fields' => [ 'skin_tag', 'is_woo' ],
				] );
				$sections = tcb_elements()->element_factory( $section_type )->get_cloud_templates( $params );
				if ( ! is_wp_error( $sections ) ) {
					/* sort templates so that the ones from the skin are always shown first */
					usort( $sections, static function ( $a, $b ) {
						$value_a = (int) empty( $a['skin_tag'] ); // if no skin tag, it will be displayed later in the list
						$value_b = (int) empty( $b['skin_tag'] );

						return $value_a === $value_b ? strcmp( $a['post_title'], $b['post_title'] ) : ( $value_a - $value_b );
					} );
					/**
					 * Change sections before showing them to the user
					 *
					 * @param array $sections
					 */
					$sections = apply_filters( 'thrive_theme_cloud_hf_templates', $sections );
				} else {
					return new WP_Error( 'tcb_api_error', $sections->get_error_message() );
				}
				break;
			default:
				try {
					$api      = Thrive_Theme_Cloud_Api_Factory::build( 'sections' );
					$sections = $api->get_items( [ 'type' => $section_type ] );
				} catch ( Exception $e ) {
					return new WP_Error( 'tcb_api_error', $e->getMessage() );
				}

				foreach ( $sections as $id => $section ) {
					$sections[ $id ]['id'] = $id;
				}
		}

		return new WP_REST_Response( [
			'success' => 1,
			'data'    => $sections,
		], 200 );
	}

	/**
	 * Create a new section
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function create( $request ) {

		$data = static::prepare_section_for_insert( $request );

		$section_id = wp_insert_post( $data );

		/* assign section to the current skin */
		wp_set_object_terms( $section_id, thrive_skin()->ID, SKIN_TAXONOMY );

		return new WP_REST_Response( $section_id, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function read( $request ) {

		$section_id = $request->get_param( 'id' );
		$query_vars = $request->get_param( 'query_vars' );

		Thrive_Utils::set_query_vars( $query_vars );

		/* add this filter to ensure that the shortcode functions know that we're in the editor */
		add_filter( 'tcb_in_editor_render', '__return_true' );

		do_action( 'thrive_theme_section_before_download' );

		$section = new Thrive_Section( $section_id );
		$section = apply_filters( 'thrive_theme_section_object', $section );

		$data = [
			'id'      => $section_id,
			'content' => $section->render(),
			'style'   => $section->style( false, true ),
		];

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Update section data
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function update( $request ) {

		$title      = $request->get_param( 'title' );
		$meta       = $request->get_param( 'meta_input' );
		$section_id = $request->get_param( 'id' );

		if ( ! empty( $meta ) ) {
			if ( is_string( $meta ) ) {
				$meta = json_decode( $meta, true );
			}

			foreach ( Thrive_Section::$meta_fields as $key => $value ) {
				if ( isset( $meta[ $key ] ) ) {
					update_post_meta( $section_id, $key, $meta[ $key ] );
				}
			}

			if ( ! empty( $meta['conditional-display-groups'] ) ) {
				Conditional_Display_Group::save_groups( $meta['conditional-display-groups'] );
			}

			thrive_skin()->generate_style_file();
		}

		if ( method_exists( '\TCB\Lightspeed\Main', 'handle_optimize_saves' ) ) {
			\TCB\Lightspeed\Main::handle_optimize_saves( $section_id, $request );
		}

		if ( ! empty( $title ) ) {
			wp_update_post( [
				'ID'          => $section_id,
				'post_title'  => $title,
				'post_status' => 'publish',
			] );
		}

		return new WP_REST_Response( $section_id, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function delete( $request ) {
		$section_id = $request->get_param( 'id' );
		$type       = $request->get_param( 'type' );

		if ( in_array( $type, [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ], true ) ) {
			$section = new Thrive_HF_Section( $section_id, $type );
		} else {
			$section = new Thrive_Section( $section_id );
		}

		$section->delete();

		return new WP_REST_Response( $section_id, 200 );
	}

	/**
	 * Prepare section before wp_insert_post
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return array
	 */
	private static function prepare_section_for_insert( $request ) {

		$section = [
			'post_status' => 'publish',
			'post_type'   => THRIVE_SECTION,
		];

		$meta                  = $request->get_param( 'meta_input' );
		$section['post_title'] = $request->get_param( 'post_title' );

		/* First let's make sure we have all the default meta values*/
		$section['meta_input'] = wp_parse_args( $meta, Thrive_Section::$meta_fields );

		/* if no skin tag was passed, use the one from the current active skin */
		if ( empty( $section['meta_input']['skin_tag'] ) ) {
			$section['meta_input']['skin_tag'] = thrive_skin()->get_tag();
		}

		$section['meta_input'][ THRIVE_EXPORT_ID ] = Thrive_Utils::get_unique_id();

		/**
		 * Allows changes of the section right before save
		 */
		return apply_filters( 'thrive_theme_rest_section_before_insert', $section );
	}

	/**
	 * Save thumbnail for section
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public static function preview( $request ) {

		if ( ! isset( $_FILES['img_data'] ) ) {
			return new WP_Error( 'section_preview_error', __( 'Wrong section preview data', 'thrive-theme' ) );
		}

		$id = $request->get_param( 'id' );

		try {
			$section = new Thrive_Section( $id );

			$response = new WP_REST_Response( $section->create_preview(), 200 );
		} catch ( Exception $ex ) {
			$response = new WP_Error( 'section_preview_error', $ex->getMessage() );
		}

		return $response;
	}

	/**
	 * Check if a given request has access to the route.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function route_permission( $request ) {
		return Thrive_Theme_Product::has_access();
	}
}
