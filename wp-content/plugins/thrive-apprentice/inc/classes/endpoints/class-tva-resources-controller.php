<?php

use function TVA\Architect\Resources\tva_resource_content;

class TVA_Resources_Controller extends TVA_REST_Controller {
	public $base = 'resources';

	/**
	 * Checks if a given request has access to read posts of this type.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function permissions_check( $request ) {
		return TVA_Product::has_access();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		register_rest_route(
			static::$namespace . static::$version,
			$this->base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'lesson_id' => array(
							'type'        => 'integer',
							'description' => 'ID of the lesson',
							'required'    => true,
						),
					),
				),
			)
		);

		register_rest_route(
			static::$namespace . static::$version,
			$this->base . '/mass-update',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'mass_update' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'lesson_id'   => array(
							'type'        => 'integer',
							'description' => 'ID of the lesson',
							'required'    => true,
						),
						'resources'   => array(
							'type' => 'array',
						),
						'removed_ids' => array(
							'type' => 'array',
						),
					),
				),
			)
		);

		register_rest_route( static::$namespace . static::$version,
			$this->base . '/html', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'get_html' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => 'ID of the lesson',
							'required'    => true,
						),
						'dynamic' => array(
							'type'        => 'integer',
							'description' => 'Is or not is dynamic',
							'required'    => false,
						),
					),
				),
			) );
		register_rest_route( static::$namespace . static::$version,
			$this->base . '/hide_default', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'hide_template_shortcode' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id'    => array(
							'type'        => 'integer',
							'description' => 'ID of the lesson',
							'required'    => true,
						),
						'value' => array(
							'type'        => 'integer',
							'description' => 'Possible values that may have',
							'required'    => true,
							'enum'        => array( 0, 1 ),
						),
					),
				),
			) );

		register_rest_route( static::$namespace . static::$version,
			$this->base . '/shortcode', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'render_shortcode' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'shortcode' => array(
							'type'        => 'string',
							'description' => 'Resources shortcode',
							'required'    => true,
						),
					),
				),
			) );

		register_rest_route(
			static::$namespace . static::$version,
			$this->base . '/get_data',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_editor_data' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Load data needed for TAR element settings
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_editor_data( $request ) {
		$courses = TVA_Course_V2::get_items();
		$return  = [];
		foreach ( $courses as $course ) {
			$return[ $course->id ] = array(
				'id'        => $course->get_id(),
				'name'      => $course->name,
				'edit_link' => $course->get_edit_link(),
				'lessons'   => $course->get_all_lessons(),
			);
		}

		return new WP_REST_Response( [ 'courses' => $return ], 200 );
	}

	/**
	 * @param $request
	 *
	 * @return bool
	 */
	public function hide_template_shortcode( $request ) {
		$id = (int) $request->get_param( 'id' );

		if ( ! empty( $id ) ) {
			update_post_meta( $id, 'tva_hide_default_resources', (int) $request->get_param( 'value' ) );

			return true;
		}

		return false;
	}

	/**
	 * Render resources for a cloud template
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function render_shortcode( $request ) {
		$shortcode = $request->get_param( 'shortcode' );

		if ( empty( $shortcode ) ) {
			return new WP_REST_Response( array( 'message' => 'Invalid Lesson ID' ), 401 );
		}

		return new WP_REST_Response( [ 'html' => do_shortcode( $shortcode ) ], 200 );
	}

	/**
	 * Get default resource element for a specific lesson
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_html( $request ) {
		$id = (int) $request->get_param( 'id' );

		if ( empty( $id ) ) {
			return new WP_REST_Response( array( 'message' => 'Invalid Lesson ID' ), 401 );
		}

		return new WP_REST_Response( [
			'html' => tva_resource_content()->default_lesson_resources( [
				'id'      => $id,
				'dynamic' => (int) $request->get_param( 'dynamic' ),
			] ),
		], 200 );
	}

	/**
	 * Get the list of resources for a lesson.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		return rest_ensure_response( TVA_Resource::all( $request->get_param( 'lesson_id' ), [ 'show_all_resources' => 1 ] ) );
	}

	/**
	 * Mass-update resources for a lesson. Updates, adds and removes resources
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function mass_update( $request ) {
		$resources   = array();
		$removed_ids = $request->get_param( 'removed_ids' );
		$to_save     = $request->get_param( 'resources' );
		$lesson_id   = (int) $request->get_param( 'lesson_id' );

		if ( $removed_ids && is_array( $removed_ids ) ) {
			$removed_ids = array_filter( $removed_ids );
			foreach ( $removed_ids as $id ) {
				wp_delete_post( (int) $id );
			}
		}

		if ( $to_save && is_array( $to_save ) ) {
			foreach ( $to_save as $data ) {
				$data['lesson_id'] = $lesson_id;
				$resources[]       = TVA_Resource::one( $data )->save();
			}
		}

		update_post_meta( $lesson_id, 'tva_resource_count', count( $resources ) );

		return rest_ensure_response( $resources );
	}
}
