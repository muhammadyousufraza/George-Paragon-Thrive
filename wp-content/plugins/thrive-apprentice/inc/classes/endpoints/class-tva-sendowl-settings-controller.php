<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/23/2019
 * Time: 16:00
 */

/**
 * @deprecated
 */
class TVA_Sendowl_Settings_Controller extends TVA_REST_Controller {

	/**
	 * Controller base
	 *
	 * @var string
	 */
	public $base = 'so_settings';

	/**
	 * @var WP_REST_Request
	 */
	public $request;

	/**
	 * Register required rest routes
	 */
	public function register_routes() {

		/**
		 * Register the route for checkout page
		 */
		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_checkout_page/',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_checkout_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_checkout_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_checkout_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
			)
		);

		/**
		 * Register the route for thankyou page
		 */
		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_thankyou_page/',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_thankyou_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_thankyou_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_thankyou_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
			)
		);

		/**
		 * Register the route for thankyou page used when user has access to more than one course after purchasing a product
		 */
		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_thankyou_multiple_page/',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_thankyou_multiple_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_thankyou_multiple_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_checkout_multiple_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_thankyou_page_type/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_thankyou_page_type' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			)
		);

		/**
		 * Register the route for saving account keys
		 */
		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_account_keys/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_account_keys' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/refresh_data/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'refresh_data' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			)
		);


		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/sendowl_tutorial/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'mark_sendowl_tutorial' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_welcome_message/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_welcome_message' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			)
		);

		/**
		 * Register the routes for Login page
		 */
		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/save_login_page/',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_login_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_login_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_login_page' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * @return int|WP_Error
	 */
	public function create_page() {
		$data = array(
			'post_type'    => 'page',
			'post_title'   => $this->request->get_param( 'name' ),
			'post_content' => '',
			'post_status'  => 'publish',
		);

		return wp_insert_post( $data );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_checkout_page( $request ) {
		$id   = $request->get_param( 'ID' );
		$args = array(
			'name'   => $request->get_param( 'name' ),
			'ID'     => $id,
			'old_ID' => $request->get_param( 'old_ID' ),
		);

		if ( empty( $id ) ) {
			$args['ID'] = $this->create_page( $request );
		}

		update_option( TVA_Sendowl_Settings::CHECKOUT_PAGE, $args );

		$this->_remove_checkout_element( $args );
		$this->_add_checkout_element( $args );

		return new WP_REST_Response( $this->prepare_response( $args ), 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_thankyou_page( $request ) {
		$args = $this->parse_thankyou_request( $request );

		update_option( TVA_Sendowl_Settings::THANKYOU_PAGE, $args );

		return new WP_REST_Response( $this->prepare_response( $args ), 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_thankyou_multiple_page( $request ) {
		$args = $this->parse_thankyou_request( $request );

		update_option( TVA_Sendowl_Settings::TH_MULTIPLE_PAGE, $args );

		return new WP_REST_Response( $this->prepare_response( $args ), 200 );
	}

	/**
	 * @return bool
	 */
	public function settings_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Add checkout element on checkout page
	 *
	 * @param $checkout
	 */
	private function _add_checkout_element( $checkout ) {
		if ( empty( $checkout['ID'] ) ) {
			return;
		}

		ob_start();
		include( TVA_Const::plugin_path( '/tcb-bridge/editor-layouts/elements/checkout.php' ) );

		$element = ob_get_contents();

		$save_post    = get_post_meta( $checkout['ID'], 'tve_save_post', true );
		$updated_post = get_post_meta( $checkout['ID'], 'tve_updated_post', true );

		if ( strpos( $updated_post, 'thrv-checkout' ) === false ) {
			$save_post    .= $element;
			$updated_post .= $element;
			update_post_meta( $checkout['ID'], 'tve_save_post', $save_post );
			update_post_meta( $checkout['ID'], 'tve_updated_post', $updated_post );
		}

		ob_end_clean();
	}

	/**
	 * Remove a html element from a page based on provided class name
	 *
	 * @param array  $page_data
	 * @param string $class_name
	 */
	private function _remove_element( $page_data, $class_name = '' ) {
		$save_post    = get_post_meta( $page_data['old_ID'], 'tve_save_post', true );
		$updated_post = get_post_meta( $page_data['old_ID'], 'tve_updated_post', true );

		$save_post    = $this->_remove_node( $save_post, $class_name );
		$updated_post = $this->_remove_node( $updated_post, $class_name );

		update_post_meta( $page_data['old_ID'], 'tve_save_post', $save_post );
		update_post_meta( $page_data['old_ID'], 'tve_updated_post', $updated_post );
	}

	/**
	 * Remove checkout element on checkout page
	 *
	 * @param $checkout
	 */
	private function _remove_checkout_element( $checkout ) {

		if ( empty( $checkout['old_ID'] ) ) {
			return;
		}

		$this->_remove_element( $checkout, 'thrv-checkout' );
	}

	/**
	 * Remove an HTML node
	 *
	 * @param string $html
	 * @param string $classname
	 *
	 * @return null|string|string[]
	 */
	private function _remove_node( $html, $classname = '' ) {
		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		$finder   = new DomXPath( $dom );
		$elements = $finder->query( "//*[contains(@class, '$classname')]" );

		foreach ( $elements as $element ) {
			$element->parentNode->removeChild( $element );
		}

		$html_fragment = preg_replace(
			'/^<!DOCTYPE.+?>/',
			'',
			str_replace(
				array(
					'<html>',
					'</html>',
					'<body>',
					'</body>',
				),
				array(
					'',
					'',
					'',
					'',
				),
				$dom->saveHTML()
			)
		);

		return $html_fragment;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function parse_thankyou_request( $request ) {
		$args = array(
			'name'    => $request->get_param( 'name' ),
			'ID'      => $request->get_param( 'ID' ),
			'old_ID'  => $request->get_param( 'old_ID' ),
			'message' => $request->get_param( 'message' ),
			'type'    => $request->get_param( 'type' ),
		);

		return $args;
	}

	/**
	 * @param $args
	 *
	 * @return array
	 */
	public function prepare_response( $args ) {
		$args['preview_url'] = get_permalink( $args['ID'] );
		$args['edit_url']    = tva_get_editor_url( $args['ID'] );
		$args['state']       = 'normal';

		return $args;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_thankyou_page( $request ) {
		update_option( TVA_Sendowl_Settings::THANKYOU_PAGE, TVA_Sendowl_Settings::$default_page );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @return WP_REST_Response
	 */
	public function delete_checkout_page() {
		update_option( TVA_Sendowl_Settings::CHECKOUT_PAGE, TVA_Sendowl_Settings::$default_page );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @return WP_REST_Response
	 */
	public function delete_checkout_multiple_page() {
		update_option( TVA_Sendowl_Settings::TH_MULTIPLE_PAGE, TVA_Sendowl_Settings::$default_page );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function create_checkout_page( $request ) {
		$this->request = $request;

		$old_id = $request->get_param( 'old_ID' );

		if ( ! empty( $old_id ) ) {
			$this->_remove_checkout_element( array( 'old_ID' => $old_id ) );
		}

		$args = $this->_save_option( TVA_Sendowl_Settings::CHECKOUT_PAGE );
		$this->_add_checkout_element( $args );

		return $this->prepare_response( $args );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function create_thankyou_page( $request ) {
		$this->request = $request;

		$args = $this->_save_option( TVA_Sendowl_Settings::THANKYOU_PAGE );

		return $this->prepare_response( $args );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function create_thankyou_multiple_page( $request ) {
		$this->request = $request;

		$args = $this->_save_option( TVA_Sendowl_Settings::TH_MULTIPLE_PAGE );

		return $this->prepare_response( $args );
	}


	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_thankyou_page_type( $request ) {
		$type = $request->get_param( 'type' );

		update_option( TVA_Sendowl_Settings::THANKYOU_PAGE_TYPE, $type );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @param $option
	 *
	 * @return array
	 */
	private function _save_option( $option ) {
		$args = array(
			'ID'   => $this->create_page(),
			'name' => $this->request->get_param( 'name' ),
		);

		update_option( $option, $args );

		return $args;
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function refresh_data( $request ) {
		$option   = sanitize_text_field( $request->get_param( 'option' ) );
		$response = array();

		if ( ! in_array( $option, array( 'products', 'bundles', 'discounts' ) ) ) {
			$response['message'] = __( 'Invalid Request', 'thrive-apprentice' );

			return new WP_REST_Response( $response, 401 );
		}

		switch ( $option ) {
			case 'products':
				$response['items'] = TVA_SendOwl::get_products( true );
				break;
			case 'bundles':
				$response['items'] = TVA_SendOwl::get_bundles( true );
				break;
			case 'discounts':
				$response['items'] = TVA_SendOwl::get_discounts_v2( true );
				break;
		}

		TVA_Sendowl_Manager::update_sendowl_products();

		$response['message'] = $option . ' ' . __( 'refreshed', 'thrive-apprentice' );

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_account_keys( $request ) {
		$key    = sanitize_text_field( $request->get_param( 'key' ) );
		$secret = sanitize_text_field( $request->get_param( 'secret' ) );

		$api         = Thrive_Dash_List_Manager::connection_instance( 'sendowl' );
		$credentials = $api->get_credentials();

		if ( empty( $key ) || $key === $credentials['key'] ) {
			return new WP_REST_Response( array( 'message' => 'Not Allowed' ), 405 );
		}

		if ( empty( $secret ) || $secret === $credentials['secret'] ) {
			return new WP_REST_Response( array( 'message' => 'Not Allowed' ), 405 );
		}

		TVA_SendOwl::set_account_keys( array(
			'key'    => $key,
			'secret' => $secret,
		) );

		return new WP_REST_Response( array( 'message' => __( 'API Account Key & Secret have been saved!', 'thrive-apprentice' ) ), 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function mark_sendowl_tutorial( $request ) {
		$args      = $request->get_param( 'sendowl_tutorial' );
		$completed = isset( $args['completed'] ) ? $args['completed'] : '';

		update_option( TVA_Sendowl_Settings::TUTORIAL_COMPLETED, $completed );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_welcome_message( $request ) {

		$message = $request->get_param( 'message' );

		update_option( TVA_Sendowl_Settings::WELCOME_MESSAGE, $message );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Create Sendowl Login Page
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function create_login_page( $request ) {

		$this->request = $request;

		$args = $this->_save_option( TVA_Settings::LOGIN_PAGE );
		$this->_add_login_element( $args );

		return $this->prepare_response( $args );
	}

	/**
	 * Save Sendowl Login page
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_login_page( $request ) {

		$id   = $request->get_param( 'ID' );
		$args = array(
			'name'   => $request->get_param( 'name' ),
			'ID'     => $id,
			'old_ID' => $request->get_param( 'old_ID' ),
		);

		if ( empty( $id ) ) {
			$args['ID'] = $this->create_page();
		}

		update_option( TVA_Settings::LOGIN_PAGE, $args );

		$this->_add_login_element( $args );

		return new WP_REST_Response( $this->prepare_response( $args ), 200 );
	}

	/**
	 * Reset Sendowl Login Page Option
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_login_page( $request ) {

		update_option( TVA_Settings::LOGIN_PAGE, TVA_Settings::$default_page );

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Add Login Element On Sendol Login Page
	 *
	 * @param array $args
	 */
	private function _add_login_element( $args ) {

		if ( empty( $args['ID'] ) ) {
			return;
		}

		ob_start();

		tcb_template( 'elements/login.php', array(), false );

		$element = ob_get_contents();

		$save_post    = get_post_meta( $args['ID'], 'tve_save_post', true );
		$updated_post = get_post_meta( $args['ID'], 'tve_updated_post', true );

		if ( strpos( $updated_post, 'thrv-login-element' ) === false ) {
			$save_post    .= $element;
			$updated_post .= $element;
			update_post_meta( $args['ID'], 'tve_save_post', $save_post );
			update_post_meta( $args['ID'], 'tve_updated_post', $updated_post );
		}

		ob_end_clean();
	}
}
