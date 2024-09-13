<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 27-May-19
 * Time: 01:41 PM
 */

/**
 * Class TVA_Unknown_Integration
 *
 * Used for rules which had been inserted but their plugin membership integration was deleted
 */
class TVA_Unknown_Integration extends TVA_Integration {

	protected function init_items() {
	}

	protected function _get_item_from_membership( $key, $value ) {
	}

	/**
	 * @param array $rule_items
	 */
	public function push_items( $rule_items ) {

		if ( false === is_array( $rule_items ) ) {
			$rule_items = array();
		}

		$items = array();

		foreach ( $rule_items as $item ) {
			try {

				$items[] = new TVA_Integration_Item( $item['id'], $item['name'] );

			} catch ( Exception $e ) {

			}
		}

		$this->set_items( $items );
	}

	public function is_rule_applied( $rule ) {
		return false;
	}

	public function trigger_no_access() {
		//do nothing here so that the WP Login form is displayed
	}

	public function get_customer_access_items( $customer ) {
		return array();
	}
}
