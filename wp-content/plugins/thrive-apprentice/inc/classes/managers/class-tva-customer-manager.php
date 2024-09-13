<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Customer_Manager
 */
class TVA_Customer_Manager {

	/**
	 * Returns the import types needed for Import Customer Modals
	 *
	 * @return array
	 */
	public static function get_import_types() {
		return array(
			array(
				'icon' => 'csv-import',
				'text' => __( 'CSV file', 'thrive-apprentice' ),
				'step' => 1,
			),
			array(
				'icon' => 'list-import',
				'text' => __( 'List of names and emails', 'thrive-apprentice' ),
				'step' => 2,
			),
		);
	}

	/**
	 * Returns the service configuration array
	 *
	 * @return array
	 */
	public static function get_services() {

		$sendowl_is_connected = TVA_SendOwl::is_connected();

		return array(
			'course_ids'       => array(
				'img'          => 'integration-tcart.png',
				'text'         => __( 'ThriveCart Products', 'thrive-apprentice' ),
				'items'        => array(),
				'gateway'      => TVA_Const::MANUAL_GATEWAY,
				'is_available' => true,
				'customer_fn'  => 'get_thrivecart_courses',
			),
			'sendowl_products' => array(
				'img'          => 'integration-product-h.png',
				'text'         => __( 'SendOwl Products', 'thrive-apprentice' ),
				'items'        => TVA_SendOwl::get_products(),
				'gateway'      => TVA_Const::SENDOWL_GATEWAY,
				'is_available' => $sendowl_is_connected,
				'customer_fn'  => 'get_sendowl_products',
			),
			'sendowl_bundles'  => array(
				'img'          => 'integration-bundle-h.png',
				'text'         => __( 'SendOwl Bundles', 'thrive-apprentice' ),
				'items'        => TVA_SendOwl::get_bundles(),
				'gateway'      => TVA_Const::SENDOWL_GATEWAY,
				'is_available' => $sendowl_is_connected,
				'customer_fn'  => 'get_sendowl_bundles',
			),
		);
	}

	/**
	 * Inserts a new Apprentice Customer together with orders
	 *
	 * @param array $data     Customer Data
	 * @param array $services Services (Thrive Cart, SendOwl)
	 * @param array $config   Configuration array (send email, email templates)
	 *
	 * @return array|false
	 */
	public static function insert_customer( $data = array(), $services = array(), $config = array() ) {

		$config = array_merge( array(
			'send_email'     => false,
			'email_template' => false,
			'order_type'     => '',
		), $config );

		if ( empty( $data['email'] ) || empty( $data['name'] ) || ! is_email( $data['email'] ) ) {
			return false;
		}

		$buyer_email = sanitize_email( $data['email'] );
		$buyer_name  = sanitize_text_field( $data['name'] );

		$args = array(
			'user_email' => $buyer_email,
			'user_login' => $buyer_email,
			'first_name' => $buyer_name,
			'user_pass'  => '',
			'role'       => 'subscriber',
		);

		if ( $config['send_email'] === true ) {
			/**
			 * Add the actions for the Apprentice User Email
			 */
			tva_email_templates()->trigger_process( $config['email_template'] );
		}

		$user_obj = get_user_by_email( $buyer_email );

		if ( is_multisite() && $user_obj ) {
			add_user_to_blog( get_current_blog_id(), $user_obj->ID, 'subscriber' );
		} else {
			$user = wp_insert_user( $args );

			if ( $user instanceof WP_Error && ! in_array( $user->get_error_code(), array( 'existing_user_login', 'existing_user_email' ) ) ) {
				return false;
			}

			$user_obj = get_user_by_email( $buyer_email );
		}

		if ( $config['send_email'] === true ) {
			wp_send_new_user_notifications( $user_obj->ID, 'user' );
		}

		$all_services = static::get_services();

		/**
		 * We loop through passed services and create an order for each of one
		 */
		foreach ( $services as $service_key => $items ) {
			if ( empty( $all_services[ $service_key ] ) || empty( $items ) ) {
				continue;
			}

			$admin_id = get_current_user_id();

			$new_items = []; // stores IDs for products where the user did not have access before and receives access now - used in the trigger automator action from create_order_for_customer

			tva_access_manager()->set_tva_user( $user_obj );
			wp_set_current_user( $user_obj->ID );

			foreach ( $items as $item ) {
				$product = new \TVA\Product( $item );

				$allowed = tva_access_manager()->set_product( $product )->check_rules();

				if ( ! $allowed ) {
					$new_items[] = $item;
				}
			}

			wp_set_current_user( $admin_id );

			static::create_order_for_customer( $user_obj, $service_key, $items, array(
				'gateway' => $all_services[ $service_key ]['gateway'],
				'type'    => $config['order_type'],
			), $new_items );
		}

		$tva_user_object = new TVA_Customer( $user_obj->ID );

		return $tva_user_object->jsonSerialize();
	}

	/**
	 * Create a new Order for a customer
	 *
	 * @param WP_User    $customer
	 * @param string     $service_key            Must be a key from get_services function
	 * @param int[]      $items                  A list of IDs containing the products
	 * @param array      $order_data
	 * @param array|null $trigger_items_override If set, these item IDs will be used for the trigger instead of the ones from the order
	 */
	public static function create_order_for_customer( $customer, $service_key, $items = array(), $order_data = array(), $trigger_items_override = null ) {

		if ( ! $customer instanceof WP_User || empty( $service_key ) ) {
			return;
		}

		$order_data = array_merge( array(
			'gateway' => '',
			'status'  => TVA_Const::STATUS_COMPLETED,
			'type'    => TVA_Order::MANUAL,
		), $order_data );


		$order = new TVA_Order();
		$order->set_user_id( $customer->ID )
			  ->set_gateway( $order_data['gateway'] )
			  ->set_status( $order_data['status'] )
			  ->set_type( $order_data['type'] );

		foreach ( $items as $item ) {
			$order_item = new TVA_Order_Item();

			if ( method_exists( __CLASS__, 'set_order_item_for_' . $service_key ) ) {
				call_user_func_array( array( __CLASS__, 'set_order_item_for_' . $service_key ), array( $item, &$order_item, $order ) );
			}
			$order->set_order_item( $order_item );
		}

		$order->save( true );

		//trigger enrollments to courses and/or bundles
		$customer = new TVA_Customer( $order->get_user_id() );

		$trigger_items = $trigger_items_override && is_array( $trigger_items_override ) ? $trigger_items_override : $items;

		if ( ! empty( $trigger_items ) ) {
			$customer->trigger_product_received_access( $trigger_items );
		}
	}

	/**
	 * Sets an order item for ThriveCart order
	 *
	 * Dynamically called from create_order_for_customer function
	 *
	 * @param int            $id
	 * @param TVA_Order_Item $order_item
	 * @param TVA_Order      $order
	 */
	public static function set_order_item_for_course_ids( $id, &$order_item, $order = null ) {

		$course  = new TVA_Course_V2( (int) $id );
		$bundle  = TVA_Bundle::init_by_number( $id );
		$product = new \TVA\Product( $id );

		$name = 'Manually added';

		if ( false === is_wp_error( $course->get_wp_term() ) ) {
			$name = $course->name;
		} else if ( false === is_wp_error( $bundle ) ) {
			$name = $bundle->name;
			$order->set_gateway( 'Apprentice Bundle' );
		} else if ( false === is_wp_error( $product ) ) {
			$name = $product->get_name();
			$order->set_gateway( 'Apprentice Product' );
		}

		$order_item->set_product_id( $id );
		$order_item->set_product_name( $name );
		$order_item->set_product_type( 'manually' );
		$order_item->set_product_price( 0 );
		$order_item->set_currency( 'USD' );
	}

	/**
	 * Sets an order item for SendOwl Product
	 *
	 * Dynamically called from create_order_for_customer function
	 *
	 * @param int            $product_id
	 * @param TVA_Order_Item $order_item
	 * @param TVA_Order      $order
	 */
	public static function set_order_item_for_sendowl_products( $product_id, &$order_item, $order = null ) {
		$product = TVA_SendOwl::get_product_by_id( $product_id );

		if ( ! $product ) {
			return;
		}

		$order_item->set_product_id( $product['id'] );
		$order_item->set_product_name( $product['name'] );
		$order_item->set_product_type( $product['product_type'] );
		$order_item->set_product_price( $product['price'] );
		$order_item->set_currency( $product['currency_code'] );
	}

	/**
	 * Sets an order item for SendOwl Bundle
	 *
	 * Dynamically called from create_order_for_customer function
	 *
	 * @param int            $bundle_id
	 * @param TVA_Order_Item $order_item
	 * @param TVA_Order      $order
	 */
	public static function set_order_item_for_sendowl_bundles( $bundle_id, &$order_item, $order = null ) {

		$bundle = TVA_SendOwl::get_bundle_by_id( $bundle_id );

		if ( ! $bundle ) {
			return;
		}

		$order_item->set_product_id( $bundle['id'] );
		$order_item->set_product_name( $bundle['name'] );
		$order_item->set_product_type( 'bundle' );
		$order_item->set_product_price( $bundle['price'] );
		$order_item->set_currency( $bundle['currency_code'] );
	}

	/**
	 * Process data from CSV
	 *
	 * @param array $data
	 *
	 * @return array|bool
	 */
	public static function process_data_from_csv( $data = array() ) {

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
			$row['is_valid'] = 1;
			$email           = sanitize_email( $row[ $email_key ] );

			/**
			 * If the email is invalid we unset the current row
			 */
			if ( ! is_email( $email ) ) {
				$row['is_valid'] = 0;
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
}
