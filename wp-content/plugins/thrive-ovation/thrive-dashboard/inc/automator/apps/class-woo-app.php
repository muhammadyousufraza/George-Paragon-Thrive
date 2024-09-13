<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Automator\Items\App;

class Woo_App extends App {
	public static function get_id() {
		return 'woocommerce';
	}

	public static function get_name() {
		return 'WooCommerce';
	}

	public static function get_description() {
		return 'WooCommerce';
	}

	public static function get_logo() {
		return 'tap-woocommerce-logo';
	}

	public static function has_access() {
		return class_exists( 'WooCommerce' );
	}
}
