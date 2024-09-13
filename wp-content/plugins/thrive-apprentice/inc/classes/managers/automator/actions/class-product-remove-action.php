<?php

namespace TVA\Automator;


use Thrive\Automator\Items\Action;
use Thrive\Automator\Utils;
use TVA_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Product_Remove extends Action {

	protected $products;

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/removefromproduct';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Remove access from product';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Remove user from selected products';
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-remove-product';
	}

	/**
	 * Get the name of app to which action belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	/**
	 * Array of action-field keys, required for the action to be setup
	 *
	 * @return array
	 */
	public static function get_required_action_fields() {
		return [ 'products' ];
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return [ 'user_data' ];
	}

	public function prepare_data( $data = [] ) {
		if ( ! empty( $data['products']['value'] ) ) {
			$this->products = $data['products']['value'];
		}
	}

	/**
	 * This has been changed to remove user for product
	 *
	 * @return false|void
	 */
	public function do_action( $data ) {
		global $automation_data;
		if ( empty( $automation_data->get( 'user_data' ) ) ) {
			return false;
		}
		foreach ( $this->products as $product ) {
			$product = Utils::get_dynamic_data_object_from_automation( $product, 'product_id' );

			TVA_Customer::remove_user_from_product( $automation_data->get( 'user_data' )->get_value( 'user_id' ), $product );
		}
	}

}
