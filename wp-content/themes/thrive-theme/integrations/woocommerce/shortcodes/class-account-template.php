<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Account_Template
 * @package Thrive\Theme\Integrations\WooCommerce\Shortcodes
 */
class Account_Template {

	const SHORTCODE = 'thrive_account_template';

	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render( $attr = [] ) {
		$classes = [ 'account-template-wrapper', THRIVE_WRAPPER_CLASS ];

		if ( \Thrive_Utils::is_inner_frame() ) {
			$classes[] = 'tcb-selector-no_clone';
		}

		return \TCB_Utils::wrap_content( \WC_Shortcodes::my_account( [] ), 'div', '', $classes, \Thrive_Utils::create_attributes( $attr ) );
	}
}

return Account_Template::class;
