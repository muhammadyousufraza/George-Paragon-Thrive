<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * modify the localization parameters for the javascript on the editor page (in editing mode)
 */
add_filter( 'tcb_editor_javascript_params', 'tva_editor_javascript_params', 10, 3 );

/**
 * Check the license
 */
add_filter( 'tcb_user_can_edit', 'tva_editor_check_license' );

/**
 * Custom Layouts
 */
add_filter( 'tcb_custom_post_layouts', 'tva_editor_layout', 10, 3 );

/**
 * Disable Revision Manager For Thrive Apprentice Pages
 */
add_filter( 'tcb_has_revision_manager', 'tva_disable_revision_manager', 10, 1 );

/**
 * Add some Apprentice post types to Architect Post Grid Element Banned Types
 */
add_filter( 'tcb_post_grid_banned_types', 'tva_add_post_grid_banned_types', 10, 1 );

/**
 * Adds TA product to TVA
 */
add_filter( 'tcb_element_instances', 'tva_architect_elements', 10, 1 );

/**
 * Include TVA Checkout Component Menu
 */
add_filter( 'tcb_menu_path_checkout', 'tva_include_checkout_menu', 10, 1 );
add_filter( 'tcb_menu_path_checkout_form', 'tva_include_checkout_form_menu', 10, 1 );

/**
 * Adds extra script(s) to the main frame
 */
add_action( 'tcb_main_frame_enqueue', 'tva_add_script_to_main_frame', 9 );
add_filter( 'tve_main_js_dependencies', 'tva_main_js_dependencies' );


/**
 * Set it to 100 because there is an option for not loading vendors in Settings
 *
 * @see tva_frontend_enqueue_scripts
 */
add_action( 'wp_enqueue_scripts', 'tva_enqueue_tcb_scripts', 100 );

/**
 * Adds extra SVG icons to editor page
 */
add_action( 'tcb_editor_iframe_after', 'tva_output_extra_control_panel_svg', 10, 0 );
add_action( 'tcb_output_extra_editor_svg', 'tva_output_extra_editor_svg', 10, 0 );

/**
 * Loading templates of checkout element
 */
add_filter( 'tcb_backbone_templates', 'tve_include_backbone_template', 10, 1 );

add_filter( 'tcb_post_editable', 'tva_post_editable', 10, 3 );

/**
 * Allow Apprentice Pages to be edit with TAr on LpBuild
 *
 * @deprecated this filter is not applied anymore in TAr
 */
add_filter( 'tve_post_type_can_use_landing_page', 'tva_post_type_can_use_landing_page', 10, 1 );

add_filter( 'tcb_can_use_landing_pages', 'tva_can_use_landing_page_templates', 10, 1 );
add_filter( 'tcb_has_templates_tab', 'tva_can_use_landing_page_templates', 10, 1 );
add_filter( 'tcb_allow_landing_page_edit', 'tva_allow_landing_page_edit' );

/**
 * Include Thrive Apprentice texts to TAR Editor
 */
add_filter( 'tcb_js_translate', 'tva_include_items_to_js_translate', 10, 1 );
add_filter( 'tcb_inline_shortcodes', 'tva_add_sendowl_product_shortcode', 10, 1 );

///////////////////////////////////////////////////////////////
/// 			    CLOUD TEMPLATES API                     ///
/// ///////////////////////////////////////////////////////////
add_filter( 'tcb_cloud_request_params', 'tva_filter_cloud_request_params' );
add_action( 'wp_ajax_tva_cloud_templates', 'tva_ajax_landing_page_cloud' );

/**
 * Sets a new transient name for caching cloud templates based on page type: thankyou/checkout/etc
 * If the page/post is not a TA one then leave the transient name as it is set in TAr
 * - fixes displaying right TA Checkout/Thankyou templates
 */
add_filter( 'tve_cloud_templates_transient_name', function ( $transient_name ) {

	if ( isset( $_POST['post_id'] ) ) {
		$post_id   = (int) sanitize_text_field( $_POST['post_id'] );
		$page_type = TVA_Const::get_page_type( $post_id );

		$transient_name .= $page_type ? '_' . $page_type : '';
	}

	return $transient_name;
} );


/**
 * Include Modal Template for checkout
 */
add_filter( 'tcb_modal_templates', 'tva_include_tcb_modals' );

add_filter( 'tcb_can_use_page_events', 'tva_can_user_page_events' );

/**
 * Include backbone templates for TCB
 */
add_filter( 'tcb_backbone_templates', 'include_tcb_templates' );

add_action( 'wp', 'tva_simulate_tve_page_events' );

/**
 * called when trying to edit a post to check TVA capability with TA deactivated
 */
add_filter( 'tcb_user_has_plugin_edit_cap', 'tva_check_can_use_plugin' );
/**
 * Called when edit with TAR buttons should be displayed or not
 */
add_filter( 'tcb_user_has_post_access', 'tva_enable_tar_button' );

/**
 * Called from TAr on LP Modal
 * Where to display Smart LP Templates Section or NOT
 */
add_filter( 'tcb_show_smart_landing_pages', 'tva_display_smart_templates' );

add_filter( 'tcb_can_import_content', 'tva_allow_import_export_content', 10, 2 );
add_filter( 'tcb_can_export_content', 'tva_allow_import_export_content', 10, 2 );

/**
 * Search apprentice post types if they have a specific string in their architect content
 */
add_filter( 'tcb_architect_content_has_string', static function ( $has_string, $string, $post_id ) {

	if ( ! $has_string ) {
		$posts = get_posts( [
			'posts_per_page' => 1,
			'post_type'      => [
				TVA_Const::CHAPTER_POST_TYPE,
				TVA_Const::MODULE_POST_TYPE,
				TVA_Const::COURSE_POST_TYPE,
				TVA_Const::LESSON_POST_TYPE,
				TVA_Resource::POST_TYPE,
				TVA_Access_Restriction::POST_TYPE,
			],
			'meta_query'     => [
				[
					'key'     => 'tve_updated_post',
					'value'   => $string,
					'compare' => 'LIKE',
				],
			],
		] );

		if ( ! empty( $posts ) ) {
			$has_string = true;
		}
	}

	return $has_string;
}, 12, 3 );

/**
 * For the time being only display those options for lessons while being in TA environment
 *
 * @param $allow
 * @param $post
 *
 * @return false|mixed
 */
function tva_allow_import_export_content( $allow, $post ) {
	if ( in_array( $post->post_type, [ TVA_Course_Overview_Post::POST_TYPE, TVA_Const::MODULE_POST_TYPE, TVA_Const::COURSE_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE ] )
		 || tva_get_settings_manager()->is_system_page( $post ) ) {
		$allow = false;
	}

	return $allow;
}

/**
 * Remove blank_hf & completely_blank items from page wizard if the page is an apprentice page
 */
add_filter( 'tcb_get_page_wizard_items', function ( $items = array() ) {
	$post_id = get_the_ID();

	if ( wp_doing_ajax() && empty( $post_id ) && isset( $_POST['post_id'] ) ) {
		$post_id = $_POST['post_id'];
	}

	if ( tva_is_ta_editor_page( $post_id ) ) {
		$items = array_filter( $items, function ( $item ) {
			return ! in_array( $item['layout'], array( 'blank_hf', 'completely_blank' ) );
		} );
	}

	return $items;
}, PHP_INT_MAX );

/**
 * Include Backbone templates
 *
 * @param $files
 *
 * @return array
 */
function tva_include_tcb_modals( $files ) {

	if ( apply_filters( 'tva_include_tcb_modals', TVA_Const::tva_is_checkout_page() ) ) {
		$files[] = TVA_Const::plugin_path( 'tcb-bridge/views/modals/checkout.php' );
	}

	return $files;
}

/**
 * For checkout pages do not display in Landing Page Modal
 * Smart Templates cos it's empty anyway
 *
 * @param bool $display
 *
 * @return bool
 */
function tva_display_smart_templates( $display ) {

	if ( tva_is_ta_editor_page() ) {
		$display = false;
	}

	return $display;
}

/**
 * Include templates for checkout
 *
 * @param $templates
 *
 * @return array
 */
function include_tcb_templates( $templates ) {
	global $post;

	if ( apply_filters( 'include_tcb_templates', TVA_Const::tva_is_checkout_page( $post->ID ) ) ) {
		$templates['checkout-item'] = TVA_Const::plugin_path( 'tcb-bridge/views/cloud-templates/checkout-item.phtml' );
	}

	return $templates;
}

/**
 * Get A template
 */
function tva_ajax_landing_page_cloud() {

	if ( empty( $_POST['task'] ) ) {
		$error = __( 'Invalid request', 'thrive-apprentice' );
	}

	if ( ! isset( $error ) ) {

		try {
			switch ( $_POST['task'] ) {
				case 'download':
					$template = isset( $_POST['template'] ) ? $_POST['template'] : '';
					$post_id  = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';
					if ( empty( $template ) ) {
						throw new Exception( __( 'Invalid template', 'thrive-apprentice' ) );
					}
					$force_download = defined( 'TCB_CLOUD_DEBUG' ) && TCB_CLOUD_DEBUG;
					if ( ! $force_download ) {
						$transient_name = 'tva_template_download_' . $template;
						if ( get_transient( $transient_name ) === false ) {
							$force_download = true;
							set_transient( $transient_name, 1, DAY_IN_SECONDS );
						}
					}
					$downloaded = tve_get_downloaded_templates();

					if ( $force_download || ! array_key_exists( $template, $downloaded ) || tve_get_landing_page_config( $template ) === false ) {

						/**
						 * this will throw Exception if anything goes wrong
						 */
						add_filter( 'tcb_cloud_request_params', 'tva_filter_cloud_request_params' );

						TCB_Landing_Page_Cloud_Templates_Api::getInstance()->download( $template, 2 );

						remove_filter( 'tcb_cloud_request_params', 'tva_filter_cloud_request_params' );
					}

					tcb_landing_page( $post_id )->set_cloud_template( $template );

					wp_send_json(
						array(
							'success' => true,
						)
					);
			}
		} catch ( Exception $e ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				)
			);
		}
	}

	wp_die();
}

/**
 * Add Sendowl Shortcode
 *
 * @param $shortcodes
 *
 * @return array
 */
function tva_add_sendowl_product_shortcode( $shortcodes ) {

	$allowed = ! empty( $_REQUEST[ TVE_FRAME_FLAG ] ) && ( TVA_Const::tva_is_checkout_page() );
	$allowed = $allowed || TVA_Const::tva_is_thankyou_page();
	$allowed = $allowed || tva_get_settings_manager()->is_thankyou_multiple_page();

	if ( false === $allowed ) {
		return $shortcodes;
	}

	$shortcode = array(
		__( 'Thrive Product', 'thrive-apprentice' ) => array(
			array(
				'name'   => __( 'Product Name', 'thrive-apprentice' ),
				'option' => __( 'Product Name', 'thrive-apprentice' ),
				'value'  => 'tva_sendowl_product',
			),
		),
	);

	$shortcodes = array_merge( $shortcodes, $shortcode );

	return $shortcodes;
}

/**
 * Disable landing page functionality when TCB is not active
 *
 * @param $value
 *
 * @return bool
 */
function tva_can_use_landing_page_templates( $value ) {

	$post_type = get_post_type();

	$black_list = array(
		TVA_Const::LESSON_POST_TYPE,
		TVA_Const::MODULE_POST_TYPE,
		TVA_Access_Restriction::POST_TYPE,
		TVA_Course_Overview_Post::POST_TYPE,
		TVA_Course_Completed::POST_TYPE,
		TVA_Const::ASSESSMENT_POST_TYPE,
	);

	if ( in_array( $post_type, $black_list, true ) ) {
		return false;
	}

	if ( tva_is_ta_editor_page() ) {

		if ( tva_get_settings_manager()->is_login_page( get_post() ) ) {
			/**
			 * The Login Page Always has access to landing pages no matter if the user has TAR or Theme Activated
			 */
			return true;
		}

		return tve_in_architect();
	}

	return $value;
}

/**
 * If the user doesn't have TAR or TTB installed, we allow him to edit the login page as a landing page
 *
 * @param {bool} $allow
 *
 * @return bool
 */
function tva_allow_landing_page_edit( $allow ) {
	$settings_manager = tva_get_settings_manager();

	if ( $settings_manager->is_login_page( get_post() ) ) {
		$allow = true;
	} elseif ( $settings_manager->is_index_page() ) { // do not display a LP template for the school overview page
		$allow = false;
	}

	return $allow;
}

/**
 * Inject apprentice_pages post type in list of post types which can be landing pages
 * If TAr is not activated: EXTERNAL_TCB === 0 then we cannot edit landing/checkout pages
 *
 * @param $post_types array
 *
 * @return array $post_types
 */
function tva_post_type_can_use_landing_page( $post_types ) {

	$post_types[] = 'apprentice_pages';

	global $post;

	if ( ! tve_in_architect() && tva_get_settings_manager()->is_checkout_page( $post ) ) {
		return array();
	}

	return $post_types;
}

/**
 * Allow Apprentice lessons modules and chapters and the checkout page to be editable with TCB
 *
 * @param $return    bool
 * @param $post_type string
 * @param $post_id   int
 *
 * @return bool
 */
function tva_post_editable( $return, $post_type, $post_id ) {

	/**
	 * Never allow editing TA index page
	 */
	if ( tva_get_settings_manager()->is_index_page( $post_id ) ) {
		return false;
	}

	/**
	 * No need to go further if TAR is active
	 */
	if ( tve_in_architect() ) {
		return $return;
	}

	/**
	 * Enable editing for allowed TA posts
	 */
	if ( tva_is_post_editable_with_tar( $post_type, intval( $post_id ) ) ) {
		$return = true;
	}

	return $return;
}

/**
 * Is Editable with TCB
 *
 * @param int|string $post_or_type
 *
 * @return bool
 */
function tva_is_editable( $post_or_type ) {

	$post_or_type = is_numeric( $post_or_type ) ? get_post_type( $post_or_type ) : $post_or_type;

	global $post;

	$white_post_types = array(
		TVA_Const::LESSON_POST_TYPE,
		TVA_Const::MODULE_POST_TYPE,
		TVA_Access_Restriction::POST_TYPE,
		TVA_Course_Overview_Post::POST_TYPE,
		TVA_Course_Certificate::POST_TYPE,
		TVA_Course_Completed::POST_TYPE,
		TVA_Const::ASSESSMENT_POST_TYPE,
	);

	$return = in_array( $post_or_type, $white_post_types, true );
	$return = $return || ( $post instanceof WP_Post && TVA_Const::tva_is_checkout_page() );
	$return = $return || ( $post instanceof WP_Post && TVA_Sendowl_Settings::instance()->is_thankyou_page( $post ) );
	$return = $return || ( $post instanceof WP_Post && TVA_Sendowl_Settings::instance()->is_thankyou_multiple_page( $post ) );
	$return = $return || ( $post instanceof WP_Post && tva_get_settings_manager()->is_login_page( $post ) );
	$return = $return || ( $post instanceof WP_Post && tva_get_settings_manager()->is_certificate_validation_page( $post ) );
	$return = $return || ( defined( 'DOING_AJAX' ) && DOING_AJAX );

	return apply_filters( 'tva_is_editable', $return, $post_or_type );
}

/**
 * Check if the post can be edited by checking access and post type
 *
 * @param bool $has_access
 *
 * @return bool
 */
function tva_check_can_use_plugin( $has_access ) {
	$post = get_post();
	if ( ! $post && ! empty( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
		$post = get_post( $_POST['post_id'] );
	}
	$post_type = get_post_type( $post );

	if ( tva_get_settings_manager()->is_checkout_page( $post )
		 || tva_get_settings_manager()->is_thankyou_page( $post )
		 || tva_get_settings_manager()->is_login_page( $post )
		 || in_array( $post_type, [
			TVA_Const::LESSON_POST_TYPE,
			TVA_Const::CHAPTER_POST_TYPE,
			TVA_Const::MODULE_POST_TYPE,
			TVA_Course_Overview_Post::POST_TYPE,
			TVA_Access_Restriction::POST_TYPE,
			TVA_Course_Certificate::POST_TYPE,
			TVA_Course_Completed::POST_TYPE,
			TVA_Const::ASSESSMENT_POST_TYPE,
		] )
	) {
		$has_access = TVA_Product::has_access();
	}

	return $has_access;
}

/**
 * Check if the page is register page so we show the Edit with TAR in pages tab
 *
 * @param $access
 *
 * @return bool
 */
function tva_enable_tar_button( $access ) {
	$post = get_post();
	if ( $post && tva_get_settings_manager()->is_checkout_page( $post ) || tva_get_settings_manager()->is_login_page( $post ) ) {
		$access = true;
	}

	return $access;
}


function tva_editor_check_license( $valid ) {
	if ( ! tva_is_apprentice() ) {
		return $valid;
	}

	if ( ! tva_license_activated() ) {
		add_action( 'wp_print_footer_scripts', 'tva_license_warning' );

		return false;
	}

	return true;
}

/**
 * Disable Revision Manager For Quiz Builder Pages
 *
 * @param bool $status
 *
 * @return bool
 */
function tva_disable_revision_manager( $status = true ) {
	$post_type = get_post_type();

	if ( tva_is_editable( $post_type ) ) {
		return ! $status;
	}

	return $status;
}

/**
 * Add some Apprentice post types to Architect Post Grid Element Banned Types
 *
 * @param array $banned_types
 *
 * @return array
 */
function tva_add_post_grid_banned_types( $banned_types = array() ) {
	$banned_types[] = TVA_Const::COURSE_POST_TYPE;

	return $banned_types;
}

/**
 * Set JS params
 *
 * @param $js_params
 * @param $post_id
 *
 * @return mixed
 */
function tva_editor_javascript_params( $js_params, $post_id ) {

	$ta_page = ! tva_is_apprentice() && ( ! tva_get_settings_manager()->is_checkout_page( $post_id ) || ! tva_get_settings_manager()->is_login_page( $post_id ) );

	if ( ( $ta_page ) || tve_in_architect() ) {
		return $js_params;
	}

	/** clear out any data that's not necessary on the editor  */
	$js_params['landing_page']          = '';
	$js_params['landing_page_config']   = array();
	$js_params['landing_pages']         = array();
	$js_params['page_events']           = array();
	$js_params['landing_page_lightbox'] = array();
	$js_params['style_families']        = tve_get_style_families();
	$js_params['style_classes']         = array(
		'Flat'    => 'tve_flt',
		'Classy'  => 'tve_clsy',
		'Minimal' => 'tve_min',
	);
	$js_params['read_more_tag']         = false;

	return $js_params;
}

function tva_editor_layout( $current_templates, $post_id, $post_type ) {
	if ( ! tva_is_editable( $post_type ) ) {
		return $current_templates;
	}

	if ( TVA_Access_Restriction::POST_TYPE === $post_type ) {
		/* edit this content using a blank layout */
		tva_enqueue_style( 'tva-access-restriction', TVA_Const::plugin_url( 'css/access-restriction.css' ) );
		$current_templates = array( TVA_Const::plugin_path( 'templates/access-restriction/tcb-editor-layout.php' ) );
	}

	// QUIZ BUILDER??? What's this doing here ??!!
	/* flat is the default style for Thrive Quiz Builder designs */
	global $tve_style_family_classes;
	$tve_style_families = tve_get_style_families();
	$style_family       = 'Flat';
	$style_key          = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $style_family ] );
	/* Style family */
	wp_style_is( $style_key ) || tve_enqueue_style( $style_key, $tve_style_families[ $style_family ] );

	return $current_templates;
}

/**
 * show a box with a warning message and a link to take the user to the license activation page
 * this will be called only when no valid / activated license has been found
 *
 * @return mixed
 */
function tva_license_warning() {

	return include TVA_Const::plugin_path( 'admin/views/license_inactive.php' );
}

add_action( 'init', array( '\TVA\Architect\Main', 'init' ) );
add_action( 'init', array( '\TVA\Architect\Main', 'load_conditional_display' ), 11 );

/**
 * Adds checkout element to TAr Editor
 *
 * @param array $elements
 *
 * @return array
 */
function tva_architect_elements( $elements = array() ) {

	$post = get_post();

	if ( tva_is_editable( $post ) ) {

		if ( tva_get_settings_manager()->is_checkout_page( $post ) || wp_doing_ajax() ) {

			require_once dirname( __FILE__ ) . '/editor-elements/class-tcb-checkout-element.php';
			require_once dirname( __FILE__ ) . '/editor-elements/class-tcb-checkout-form-element.php';

			$elements['checkout']      = new TCB_Checkout_Element( 'checkout' );
			$elements['checkout_form'] = new TCB_Checkout_Form_Element( 'checkout_form' );
		}
	}

	return $elements;
}

/**
 * Gets Checkout Element Menu Component file path
 *
 * @param $component_file_path string
 *
 * @return string
 */
function tva_include_checkout_menu( $component_file_path ) {

	if ( tva_is_editable( get_post_type() ) ) {

		return TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/checkout.php' );
	}

	return $component_file_path;
}

/**
 * Gets CheckoutForm Element Menu Component file path
 *
 * @param $component_file_path string
 *
 * @return string
 */
function tva_include_checkout_form_menu( $component_file_path ) {

	if ( tva_is_editable( get_post_type() ) ) {

		return TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/checkout_form.php' );
	}

	return $component_file_path;
}

function tva_add_script_to_main_frame() {
	if ( tva_is_editable( get_post_type() ) ) {
		tva_enqueue_script( 'tva-internal-editor', TVA_Const::plugin_url( 'tcb-bridge/assets/js/tva-tcb-internal.min.js' ), array( 'jquery', 'underscore' ) );
		tva_enqueue_style( 'tva-ce-preview-list', TVA_Const::plugin_url( 'tcb-bridge/assets/css/main_frame.css' ) );
	}

	if ( tva_is_apprentice_template() ) {
		tva_enqueue_script( 'tva-visual-builder-editor', TVA_Const::plugin_url( 'tcb-bridge/assets/js/tva-tcb-visual-builder.min.js' ), array( 'jquery', 'underscore' ) );
		tva_enqueue_style( 'tva-visual-builder-main-frame', TVA_Const::plugin_url( 'tcb-bridge/assets/css/visual-builder-main-frame.css' ) );
	}

	tva_enqueue_style( 'tva-architect-main-frame', TVA_Const::plugin_url( 'tcb-bridge/assets/css/architect_main_frame.css' ) );
	tva_enqueue_script( 'tva-shared-editor', TVA_Const::plugin_url( 'tcb-bridge/assets/js/tva-tcb-shared.min.js' ), array( 'jquery', 'underscore' ) );
	wp_localize_script( 'tva-shared-editor', 'TVA', array(
		'courses'           => TVA_Course_V2::get_items_for_architect_integration(),
		'nonce'             => wp_create_nonce( 'wp_rest' ),
		'routes'            => array(
			'course'      => tva_get_route_url( 'course_element' ),
			'course_list' => tva_get_route_url( 'course_list_element' ),
			'resources'   => tva_get_route_url( 'resources' ),
			'assessments' => tva_get_route_url( 'assessments' ),
			'products'    => tva_get_route_url( 'products' ),
		),
		'plugin_url'        => TVA_Const::plugin_url(),
		'tva_buy_now_label' => TVA_Dynamic_Labels::get_cta_label( 'buy_now' ),
	) );
}

/**
 * Add the external editor js as a dependency for the main js from architect
 *
 * @param $dependencies
 *
 * @return mixed
 */
function tva_main_js_dependencies( $dependencies = [] ) {

	if ( tva_is_apprentice_template() ) {
		$dependencies[] = 'tva-visual-builder-editor';
	}

	$dependencies[] = 'tva-shared-editor';

	if ( tva_is_editable( get_post_type() ) ) {
		$dependencies[] = 'tva-internal-editor';
	}

	return $dependencies;
}

/**
 * Checks if the $post contains any of TA elements in its TAr content meta
 *
 * @param WP_Post $post
 *
 * @return bool
 */
function tva_post_has_tva_elements( $post ) {

	if ( false === $post instanceof WP_Post ) {
		return false;
	}

	return tva_content_has_tva_elements( tve_get_post_meta( $post->ID, 'tve_updated_post' ) );
}

/**
 * Checks if the content has any TA element identifiers.
 *
 * @param $content
 *
 * @return bool
 */
function tva_content_has_tva_elements( $content ) {
	$tva_elements = [
		'tva-course',
		'tva-course-list',
		'tva_course_list',
		'tva-lesson-resources',
		'tva-assessment',
		'tva_stripe_url',
	];

	preg_match( '/' . implode( '|', $tva_elements ) . '/', $content, $matches );

	return ! empty( $matches );
}

/**
 * Add the apprentice frontend assets to a collector array when we detect apprentice identifiers in the content.
 */
add_filter( 'tcb_external_resources_for_content', function ( $resources, $content ) {
	if ( tva_content_has_tva_elements( $content ) ) {
		if ( isset( $resources['js'] ) ) {
			$frontend_js = tva_get_frontend_js();

			$resources['js'][ $frontend_js['id'] ] = [ 'url' => $frontend_js['url'], 'dependencies' => $frontend_js['dependencies'] ];
		}

		if ( isset( $resources['css'] ) ) {
			$frontend_css = tva_get_frontend_css();

			$resources['css'][ $frontend_css['id'] ] = $frontend_css['url'];
		}
	}

	return $resources;
}, 10, 2 );

/**
 * Enqueue the editor scripts
 * - it should be included where it is required only
 */
function tva_enqueue_tcb_scripts() {

	$frontend = false;

	global $post;

	//if is post and has TAr content
	if ( $post instanceof WP_Post && tva_post_has_tva_elements( $post ) ) {
		$frontend = true;
	}

	/**
	 * Apply filters for allowing vendors to hook in and
	 * - enqueue TA frontend styles file
	 */
	if ( apply_filters( 'tva_enqueue_frontend', $frontend ) || is_editor_page() ) {
		$frontend_css = tva_get_frontend_css();
		$frontend_js  = tva_get_frontend_js();

		tve_enqueue_style( $frontend_css['id'], $frontend_css['url'] );
		tva_enqueue_script( $frontend_js['id'], $frontend_js['url'], $frontend_js['dependencies'], false, true );
	}

	if ( is_editor_page() ) {
		tve_enqueue_style( 'tva_tcb_style_editor', TVA_Const::plugin_url( 'tcb-bridge/assets/css/editor.css' ) );
	}
}

/**
 * @return array
 */
function tva_get_frontend_css() {
	return [
		'id'  => 'tva_tcb_style_frontend',
		'url' => TVA_Const::plugin_url( 'tcb-bridge/assets/css/frontend.css' ),
	];
}

/**
 * @return array
 */
function tva_get_frontend_js() {
	$dependencies = [ 'jquery' ];

	if ( wp_script_is( 'tve_frontend' ) ) {
		$dependencies [] = 'tve_frontend';
	}

	return [
		'id'           => 'tva-tcb-frontend-js',
		'url'          => TVA_Const::plugin_url( 'tcb-bridge/assets/js/tva-tcb-frontend.min.js' ),
		'dependencies' => $dependencies,
	];
}

/**
 * Loading checkout element's templates only in editor
 *
 * @param array $templates
 *
 * @return array
 */
function tve_include_backbone_template( $templates = array() ) {

	$templates = array_merge( $templates, tve_dash_get_backbone_templates( plugin_dir_path( dirname( __FILE__ ) ) . 'tcb-bridge/editor-layouts/backbone', 'backbone' ) );

	//append controls templates
	return array_merge(
		$templates,
		tve_dash_get_backbone_templates(
			TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/controls' ),
			'controls'
		)
	);
}

/**
 * Outputs Extra SVG Icons to editor page (Control Panel)
 */
function tva_output_extra_control_panel_svg() {

	if ( tva_is_editable( get_post_type() ) ) {
		include dirname( __FILE__ ) . '/assets/fonts/tva-main.svg';
	}

	include dirname( __FILE__ ) . '/assets/fonts/tva-shared.svg';

	if ( tva_is_apprentice_template() ) {
		include dirname( __FILE__ ) . '/assets/fonts/tva-main-visual-builder.svg';
	}
}

/**
 * Output the External SVG Icons in the iframe
 */
function tva_output_extra_editor_svg() {
	include dirname( __FILE__ ) . '/assets/fonts/tva-editor-shared.svg';
}

/**
 * Include Thrive Apprentice Text to TAR Editor
 *
 * @param array $translate_arr
 *
 * @return array
 */
function tva_include_items_to_js_translate( $translate_arr = array() ) {

	if ( tva_is_editable( get_post_type() ) ) {
		if ( ! empty( $translate_arr['cf_errors'] ) && is_array( $translate_arr['cf_errors'] ) ) {
			$translate_arr['cf_errors']['existing_user_email'] = array(
				'label' => __( 'Email Is Already Used', 'thrive-apprentice' ),
			);
		}
	}

	return $translate_arr;
}

function tva_filter_cloud_request_params( $params ) {

	if ( empty( $params['type'] ) ) {
		global $post;


		$post_id = null;
		$type    = null;

		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} else {
			/**
			 * $page_id is sent over $_REQUEST when zip template is imported from page settings
			 */
			$post_id = ! empty( $_REQUEST['page_id'] ) ? $_REQUEST['page_id'] : null;
		}

		/**
		 * loading a template through ajax $post_id param is used
		 */

		if ( ! isset( $post_id ) ) {
			$post_id = ! empty( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : null;
		}

		if ( isset( $post_id ) ) {
			$type = TVA_Const::get_page_type( $post_id );
		}

		if ( isset( $type ) ) {
			$params['appr'] = '1';
			$params['type'] = $type;
		}
	}

	return $params;
}

/**
 * There is a strange/edge case when a user sets a page event for a checkout page and then deactivates TAr
 * then in frontend TAr core tries to parse the events but the function does not exists and a fatal error is thrown
 *
 * Fix for this is to add the function after the TAr is loaded
 */
function tva_simulate_tve_page_events() {
	if ( false === function_exists( 'tve_page_events' ) ) {
		function tve_page_events() {
		}
	}
}

/**
 * Page events from TAr can be used only if TAr is installed and activated
 *
 * @param $can_use
 *
 * @return bool
 */
function tva_can_user_page_events( $can_use ) {

	if ( ( TVA_Const::tva_is_checkout_page() || TVA_Const::tva_is_thankyou_page() ) && ! tve_in_architect() ) {
		$can_use = false;
	}

	return $can_use;
}

/**
 * Action to trash post
 *
 * Checks if the trashed post is an apprentice page -> It resets that particular setting
 */
add_action( 'wp_trash_post', 'tva_trash_post' );

/**
 * Resets the page value when that post is trashed
 *
 * @param int $post_id Post ID
 */
function tva_trash_post( $post_id ) {

	$reset_pages = tva_get_settings_manager()->pages_indexes();

	foreach ( $reset_pages as $page ) {
		$page_factory = tva_get_settings_manager()->factory( $page );

		if ( $page_factory->get_value() === $post_id ) {
			$page_factory->set_value( 0 );

			return; //Return if you find one to skipp all the iterations
		}
	}
}

/**
 * Adds Thrive Apprentice Dynamic Links to TAr
 */
add_filter( 'tcb_dynamiclink_data', static function ( $data ) {
	if ( get_post_type() === TVA_Access_Restriction::POST_TYPE ) {
		/** @var $tva_shortcodes TVA_Shortcodes */
		global $tva_shortcodes;
		$data['Thrive Apprentice'] = [
			'links'     => [
				/* a single optgroup */
				[
					[
						'name' => __( 'Login & registration page', 'thrive-apprentice' ),
						'show' => true,
						'id'   => 'login',
						'url'  => $tva_shortcodes->dynamic_link( [ 'id' => 'login' ] ),
					],
				],
			],
			'options'   => [
				'login' => [
					'label'   => __( 'Default state', 'thrive-apprentice' ),
					'type'    => 'select',
					'options' => [
						'login'    => __( 'Login', 'thrive-apprentice' ),
						'register' => __( 'Register', 'thrive-apprentice' ),
					],
				],
			],
			'shortcode' => 'tva_dynamic_link',
		];
	}

	return $data;
} );

/**
 * Filter the iframe url for course overview post
 * - so that it returns the course URL to be loaded in iFrame
 */
add_filter(
	'tcb_frame_request_uri',
	static function ( $editor_link, $post_id ) {

		if ( $post_id && TVA_Course_Overview_Post::POST_TYPE === get_post_type( (int) $post_id ) ) {
			$terms = wp_get_post_terms( (int) $post_id, TVA_Const::COURSE_TAXONOMY );
			if ( ! empty( $terms ) ) {
				/** @var WP_Term $term */
				$term        = $terms[0];
				$editor_link = get_term_link( $term->term_id );
			}
		}

		return $editor_link;
	},
	10,
	2
);
/**
 * Add to the list of excluded posts from post list the demo lessons from apprentice
 *
 * @param $excluded - array with the excluded ids
 *
 * @return array
 */
add_filter(
	'tcb_post_list_excluded_post_ids',
	static function ( $excluded ) {
		$demo_lessons = array(
			'post_type'      => 'tva_lesson',
			'posts_per_page' => - 1,
			'meta_key'       => 'tva_is_demo',
			'fields'         => 'ids',
		);

		return array_merge( $excluded, get_posts( $demo_lessons ) );
	},
	10, 2
);


/**
 * Overwrite tcb attributes on ovation post types
 */
add_filter( 'tve_lcns_attributes', static function ( $attributes, $post_type ) {
	$tag = 'tva';

	if ( in_array( $post_type, [
			TVA_Const::LESSON_POST_TYPE,
			TVA_Const::MODULE_POST_TYPE,
			TVA_Const::ASSESSMENT_POST_TYPE,
			TVA_Access_Restriction::POST_TYPE,
			TVA_Course_Overview_Post::POST_TYPE,
			TVA_Course_Certificate::POST_TYPE,
			TVA_Course_Completed::POST_TYPE,
		], true ) || ( ! empty( $_REQUEST['tva_skin_id'] ) && $post_type === THRIVE_TEMPLATE ) || TVA_Page_Setting::get( 'login_page' ) === get_the_ID() ) {
		return [
			'source'        => $tag,
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( $tag ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $tag ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( $tag ),
			'link'          => tvd_get_individual_plugin_license_link( $tag ),
			'product'       => 'Thrive Apprentice',
		];
	}

	return $attributes;
}, 12, 2 );

add_filter( 'thrive_video_save_range_response', static function ( $data, $request ) {
	$post_id   = $request->get_param( 'post_id' );
	$post_type = get_post_type( $post_id );

	if ( $post_type === TVA_Const::LESSON_POST_TYPE ) {
		$data['allowMarkComplete'] = ( new TVA_Lesson( $post_id ) )->can_be_marked_as_completed();
	}

	return $data;
}, 10, 2 );
