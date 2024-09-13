<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-university
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once dirname( __FILE__ ) . '/functions.php';
require_once dirname( __FILE__ ) . '/includes/tva-class-admin.php';

/**
 * like homepage or blog page we display a label for checkout and thank you page
 */
add_filter( 'display_post_states', 'tva_display_post_states', 10, 2 );
