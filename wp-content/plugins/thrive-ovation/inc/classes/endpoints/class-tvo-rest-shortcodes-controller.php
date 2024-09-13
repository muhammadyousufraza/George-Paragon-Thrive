<?php

/**
 * Class TVO_REST_Shortcodes_Controller
 */
class TVO_REST_Shortcodes_Controller extends TVO_REST_Controller {

	public $base = 'shortcodes';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/frontend/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item_frontend' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/render', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Return shortocode config and html
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$config = $request->get_param( 'config' );

		if ( empty( $config ) ) {
			return new WP_Error( 'cant-get-data', __( 'message', 'thrive-ovation' ), array( 'status' => 500 ) );
		}

		$html = tvo_render_shortcode( $config );

		return new WP_REST_Response( array(
			'config' => $config,
			'html'   => $html,
		), 200 );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$type = $request->get_param( 'type' );

		$items = tvo_get_shortcodes( $type );

		if ( ! is_array( $items ) ) {
			return new WP_Error( 'cant-get-data', __( 'message', 'thrive-ovation' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $items, 200 );
	}

	/**
	 * Get item config
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_frontend( $request ) {
		$id = $request->get_param( 'id' );

		$config = tvo_get_shortcode_config( $id );

		$template = tvo_render_shortcode( $config );

		return new WP_REST_Response( array(
			'config'   => $config,
			'template' => $template,
		), 200 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$params         = $request->get_params();
		$shortcode_type = $params['type'] === 'capture' ? TVO_CAPTURE_POST_TYPE : TVO_DISPLAY_POST_TYPE;
		$shortcode      = array(
			'post_status'  => 'publish',
			'post_content' => $params['content'],
			'post_title'   => $params['name'],
			'post_type'    => $shortcode_type,
			'type'         => $shortcode_type,
			'meta_input'   => array(
				'tvo_shortcode_type'   => $params['type'],
				'tvo_shortcode_config' => empty( $params['config'] ) ? array() : $params['config'],
			),
		);

		$shortcode_id = wp_insert_post( $shortcode, true );

		if ( ! empty( $shortcode_id ) && ! is_wp_error( $shortcode_id ) ) {
			$shortcode['url']    = tcb_get_editor_url( $shortcode_id );
			$shortcode['id']     = $shortcode_id;
			$shortcode['config'] = tvo_get_shortcode_config( $shortcode_id, $params['type'] );

			if ( ! empty( $params['html'] ) ) {
				/* if needed return the html also */
				$shortcode['html'] = tvo_render_shortcode( $shortcode['config'] );
			}

			return new WP_REST_Response( $shortcode, 200 );
		}

		return new WP_Error( 'cant-create', __( 'message', 'thrive-ovation' ), array( 'status' => 500 ) );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$post = $this->prepare_item_for_database( $request );

		$post_id = wp_update_post( $post, true );

		if ( is_wp_error( $post_id ) ) {
			$error = $post_id->get_error_message();

			return new WP_Error( 'cant-update', __( $error, 'thrive-ovation' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $post_id, 200 );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return array
	 */
	protected function prepare_item_for_database( $request ) {

		$id     = $request->get_param( 'id' );
		$title  = $request->get_param( 'name' );
		$config = $request->get_param( 'config' );

		$post = array( 'ID' => $id );

		/* update shortcode title from admin area */
		if ( ! empty( $title ) ) {
			$post['post_title'] = $title;
		}

		/* update config shortcode */
		if ( ! empty( $config ) ) {
			$post['meta_input']['tvo_shortcode_config'] = $config;
		}

		return $post;

	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}


	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$shortcode = get_post( $id );

		if ( ! empty( $shortcode ) ) {
			if ( wp_trash_post( $id ) != false ) {
				return new WP_REST_Response( true, 200 );
			}
		}

		return new WP_Error( 'Invalid shortcode ID', __( 'Delete action failed', 'thrive-ovation' ), array( 'status' => 500 ) );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}
}
