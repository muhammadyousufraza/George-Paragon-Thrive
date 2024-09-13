<?php

use TVA\Product_Migration;

/**
 * Class TVA_Orders_Controller
 * - routes for Order with their endpoints
 */
class TVA_Orders_Controller extends WP_REST_Controller {

	/**
	 * @var string namespace
	 */
	protected $namespace = 'tva/v1';

	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/newOrder',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'api_token_permission_check' ),
					'args'                => $this->get_create_item_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/refundOrder',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'refund_order' ),
					'permission_callback' => array( $this, 'api_token_permission_check' ),
					'args'                => $this->get_refund_args(),
				),
			)
		);
	}

	/**
	 * Create a new order and assign to it a user
	 * - if user by email doesn't exist then a new user is registered
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		TVA_Logger::set_type( 'REQUEST NewOrder' );
		TVA_Logger::log(
			'/newOrder',
			array_merge( $request->get_params(), $_SERVER ),
			true
		);

		$order = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $order ) ) {
			return $order;
		}

		if ( $order instanceof TVA_Order ) {
			foreach ( $this->prepare_order_items_for_database( $request ) as $item ) {
				$order->set_order_item( $item );
			}
		}

		$user = $this->prepare_user_for_database( $request );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$order->set_user_id( $user->ID );
		$order_id = (int) $order->save();

		$enrolled_courses = array();
		foreach ( $order->get_order_items() as $order_item ) {
			$enrolled_courses[] = $order_item->get_product_id();
		}

		//trigger user enrollment
		$customer = new TVA_Customer( $user->ID );
		if ( ! empty( $enrolled_courses ) ) {
			$customer->trigger_course_purchase( $order, 'ThriveCart' );
			$customer->trigger_product_received_access( $enrolled_courses );
			$customer->trigger_purchase( $order );
		}

		return rest_ensure_response(
			array(
				'order_id' => $order_id,
				'user_id'  => (int) $user->ID,
			)
		);
	}

	/**
	 * Disable and order from DB based on $request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function refund_order( $request ) {

		TVA_Logger::set_type( 'REQUEST refundOrder' );
		TVA_Logger::log(
			'/refundOrder',
			array_merge( $request->get_params(), $_SERVER ),
			true
		);

		$order = TVA_Order::get_order(
			array(
				'gateway_order_id' => (int) $request->get_param( 'order_id' ),
				'gateway'          => TVA_Const::THRIVECART_GATEWAY,
			)
		);

		$course_ids = $request->get_param( 'course_ids' );
		if ( false === is_array( $course_ids ) ) {
			$course_ids = array();
		}
		$course_ids = array_map( [ '\TVA\Product_Migration', 'validate_product_id' ], $course_ids );

		$order_id = $order->get_id();
		$saved    = false;

		if ( empty( $order_id ) ) {
			return rest_ensure_response(
				new WP_Error(
					'resource_not_found',
					'The specified resource does not exists.',
					array(
						'status' => 404,
					)
				)
			);
		}

		/**
		 * remove access for each course id in request
		 */
		foreach ( $order->get_order_items() as $order_item ) {
			if ( true === in_array( (int) $order_item->get_product_id(), $course_ids, true ) ) {
				$saved = $order_item->delete();
			} else {
				//we need to check if the product ID is from a migrated bundle
				//RefundOrder for bundles did not work from the start
				$migration_id = Product_Migration::get_migration_id( $order_item->get_product_id() );
				if ( ! empty( $migration_id ) && is_string( $migration_id ) && strpos( $migration_id, 'course.bundle' ) !== false ) {
					$saved = $order_item->delete();
				}
			}
		}

		$order       = new TVA_Order( $order_id );
		$order_items = $order->get_order_items();
		if ( empty( $order_items ) || empty( $course_ids ) ) {
			$order->set_status( 0 ); //disable the whole order
			$saved = $order->save( false );
		}

		if ( false === $saved ) {
			return rest_ensure_response(
				new WP_Error( 'internal_server_error', __( 'Internal Server Error. Order could not be saved!', 'thrive-apprentice' ), array( 'status' => 500 ) )
			);
		}

		return rest_ensure_response( $order->get_gateway_order_id() );
	}

	/**
	 * Prepare order instance for database
	 * - reads the request and set them to tva order instance
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Order|WP_Error
	 */
	protected function prepare_item_for_database( $request ) {

		$order         = new TVA_Order();
		$order_details = $request->get_param( 'order' );
		$price         = $order_details['total'];

		$order->set_gateway( TVA_Const::THRIVECART_GATEWAY );
		$order->set_buyer_name(
			implode(
				' ',
				array(
					$request->get_param( 'first_name' ),
					$request->get_param( 'last_name' ),
				)
			)
		);
		$order->set_buyer_email( $request->get_param( 'email' ) );
		$order->set_gateway_order_id( $order_details['order_id'] );
		$order->set_price( $price );
		$order->set_price_gross( $price );
		$order->set_currency( $order_details['currency'] );
		$order->set_payment_method( $order_details['processor'] );
		$order->set_status( 1 );
		$order->set_type( TVA_Order::PAID );

		return $order;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Order_Item[]
	 */
	protected function prepare_order_items_for_database( $request ) {

		$items = array();

		$order_details = $request->get_param( 'order' );

		foreach ( $order_details['charges'] as $charge ) {
			foreach ( $charge['courses'] as $item_id ) {
				$bundle     = TVA_Bundle::init_by_number( $item_id );
				$name       = $bundle instanceof TVA_Bundle ? $bundle->name : $charge['name'];
				$order_item = new TVA_Order_Item();
				$order_item->set_product_type( $charge['type'] );
				$order_item->set_product_name( $name );
				$order_item->set_product_id( $item_id );

				$price = $charge['amount'];
				$order_item->set_unit_price( $price );
				$order_item->set_total_price( $price );
				$order_item->set_product_price( $price );
				$items[] = $order_item;
			}
		}

		return $items;
	}

	/**
	 * Searches for existing user by email
	 * - if it doesn't exist then create a new one
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_User|WP_Error
	 */
	protected function prepare_user_for_database( $request ) {
		$email = $request->get_param( 'email' );
		$user  = get_user_by( 'email', $email );

		if ( false === $user instanceof WP_User ) {
			$first_name = $request->get_param( 'first_name' );
			$last_name  = $request->get_param( 'last_name' );
			$user       = tva_ensure_new_user( $email, 'thrivecart', $first_name, $last_name );
		}

		return $user;
	}

	public function get_refund_args() {

		return array(
			'order_id' => array(
				'description' => __( 'ThriveCart order ID. The order ID associated with the purchase.', 'thrive-apprentice' ),
				'type'        => 'number',
				'context'     => array( 'edit' ),
				'required'    => true,
			),
		);
	}

	/**
	 * Validate for empty string
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param_name
	 *
	 * @return bool
	 */
	public function validate_empty_string( $value, $request, $param_name ) {

		$allow_empty_string = array(
			'first_name',
			'last_name',
		);

		$valid = ! empty( $value );

		if ( in_array( $param_name, $allow_empty_string, true ) && empty( $value ) ) {
			$valid = true;
		}

		return $valid;
	}

	public function get_create_item_args() {

		return array(
			'source'      => array(
				'description'       => __( 'Description of where the request has come from. Example: ThriveCart', 'thrive-apprentice' ),
				'type'              => 'string',
				'context'           => array( 'edit' ),
				'required'          => true,
				'validate_callback' => array( $this, 'validate_empty_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'first_name'  => array(
				'description'       => __( 'First name of the customer that made the purchase', 'thrive-apprentice' ),
				'type'              => 'string',
				'context'           => array( 'edit' ),
				'required'          => false,
				'validate_callback' => array( $this, 'validate_empty_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'last_name'   => array(
				'description'       => __( 'Last name of the customer that made the purchase', 'thrive-apprentice' ),
				'type'              => 'string',
				'context'           => array( 'edit' ),
				'required'          => false,
				'validate_callback' => array( $this, 'validate_empty_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email'       => array(
				'description'       => __( 'The email address of the customer that’s made the purchase.This should be the email address to which course access will be given', 'thrive-apprentice' ),
				'type'              => 'string',
				'format'            => 'email',
				'context'           => array( 'edit' ),
				'required'          => true,
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'customer_id' => array(
				'description' => __( 'The customer ID associated with the purchase generated by the vendor', 'thrive-apprentice' ),
				'type'        => 'integer',
				'context'     => array( 'edit' ),
			),
			'order'       => array(
				'description'       => __( 'The order object', 'thrive-apprentice' ),
				'type'              => 'object',
				'context'           => array( 'edit' ),
				'required'          => true,
				'validate_callback' => array( $this, 'validate_order_arg' ),
				'sanitize_callback' => array( $this, 'sanitize_order' ),
				'properties'        => array(
					'order_id'  => array(
						'description' => __( 'Unique order identifier', 'thrive-apprentice' ),
						'type'        => 'integer',
						'required'    => true,
						'context'     => array( 'edit' ),
						'readonly'    => true,
					),
					'total'     => array(
						'description'      => __( 'The total amount paid by the customer', 'thrive-apprentice' ),
						'type'             => 'number',
						'minimum'          => 0.0,
						'exclusiveMinimum' => false,
						'required'         => true,
						'context'          => array( 'edit' ),
					),
					'currency'  => array(
						'description'       => __( 'The currency paid by the customer', 'thrive-apprentice' ),
						'type'              => 'string',
						'required'          => true,
						'context'           => array( 'edit' ),
						'validate_callback' => array( $this, 'validate_empty_string' ),
					),
					'processor' => array(
						'description'       => __( 'Label for the payment processor used to make the payment', 'thrive-apprentice' ),
						'type'              => 'string',
						'context'           => array( 'edit' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'charges'   => array(
						'description'       => __( 'A list of charges/order items that make up an order', 'thrive-apprentice' ),
						'type'              => 'array',
						'required'          => true,
						'validate_callback' => array( $this, 'validate_charge_arg' ),
						'sanitize_callback' => array( $this, 'sanitize_charges' ),
						'items'             => array(
							'description'       => __( 'Order item', 'thrive-apprentice' ),
							'type'              => 'object',
							'sanitize_callback' => array( $this, 'sanitize_single_charge' ),
							'properties'        => array(
								'type'            => array(
									'description' => __( 'Used to determine whether this is a single purchase or a recurring purchase', 'thrive-apprentice' ),
									'type'        => 'string',
									'enum'        => array( 'single', 'recurring' ),
									'context'     => array( 'edit' ),
									'required'    => true,
								),
								'courses'         => array(
									'description' => __( "An array of Thrive Apprentice course ID's or Course Bundles that the customer should receive access to.", 'thrive-apprentice' ),
									'type'        => 'array',
									'required'    => true,
									'context'     => array( 'edit' ),
								),
								'name'            => array(
									'description'       => __( 'Human readable name for item/product.  Example: "Active Growth Membership ($100/month)', 'thrive-apprentice' ),
									'type'              => 'string',
									'required'          => true,
									'context'           => array( 'edit' ),
									'validate_callback' => array( $this, 'validate_empty_string' ),
									'sanitize_callback' => 'sanitize_text_field',
								),
								'reference'       => array(
									'description' => __( 'Line item reference', 'thrive-apprentice' ),
									'type'        => 'string',
								),
								'item_id'         => array(
									'description' => __( 'Line item ID', 'thrive-apprentice' ),
									'type'        => 'integer',
									'required'    => true,
								),
								'amount'          => array(
									'description'      => __( 'Total paid for the line item', 'thrive-apprentice' ),
									'type'             => 'number',
									'minimum'          => 0,
									'exclusiveMinimum' => false,
									'required'         => true,
								),
								'transaction_id'  => array(
									'description' => __( 'Unique ID for the transaction from vendor', 'thrive-apprentice' ),
									'type'        => 'string',
								),
								'subscription_id' => array(
									'description' => __( 'Unique ID for the subscription', 'thrive-apprentice' ),
									'type'        => 'string',
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Validates order arguments sent to endpoint
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return true|WP_Error
	 */
	public function validate_order_arg( $value, $request, $param ) {

		if ( empty( $value ) ) {
			/* translators: %s param name */
			return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s cannot be empty', 'thrive-apprentice' ), $param ) );
		}

		if ( ! is_array( $value ) ) {
			/* translators: %s param name */
			return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s invalid type', 'thrive-apprentice' ), $param ) );
		}

		$attributes     = $request->get_attributes();
		$args           = $attributes['args'][ $param ];
		$missing_params = array();

		foreach ( $args['properties'] as $key => $properties ) {
			if ( ! empty( $properties['required'] ) && ! array_key_exists( $key, $value ) ) {
				$missing_params[] = $key;
			}
			if ( 'currency' === $key && ! in_array( $key, $missing_params, true ) ) { //not missing from value
				$is_valid = is_callable( $properties['validate_callback'] ) && call_user_func( $properties['validate_callback'], $value[ $key ], $request, $key );
				if ( ! ( $is_valid ) ) {
					return new WP_Error(
						'rest_invalid_param',
						__( 'Invalid param currency', 'thrive-apprentice' )
					);
				}
			}
			if ( 'charges' === $key && ! in_array( $key, $missing_params, true ) ) { //not missing from value
				$is_valid = call_user_func( $properties['validate_callback'], $value[ $key ], $request, $key );
				if ( is_wp_error( $is_valid ) ) {
					return $is_valid;
				}
			}
		}

		if ( ! empty( $missing_params ) ) {

			return new WP_Error(
				'rest_missing_order_param',
				/* translators: 1: Parameter */
				sprintf( __( 'Missing parameter(s): %1$s', 'thrive-apprentice' ), implode( ', ', $missing_params ) )
			);
		}

		return rest_validate_value_from_schema( $value, $args, $param );
	}

	/**
	 * Sanitize order object
	 *
	 * @param mixed           $value order object
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return mixed
	 */
	public function sanitize_order( $value, $request, $param ) {

		$attributes = $request->get_attributes();
		if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
			return $value;
		}
		$args = $attributes['args'][ $param ];

		foreach ( $value as $property => $v ) {
			if ( ! empty( $args['properties'][ $property ]['sanitize_callback'] ) && is_callable( $args['properties'][ $property ]['sanitize_callback'] ) ) {
				$value[ $property ] = call_user_func( $args['properties'][ $property ]['sanitize_callback'], $v, $args['properties'][ $property ] );
			} else {
				$value[ $property ] = rest_sanitize_value_from_schema( $value[ $property ], $args['properties'][ $property ] );
			}
		}

		return $value;
	}

	/**
	 * Sanitize list of charges
	 * - fallback is rest_sanitize_value_from_schema()
	 *
	 * @param array $value
	 * @param array $args
	 *
	 * @return mixed
	 * @see rest_sanitize_value_from_schema()
	 * @see get_create_item_args()
	 * @see sanitize_single_charge()
	 *
	 */
	public function sanitize_charges( $value, $args ) {

		foreach ( $value as $index => $charge ) {
			if ( ! empty( $args['items']['sanitize_callback'] ) && is_callable( $args['items']['sanitize_callback'] ) ) {
				$value[ $index ] = call_user_func( $args['items']['sanitize_callback'], $charge, $args['items'] );
			} else {
				$value[ $index ] = rest_sanitize_value_from_schema( $value[ $index ], $args['items'] );
			}
		}

		return $value;
	}

	/**
	 * Callback for sanitizing a single charge
	 * - an order may have multiple charges
	 *
	 * @param $value
	 * @param $args
	 *
	 * @return mixed
	 *
	 * @see sanitize_charges()
	 * @see get_create_item_args()
	 */
	public function sanitize_single_charge( $value, $args ) {

		foreach ( $value as $property => $v ) {
			if ( ! empty( $args['properties'][ $property ]['sanitize_callback'] ) && is_callable( $args['properties'][ $property ]['sanitize_callback'] ) ) {
				$value[ $property ] = call_user_func( $args['properties'][ $property ]['sanitize_callback'], $v, $args['properties'][ $property ] );
			} else {
				$value[ $property ] = rest_sanitize_value_from_schema( $v, $args['properties'][ $property ] );
			}
		}

		return $value;
	}

	/**
	 * Validate charge sent to endpoint
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return true|WP_Error
	 */
	public function validate_charge_arg( $value, $request, $param ) {

		if ( empty( $value ) ) {
			return new WP_Error( 'invalid_order_charges', __( 'Charges for an order cannot be empty', 'thrive-apprentice' ) );
		}

		$attributes     = $request->get_attributes();
		$args           = $attributes['args']['order']['properties'][ $param ];
		$missing_params = array();

		foreach ( $value as $index => $charge ) {
			foreach ( $args['items']['properties'] as $key => $properties ) {
				if ( ! empty( $properties['required'] ) && is_array( $charge ) && ! array_key_exists( $key, $charge ) ) {
					$missing_params[] = $key;
				}
				if ( ! empty( $properties['validate_callback'] ) ) {
					$val      = ! empty( $value[ $index ][ $key ] ) ? $value[ $index ][ $key ] : null;
					$is_valid = call_user_func( $properties['validate_callback'], $val, $request, $key );
					if ( ! $is_valid ) {

						return new WP_Error( 'invalid_order_charge_name', __( 'Invalid product name for a charge', 'thrive-apprentice' ) );
					}
				}
			}
			if ( ! empty( $missing_params ) ) {
				break;
			}
		}

		if ( ! empty( $missing_params ) ) {

			return new WP_Error(
				'rest_missing_charge_param',
				/* translators: 1: Parameter */
				sprintf( __( 'Missing parameter(s): %1$s', 'thrive-apprentice' ), implode( ', ', $missing_params ) )
			);
		}

		return rest_validate_request_arg( $value, $request, $param );
	}

	/**
	 * API Token Authorization
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function api_token_permission_check( $request ) {

		if ( ! empty( $_SERVER['HTTP_TVA'] ) ) {
			$_SERVER['PHP_AUTH_PW'] = $_SERVER['HTTP_TVA'];
		}

		return $this->create_item_permission_check( $request );
	}

	/**
	 * Checks if user has right to create a new order
	 * - Basic Authorization
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return true|WP_Error
	 */
	public function create_item_permission_check( $request ) {

		$header   = null;
		$auth_key = $request->get_param( 'auth' );

		if ( function_exists( 'getallheaders' ) ) {
			$all_header = getallheaders();
			$header     = ! empty( $all_header['Authorization'] ) ? $all_header['Authorization'] : null;
		}

		if ( ! $header ) {
			$header = $request->get_header( 'Authorization' );
		}

		if ( ! $header && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
			$header = 'Basic ' . base64_encode( 'username:' . $_SERVER['PHP_AUTH_PW'] );
		}

		if ( empty( $header ) && empty( $auth_key ) ) {

			$error = new WP_Error( 'authentication_failure', __( 'Authentication failed due to invalid authentication credentials.', 'thrive-apprentice' ), array( 'status' => 401 ) );

			TVA_Logger::set_type( 'REQUEST Auth' );
			TVA_Logger::log(
				$error->get_error_code(),
				$_SERVER,
				true
			);

			return $error;
		}

		$auth     = base64_decode( str_replace( 'Basic ', '', $header ? $header : $auth_key ) );
		$username = null;
		$password = null;

		if ( strpos( $auth, ':' ) ) {
			list( $username, $password ) = explode( ':', $auth );
		}

		$username = trim( $username );
		$password = trim( $password );

		$has_access = TVA_Token::auth( $username, $password );
		if ( ! $has_access ) {
			$error = new WP_Error( 'not_authorized', __( 'Authorization failed due to insufficient permissions.', 'thrive-apprentice' ), array( 'status' => 403 ) );
			TVA_Logger::set_type( 'REQUEST Auth' );
			TVA_Logger::log(
				$error->get_error_code(),
				$_SERVER,
				true
			);

			return $error;
		}

		return true;
	}
}
