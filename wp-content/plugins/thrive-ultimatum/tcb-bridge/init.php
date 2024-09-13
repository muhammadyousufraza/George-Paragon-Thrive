<?php
/**
 * main entry point for setups that do have TCB as a separate plugin
 */

/**
 * this will make sure that posts and pages are not editable with TCB when the user only has TU
 */
add_filter( 'tcb_post_types', 'tve_ult_disable_tcb_edit' );

/**
 * this will hide the Thrive Lightboxes menu link that's added from TCB - in case the TCB plugin is not installed
 */
add_filter( 'tcb_lightbox_menu_visible', '__return_false' );

/**
 * if the plugin-core.php file has not yet been included, include it here
 */
if ( ! defined( 'TVE_TCB_CORE_INCLUDED' ) ) {
	require_once TVE_Ult_Const::plugin_path() . 'tcb/external-architect.php';
}

/**
 *
 * block regular posts / pages etc to be edited with TCB - this uses a force_whitelist array key that will just return the posts editable with TCB when TU is installed
 *
 * @param array $post_types
 *
 * @return array
 */
function tve_ult_disable_tcb_edit( $post_types ) {
	$post_types['force_whitelist'] = isset( $post_types['force_whitelist'] ) ? $post_types['force_whitelist'] : array();
	$post_types['force_whitelist'] = array_merge( $post_types['force_whitelist'], array(
		TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN,
	) );

	return $post_types;
}
