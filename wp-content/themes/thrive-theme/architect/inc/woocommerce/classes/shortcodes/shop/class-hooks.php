<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Shop;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Hooks {
	public static function add() {
		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );

		add_filter( 'woocommerce_pagination_args', [ __CLASS__, 'woocommerce_pagination_args' ] );
	}

	/**
	 * Allow the shop shortcode to be rendered in the editor
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public static function content_allowed_shortcodes_filter( $shortcodes ) {
		if ( is_editor_page() ) {
			$shortcodes[] = Main::SHORTCODE;
		}

		return $shortcodes;
	}

	/**
	 * Make the woo pagination think we're on the second page while in the editor ( so we can style the previous navigation element )
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public static function woocommerce_pagination_args( $args ) {
		if ( is_editor_page_raw( true ) && $args['total'] >= 2 ) {
			$args['current'] = 2;
		}

		return $args;
	}

	/**
	 * @param $instances
	 *
	 * @return mixed
	 */
	public static function tcb_element_instances( $instances ) {

		$shop_element = require_once __DIR__ . '/class-element.php';

		$instances[ $shop_element->tag() ] = $shop_element;

		$files = array_diff( scandir( __DIR__ . '/sub-elements' ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			$instance                      = require_once __DIR__ . '/sub-elements/' . $file;
			$instances[ $instance->tag() ] = $instance;
		}

		return $instances;
	}
}
