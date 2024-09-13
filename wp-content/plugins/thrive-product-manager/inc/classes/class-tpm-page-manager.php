<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Page_Manager {

	protected static $_instance;

	private function __construct() {
	}

	public function render() {

		if ( TPM_Connection::get_instance()->is_connected() === false ) {
			TPM_Connection::get_instance()->render();
		} else {
			TPM_Product_List::get_instance()->render();
		}
	}

	public static function get_instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
