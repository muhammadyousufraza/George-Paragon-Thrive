<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Inline;

use TCB\Integrations\WooCommerce\Main as Woo_Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Inline
 */
class Hooks {

	public static function add() {
		add_filter( 'tcb_inline_shortcodes', [ __CLASS__, 'tcb_inline_shortcodes' ], 100 );

		add_filter( 'tcb_post_list_post_info', [ __CLASS__, 'shortcode_real_data' ], 10, 2 );

		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tcb_dynamiclink_data', [ __CLASS__, 'dynamiclink_data_filter' ] );
	}

	/**
	 * Add WooCommerce inline shortcodes
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public static function tcb_inline_shortcodes( $shortcodes ) {
		foreach ( Helpers::available_shortcodes() as $shortcode_id => $config ) {
			/* each shortcode config has a hidden ID field */
			$shortcode_id_config = [
				'id' => [
					'extra_options' => [],
					'real_data'     => $config['name'],
					'type'          => 'hidden',
					'value'         => $shortcode_id,
				],
			];

			$shortcodes['Post'][] = array(
				'name'        => $config['name'],
				'option'      => $config['name'],
				'value'       => Main::META_SHORTCODE,
				'extra_param' => $shortcode_id,
				'input'       => array_merge( $shortcode_id_config, $config['controls'] ), /* some shortcodes have extra controls */
			);
		}

		return $shortcodes;
	}

	/**
	 * Add extra info for products needed for inline shortcodes
	 *
	 * @param $post_info
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function shortcode_real_data( $post_info, $post_id ) {

		if ( get_post_type( $post_id ) === Woo_Main::POST_TYPE ) {
			foreach ( Helpers::available_shortcodes() as $shortcode_id => $config ) {
				$attr = [];

				/* if the shortcode is a 'price' shortcode, make sure we get the full price ( including decimals ) at the start */
				if ( $config['type'] === 'price' ) {
					$attr[ Main::PRICE_SHOW_DECIMALS ] = 1;
				}

				$shortcode_value = Main::do_shortcode( $shortcode_id, $attr );
				$shortcode_data  = [
					'value' => $shortcode_value,
				];

				if ( $config['type'] === 'price' ) {
					$shortcode_data['price_without_decimals'] = Helpers::get_price_without_decimals( $shortcode_value );
				}

				$post_info[ Main::META_SHORTCODE ][ $shortcode_id ] = $shortcode_data;
			}
		}

		return $post_info;
	}

	/**
	 * When editing a landing page, allow woo shortcodes to render
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public static function content_allowed_shortcodes_filter( $shortcodes ) {
		if ( tve_post_is_landing_page() && is_editor_page() ) {
			$shortcodes[] = Main::META_SHORTCODE;
			$shortcodes[] = Main::LINK_SHORTCODE;
		}

		return $shortcodes;
	}

	/**
	 * Add dynamic WooCommerce links
	 *
	 * @param $dynamic_links
	 *
	 * @return array
	 */
	public static function dynamiclink_data_filter( $dynamic_links ) {
		return array_merge_recursive( $dynamic_links, Helpers::get_dynamic_links() );
	}
}
