<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( is_shop() ) {
	echo Thrive\Theme\Integrations\WooCommerce\Shortcodes\Shop_Template::render();
}
