<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/18/2018
 * Time: 9:49 AM
 */

/**
 * Class TVA_User_Controller
 *
 * @deprecated
 */
class TVA_User_Controller extends TVA_REST_Controller {

	/**
	 * endpoint base
	 *
	 * @var string
	 */
	public $base = 'user';

	/**
	 * The User ID
	 *
	 * @var $user_id
	 */
	private $user_id;

	/**
	 * The request
	 *
	 * @var $request
	 */
	private $request;

	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/login/',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'process_login' ),
					'permission_callback' => array( $this, 'user_login_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/tva_register/',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'process_register' ),
					'permission_callback' => array( $this, 'user_register_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/recover/',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'process_recover' ),
					'permission_callback' => array( $this, 'user_login_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/process_redirect/',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'process_redirect' ),
					'permission_callback' => array( $this, 'user_can_register' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/upload_file/',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'tva_upload_file' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/(?P<ID>[\d]+)/accessItems',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_access_items' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/(?P<ID>[\d]+)/deleteAccessItem',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'delete_access_item' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/get_customers',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE . ',' . WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_customers' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			self::$namespace . self::$version,
			'/users',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$args               = array();
		$args['offset']     = $request->get_param( 'offset' );
		$args['limit']      = $request->get_param( 'limit' );
		$args['product_id'] = $request->get_param( 'course_id' );
		$args['s']          = $request->get_param( 's' );

		$customers = TVA_Customer::get_list( $args );
		$total     = (int) TVA_Customer::get_list( $args, true );

		$response = array(
			'total' => $total,
			'items' => $customers,
		);

		return new WP_REST_Response( $response );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array|object|null
	 */
	public function get_customers( $request ) {

		$args               = array();
		$args['offset']     = $request->get_param( 'offset' );
		$args['limit']      = $request->get_param( 'limit' );
		$args['product_id'] = $request->get_param( 'course_id' );
		$args['s']          = $request->get_param( 's' );

		if ( empty( $args['product_id'] ) ) {
			$args['product_id'] = $request->get_param( 'product_id' );
		}

		$data = array(
			'customers' => TVA_Customer::get_list( $args ),
			'count'     => $request->get_param( 'count_value' ),
		);

		if ( (bool) $request->get_param( 'count' ) === true ) {
			$data['count'] = TVA_Customer::get_list( $args, true );
		}

		return $data;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function edit_customer( $request ) {
		$id             = $request->get_param( 'ID' );
		$memberships    = (array) $request->get_param( 'membership_ids' );
		$bundles        = (array) $request->get_param( 'bundle_ids' );
		$p_ids          = array_merge( $memberships, $bundles );
		$existing_ids   = array();
		$orders_updated = array();
		$tva_user       = new TVA_User( $id );

		foreach ( $tva_user->get_orders_by_status( TVA_Const::STATUS_COMPLETED ) as $order ) {
			/** @var TVA_Order $order */
			foreach ( $order->get_order_items() as $item ) {
				/** @var TVA_Order_Item $item */

				/**
				 * If an order item is fond on user obj, but no longer in request means that the user removed access
				 * to that product
				 */
				if ( ! in_array( $item->get_product_id(), $p_ids ) ) {
					$order->unset_order_item( $item );
					$item->delete(); // TODO: WE SHOULD THINK HARDER HERE
					unset( $p_ids[ array_search( $item->get_product_id(), $p_ids ) ] );
					$orders_updated[] = $order->get_id();
				} else {
					$existing_ids[] = $item->get_product_id();
				}
			}

			if ( count( $order->get_order_items() ) === 0 ) {
				$order->set_status( TVA_Const::STATUS_EMPTY );
				$order->save();
			}
		}

		$existing_ids = array_unique( $existing_ids );
		$bundles      = array_diff( $bundles, $existing_ids );
		$memberships  = array_diff( $memberships, $existing_ids );

		if ( ! empty( $bundles ) || ! empty( $memberships ) ) {
			$user = get_userdata( $id );

			$user->bundle_ids     = $bundles;
			$user->membership_ids = $memberships;

			$this->tva_create_customer_order( $user );
		}

		return new WP_REST_Response( $orders_updated, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function tva_upload_file( $request ) {
		$file_data = $request->get_param( 'tva_csv_file' );
		$rows      = array();

		if ( ( $handle = fopen( $file_data['tmp_name'], 'r' ) ) !== false ) {
			while ( ( ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) ) && count( $rows ) < 1001 ) {
				$rows[] = $data;
			}
			fclose( $handle );
		}

		$data = $this->tva_process_data_from_csv( $rows );

		if ( empty( $data ) ) {
			$request['invalid_file'] = true;

			return new WP_REST_Response( array( 'invalid_file' => true ), 200 );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * @param $data
	 *
	 * @return array|bool
	 */
	public function tva_process_data_from_csv( $data ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		$headers = isset( $data[0] ) ? $data[0] : false;

		if ( ! is_array( $headers ) ) {
			return false;
		}

		$_headers  = array();
		$_data     = array();
		$email_key = null;
		$name_key  = null;

		foreach ( $headers as $key => $header ) {
			$_key       = trim( strtolower( $header ) );
			$_key       = str_replace( array( '/', '&', '?', '@', ',', '"' ), '', $_key );
			$_key       = str_replace( ' ', '_', $_key );
			$_headers[] = $_key;

			if ( $_key === 'buyer_name' ) {
				$name_key = $key;
			}

			if ( $_key === 'buyer_email' ) {
				$email_key = $key;
			}
		}

		if ( ! in_array( 'buyer_name', $_headers ) || ! in_array( 'buyer_email', $_headers ) ) {
			return array( 'invalid_file' => true );
		}

		unset( $data[0] );

		foreach ( $data as $key => $row ) {
			// invalid row
			if ( count( $row ) !== count( $_headers ) ) {
				continue;
			}
			$row['is_valid'] = true;
			$email           = sanitize_email( $row[ $email_key ] );

			/**
			 * If the email is invalid we unset the current row
			 */
			if ( ! is_email( $email ) ) {
				$row['is_valid'] = false;
			}
			/**
			 * If we don't have a name in order, we will use the first part of the email
			 */
			if ( empty( $row[ $name_key ] ) ) {
				$chunks           = explode( '@', $email );
				$row[ $name_key ] = sanitize_text_field( $chunks[0] );
			}

			foreach ( $row as $row_key => $item ) {
				unset( $row[ $row_key ] );

				$item            = str_replace( array( '/', '&', '?', ',', '"' ), '', $item );
				$row_key         = isset( $_headers[ $row_key ] ) ? $_headers[ $row_key ] : $row_key;
				$row[ $row_key ] = $item;
			}

			$emails = wp_list_filter( $_data, array( 'buyer_email' => $row['buyer_email'] ) );

			/**
			 * Make sure we push a user only once
			 */
			if ( empty( $emails ) ) {
				$_data[] = $row;
			}
		}

		return $_data;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function process_login( $request ) {
		$this->request         = $request;
		$info                  = array();
		$info['user_login']    = $request->get_param( 'username' );
		$info['user_password'] = $request->get_param( 'password' );
		$info['remember']      = true;

		if ( function_exists( 'wishlistmember_instance' ) ) {
			remove_action( 'wp_login', array( wishlistmember_instance(), 'login' ) );
		}

		$user_signon = wp_signon( $info, false );

		if ( function_exists( 'wishlistmember_instance' ) ) {
			add_action( 'wp_login', array( wishlistmember_instance(), 'login' ) );
		}

		if ( is_wp_error( $user_signon ) ) {
			return new WP_Error( 'error_while_login', __( 'Wrong username or password.', 'thrive-apprentice' ) );
		}

		$this->user_id = $user_signon->ID;
		/**
		 * Process payment
		 */
		$payment_processor = $request->get_param( 'payment_processor' );
		$bid               = (int) $request->get_param( 'bid' );
		$pid               = (int) $request->get_param( 'pid' );
		$discount          = $request->get_param( 'thrv_so_discount' );

		$type  = ! empty( $bid ) ? 'bid' : '';
		$value = ! empty( $bid ) ? $bid : 0;

		if ( ! empty( $pid ) ) {
			$type  = 'pid';
			$value = $pid;
		}

		$result = $this->process_payment( $payment_processor, $type, $value, $discount );

		if ( $result ) {
			$response = array(
				'message'  => __( 'Login successful, redirecting...', 'thrive-apprentice' ),
				'redirect' => $result,
			);
		} else {
			$url = tva_get_settings_manager()->factory( 'index_page' )->get_link();

			$response['redirect'] = $url ? $url : get_home_url();
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function process_register( $request ) {
		$this->request = $request;
		$fields        = array(
			'user_login'       => sanitize_email( $request->get_param( 'email' ) ),
			'user_email'       => sanitize_email( $request->get_param( 'email' ) ),
			'user_pass'        => esc_attr( $request->get_param( 'password' ) ),
			'confirm_password' => esc_attr( $request->get_param( 'confirm_password' ) ),
			'first_name'       => sanitize_text_field( $request->get_param( 'first_name' ) ),
			'role'             => 'subscriber',
		);

		foreach ( $fields as $field ) {
			if ( empty( $field ) ) {
				return new WP_Error( 'empty_field_check', __( 'Please do not leave empty fields', 'thrive-apprentice' ) );
				break;
			}
		}

		if ( ! is_email( $fields['user_email'] ) ) {
			return new WP_Error( 'email_not_valid', __( 'The email you enter is not a valid email!', 'thrive-apprentice' ) );
		}

		if ( $fields['user_pass'] !== $fields['confirm_password'] ) {
			return new WP_Error( 'password_not_match', __( 'The passwords you enter did not match!', 'thrive-apprentice' ) );
		}

		//check if there are templates set for sendowl trigger
		$email_template = tva_email_templates()->check_templates_for_trigger( 'sendowl' );
		if ( false !== $email_template ) {
			$email_template['user_pass'] = $fields['user_pass'];
			tva_email_templates()->trigger_process( $email_template );
		}

		$user = wp_insert_user( $fields );

		if ( is_wp_error( $user ) ) {
			return new WP_Error( $user->get_error_code(), $user->get_error_message() );
		}

		//because of wp_insert() notification has to be triggered manually
		if ( false !== $email_template ) {
			wp_send_new_user_notifications( $user, 'both' );
		}

		$route = '/' . self::$namespace . self::$version . '/' . $this->base . '/login';
		$login = new WP_REST_Request( 'POST', $route );

		$login->set_query_params( array(
			'username'          => $fields['user_login'],
			'password'          => $fields['user_pass'],
			'payment_processor' => $request->get_param( 'payment_processor' ),
			'bid'               => $request->get_param( 'bid' ),
			'pid'               => $request->get_param( 'pid' ),
			'thrv_so_discount'  => $request->get_param( 'thrv_so_discount' ),
		) );

		$response = rest_do_request( $login );
		$server   = rest_get_server();
		$data     = $server->response_to_data( $response, false );

		if ( $data ) {
			return new WP_REST_Response( $data, 200 );
		}

		return new WP_Error( 'error_while_inserting_user', __( 'Oops, Something went wrong!', 'thrive-apprentice' ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function process_recover( $request ) {

		$user_login = $request->get_param( 'user_login' );
		if ( empty( $user_login ) || ! is_string( $user_login ) ) {
			return new WP_Error( 'empty_username', __( 'Enter a username or email address.', 'thrive-apprentice' ) );
		} elseif ( strpos( $user_login, '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $user_login ) ) );
			if ( empty( $user_data ) ) {
				return new WP_Error( 'invalid_email', __( 'There is no user registered with that email address.', 'thrive-apprentice' ) );
			}
		} else {
			$user_data = get_user_by( 'login', trim( $user_login ) );
		}

		if ( ! $user_data ) {
			return new WP_Error( 'invalidcombo', __( 'Invalid username or email.', 'thrive-apprentice' ) );
		}

		$wp_mail_response = $this->recover_password( $user_data );

		if ( ! $wp_mail_response ) {
			return new WP_Error( 'invalidcombo', __( 'The email could not be sent. Possible reason: your host may have disabled the mail() function.', 'thrive-apprentice' ) );
		}

		return new WP_REST_Response( array(
			'message' => null,
			'state'   => 'reset_confirmation',
		), 200 );
	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function process_redirect( $request ) {
		/**
		 * Process payment
		 */
		$this->request     = $request;
		$payment_processor = $request->get_param( 'payment_processor' );
		$bid               = (int) $request->get_param( 'bid' );
		$pid               = (int) $request->get_param( 'pid' );
		$discount          = $request->get_param( 'thrv_so_discount' );

		$type  = ! empty( $bid ) ? 'bid' : '';
		$value = ! empty( $bid ) ? $bid : 0;

		if ( ! empty( $pid ) ) {
			$type  = 'pid';
			$value = $pid;
		}

		$this->user_id = get_current_user_id();

		$result = $this->process_payment( $payment_processor, $type, $value, $discount );

		if ( $result ) {
			$response = array(
				'message'  => __( 'Login successful, redirecting...', 'thrive-apprentice' ),
				'redirect' => $result,
			);
		} else {
			return new WP_Error( 'invalidcombo', __( 'Something went wrong, please try again ...', 'thrive-apprentice' ) );
		}

		return new WP_REST_Response( $response, 200 );
	}


	/**
	 * @param     $payment_processor
	 * @param     $type
	 * @param int $id
	 *
	 * @return bool|string
	 */
	public function process_payment( $payment_processor, $type, $id = 0, $discount = 0 ) {
		$allow_payment_processors = array( 'Sendowl' );

		if ( ! in_array( $payment_processor, $allow_payment_processors ) || empty( $type ) || empty( $id ) ) {
			return false;
		}

		$order = new TVA_Order();
		$order->set_user_id( $this->user_id );
		$order->set_gateway( $payment_processor );
		$order->set_number( uniqid( $this->user_id ) );

		if ( 'pid' === $type ) {
			$product = TVA_SendOwl::get_product_by_id( $id );
		} else {
			$product = TVA_SendOwl::get_bundle_by_id( $id );
		}

		if ( $product ) {
			$product_type = isset( $product['product_type'] ) ? $product['product_type'] : 'bundle';
			$order_item   = new TVA_Order_Item();
			$order_item->set_product_id( $product['id'] );
			$order_item->set_product_name( $product['name'] );
			$order_item->set_product_type( $product_type );
			$order_item->set_product_price( $product['price'] );
			$order_item->set_currency( $product['currency_code'] );

			$order->set_order_item( $order_item );
		}

		$result = $order->save();

		/**
		 * Drop a cookie on the checkout page for sendowl for us to know which product is purchased
		 */

		$data = array(
			'type' => $type,
			'id'   => $id,
		);

		setcookie( TVA_Const::TVA_SENDOWL_COOKIE_NAME, maybe_serialize( $data ), time() + 3600, '/' );

		if ( $result ) {
			$user_data = get_userdata( $this->user_id );
			$name      = urlencode( $user_data->first_name );

			$purchase_url = $product['instant_buy_url'];

			$purchase_url = add_query_arg(
				array(
					'tag'         => tva_calculate_order_tag( array( $order->get_number() ) ),
					'buyer_name'  => $name,
					'buyer_email' => $user_data->user_email,
				),
				$purchase_url
			);

			if ( ! empty( $discount ) ) {
				$purchase_url = add_query_arg(
					array(
						'discount_code' => $discount,
					),
					$purchase_url
				);
			}

			TVA_Cookie_Manager::set_cookie( TVA_Thankyou::LAST_PURCHASED_PRODUCT, $product['id'] );

			return $purchase_url;
		}

		return false;
	}

	/**
	 * Check if the user has permission to execute this ajax call
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function user_login_permission_check( $request ) {
		return ! is_user_logged_in();
	}

	/**
	 * Check if the user has permission to execute this ajax call
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function user_register_permission_check( $request ) {
		return intval( get_option( 'users_can_register' ) );
	}

	/**
	 * Check if the user can register
	 *
	 * @return bool
	 */
	public function user_can_register() {
		if ( is_user_logged_in() ) {
			return true;
		}

		return boolval( get_option( 'users_can_register' ) );
	}

	/**
	 * Constructs the user recover email and sends the email
	 *
	 * @param $user_data
	 *
	 * @return bool
	 */
	private function recover_password( $user_data ) {
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		$key = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return false;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		/**
		 * Password Recover Email Subject
		 */
		$title = sprintf( __( '[%s] Password Reset' ), $site_name );

		return wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function admin_permission_check( $request ) {

		return TVA_Product::has_access();
	}

	/**
	 * @param WP_User $customer
	 * @param array   $order_data
	 *
	 * @return bool|false|int
	 */
	public function tva_create_customer_order( $customer, $order_data = array() ) {

		if ( ! $customer instanceof WP_User ) {
			return false;
		}

		$order = new TVA_Order();
		$order->set_user_id( $customer->ID );
		$order->set_gateway( ! empty( $order_data['gateway'] ) ? $order_data['gateway'] : TVA_Const::MANUAL_GATEWAY );
		$order->set_status( ! empty( $order_data['status'] ) ? $order_data['status'] : TVA_Const::STATUS_COMPLETED );

		$order_data['sendowl_bundles']  = empty( $order_data['sendowl_bundles'] ) ? array() : $order_data['sendowl_bundles'];
		$order_data['sendowl_products'] = empty( $order_data['sendowl_products'] ) ? array() : $order_data['sendowl_products'];
		$order_data['course_ids']       = empty( $order_data['course_ids'] ) ? array() : $order_data['course_ids'];

		foreach ( $order_data['sendowl_bundles'] as $bundle_id ) {
			$product = TVA_SendOwl::get_bundle_by_id( $bundle_id );

			if ( ! $product ) {
				continue;
			}

			$product_type = 'bundle';
			$order_item   = new TVA_Order_Item();
			$order_item->set_product_id( $product['id'] );
			$order_item->set_product_name( $product['name'] );
			$order_item->set_product_type( $product_type );
			$order_item->set_product_price( $product['price'] );
			$order_item->set_currency( $product['currency_code'] );

			$order->set_order_item( $order_item );
		}

		foreach ( $order_data['sendowl_products'] as $product_id ) {
			$product = TVA_SendOwl::get_product_by_id( $product_id );

			if ( ! $product ) {
				continue;
			}

			$product_type = $product['product_type'];
			$order_item   = new TVA_Order_Item();
			$order_item->set_product_id( $product['id'] );
			$order_item->set_product_name( $product['name'] );
			$order_item->set_product_type( $product_type );
			$order_item->set_product_price( $product['price'] );
			$order_item->set_currency( $product['currency_code'] );

			$order->set_order_item( $order_item );
		}

		if ( ! empty( $order_data['course_ids'] ) ) {
			foreach ( $order_data['course_ids'] as $course_id ) {

				$order_item = new TVA_Order_Item();
				$order_item->set_product_id( $course_id );
				$order_item->set_product_name( 'Manually added' );
				$order_item->set_product_type( 'manually' );
				$order_item->set_product_price( 0 );
				$order_item->set_currency( 'USD' );

				$order->set_order_item( $order_item );
			}
		}

		if ( ! empty( $order_data['type'] ) ) {
			$order->set_type( $order_data['type'] );
		}

		return $order->save( true );
	}

	/**
	 * @param WP_User $user
	 */
	public function tva_send_customer_email( $user ) {
		global $WishListMemberInstance;

		$is_wl = class_exists( 'WishListMember3' ) && $WishListMemberInstance instanceof WishListMember3;

		if ( true === $is_wl ) {
			remove_action( 'retrieve_password', array( $WishListMemberInstance, 'RetrievePassword' ) );
		}

		$key = get_password_reset_key( $user );

		if ( true === $is_wl ) {
			add_action( 'retrieve_password', array( $WishListMemberInstance, 'RetrievePassword' ) );
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'A new account has been created for you:' ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n";

		/**
		 * Password Recover Email Subject
		 */
		$title = sprintf( __( '[%s] New account' ), $site_name );

		wp_mail( $user->user_email, wp_specialchars_decode( $title ), $message );
	}

	/**
	 * Fetches from the server a list of courses current user has access to
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_access_items( $request ) {

		$items    = array();
		$user     = new WP_User( $request->get_param( 'ID' ) );
		$products = \TVA\Product::get_items();
		tva_access_manager()->set_user( $user );

		foreach ( $products as $product ) {

			$allowed = tva_access_manager()
				->set_product( $product )
				->check_rules();

			if ( $allowed ) {
				$order       = tva_access_manager()->get_allowed_integration()->get_order();
				$order_item  = tva_access_manager()->get_allowed_integration()->get_order_item();
				$access_type = tva_access_manager()->get_allowed_integration()->get_label();
				$type        = tva_access_manager()->get_allowed_integration()->get_slug();
				$gateway     = tva_access_manager()->get_allowed_integration()->get_order()->get_gateway();

				/**
				 * if the order's gateway or type is manual
				 */
				if ( false !== stripos( $gateway, 'manual' ) || $order->get_type() === TVA_Order::MANUAL ) {
					$access_type = __( 'Manually added', 'thrive-apprentice' );
					$type        = 'added_manually';
				} elseif ( false !== stripos( $gateway, 'import' ) || $order->get_type() === TVA_Order::IMPORTED ) {
					/**
					 * imported order
					 */
					$access_type = __( 'Imported', 'thrive-apprentice' );
					$type        = 'imported';
				}

				$items[] = array(
					'name'          => $product->get_name(),
					'access_type'   => $access_type,
					'type'          => $type,
					'order_item_id' => $order_item->get_ID(),
				);
			}
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Loop through each order item and remove the course id
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return true
	 */
	public function delete_access_item( $request ) {

		global $wpdb;
		$id               = (int) $request->get_param( 'order_item_id' );
		$items_table_name = TVA_Order_Item::get_table_name();

		$item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $items_table_name WHERE ID = %d",
				array(
					$id,
				)
			)
		);

		$order_id = null;

		if ( ! empty( $item ) ) {
			$wpdb->delete( TVA_Order_Item::get_table_name(), array( 'ID' => $id ) );
			$order_id = (int) $item->order_id;
		}

		$order       = new TVA_Order( $order_id );
		$order_items = $order->get_order_items();
		if ( empty( $order_items ) ) {
			$order->set_status( TVA_Const::STATUS_EMPTY );
			$order->save( false );
		}

		return true;
	}
}
