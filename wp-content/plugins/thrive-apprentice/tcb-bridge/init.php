<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 *
 * This file is included if TAr does not exists as plugin and is not activated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * if the plugin-core.php file has not yet been included, include it here
 */

require_once dirname( dirname( __FILE__ ) ) . '/tcb/external-architect.php';

/**
 * This will add the "tve_custom_style" div usable for TCB editor CSS code
 */
add_action( 'wp_head', 'tve_load_custom_css', 100, 0 );

add_filter( 'tcb_post_types', 'tva_tcb_post_types', 100 );

/**
 * @param $blacklist_post_types
 *
 * @return mixed
 */
function tva_tcb_post_types( $blacklist_post_types ) {

	/**
	 * if page, the it might be checkout page then
	 * return null for force_whitelist and so letting tva_post_editable() apply its logic
	 *
	 * @see tva_post_editable()
	 */
	if ( tva_get_settings_manager()->is_login_page()
	     || tva_get_settings_manager()->is_checkout_page()
	     || TVA_Access_Restriction::is_custom_redirect_page( get_the_ID() )
	     || is_tax() //allow course overview to be loaded in tar's iframe
	) {
		$blacklist_post_types['force_whitelist'] = null;

		return $blacklist_post_types;
	}

	$blacklist_post_types['force_whitelist'] = isset( $blacklist_post_types['force_whitelist'] ) ? $blacklist_post_types['force_whitelist'] : array();
	$blacklist_post_types['force_whitelist'] = array_merge(
		$blacklist_post_types['force_whitelist'],
		array(
			'apprentice_pages',
			TVA_Const::LESSON_POST_TYPE,
			TVA_Const::MODULE_POST_TYPE,
			TVA_Const::ASSESSMENT_POST_TYPE,
			TVA_Access_Restriction::POST_TYPE,
			TVA_Course_Overview_Post::POST_TYPE,
			TVA_Course_Certificate::POST_TYPE,
			TVA_Course_Completed::POST_TYPE,
		)
	);

	return $blacklist_post_types;
}
