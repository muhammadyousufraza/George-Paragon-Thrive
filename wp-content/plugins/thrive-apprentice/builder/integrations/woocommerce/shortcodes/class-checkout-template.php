<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Shortcodes;

use TCB\Integrations\WooCommerce\Shortcodes\MiniCart\Main as MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Checkout_Template
 * @package Thrive\Theme\Integrations\WooCommerce\Shortcodes
 */
class Checkout_Template {

	const SHORTCODE = 'thrive_checkout_template';

	public static $non_toggleable_fields = [
		'billing_first_name',
		'billing_last_name',
		'billing_country',
		'billing_email',
	];

	public static $non_sortable_fields = [
		'billing_first_name',
		'billing_last_name',
		'order_comments',
	];

	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );
	}

	/**
	 * Render the checkout element.
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render( $attr = [] ) {
		$classes = [ 'checkout-template-wrapper', THRIVE_WRAPPER_CLASS ];

		Checkout_Template::add_billing_fields_filters();

		if ( \Thrive_Utils::is_inner_frame() || \Thrive_Utils::during_ajax() ) {
			$classes[] = 'tcb-selector-no_clone';

			if ( empty( wc()->cart->get_cart() ) ) {
				MiniCart::generate_dummy_cart();

				/**
				 * In the editor, we want to display some products in the checkout cart even if the cart is currently empty.
				 *
				 * In order to make this happen, we also have to add a filter to prevent the redirect that Woo does by default
				 * @see allow_checkout_redirect() from Thrive\Theme\Integrations\WooCommerce\Filters, filter name: woocommerce_checkout_redirect_empty_cart
				 */
				$checkout = \WC_Shortcodes::checkout( [] );

				/* empty the cart to remove the products that we just added */
				WC()->cart->empty_cart();
			}
		}

		if ( empty( $checkout ) ) {
			$checkout = \WC_Shortcodes::checkout( [] );
		}

		return \TCB_Utils::wrap_content( $checkout, 'div', '', $classes, \Thrive_Utils::create_attributes( $attr ) );
	}

	/**
	 * @param $fields_data
	 *
	 * @return array
	 */
	public static function initialize_fields( $fields_data ) {
		$fields_to_localize = [];
		$current_priority   = 10;

		foreach ( $fields_data as $key => $field ) {
			$fields_to_localize[] = [
				'visible'  => true,
				'id'       => $key,
				'priority' => $current_priority,
			];

			$current_priority += 10;
		}

		return $fields_to_localize;
	}

	/**
	 * Returns the relevant SAVED billing fields info as an associative array or an empty array if nothing was saved
	 * @return array
	 */
	public static function get_billing_fields_info() {
		$fields                   = thrive_template()->get_meta_from_sections( 'checkout_field_data' );
		$saved_billing_field_data = [];

		if ( ! empty( $fields ) ) {
			//turn $fields into an associative array
			foreach ( $fields as $field ) {
				$saved_billing_field_data[ $field['id'] ] = $field;
			}
		}

		return $saved_billing_field_data;
	}

	public static function is_toggleable( $field ) {
		return ! in_array( $field, static::$non_toggleable_fields );
	}

	public static function is_sortable( $field ) {
		return ! in_array( $field, static::$non_sortable_fields );
	}

	public static function add_billing_fields_filters() {
		$saved_billing_field_data = Checkout_Template::get_billing_fields_info();

		//inside the editor we need the fields to be rendered (can't unset them) but hidden so we add a class
		if ( ! empty( $saved_billing_field_data ) && is_editor_page_raw( true ) ) {
			add_filter( 'woocommerce_form_field_args', function ( $args ) use ( $saved_billing_field_data ) {
				if ( ! empty( $saved_billing_field_data[ $args['id'] ] ) && ! $saved_billing_field_data[ $args['id'] ]['visible'] ) {
					$args['class'][] = 'hidden-billing-field';
				}

				return $args;
			} );
		}
	}
}

return Checkout_Template::class;
