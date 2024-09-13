<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Theme\Integrations\WooCommerce\Shortcodes\Shop_Template;

echo '[' . Shop_Template::SHORTCODE . ' posts_per_page=' . Shop_Template::DEFAULT_PRODUCTS_TO_DISPLAY . ']';
