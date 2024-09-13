<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/24/2019
 * Time: 16:56
 */

class TVA_Sendowl_Manager {

	/**
	 * TVA_Sendowl_Manager constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {

		add_action( 'init', array( $this, 'check_sendowl_products' ) );
		/**
		 * Filter disconnect TD Connection by allowing it or not
		 */
		add_filter( 'tve_dash_disconnect_sendowl', array( $this, 'remove_rules' ) );

		/**
		 * A new SendOwl order has been received through IPN
		 */
		add_action( 'tva_after_sendowl_process_notification', array( $this, 'tva_after_sendowl_process_notification' ), 10, 4 );
	}

	/**
	 * Callback for tva_after_sendowl_process_notification action
	 * - when a new SendOwl order has been launched by a user
	 * - fires a new do_action with following params:
	 *   - user who launched the order
	 *   - list of courses the user has been given access to
	 *
	 * @param stdClass  $data     json_decode of raw data
	 * @param array     $server   $_SERVER
	 * @param string    $raw_data body from request
	 * @param TVA_Order $order    which has just been saved
	 *
	 * @return array $courses for which the trigger/do_action has been fired
	 */
	public function tva_after_sendowl_process_notification( $raw_data, $data, $server, $order ) {

		$courses = array();

		if ( is_numeric( $order ) ) {
			$order = new TVA_Order( $order );
		}

		//apprentice product ids which have been purchased with this order
		$apprentice_product_ids = [];

		//sendowl ids which have been purchased with this order
		$sendowl_ids            = array_map( function ( $order_item ) use ( &$apprentice_product_ids ) {
			$sendowl_product_id = $order_item->get_product_id();
			foreach ( (array) TVA_SendOwl_Manager::get_products_that_have_protection( $order_item->get_product_id() ) as $product ) {
				$apprentice_product_ids[] = $product->get_id();
			}

			return $sendowl_product_id;
		}, $order->get_order_items() );
		$apprentice_product_ids = array_unique( $apprentice_product_ids );

		/** @var TVA_Terms_Collection $protected_items */
		$protected_items = TVA_Terms_Collection::make(
			tva_term_query()->get_protected_items()
		)->get_sendowl_protected_items()->get_items();

		/** @var TVA_Term_Model $course_model */
		foreach ( $protected_items as $course_model ) {
			//if the courses is protected at least by one id which exists in purchased ids
			$intersection = array_intersect( $sendowl_ids, $course_model->get_all_sendowl_protection_ids() );
			if ( ! empty( $intersection ) ) {
				$courses[] = $course_model->get_id();
			}
		}

		//fire the trigger for user enrollment
		$tva_customer = new TVA_Customer( $order->get_user_id() );
		$tva_customer->trigger_course_purchase( $order, 'SendOwl' );
		$tva_customer->trigger_product_received_access( $apprentice_product_ids );
		$tva_customer->trigger_purchase( $order );

		return $courses;
	}

	/**
	 * Removes rules which belong to some integrations
	 * - by default removes rules with sendowl_product and sendowl_bundle integrations
	 *
	 * @param string[] $integrations what rules to remove
	 *
	 * @return true
	 */
	public function remove_rules( $integrations = array() ) {

		if ( false === is_array( $integrations ) || empty( $integrations ) ) {
			$integrations = array(
				'sendowl_product',
				'sendowl_bundle',
			);
		}

		/**
		 * @var TVA_Course_V2|\TVA\Product $course_or_product
		 */
		foreach ( array_merge( TVA_Course_V2::get_items(), \TVA\Product::get_items() ) as $course_or_product ) {

			/**
			 * Get those rules which do not belong to integrations
			 */
			$rules = array_values( array_filter( (array) $course_or_product->get_rules(), static function ( $rule ) use ( $integrations ) {
				$slug = ! empty( $rule['integration'] ) ? $rule['integration'] : '';

				return ! empty( $rule['integration'] ) && false === in_array( $slug, $integrations );
			} ) );

			tva_integration_manager()->save_rules( $course_or_product->get_id(), $rules );
		}

		return true;
	}

	/**
	 * Check if products and bundles received from sendowl api must be parsed and updated as needed for further use
	 */
	public function check_sendowl_products() {
		$checked = get_option( 'tva_sendowl_products_updated', false );

		if ( 1 === (int) $checked ) {
			return;
		}

		self::update_sendowl_products();
	}

	/**
	 * Update sendowl products. Here we have them into a format we can use further in TA
	 */
	public static function update_sendowl_products() {
		update_option( 'tva_sendowl_products', self::get_updated_products() );
		update_option( 'tva_sendowl_products_updated', 1 );
	}

	/**
	 * This method will return an array which contains both products and bundles received from sendowl api
	 *
	 * It will also contain the $protected_terms prop which include any course protected by a given product
	 *
	 * @return array
	 */
	public static function get_updated_products() {
		$new_products = TVA_Products_Collection::make( self::get_products_from_transient() );
		$old_products = TVA_Products_Collection::make( self::get_products() );

		foreach ( $old_products->get_items() as $item ) {
			/** @var TVA_Model $item */

			! $new_products->get_from_key( $item->get_id() )
				? $old_products->remove( $item->get_id() )  //The item no longer exist in sendowl so we don't need it anymore
				: $new_products->remove( $item->get_id() ); //We don't need the item among the new items if it's among the old items
		}

		return array_merge( $new_products->prepare_for_db(), $old_products->prepare_for_db() );
	}

	/**
	 * Get both products and bundles from transients
	 *
	 * @return array
	 */
	public static function get_products_from_transient() {
		$products = TVA_Products_Collection::make( TVA_SendOwl::get_products() );
		$bundles  = TVA_Bundles_Collection::make( TVA_SendOwl::get_bundles() );

		foreach ( $products->get_items() as $key => $item ) {
			/** @var TVA_Product_Model $item */
			$item->set_protected_terms();

			$products->set( $key, $item );
		}

		foreach ( $bundles->get_items() as $key => $item ) {
			/** @var TVA_Bundle_Model $item */
			$item->set_protected_terms();

			$bundles->set( $key, $item );
		}

		return array_merge( $products->prepare_for_db(), $bundles->prepare_for_db() );
	}

	/**
	 * @return array
	 */
	public static function get_products() {
		return get_option( 'tva_sendowl_products', array() );
	}

	/**
	 * Return all the products that have as protection a specific ID
	 *
	 * @param int $protection_id
	 *
	 * @return \TVA\Product[]
	 */
	public static function get_products_that_have_protection( $protection_id ) {
		$products = array();

		/** @var TVA_Terms_Collection $protected_items */
		$protected_items = TVA_Terms_Collection::make(
			array_map( static function ( $product ) {
				return $product->get_term();
			}, \TVA\Product::get_items() )
		)->get_sendowl_protected_items()->get_items();

		/** @var TVA_Term_Model $course_model */
		foreach ( $protected_items as $product_model ) {
			//if the product is protected at least by one id which exists in purchased ids
			if ( in_array( $protection_id, $product_model->get_all_sendowl_protection_ids() ) ) {
				$products[] = new \TVA\Product( $product_model->get_id() );
			}
		}

		return $products;
	}
}

