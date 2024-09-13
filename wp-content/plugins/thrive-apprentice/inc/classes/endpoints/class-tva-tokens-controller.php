<?php

/**
 * Class TVA_Tokens_Controller
 * - used for handling wp-json requests
 */
class TVA_Tokens_Controller extends WP_REST_Controller {

	protected $namespace = 'tva/v1';

	/**
	 * Routes used for API Settings view on client side
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/tokens',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'permission_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/token/generate',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'generate_item' ),
				'permission_callback' => array( $this, 'permission_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/token',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'permission_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/token/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
			)
		);
	}

	/**
	 * Fetches tokens from DB
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$tokens = TVA_Token::get_items();

		return rest_ensure_response( $tokens );
	}

	/**
	 * Save a API Token to DB
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$token = new TVA_Token( $request->get_params() );

		if ( ! is_wp_error( $token->save() ) ) {
			return rest_ensure_response( $token->get_data() );
		}

		return new WP_Error( 'cannot_create_token', esc_html__( 'Token could not be created', 'thrive-apprentice' ), array( 'status' => 500 ) );
	}

	/**
	 * Deletes and item from DB
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {

		$id    = (int) $request->get_param( 'id' );
		$token = new TVA_Token( $id );

		if ( $token->delete() ) {
			return new WP_REST_Response( true, 200 );
		}

		return new WP_Error( 'token_not_deleted', esc_html__( 'Token item could not be deleted', 'thrive-apprentice' ), array( 'status' => 500 ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$token = new TVA_Token( $request->get_params() );

		if ( true === $token->save() ) {
			return rest_ensure_response( $token->get_data() );
		}

		return new WP_Error(
			'token_not_updated',
			esc_html__( 'Token could not be updated', 'thrive-apprentice' ),
			array( 'status' => 500 )
		);
	}

	/**
	 * Generates a new API Token without saving it to DB
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function generate_item() {

		$token = new TVA_Token( array() );

		return rest_ensure_response( $token->get_data() );
	}

	/**
	 * Check for get items permission
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function permission_check( $request ) {

		if ( false === current_user_can( 'manage_options' ) ) {

			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You cannot view the resource.', 'thrive-apprentice' ),
				array( 'status' => $this->authorization_status_code() )
			);
		}

		return true;
	}

	/**
	 * Based on current user returns 401 or 403 status code
	 *
	 * @return int status
	 */
	public function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}
