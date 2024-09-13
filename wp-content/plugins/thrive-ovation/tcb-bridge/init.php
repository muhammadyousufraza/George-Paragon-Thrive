<?php
/**
 * this file is used only when there is no TCB plugin available
 *
 * it should handle the following:
 * - make sure the frontend scripts are included when in editing mode and frontend
 * - add 'post', 'page' to the blacklist for editable post type (so that users cannot edit those types of posts)
 * - however, the user must still be able to edit a tvo_capture, tvo_display
 * - surpass the license check for the TCB plugin (there is a filter in WP called pre_option_{option_name}), where option_name would be tve_license_status
 */


require_once dirname( __DIR__ ) . '/tcb/external-architect.php';

/* short-circuit the tve_license_check notice by always returning true */
add_filter( 'pre_option_tve_license_status', '__return_true' );

/* force only capture and display testimonials element to be editable with TCB */
add_filter( 'tcb_post_types', 'tve_ovation_disable_edit', 5 );

/* enqueue scripts for the frontend - used only in editing and preview modes */
add_action( 'wp_enqueue_scripts', 'tve_ovation_frontend_enqueue_scripts' );

/**
 * posts and pages must not be editable
 *
 * @param array $post_types
 *
 * @return array
 */
function tve_ovation_disable_edit( $post_types ) {
	$post_types['force_whitelist'] = isset( $post_types['force_whitelist'] ) ? $post_types['force_whitelist'] : array();
	$post_types['force_whitelist'] = array_merge( $post_types['force_whitelist'], array(
		TVO_CAPTURE_POST_TYPE,
		TVO_DISPLAY_POST_TYPE,
	) ); // only allow these types of posts to be editable with TCB

	return $post_types;
}

/**
 * enqueue scripts for the frontend - used only in editing and preview modes
 *
 * for the rest of the pages (where the forms are actually displayed), we need to include these from the point where we detect that a form will be displayed
 */
function tve_ovation_frontend_enqueue_scripts() {
	if ( ! is_singular( array( TVO_CAPTURE_POST_TYPE, TVO_DISPLAY_POST_TYPE ) ) ) {
		return false;
	}
	global $post;
	$post_id = $post instanceof WP_Post ? $post->ID : null;
	if ( tve_get_post_meta( get_the_ID(), 'tve_has_masonry' ) ) {
		wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
	}

	tve_enqueue_style_family();

	/* params for the frontend script */
	$frontend_options = array(
		'ajaxurl'        => admin_url( 'admin-ajax.php' ),
		'is_editor_page' => true,
		'is_single'      => (string) ( (int) is_singular() ),
		'dash_url'       => TVE_DASH_URL,
	);

	if ( ! empty( $frontend_options['is_single'] ) ) {

		$frontend_options['post_id'] = $post_id;
	}

	if ( ! is_editor_page() ) {
		$frontend_options['is_editor_page'] = false;
	}

	TCB\Lightspeed\Css::get_instance( $post_id )->load_optimized_style( 'base' );
	TCB\Lightspeed\JS::get_instance( $post_id )->load_modules();
	tve_load_custom_css();
	tve_enqueue_custom_fonts();
	wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );
}
