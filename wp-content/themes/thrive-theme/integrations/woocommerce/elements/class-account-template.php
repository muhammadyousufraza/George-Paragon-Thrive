<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Account_Template
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Account_Template extends \Thrive_Theme_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'My Account', 'thrive-theme' );
	}

	/**
	 * Set WooCommerce as alternate text for search
	 *
	 * @return string
	 */
	public function alternate() {
		return 'woocommerce';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'woo';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.account-template-wrapper';
	}

	/**
	 * This element is a shortcode
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return WooCommerce\Shortcodes\Account_Template::SHORTCODE;
	}

	/**
	 * If an element has selector or a data-css will be generated
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	public function own_components() {
		/* only the layout, background and borders are visible */
		$components = [
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'layout'           => [
				'disabled_controls' => [],
			],
		];

		return $components;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return WooCommerce\Helpers::get_products_category_label();
	}
}

return new Account_Template( 'account-template' );
