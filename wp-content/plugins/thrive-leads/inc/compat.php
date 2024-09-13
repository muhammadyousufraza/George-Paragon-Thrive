<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-leads
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/* Display TL Groups on Shop page */
add_filter( 'thrive_leads_is_page', 'tve_leads_is_shop_template' );
add_filter( 'thrive_leads_current_post', 'tve_leads_get_shop_template' );

/**
 * Checks if the page is a shop page
 *
 * @param $is_page
 *
 * @return bool|mixed
 */
function tve_leads_is_shop_template( $is_page ) {
	if ( class_exists( 'WooCommerce', false ) && is_shop() ) {
		$is_page = true;
	}

	return $is_page;
}

/**
 * Returns the shop page
 *
 * @param $post
 *
 * @return array|mixed|\WP_Post|null
 */
function tve_leads_get_shop_template( $post ) {
	if ( class_exists( 'WooCommerce', false ) && is_shop() ) {
		$post = get_post( wc_get_page_id( 'shop' ) );
	}

	return $post;
}

add_action( 'wp_enqueue_scripts', static function () {
	/* fixes a conflict with TheGem theme SUPP-13635 */
	if ( is_editor_page() && tve_leads_post_type_editable( get_post_type() ) ) {
		wp_dequeue_script( 'thegem-menu-init-script' );
		wp_dequeue_script( 'thegem-thegem-form-elements' );
		wp_dequeue_script( 'thegem-header' );
		wp_dequeue_script( 'jquery-fancybox' );
		wp_dequeue_script( 'fancybox-init-script' );
		wp_dequeue_script( 'thegem-scripts' );
	}
}, 9000 );
