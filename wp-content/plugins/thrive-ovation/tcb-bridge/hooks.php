<?php

add_filter( 'wp', 'tvo_shortcode_post' );

add_shortcode( 'tvo_shortcode', 'tvo_render_shortcode' );

/**
 * TCB 2.0 Hooks
 */

/**
 * Adds extra script(s) to the main frame
 */
add_action( 'tcb_main_frame_enqueue', 'tvo_tcb_enqueue_scripts', 10, 0 );

/**
 * Add menu components/controls for ovation elements
 */
add_filter( 'tcb_menu_path_ovation_capture', 'tvo_tcb_capture_menu_path' );
add_filter( 'tcb_menu_path_ovation_display', 'tvo_tcb_display_menu_path' );

/**
 * Import backbone templates to editor page
 */
add_filter( 'tcb_backbone_templates', 'tvo_tcb_add_backbone_templates' );
add_filter( 'tcb_modal_templates', 'tvo_tcb_add_modal_templates' );

/**
 * Enqueue tcb editor scripts
 */
add_action( 'tcb_editor_enqueue_scripts', 'tvo_tcb_load_editor_scripts' );

/**
 * Add some Ovation post types to Architect Post Grid Element Banned Types
 */
add_filter( 'tcb_post_grid_banned_types', 'tvo_add_post_grid_banned_types', 10, 1 );


add_action( 'init', function () {
	require_once __DIR__ . '/classes/display-testimonials/class-main.php';
	\TVO\DisplayTestimonials\Main::init();
} );

/**
 * Add Ovation instances to tcb elements
 */
add_filter( 'tcb_element_instances', 'tvo_tcb_element_instances' );

/**
 * Add Ovation to Dash API connections
 */
add_filter( 'tve_filter_available_connection', static function ( $list ) {
	$list['ovation'] = 'Thrive_Dash_List_Connection_Ovation';

	return $list;
} );

add_filter( 'tve_filter_all_api_credentials', static function ( $list ) {
	if ( empty( $list['ovation'] ) ) {
		$list['ovation'] = [ 'connected' => true ];
	}

	return $list;
} );

add_action( 'wp_enqueue_scripts', 'tvo_tcb_frontend_enqueue_scripts', 100 );

/**
 * Add the apprentice frontend assets to a collector array when we detect apprentice identifiers in the content.
 */
add_filter( 'tcb_external_resources_for_content', function ( $resources, $content ) {
	if ( tvo_content_has_tvo_elements( $content ) ) {
		if ( isset( $resources['js'] ) ) {
			$frontend_js = tvo_get_frontend_js();

			$resources['js'][ $frontend_js['id'] ] = [ 'url' => $frontend_js['url'], 'dependencies' => $frontend_js['dependencies'] ];
		}
	}

	return $resources;
}, 10, 2 );

/**
 * get the editing layout for each form type
 */
add_filter( 'tcb_custom_post_layouts', 'tvo_get_editor_layout', 10, 3 );

/**
 * TCB will not include by default CSS / JS on preview form pages, we need to override that functionlity
 */
add_filter( 'tcb_enqueue_resources', static function ( $enqueue_resources ) {

	if ( has_block( 'thrive/' . TVO_Block::OVATION_BLOCK ) || has_shortcode( get_the_content(), 'tvo_shortcode' ) ) {
		$enqueue_resources = true;
	}

	return $enqueue_resources;
} );

/**
 * Overwrite tcb attributes on ovation post types
 */
add_filter( 'tve_lcns_attributes', static function ( $attributes, $post_type ) {
	$tag = 'tvo';
	if ( in_array( $post_type, [ TVO_CAPTURE_POST_TYPE, TVO_DISPLAY_POST_TYPE ], true ) ) {
		return [
			'source'        => $tag,
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( $tag ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $tag ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( $tag ),
			'product'       => 'Thrive Ovation',
			'link'          => tvd_get_individual_plugin_license_link( 'tvo' )
		];
	}

	return $attributes;
}, 10, 2 );

/**
 * Disable settings from TAR
 */
add_filter( 'thrive_theme_allow_page_edit', 'tvo_disable_tar_settings', 10, 1 );
add_filter( 'tcb_can_use_page_events', 'tvo_disable_tar_settings', 10, 1 );
add_filter( 'tcb_can_use_landing_pages', 'tvo_disable_tar_settings', 10, 1 );
add_filter( 'tcb_can_import_content', 'tvo_disable_tar_settings', 10, 1 );
add_filter( 'tcb_can_export_content', 'tvo_disable_tar_settings', 10, 1 );
add_filter( 'tcb_post_grid_banned_types', 'tvo_add_post_types_to_array', 10, 1 );
add_filter( 'tcb_post_visibility_options_availability', 'tvo_add_post_types_to_array', 10, 1 );

/**
 * filter that gets called when the following situation occurs:
 * TCB is installed and enabled, but there is no active license activated
 * in this case, we should only allow users to edit: tvo_capture_post, tvo_display_post
 */
add_filter( 'tcb_skip_license_check', 'tvo_tcb_license_override' );
/**
 * called when trying to edit a post to check TO capability with TA deactivated
 */
add_filter( 'tcb_user_has_plugin_edit_cap', 'tvo_user_can_use_plugin' );
