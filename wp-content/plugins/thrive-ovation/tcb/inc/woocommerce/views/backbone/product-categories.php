<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

echo TCB\Integrations\WooCommerce\Shortcodes\Product_Categories\Main::render(); // phpcs:ignore
