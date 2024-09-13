<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use Thrive\Theme\Integrations\WooCommerce;

if ( is_product() ) {
	$content = WooCommerce\Helpers::get_template_content( WooCommerce\Main::SINGLE_PRODUCT_CONTENT );

	$content = preg_replace( '/<script>[^<]*<\/script>/', '', $content );

	echo TCB_Utils::wrap_content( $content, 'div', '', [ 'product-template-wrapper' ] );
}
