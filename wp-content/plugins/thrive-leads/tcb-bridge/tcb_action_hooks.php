<?php

/**
 * section that defines each of the fields used when saving data from the editor
 */
/**
 * inline style rules (custom colors)
 */
define( 'TVE_LEADS_FIELD_INLINE_CSS', 'inline_css' );

/**
 * user-defined custom css
 */
define( 'TVE_LEADS_FIELD_USER_CSS', 'user_css' );

/**
 * user-defined custom css
 */
define( 'TVE_LEADS_FIELD_FORM_HEIGHT', 'form-height' );

/**
 * meta field where the post content should reside
 */
define( 'TVE_LEADS_FIELD_SAVED_CONTENT', 'content' );

/**
 * meta field where we store if we have an icon pack or not
 */
define( 'TVE_LEADS_FIELD_ICON_PACK', 'icons' );

/**
 * meta field used to store the tve_globals parameters setup from the editor
 */
define( 'TVE_LEADS_FIELD_GLOBALS', 'globals' );

/**
 * the array field used to save the custom fonts used
 */
define( 'TVE_LEADS_FIELD_CUSTOM_FONTS', 'fonts' );

/**
 * field for a flag that indicates whether or not the form variation needs a masonry library
 */
define( 'TVE_LEADS_FIELD_HAS_MASONRY', 'masonry' );

/**
 * field for a flag that indicates whether or not the form variation needs a typist library
 */
define( 'TVE_LEADS_FIELD_HAS_TYPEFOCUS', 'typefocus' );

/**
 * field that holds the key of the template used for the form design
 */
define( 'TVE_LEADS_FIELD_TEMPLATE', 'tpl' );

/**
 * field that stores the name of the template; used to select the proper template in list/lightbox even it's multi step or not
 */
define( 'TVE_LEADS_FIELD_SELECTED_TEMPLATE', 'selected_template' );

/**
 * field that holds the index of the state template (used in multi-step templates manager)
 */
define( 'TVE_LEADS_FIELD_STATE_INDEX', 'state_index' );

/**
 * field that holds the visibility status of the already-subscribed state
 */
define( 'TVE_LEADS_FIELD_STATE_VISIBILITY', 'visibility' );

/**
 * the action name for the save post ajax callback
 */
define( 'TVE_LEADS_ACTION_SAVE_POST', 'tve_leads_save_form' );

/**
 * main entry point for ajax requests related to choosing / resetting templates
 */
define( 'TVE_LEADS_ACTION_TEMPLATE', 'tve_leads_tpl' );

/**
 * main entry point for ajax requests related to form states actions
 */
define( 'TVE_LEADS_ACTION_STATE', 'tve_leads_state' );

/**
 * get the editing layout for each form type
 */
add_filter( 'tcb_custom_post_layouts', 'tve_leads_get_form_layout', 10, 3 );

/**
 * get the saved editor content
 */
add_filter( 'tcb_editor_get_editor_content', 'tve_leads_get_editor_content', 10, 3 );

/**
 * we need to pass in also the variation_key from the $_GET variable
 */
add_filter( 'tcb_required_get_fields', 'tve_leads_required_get_fields', 10 );

/**
 * we need to modify the preview URL for tve_form_type post types
 */
add_filter( 'tcb_editor_preview_link_query_args', 'tve_leads_append_preview_link_args', 10, 2 );

/**
 * modify the edit url by inserting also the form variation key in the query vars
 */
add_filter( 'tcb_editor_edit_link_query_args', 'tve_leads_append_preview_link_args', 10, 2 );

/**
 * modify the localization parameters for the javascript on the editor page (in editing mode)
 */
add_filter( 'tcb_main_frame_localize', 'tve_leads_editor_javascript_params', 10, 3 );

/**
 * action hook to allow output of the editor saved contents in a custom environment
 * this is called from custom editing templates
 */
add_filter( 'tve_editor_custom_content', 'tve_editor_custom_content', 10, 2 );

/**
 * filter to deny editing of form variations that are currently included in a test
 */
add_filter( 'tcb_post_editable', 'tve_leads_check_editable_post' );

/**
 * filter that gets called when the following situation occurs:
 * TCB is installed and enabled, but there is no active license activated
 * in this case, we should only allow users to edit: tve_form_types, tve_shortcodes, tcb_lightboxes
 */
add_filter( 'tcb_skip_license_check', 'tve_leads_tcb_license_override' );

/**
 * Filter applied by TCB lb_icon.php
 * Appends extra icons to lightbox
 */
add_filter( 'tcb_get_extra_icons', 'tve_leads_get_form_icons' );

/**
 * called when enqueuing scripts from the editor on editor page. it needs to check if TL has a valid license
 */
add_filter( 'tcb_user_can_edit', 'tve_leads_editor_check_license' );

add_filter( 'tcb_user_can_edit', 'tve_leads_editor_check_tcb_version' );

/**
 * action hook that overrides the default tve_save_post action from the editor
 * used to save the editor contents in custom post fields specific to the Thrive Leads plugin
 */
add_filter( 'tcb_ajax_' . TVE_LEADS_ACTION_SAVE_POST, 'tve_leads_save_editor_content' );

/**
 * main entry point for template-related actions: choose new template, reset current template
 */
add_action( 'wp_ajax_' . TVE_LEADS_ACTION_TEMPLATE, 'tve_leads_template_action' );
add_action( 'tcb_ajax_' . TVE_LEADS_ACTION_TEMPLATE, 'tve_leads_template_action' );

/**
 * main entry point for template-related actions: choose new template, reset current template
 */
add_action( 'tcb_ajax_' . TVE_LEADS_ACTION_STATE, 'tve_leads_form_state_action' );

/**
 * filter for structuring extra actions needed in the editor page
 */
add_filter( 'tcb_event_manager_action_tabs', 'tve_leads_editor_actions' );

/**
 * filter the available Event Manager action classes
 */
add_filter( 'tcb_event_action_classes', 'tve_leads_load_action_classes' );

/**
 * TCB will not include by default CSS / JS on preview form pages, we need to override that functionlity
 */
add_filter( 'tcb_enqueue_resources', 'tve_leads_enqueue_resources_preview' );

/**
 * Include the page sections menu control panel if a TL form is being edited.
 */
add_filter( 'tcb_show_page_sections_menu', 'tve_leads_show_page_section_menu' );

/**
 * Main cpanel configuration
 */
add_filter( 'tcb_main_cpanel_config', 'tve_leads_main_cpanel_config' );

/* TCB Menu Elements */
add_action( 'tcb_custom_menus_html', 'tve_leads_add_tcb_menu_elements' );

/**
 * Add Thrive Boxes to link search results
 */
add_filter( 'tcb_link_search_post_types', 'tve_leads_search_thrivebox' );

/**
 * Enqueue the TCB editor extensions from thrive leads
 */
add_action( 'tcb_main_frame_enqueue', 'tve_leads_enqueue_editor_extension' );

/**
 * Filter hook, include Thrive Leads translations for the editor page.
 */
add_filter( 'tcb_js_translate', 'tve_leads_js_translate' );

add_filter( 'tcb_backbone_templates', 'tve_get_editor_backbone_templates' );

/**
 * Action hooks - TCB2
 */
add_action( 'tcb_element_instances', 'tve_leads_add_tcb_elements' );
/**
 * Remove Some Plugin Instances From TCB - Leads Editor
 */
add_filter( 'tcb_remove_instances', 'tve_leads_remove_element_instances', 10, 1 );
add_filter( 'tcb_menu_path_tl_shortcode', 'tve_leads_shortcode_menu_path' );
add_filter( 'tcb_main_frame_localize', 'tve_leads_localize_shortcodes' );
add_filter( 'tcb_main_frame_localize', 'tve_leads_localize_templates' );
add_filter( 'tcb_ajax_render_tl_shortcode', 'tve_leads_render_tl_shortcode', 10, 2 );
add_filter( 'tcb_has_templates_tab', 'tve_leads_has_templates_tab' );
add_action( 'tcb_modal_templates', 'tve_leads_modal_templates' );
add_action( 'tcb_can_use_page_events', 'tve_leads_can_use_page_events' );
add_action( 'tcb_lead_generation_menu', 'tve_leads_insert_asset_delivery_control' );
add_action( 'tcb_elements_localize', 'tve_leads_forms_config' );

add_action( 'tcb_right_sidebar_content_settings', static function () {
	if ( tve_leads_post_type_editable( get_post_type() ) ) {
		echo tve_leads_template( 'template-settings', null, true );
	}
} );

add_action( 'tcb_right_sidebar_top_settings', static function () {
	if ( tve_leads_post_type_editable( get_post_type() ) ) {
		echo tve_leads_template( 'reset-settings', null, true );
	}
} );

/**
 *  Modify TCB Close Url For Leads Editor
 */
add_filter( 'tcb_close_url', 'tve_leads_tcb_close_url', 10, 1 );

/**
 * Disable Revision Manager For Leads Pages
 */
add_filter( 'tcb_has_revision_manager', 'tve_leads_disable_revision_manager', 10, 1 );

/**
 * Disable Page Events for Leads Pages
 */
add_filter( 'tcb_can_use_page_events', 'tve_leads_disable_page_events', 10, 1 );

add_filter( 'architect.branding', 'tve_leads_architect_branding' );

/**
 * Adds TL query string variation to preview link
 */
add_filter( 'preview_post_link', 'tve_leads_preview_post_link', 10, 2 );


/**
 * called when trying to edit a post to check TL capability with TA deactivated
 */
add_filter( 'tcb_user_has_plugin_edit_cap', 'tve_leads_user_can_use_plugin', 20 );

/**
 * Adds Thrive Leads data to the hook that fetched data for the 3rd party developers
 */
add_filter( 'tcb_parse_lead_gen_form_data', 'tve_leads_parse_lead_gen_form_data', 10, 1 );

/**
 * Overwrite tcb attributes on leads post types
 */
add_filter( 'tve_lcns_attributes', static function ( $attributes, $post_type ) {
	$tag = 'tl';
	if ( in_array( $post_type, [
		TVE_LEADS_POST_FORM_TYPE,
		TVE_LEADS_POST_GROUP_TYPE,
		TVE_LEADS_POST_SHORTCODE_TYPE,
		TVE_LEADS_POST_TWO_STEP_LIGHTBOX,
		TVE_LEADS_POST_ASSET_GROUP,
		TVE_LEADS_POST_ONE_CLICK_SIGNUP,
	], true ) ) {
		return [
			'source'        => $tag,
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( $tag ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $tag ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( $tag ),
			'product'       => 'Thrive Leads',
			'link'          => tvd_get_individual_plugin_license_link( $tag ),
		];
	}

	return $attributes;
}, 10, 2 );

/**
 * Check if the post can be edited by checking access and post type
 *
 * @param bool $has_access
 *
 * @return bool
 */
function tve_leads_user_can_use_plugin( $has_access ) {
	$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : get_the_ID();

	if ( tve_leads_post_type_editable( get_post_type( $post_id ) ) ) {
		$has_access = TL_Product::has_access();
	}

	return $has_access;
}

/**
 * Adds TL query string variation to preview link
 *
 * @param $preview_link
 * @param $post
 *
 * @return string
 */
function tve_leads_preview_post_link( $preview_link, $post = null ) {

	if ( ! empty( $_GET['_key'] ) && tve_leads_post_type_editable( get_post_type( $post ) ) ) {

		$preview_link = add_query_arg(
			array(
				'_key' => $_GET['_key'],
			),
			$preview_link
		);
	}

	return $preview_link;
}

/**
 * Called on ajax request when user adds Thrive Leads Shortcode into content
 *
 * @param $data_to_filter
 * @param $params
 *
 * @return array|mixed
 */
function tve_leads_render_tl_shortcode( $data_to_filter, $params ) {

	$params = empty( $params ) || ! is_array( $params ) ? array() : $params;
	$data   = array();

	if ( ! empty( $params['shortcode_id'] ) ) {
		$data = tve_leads_shortcode_render( array(
			'id'         => (int) $params['shortcode_id'],
			'for_editor' => true,
		) );
	}

	if ( empty( $data ) || ! is_array( $data ) || empty( $data['html'] ) ) {
		$data['html'] = '<p>' . __( 'Shortcode could not be rendered!', 'thrive-leads' ) . '</p>';
	}

	//build config html
	$config_div = '<div class="thrive-shortcode-config" style="display: none !important;">__CONFIG_leads_shortcode__' . json_encode( array( 'id' => $params['shortcode_id'] ) ) . '__CONFIG_leads_shortcode__</div>';

	//build font links html
	$font_links = '';
	foreach ( $data['fonts'] as $font_url ) {
		$font_links .= '<link href="' . $font_url . '" />';
	}

	//build css links html
	$css_links = '';
	foreach ( $data['css'] as $css_url ) {
		$css_links .= '<link href="' . $css_url . '" type="text/css" rel="stylesheet"/>';
	}

	//replace css class
	$data['html'] = str_replace( 'tve_editor_main_content', '', $data['html'] );

	if ( wp_doing_ajax() ) {
		$data['html'] = tve_leads_prepare_ajax_html( $data['html'] );
	}

	//prepare html by setting all the html in right order
	$data['html'] = $config_div . '<div class="thrive-shortcode-html">' . $data['html'] . $font_links . $css_links . '</div>';

	//skip wrapper html if not needed
	if ( empty( $params['nowrap'] ) ) {
		$data['html'] = '<div class="thrv_wrapper thrive_leads_shortcode">' . $data['html'] . '</div>';
	}

	return $data;
}

/**
 * Disable Revision Manager for Leads Pages
 *
 * @param bool $status
 *
 * @return bool
 */
function tve_leads_disable_revision_manager( $status = true ) {
	$post_type = get_post_type();

	if ( tve_leads_post_type_editable( $post_type ) ) {
		return ! $status;
	}

	return $status;
}

/**
 * Disable Page Events for Leads Pages
 *
 * @param bool $allow
 *
 * @return bool
 */
function tve_leads_disable_page_events( $allow = true ) {
	$post_type = get_post_type();

	if ( tve_leads_post_type_editable( $post_type ) ) {
		return false;
	}

	return $allow;
}

/**
 * Modifies Thrive Architect close URL when in Leads Editor
 *
 * @param string $close_url
 *
 * @return string
 */
function tve_leads_tcb_close_url( $close_url = '' ) {
	$post_type = get_post_type();

	if ( tve_leads_post_type_editable( $post_type ) ) {
		$close_url = 'javascript:window.close();';
	}

	return $close_url;
}

/**
 * Remove Elements Instances
 *
 * @param array $elements
 *
 * @return array
 */
function tve_leads_remove_element_instances( $elements = array() ) {
	$post_type = get_post_type();

	if ( tve_leads_post_type_editable( $post_type ) ) {
		/**
		 * Remove Thrive Ultimatum Countdown
		 */
		if ( ! empty( $elements['ultimatum_countdown'] ) ) {
			unset( $elements['ultimatum_countdown'] );
		}
	}

	return $elements;
}

function tve_leads_render_shortcode( $data ) {

	$html = '<p>' . __( 'Shortcode could not be rendered!', 'thrive-leads' ) . '</p>';

	return '<div class="thrv_wrapper thrive_leads_shortcode">' . $html . '</div>';
}

/**
 * Outputs the state HTML for the form being edited
 */
add_action( 'tcb_hook_editor_footer', 'tve_leads_output_editor_states' );

/**
 * get all the available fields that are changeable (editable) using the editor API
 *
 * @return array()
 */
function tve_leads_get_editor_fields() {
	return array(
		TVE_LEADS_FIELD_CUSTOM_FONTS,
		TVE_LEADS_FIELD_GLOBALS,
		TVE_LEADS_FIELD_HAS_MASONRY,
		TVE_LEADS_FIELD_HAS_TYPEFOCUS,
		TVE_LEADS_FIELD_ICON_PACK,
		TVE_LEADS_FIELD_INLINE_CSS,
		TVE_LEADS_FIELD_SAVED_CONTENT,
		TVE_LEADS_FIELD_USER_CSS,
		TVE_LEADS_FIELD_TEMPLATE,
		TVE_LEADS_FIELD_FORM_HEIGHT,
		TVE_LEADS_FIELD_STATE_INDEX,
		TVE_LEADS_FIELD_STATE_VISIBILITY,
		TVE_LEADS_FIELD_SELECTED_TEMPLATE,//template's key needed to set the selected template in list/lightbox
	);
}

/**
 * append the '_key' parameter to the required fields we need from $_GET on the editor page
 *
 * @param array $fields
 *
 * @return array
 */
function tve_leads_required_get_fields() {
	return array( '_key' );
}

/**
 * append also the variation_key as a parameter for the preview link - the link that is built for the "Preview" button in the editor
 * this should always lead to the main (Default) state of the form
 *
 * @param $current_args
 * @param $post_id
 */
function tve_leads_append_preview_link_args( $current_args, $post_id ) {
	if ( tve_leads_post_type_editable( $post_id ) && ! empty( $_REQUEST['_key'] ) ) {
		/* $_GET['key'] should always be defined - this is always called on the editor page */
		/* extending this to $_REQUEST; by Dan */
		$current_args ['_key'] = $_REQUEST['_key'];
	}

	return $current_args;
}

/**
 * append any required parameters to the global JS configuration array on the editor page
 *
 * @param array $javascript_params currently defined parameters
 *
 * @return array
 */
function tve_leads_editor_javascript_params( $javascript_params ) {
	if ( ! tve_leads_post_type_editable( get_post_type() ) || empty( $_GET['_key'] ) ) {
		return $javascript_params;
	}

	$post   = get_post();
	$parent = get_post( $post->post_parent );

	$post_id       = empty( $post ) ? 0 : $post->ID;
	$variation_key = (int) $_GET['_key'];
	$group_id      = empty( $parent ) ? 0 : $parent->ID;

	/**
	 * this should already be set from tve_leads_get_form_layout
	 *
	 * @see tve_leads_get_form_layout
	 */
	global $variation;

	if ( empty( $variation ) ) {
		$last_edited_state_key = get_post_meta( $post_id, 'tve_last_edit_state_' . $variation_key, true );
		if ( ! empty( $last_edited_state_key ) ) {
			$variation = tve_leads_get_form_variation( null, $last_edited_state_key );
		}

		/**
		 * fallback to the current one, if somehow the last edited is not set or is not existing anymore
		 */
		if ( empty( $variation ) ) {
			$variation = tve_leads_get_form_variation( $post_id, $variation_key );
		}
	}

	/* clear out any data that's not necessary on the editor and add form variation custom data */
	$javascript_params['landing_page']          = '';
	$javascript_params['landing_page_config']   = array();
	$javascript_params['landing_pages']         = array();
	$javascript_params['page_events']           = array();
	$javascript_params['landing_page_lightbox'] = array();
	$javascript_params['custom_post_data']      = array(
		'_key' => $variation['key'],
	);
	$javascript_params['save_post_action']      = TVE_LEADS_ACTION_SAVE_POST;
	$javascript_params['tve_globals']           = isset( $variation[ TVE_LEADS_FIELD_GLOBALS ] ) ? $variation[ TVE_LEADS_FIELD_GLOBALS ] : array( 'e' => 1 );
	$javascript_params['show_more_tag']         = false;


	$_temp_form_types    = array(
		'in_content'        => 'in-content',
		'lightbox'          => 'lightbox',
		'post_footer'       => 'post-footer',
		'ribbon'            => 'ribbon',
		'screen_filler'     => 'screen-filler-lightbox',
		'greedy_ribbon'     => 'scroll-mat',
		'slide_in'          => 'slide-in',
		'widget'            => 'widget',
		'php_insert'        => 'php-insert',
		'shortcode'         => 'tve_lead_shortcode',
		'two_step_lightbox' => 'tve_lead_2s_lightbox',
	);
	$_original_form_type = get_post_meta( $post_id, 'tve_form_type', true );

	/**
	 * build up the page data configuration array
	 */
	$page_data = array(
		'_key'             => $variation['key'],
		'post_id'          => $post_id,
		'group_id'         => $group_id,
		'form_type'        => $_temp_form_types[ $_original_form_type ],
		'ajaxurl'          => admin_url( 'admin-ajax.php' ),
		'tpl_action'       => TVE_LEADS_ACTION_TEMPLATE,
		'state_action'     => TVE_LEADS_ACTION_STATE,
		'has_content'      => ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ),
		'security'         => wp_create_nonce( 'tve-leads-verify-track-sender-672' ),
		'current_css'      => ! empty( $css_handle ) ? $css_handle : '',
		'states'           => tve_leads_get_form_related_states( $variation ),
		'L'                => array(
			'alert_choose_tpl'     => __( 'Please choose a template', 'thrive-leads' ),
			'confirm_tpl_reset'    => __( 'Are you sure you want to reset this Form to the default template? This action cannot be undone', 'thrive-leads' ),
			'tpl_name_required'    => __( 'Please enter a template name, it will be easier to reload it after.', 'thrive-leads' ),
			'tpl_confirm_delete'   => __( 'Are you sure you want to delete this saved template? This action cannot be undone', 'thrive-leads' ),
			'only_one_subscribed'  => __( 'You can only have one Already Subscribed state for a form', 'thrive-leads' ),
			'confirm_state_delete' => __( 'Are you sure you want to delete this state?', 'thrive-leads' ),
			'confirm_multi_step'   => __( 'If you choose a multi-step template, ALL CURRENT STATES WILL BE DELETED AND RE-CREATED. Do you want to continue?', 'thrive-leads' ),
			'state_deleted'        => __( 'State deleted', 'thrive-leads' ),
			'switch_state'         => __( 'Switch State', 'thrive-leads' ),
		),
		'is_default_state' => empty( $variation['parent_id'] ),
	);
	wp_localize_script( 'tve-leads-editor', 'tve_leads_page_data', $page_data );

	return $javascript_params;
}

/**
 * get the corresponding editing / displaying layout template based on the form type
 *
 * @param array $current_templates
 * @param       $post_id
 * @param       $post_type
 *
 * @return mixed
 */
function tve_leads_get_form_layout( $current_templates, $post_id, $post_type ) {
	global $variation;
	$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';
	if ( ! tve_leads_post_type_editable( $post_type ) ) {
		return $current_templates;
	}

	$available = tve_leads_get_default_form_types( true );

	if ( is_editor_page() ) {
		$last_edited_state_key = get_post_meta( $post_id, 'tve_last_edit_state_' . $_GET['_key'], true );
		if ( ! empty( $last_edited_state_key ) ) {
			$variation = tve_leads_get_form_variation( null, $last_edited_state_key );
		}
	}

	/**
	 * fallback to the current one, if somehow the last edited is not set or is not existing anymore
	 */
	if ( empty( $variation ) ) {
		$variation = tve_leads_get_form_variation( $post_id, $_GET['_key'] );
	}

	/**
	 * something went wrong
	 */
	if ( empty( $variation ) ) {
		return $current_templates;
	}

	$form_type = tve_leads_get_form_type_from_variation( $variation );
	if ( isset( $variation['tpl'] ) && strpos( $variation['tpl'], 'screen_filler' ) !== false ) {
		$form_type = 'screen_filler';
	}

	if ( ! array_key_exists( $form_type, $available ) ) {
		return $current_templates;
	}

	$file_path = plugin_dir_path( dirname( __FILE__ ) ) . 'editor-layouts/' . $form_type . '.php';
	if ( ! is_file( $file_path ) ) {
		return $current_templates;
	}

	$current_templates['tve_form_type'] = $file_path;

	tve_leads_enqueue_style( 'tve-leads-type-' . $form_type, TVE_LEADS_URL . 'editor-layouts/css/editor.css' );

	if ( ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
		$config = tve_leads_get_editor_template_config( $variation[ TVE_LEADS_FIELD_TEMPLATE ] );

		/* custom fonts for the form */
		if ( ! empty( $config['fonts'] ) ) {
			foreach ( $config['fonts'] as $font ) {
				wp_enqueue_style( 'tve-leads-font-' . md5( $font ), $font );
			}
		}

		/* include also the CSS for each form type design */
		if ( ! empty( $config['css'] ) ) {
			$css_handle = 'tve-leads-' . str_replace( '.css', '', $config['css'] );
			/** if its coming from cloud */
			if ( isset( $config['API_VERSION'] ) ) {
				$css_url = $config['base_url'] . '/css/' . $config['css'];
			} else {
				$css_url = TVE_LEADS_URL . 'editor-templates/_form_css/' . $config['css'];
			}
			tve_leads_enqueue_style( $css_handle, $css_url );
		}
	}

	if ( is_editor_page() ) {

		tve_leads_enqueue_script( 'tve-leads-editor', TVE_LEADS_URL . 'js/editor.min.js', array( 'tve_editor' ), false, true );

	} else {
		/* include the custom fonts, if any -> this is the preview page */
		tve_leads_enqueue_custom_fonts( $variation );
	}

	$globals = ! empty( $variation[ TVE_LEADS_FIELD_GLOBALS ] ) ? $variation[ TVE_LEADS_FIELD_GLOBALS ] : array();
	if ( ! empty( $globals['js_sdk'] ) ) {
		foreach ( $globals['js_sdk'] as $handle ) {
			$link                          = tve_social_get_sdk_link( $handle );
			$js[ 'tve_js_sdk_' . $handle ] = $link;
			wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
		}
	}

	/* "emulate" the default Thrive Lightbox using the same CSS for that */
	if ( is_editor_page() && ( $form_type == 'lightbox' || $form_type == 'screen_filler' ) ) {
		tve_enqueue_style( 'tve_lightbox_post', tve_editor_css() . '/editor_lightbox.css' );
	}

	/* need to output froala dropdown container somewhere in the footer */
	if ( tve_leads_is_editor_page() ) {
		add_action( 'get_footer', 'tve_leads_output_froala_container' );
	}

	return $current_templates;
}

/**
 * get the saved content to use in editing mode of the form variation
 *
 * @param       $current_content
 * @param       $post_id
 *
 * @param mixed $variation can be either the loaded variation or the variation_key
 *
 * @return string
 */
function tve_leads_get_editor_content( $current_content, $post_id, $variation ) {
	if ( ! tve_leads_post_type_editable( $post_id ) ) {
		return $current_content;
	}

	if ( is_numeric( $variation ) ) {
		$variation = tve_leads_get_form_variation( $post_id, $variation );
	}

	if ( is_null( $variation ) ) {
		return '';
	}

	if ( empty( $variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] ) || empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
		return '';
	}

	if ( ! tve_leads_variation_has_content( $variation ) ) {
		$variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] = tve_leads_get_editor_template_content( $variation );
		tve_leads_save_form_variation( $variation );
	}

	return $variation[ TVE_LEADS_FIELD_SAVED_CONTENT ];
}

/**
 * check if a variation has contents in the appropriate field
 * development mode: if a .ui-develop file is present in the root of the plugin, then always re-generate the content
 *
 * @param array $variation
 *
 * @return bool
 */
function tve_leads_variation_has_content( $variation ) {
	if ( empty( $variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] ) ) {
		return false;
	}

	if ( file_exists( dirname( dirname( __FILE__ ) ) . '/.ui-develop' ) ) {
		return false; // to always re-generate
	}

	return true;
}

/**
 * output custom CSS for a form variation
 *
 * UPDATE 19.11.2015 - the Custom CSS is only saved once in the default state (in the "parent" variation)
 *
 * @param mixed $variation can be either a numeric value - for variation_key or an already loaded variation array
 * @param bool  $return    whether to output the CSS or return it
 *
 * @return string the CSS, if $return was true
 */
function tve_leads_output_custom_css( $variation, $return = false ) {
	if ( is_numeric( $variation ) ) {
		$variation = tve_leads_get_form_variation( null, $variation );
	}
	if ( empty( $variation ) || ! is_array( $variation ) ) {
		return '';
	}

	$css = '';

	if ( ! empty( $variation[ TVE_LEADS_FIELD_INLINE_CSS ] ) ) { /* inline style rules = custom colors */
		$css = sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', $variation[ TVE_LEADS_FIELD_INLINE_CSS ] );
	}

	if ( class_exists( 'TCB\Lightspeed\Fonts', false ) ) {
		$css = TCB\Lightspeed\Fonts::parse_google_fonts( $css );
	}

	/* user-defined Custom CSS rules for the form */
	$custom_css = '';
	/**
	 * first, check for a parent variation
	 */
	if ( ! empty( $variation['parent_id'] ) ) {
		$parent_state = tve_leads_get_form_variation( null, $variation['parent_id'] );
		if ( ! empty( $parent_state ) && ! empty( $parent_state[ TVE_LEADS_FIELD_USER_CSS ] ) ) {
			$custom_css = tve_leads_process_custom_css( $parent_state[ TVE_LEADS_FIELD_USER_CSS ] ) . $custom_css;
		}
	}

	/**
	 * fallback / backwards-compatibility: get the CustomCSS from the state itself
	 */
	if ( ! empty( $variation[ TVE_LEADS_FIELD_USER_CSS ] ) ) {
		$custom_css = tve_leads_process_custom_css( $variation[ TVE_LEADS_FIELD_USER_CSS ] ) . $custom_css;
	}

	if ( ! empty( $custom_css ) ) {
		$css .= sprintf(
			'<style type="text/css"%s class="tve_user_custom_style">%s</style>',
			$return ? '' : ' id="tve_head_custom_css"', // if we return the CSS, do not append the id to the stylesheet
			$custom_css
		);
	}

	if ( ! is_editor_page() && function_exists( 'tve_get_shared_styles' ) ) {
		$css .= tve_get_shared_styles( $variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] );
	}

	$css = apply_filters( 'tcb_custom_css', $css );

	if ( $return === true ) {
		return $css;
	}

	echo $css;
}

/**
 * this is the main controller for editor page and preview page
 * can also be used to output the rendered content of a form by passing $variation (the form variation)
 * it will also return any custom CSS setup for this
 *
 * @param array $variation if present, the system will assume that it needs to render the content on another page (actual display logic for the forms)
 * @param array $filters   allows control of the output
 *
 * @return string
 *
 */
function tve_editor_custom_content( $variation = null, $filters = array() ) {
	/* this will hold the html for the tinymce editor instantiation, only if we're on the editor page */
	$tinymce_editor = $page_loader = '';

	$defaults = array(
		'state_div_extra' => '',
	);
	$filters  = array_merge( $defaults, $filters );

	$is_editor_page = is_editor_page();

	/* we cannot use WP is_singular() because the form can be displayed on various pages */
	/**
	 * $is_singular = true => means we are currently on the editor page (editing a form) or in preview mode (viewing a form)
	 */
	$is_singular = empty( $variation );
	if ( ! empty( $variation ) ) {
		$is_editor_page = false;
		$post_id        = $variation['post_parent'];
	} else {
		/** this should already be setup from when we selected the template */
		global $variation;

		if ( empty( $variation ) ) {
			$post_id       = get_the_ID();
			$variation_key = $_GET['_key']; // this must be present, this assumes we are in editing mode or preview mode
			/**
			 * load the form variation here, and pass this to the various hooks
			 */
			$variation = tve_leads_get_form_variation( $post_id, $variation_key );
		} else {
			$post_id = $variation['post_parent'];
		}
	}

	$form_type = tve_leads_get_form_type_from_variation( $variation );

	/**
	 * this means we are getting the content to output it on a targeted page => include also the custom CSS rules
	 */
	$custom_css = $is_singular ? '' : tve_leads_output_custom_css( $variation, true );

	/**
	 * style family class should always be Flat - we do not need that mess inside Thrive Leads
	 */
	$style_family_class = tve_get_style_family_class( $post_id );

	tve_leads_lightspeed_enqueue_variation_assets( $post_id, $variation['key'] );

	$style_family_id = $is_singular ? ' id="' . $style_family_class . '" ' : ' ';

	$wrap = array(
		'start' => '<div' . $style_family_id . 'class="' . $style_family_class . '">',
		'end'   => '</div>',
	);

	$wrap['start'] .= '<div id="tve_editor" class="tve_shortcode_editor">';
	$wrap['end']   .= '</div>';

	$_key = '';
	if ( ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
		list( $_tpl, $_key ) = explode( '|', $variation[ TVE_LEADS_FIELD_TEMPLATE ] );
	}
	if ( $form_type == 'lightbox' && ! tve_leads_is_v2_template( $_key ) ) {
		$wrap['start'] .= '<div class="tve_p_lb_control tve_editor_main_content tve_content_save">';
		$wrap['end']   .= '</div>';
	}

	/**
	 * filter to allow overriding the default field where the content is actually saved in the post
	 *
	 * In case of Thrive Leads, this will be one element in an array of post variations inside a post meta field
	 */
	$tve_saved_content = apply_filters( 'tcb_editor_get_editor_content', '', $post_id, $variation );

	if ( $is_editor_page ) {

		$page_loader = '';

	} else {

		$tve_saved_content = tve_restore_script_tags( $tve_saved_content );

		/* prepare Events configuration */
		$GLOBALS['tl_event_parse_variation']    = isset( $GLOBALS['tl_event_parse_variation'] ) ? $GLOBALS['tl_event_parse_variation'] : array();
		$GLOBALS['tl_event_parse_variation'] [] = $variation;
		tve_parse_events( $tve_saved_content );
		array_pop( $GLOBALS['tl_event_parse_variation'] );

		$parent_form_type = tve_leads_get_form_type_from_variation( $variation, true, false );

		$wrap['start'] = sprintf(
			'<div class="tve-leads-conversion-object" data-tl-type="%s">' . $wrap['start'],
			$parent_form_type == 'shortcode'
				? ( 'shortcode_' . $variation['post_parent'] )
				: ( $parent_form_type == 'two_step_lightbox' ? 'two_step_' . $variation['post_parent'] : $parent_form_type )
		);
		$wrap['end']   .= '</div>';
	}

	/**
	 * custom Thrive shortcodes
	 */
	$tve_saved_content = tve_thrive_shortcodes( $tve_saved_content, $is_editor_page );

	/* render the content added through WP Editor (element: "WordPress Content") */
	$tve_saved_content = tve_do_wp_shortcodes( $tve_saved_content, $is_editor_page );

	if ( ! $is_editor_page ) {
		$tve_saved_content = shortcode_unautop( $tve_saved_content );
		$tve_saved_content = do_shortcode( $tve_saved_content );
	}

	if ( ! empty( $variation[ TVE_LEADS_FIELD_ICON_PACK ] ) ) {
		tve_enqueue_icon_pack();
	}

	if ( ! empty( $variation[ TVE_LEADS_FIELD_HAS_MASONRY ] ) ) {
		wp_enqueue_script( 'jquery-masonry' );
	}

	$tve_saved_content = preg_replace_callback( '/__CONFIG_lead_generation__(.+?)__CONFIG_lead_generation__/s', 'tcb_lg_err_inputs', $tve_saved_content );

	if ( ! $is_editor_page ) {
		$tve_saved_content = apply_filters( 'tcb_clean_frontend_content', $tve_saved_content );
	}

	/**
	 * append any needed custom CSS
	 */
	return $custom_css . $wrap['start'] . $tve_saved_content . $wrap['end'] . $tinymce_editor . $page_loader;
}

/**
 * called via AJAX from TCB
 * receives editor content and various fields needed throughout the editor
 *
 * the following data is received:
 *  post_id
 *  _key = variation_key
 *  tve_content_more - if there is a "more" link inserted in the page (this does not apply here)
 *  tve_save_content
 *  inline_rules - inline CSS rules (custom colors)
 *  tve_custom_css - user-defined custom CSS
 *  custom_colours - user-saved custom colors for the colorpicker
 *  page_events - current defined page event (this does not apply here)
 *  tve_globals - global data used for the post
 *  custom_font_classes - (if any) all font classes that the user has applied
 *  has_icons - whether or not the current edited piece of content has custom icons used
 *  tve_has_masonry - whether or not the current edited content has a masonry layout set for the post_grid (if any)
 *
 * @return array
 */
function tve_leads_save_editor_content() {
	$post_id       = (int) $_POST['post_id'];
	$variation_key = (int) $_POST['_key'];

	$invalid_request = array(
		'success' => false,
		'message' => __( 'Invalid request', 'thrive-leads' ),
	);

	if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) || empty( $variation_key ) ) {
		return $invalid_request;
	}
	if ( ob_get_contents() ) {
		ob_clean();
	}

	$variation = tve_leads_get_form_variation( $post_id, $variation_key );
	if ( empty( $variation ) ) {
		return $invalid_request;
	}

	tve_leads_save_optimized_assets( $post_id, $variation_key );

	/* using wp_unslash will break api connections based on their settings */
	$variation[ TVE_LEADS_FIELD_SAVED_CONTENT ] = $_POST['tve_content'];
	$variation[ TVE_LEADS_FIELD_INLINE_CSS ]    = trim( $_POST['inline_rules'] );
	$variation[ TVE_LEADS_FIELD_USER_CSS ]      = $_POST['tve_custom_css'];
	$variation[ TVE_LEADS_FIELD_GLOBALS ]       = ! empty( $_POST['tve_globals'] ) ? $_POST['tve_globals'] : array( 'e' => 1 );
	$variation[ TVE_LEADS_FIELD_CUSTOM_FONTS ]  = tve_leads_get_custom_font_links( empty( $_POST['custom_font_classes'] ) ? array() : $_POST['custom_font_classes'] );
	$variation[ TVE_LEADS_FIELD_ICON_PACK ]     = empty( $_POST['has_icons'] ) ? 0 : 1;
	$variation[ TVE_LEADS_FIELD_HAS_MASONRY ]   = empty( $_POST['tve_has_masonry'] ) ? 0 : 1;
	$variation[ TVE_LEADS_FIELD_HAS_TYPEFOCUS ] = empty( $_POST['tve_has_typefocus'] ) ? 0 : 1;
	$variation[ TVE_LEADS_FIELD_FORM_HEIGHT ]   = empty( $_POST[ TVE_LEADS_FIELD_FORM_HEIGHT ] ) ? [] : $_POST[ TVE_LEADS_FIELD_FORM_HEIGHT ];

	/**
	 * UPDATE 19.11.2015 - the custom CSS will be saved in the parent form
	 */
	if ( ! empty( $variation['parent_id'] ) && ( $parent_state = tve_leads_get_form_variation( null, $variation['parent_id'] ) ) ) {
		$parent_state[ TVE_LEADS_FIELD_USER_CSS ] = $_POST['tve_custom_css'];
		$variation[ TVE_LEADS_FIELD_USER_CSS ]    = '';

		tve_leads_save_form_variation( $parent_state );
	}

	/**
	 * always re-generate CSS IDs for elements inside the variation - conflicts can occur on pages where multiple Lead Forms are shown
	 */
	Thrive_Leads_State_Manager::instance( $variation )->regenerate_css_ids( $variation );

	tve_leads_save_form_variation( $variation );

	return array(
		'success' => true,
	);
}

/**
 * get the default variation content from a pre-defined template
 *
 * @param array  $variation    the form design object
 * @param string $template_key formatted like {main_key}|{template_name}
 *
 * @return string the content
 */
function tve_leads_get_editor_template_content( &$variation, $template_key = null ) {
	if ( $template_key === null && ! empty( $variation ) && ! empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
		$template_key = $variation[ TVE_LEADS_FIELD_TEMPLATE ];
	}

	list( $type, $template ) = explode( '|', $template_key );
	$type = Thrive_Leads_Template_Manager::tpl_type_map( $type );

	$base = plugin_dir_path( __DIR__ ) . 'editor-templates/';

	/** @var array $templates all templates for a form type */
	$templates = Thrive_Leads_Template_Manager::for_variation( $variation );

	/** if the template does not exists */
	if ( strpos( $template, 'tcb2_' ) !== 0 && ! isset( $templates[ $template ] ) ) {
		return '';
	}

	/**
	 * also read in any other configuration values that might be required for this form
	 */
	$tpl_config_data = tve_leads_get_editor_template_config( $template_key );

	/** if the template is from cloud */
	if ( isset( $templates[ $template ]['API_VERSION'], $tpl_config_data['base_url'] ) ) {
		$content = file_get_contents( $templates[ $template ]['base_dir'] . '/' . $template . '.tpl' );
		/** replace the placeholder url */
		$content = str_replace( '{tve_leads_base_url}', $tpl_config_data['base_url'], $content );
	}

	/** @var string $template_path disk template file */
	$template_path = $base . $type . '/' . $template . '.php';

	/** if no cloud and no file on disk return empty content */
	if ( ! isset( $content ) && ! is_file( $template_path ) ) {
		return '';
	}

	/** if no cloud content then read it from disk */
	if ( ! isset( $content ) ) {
		ob_start();
		include $template_path;
		$content = ob_get_clean();
	}

	$meta = array();
	if ( isset( $tpl_config_data['meta'] ) ) {
		$meta = $tpl_config_data['meta'];
	}

	/**
	 * we need to make sure we don't have any left-over data from the previous template
	 */
	$variation[ TVE_LEADS_FIELD_INLINE_CSS ]    = tve_leads_get_default_template_css( $template_key, $tpl_config_data );
	$variation[ TVE_LEADS_FIELD_USER_CSS ]      = isset( $meta[ TVE_LEADS_FIELD_USER_CSS ] ) ? $meta[ TVE_LEADS_FIELD_USER_CSS ] : '';
	$variation[ TVE_LEADS_FIELD_CUSTOM_FONTS ]  = array();
	$variation[ TVE_LEADS_FIELD_ICON_PACK ]     = '';
	$variation[ TVE_LEADS_FIELD_HAS_MASONRY ]   = '';
	$variation[ TVE_LEADS_FIELD_HAS_TYPEFOCUS ] = isset( $meta[ TVE_LEADS_FIELD_HAS_TYPEFOCUS ] ) ? $meta[ TVE_LEADS_FIELD_HAS_TYPEFOCUS ] : '';

	if ( ! empty( $tpl_config_data[ TVE_LEADS_FIELD_GLOBALS ] ) ) {
		$variation[ TVE_LEADS_FIELD_GLOBALS ] = $tpl_config_data[ TVE_LEADS_FIELD_GLOBALS ];
	} elseif ( ! empty( $meta[ TVE_LEADS_FIELD_GLOBALS ] ) ) {
		$variation[ TVE_LEADS_FIELD_GLOBALS ] = $meta[ TVE_LEADS_FIELD_GLOBALS ];
	} else {
		$variation[ TVE_LEADS_FIELD_GLOBALS ] = array( 'e' => 1 );
	}

	return $content;
}

/**
 * handles template-related actions:
 * choose new template
 * reset current form design to the default content
 * perhaps at some point: save / load template versions
 */
function tve_leads_template_action() {
	add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
	add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );

	$result = array(
		'success' => false,
		'message' => __( 'Something went wrong, try again later', 'thrive-leads' ),
	);

	if ( empty( $_POST['post_id'] ) || ! current_user_can( 'edit_post', $_POST['post_id'] ) || empty( $_POST['_key'] ) || empty( $_POST['custom'] ) ) {
		return $result;
	}

	/**
	 * if in development then delete cache
	 */
	if ( defined( 'TL_CLOUD_DEBUG' ) && TL_CLOUD_DEBUG && ! empty( $_REQUEST['tpl'] ) ) {
		delete_transient( $_REQUEST['tpl'] );
	}

	/**
	 * if template is from cloud then download and set it in cache
	 */
	if ( ! empty( $_REQUEST['cloud'] ) && false === ( $downloaded = get_transient( $_REQUEST['tpl'] ) ) ) {

		$template = explode( '|', $_REQUEST['tpl'] );
		$template = end( $template );

		$tpl = array(
			'form_type'  => $_REQUEST['form_type'],
			'multi_step' => $_REQUEST['multi_step'],
		);

		try {
			/**
			 * Download the template
			 */
			Thrive_Leads_Cloud_Templates_APi::getInstance()->init( $tpl )->download( $template );

			/**
			 * Mark it as cached
			 */
			set_transient( $_REQUEST['tpl'], true, 8 * HOUR_IN_SECONDS );

		} catch ( Exception $e ) {
			$result['message'] = $e->getMessage();

			return $result;
		}
	}

	$form_type = tve_leads_get_form_type( $_POST['post_id'] );
	$variation = tve_leads_get_form_variation( $_POST['post_id'], $_POST['_key'] );

	if ( empty( $variation ) || empty( $form_type ) ) {
		return $result;
	}

	require_once dirname( dirname( __FILE__ ) ) . '/inc/classes/Thrive_Leads_Template_Manager.php';
	require_once dirname( dirname( __FILE__ ) ) . '/inc/classes/Thrive_Leads_State_Manager.php';

	Thrive_Leads_Template_Manager::instance( $variation )->api( $_POST['route'] );
}

/**
 * enqueue and return custom fonts for a specific variation
 *
 * @param array $variation the form variation
 *
 * @return array
 */
function tve_leads_enqueue_custom_fonts( $variation ) {
	if ( empty( $variation[ TVE_LEADS_FIELD_CUSTOM_FONTS ] ) ) {
		return array();
	}

	return tve_enqueue_fonts( $variation[ TVE_LEADS_FIELD_CUSTOM_FONTS ] );
}

/**
 * transform an array of font classes into links to the actual google font
 *
 * @param array $custom_font_classes the classes used for custom fonts
 *
 * @return array
 */
function tve_leads_get_custom_font_links( $custom_font_classes = array() ) {
	$all_fonts = tve_get_all_custom_fonts();

	$post_fonts = array();
	foreach ( array_unique( $custom_font_classes ) as $cls ) {
		foreach ( $all_fonts as $font ) {
			if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font->font_name ) ) {
				$post_fonts[] = Tve_Dash_Font_Import_Manager::getCssFile();
			} else if ( $font->font_class == $cls && ! tve_is_safe_font( $font ) ) {
				$post_fonts[] = tve_custom_font_get_link( $font );
				break;
			}
		}
	}

	return array_unique( $post_fonts );
}

/**
 * Checks if a form is being edited and, if yes include the Thrive Themes Page Section element in the control panel menu
 *
 * @param bool $show_menu
 *
 * @return bool
 */
function tve_leads_show_page_section_menu( $show_menu ) {
	if ( empty( $_GET['_key'] ) ) {
		return $show_menu;
	}
	$variation_key = $_GET['_key']; // this should always be present
	$variation     = tve_leads_get_form_variation( null, $variation_key );
	if ( empty( $variation ) ) {
		return $show_menu;
	}

	return true;
}

/**
 * transform the tve_globals meta field into css / html properties and rules
 *
 * @param array $variation
 *
 * @return array
 */
function tve_leads_lightbox_globals( $variation ) {
	$config = ! empty( $variation[ TVE_LEADS_FIELD_GLOBALS ] ) ? $variation[ TVE_LEADS_FIELD_GLOBALS ] : array();

	$html = array(
		'overlay' => array(
			'css'          => empty( $config['l_oo'] ) ? '' : ( 'opacity:' . $config['l_oo'] ),
			'custom_color' => empty( $config['l_ob'] ) ? '' : ( ' data-tve-custom-colour="' . $config['l_ob'] . '"' ),
		),
		'content' => array(
			'custom_color' => empty( $config['l_cb'] ) ? '' : ( ' data-tve-custom-colour="' . $config['l_cb'] . '"' ),
			'class'        => empty( $config['l_ccls'] ) ? '' : ' ' . $config['l_ccls'],
			'css'          => '',
		),
		'inner'   => array(
			'css' => '',
		),
		'close'   => array(
			'class' => '',
			'css'   => '',
		),
	);

	$html['content']['custom_color'] .= empty( $config['content_css'] ) ? '' : ( ' data-css="' . $config['content_css'] . '"' );

	if ( ! empty( $config['l_cimg'] ) ) { // background image
		$html['content']['css'] .= "background-image:url('{$config['l_cimg']}');background-repeat:no-repeat;background-size:cover;";
	} elseif ( ! empty( $config['l_cpat'] ) ) {
		$html['content']['css'] .= "background-image:url('{$config['l_cpat']}');background-repeat:repeat;";
	}

	if ( ! empty( $config['l_cbs'] ) ) { // content border style
		$html['content']['class'] .= ' ' . $config['l_cbs'];
		$html['close']['class']   .= ' ' . $config['l_cbs'];
	}

	if ( ! empty( $config['l_cbw'] ) ) { // content border width
		$html['content']['css'] .= "border-width:{$config['l_cbw']};";
		$html['close']['css']   .= "border-width:{$config['l_cbw']};";
	}

	if ( ! empty( $config['l_cmw'] ) ) { // content max width
		$html['content']['css'] .= "max-width:{$config['l_cmw']}";
	}

	// Close Custom Color settings
	$html['close']['custom_color'] = empty( $config['l_ccc'] ) ? '' : ' data-tve-custom-colour="' . $config['l_ccc'] . '"';

	/**
	 * @deprecated
	 */
	if ( ! empty( $config['l_cmh'] ) ) { // content max height
		if ( ! is_editor_page() ) {
			$_height = intval( $config['l_cmh'] );
			/* we need to substract 30px, the padding of the lightbox - when not in editing mode */
			$config['l_cmh'] = ( $_height - 30 ) . 'px';
		}
		$html['inner']['css'] .= "max-height:{$config['l_cmh']}";
	}

	return $html;
}

/**
 * check if a post type from Thrive Leads is editable with tcb. Currently supported: form types and shortcodes
 *
 * @param mixed $post_id_or_type if is_numeric -> consider it as ID
 *
 * @return bool
 */
function tve_leads_post_type_editable( $post_id_or_type ) {
	$post_type = is_numeric( $post_id_or_type ) ? get_post_type( $post_id_or_type ) : $post_id_or_type;

	return in_array( $post_type, array(
		TVE_LEADS_POST_FORM_TYPE,
		TVE_LEADS_POST_SHORTCODE_TYPE,
		TVE_LEADS_POST_TWO_STEP_LIGHTBOX,
	) );
}

/**
 * check if the current post / variation is not included in a test.
 * If there's a test running, the form should not be editable
 *
 * @param bool $is_editable
 *
 * @return bool
 */
function tve_leads_check_editable_post( $is_editable ) {
	if ( ! $is_editable || ! isset( $_GET['_key'] ) ) {
		return $is_editable;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return $is_editable;
	}
	$key = $_GET['_key'];

	/* we cannot edit a variation that's archived or deleted */
	$variation = tve_leads_get_form_variation( $post_id, $key );
	if ( $variation['post_status'] != TVE_LEADS_STATUS_PUBLISH ) {
		return false;
	}

	global $tvedb;

	$running_test = $tvedb->check_if_test_exists( $post_id, $key );

	return ! empty( $running_test ) ? false : true;

}

/**
 * called when there is no active license for TCB, but it is installed and enabled
 * the function returns true only for pieces of content that "belong" to Thrive Leads, so only the following:
 *
 *  TVE_LEADS_POST_FORM_TYPE
 *  TVE_LEADS_POST_SHORTCODE_TYPE
 *  tcb_lightbox (this is to be used in two-step optins)
 *
 * @param bool $value
 *
 * @return bool whether or not the current piece of content can be edited with TCB core functions
 */
function tve_leads_tcb_license_override( $value ) {
	/**
	 * if already valid, return it
	 */
	if ( $value ) {
		return true;
	}
	$post_type = get_post_type();

	return $post_type == 'tcb_lightbox' || tve_leads_post_type_editable( $post_type );
}

/**
 * TODO: add function documentation
 *
 * @param $existing_icons
 *
 * @return array
 */
function tve_leads_get_form_icons( $existing_icons ) {
	if ( empty( $_POST['_key'] ) ) {
		return $existing_icons;
	}
	$variation_key = $_POST['_key'];
	$variation     = tve_leads_get_form_variation( null, $variation_key );

	if ( empty( $config[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
		return $existing_icons;
	}

	$config = tve_leads_get_editor_template_config( $variation[ TVE_LEADS_FIELD_TEMPLATE ] );

	if ( empty( $config['icons'] ) ) {
		return $existing_icons;
	}

	return array_merge( $existing_icons, $config['icons'] );
}

/**
 * check if TL license if valid (only if the user is trying to edit a form)
 *
 * @param bool $valid
 *
 * @return bool
 */
function tve_leads_editor_check_license( $valid ) {
	if ( empty( $_GET['_key'] ) ) {
		return $valid;
	}
	if ( ! tve_leads_license_activated() ) {
		add_action( 'wp_print_footer_scripts', 'tve_leads_license_warning' );

		return false;
	}

	return true;
}


/**
 * Check if TCB version is valid
 *
 * @param bool $valid
 *
 * @return bool
 */
function tve_leads_editor_check_tcb_version( $valid ) {
	if ( empty( $_GET['_key'] ) ) {
		return $valid;
	}

	if ( ! $valid ) {
		return false;
	}

	if ( ! tve_leads_check_tcb_version() ) {
		add_action( 'wp_print_footer_scripts', 'tve_leads_tcb_version_warning' );

		return false;
	}

	return true;
}

/**
 * Append any necessary actions to the Animation & Action component in TCB
 *
 * @param array $action_tabs
 *
 * @return array
 */
function tve_leads_editor_actions( $action_tabs ) {
	$classes = tve_leads_load_action_classes( array() );
	$post_id = get_the_ID();
	if ( empty( $post_id ) && ! empty( $_POST['post_id'] ) ) {
		$post_id = (int) $_POST['post_id'];
	}
	global $current_variation;
	if ( empty( $current_variation ) && ! empty( $_POST['_key'] ) ) {
		/* case: ajax load for a different state */
		$post_id           = $_POST['post_id'];
		$current_variation = tve_leads_get_form_variation( null, $_POST['_key'] );
	}

	if ( empty( $current_variation ) ) {
		if ( $post_id && ! empty( $_REQUEST['_key'] ) ) {
			$last_edited_state_key = get_post_meta( $post_id, 'tve_last_edit_state_' . $_REQUEST['_key'], true );
			if ( ! empty( $last_edited_state_key ) ) {
				$current_variation = tve_leads_get_form_variation( null, $last_edited_state_key );
			}
		}
	}
	/**
	 * fallback to the current one, if somehow the last edited is not set or is not existing anymore
	 */
	if ( empty( $current_variation ) && ! empty( $_REQUEST['_key'] ) ) {
		$current_variation = tve_leads_get_form_variation( null, $_REQUEST['_key'] );
	}

	$action_tabs['popup']['actions']['thrive_leads_2_step'] = array(
		'class' => $classes['thrive_leads_2_step'],
		'order' => 20,
	);

	if ( tve_leads_post_type_editable( $post_id ) ) {
		$parent_form_type = get_post_meta( $current_variation['post_parent'], 'tve_form_type', true );
		$parent_form_type = Thrive_Leads_Template_Manager::tpl_type_map( $parent_form_type );

		$action_tabs['popup']['actions']['thrive_lightbox']['available']     = false;
		$action_tabs['popup']['actions']['thrive_leads_2_step']['available'] = false;
		if ( $parent_form_type == 'lightbox' || $current_variation['form_state'] == 'lightbox' ) {
			$action_tabs['popup']['visible'] = false;
		}
		if ( $parent_form_type !== 'lightbox' ) {
			/**
			 * Open Lightbox state (popup)
			 */
			$action_tabs['custom']['actions']['tl_state_lightbox'] = array(
				'class' => $classes['tl_state_lightbox'],
				'order' => 10,
			);
		}
		if ( $parent_form_type === 'lightbox' || $current_variation['form_state'] !== 'lightbox' ) {
			$action_tabs['custom']['actions']['tl_state_switch'] = array(
				'class' => $classes['tl_state_switch'],
				'order' => 15,
			);
		}

		$action_tabs['custom']['actions']['thrive_leads_form_close'] = array(
			'class' => $classes['thrive_leads_form_close'],
			'order' => 20,
		);
	}

	return $action_tabs;
}

/**
 * Loads Thrive Leads classes and appends them to the default tcb action classes
 *
 * @param array $actions
 *
 * @return array
 */
function tve_leads_load_action_classes( $actions ) {
	require_once dirname( __FILE__ ) . '/event-manager/actions/Thrive_Leads_State_Switch_Action.php';
	require_once dirname( __FILE__ ) . '/event-manager/actions/Thrive_Leads_State_Lightbox_Action.php';
	require_once dirname( __FILE__ ) . '/event-manager/actions/Thrive_Leads_State_Lightbox_Close_Action.php';
	require_once dirname( __FILE__ ) . '/event-manager/actions/Thrive_Leads_State_Screen_Filler_Close_Action.php';
	require_once dirname( __FILE__ ) . '/event-manager/actions/Thrive_Leads_Two_Step_Action.php';
	require_once dirname( __FILE__ ) . '/event-manager/actions/Thrive_Leads_Form_Close_Action.php';

	$actions['tl_state_switch']         = 'Thrive_Leads_State_Switch_Action';
	$actions['tl_state_lightbox']       = 'Thrive_Leads_State_Lightbox_Action';
	$actions['tl_state_lb_close']       = 'Thrive_Leads_State_Lightbox_Close_Action';
	$actions['tl_state_sf_close']       = 'Thrive_Leads_State_Screen_Filler_Close_Action';
	$actions['thrive_leads_2_step']     = 'Thrive_Leads_Two_Step_Action';
	$actions['thrive_leads_form_close'] = 'Thrive_Leads_Form_Close_Action';

	return $actions;
}

/**
 * handles state-related actions:
 * add new state, change current state etc
 *
 */
function tve_leads_form_state_action() {
	$post_id = (int) $_POST['post_id'];
	$key     = (int) $_POST['_key'];
	if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) || empty( $key ) || empty( $_POST['custom'] ) ) {
		exit();
	}

	add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
	add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );

	$variation = tve_leads_get_form_variation( $post_id, $key );
	$form_type = tve_leads_get_form_type( $post_id );
	if ( empty( $variation ) || empty( $form_type ) ) {
		exit();
	}

	require_once dirname( dirname( __FILE__ ) ) . '/inc/classes/Thrive_Leads_State_Manager.php';

	return Thrive_Leads_State_Manager::instance( $variation )->api( $_POST['custom_action'] );
}

/**
 *
 * check if the user is currently previewing a form and if that's the case we need to make sure we include the TCB default CSS / js
 *
 * @param bool $enqueue_resources
 *
 * @return bool
 */
function tve_leads_enqueue_resources_preview( $enqueue_resources ) {
	if ( $enqueue_resources ) {
		return true;
	}

	return tve_leads_is_preview_page();
}

/**
 * Add the asset delivery option
 */
function tve_leads_delivery_connection() {
	$connection     = get_option( 'tve_api_delivery_service', false );
	$email_body     = get_option( 'tve_leads_asset_mail_subject', false );
	$email_subject  = get_option( 'tve_leads_asset_mail_body', false );
	$connected_apis = Thrive_List_Manager::get_available_apis( true, [ 'include_types' => [ 'email' ] ] );
	$asset          = ! empty( $_POST['asset_option'] ) ? $_POST['asset_option'] : '0';
	$asset_group    = ! empty( $_POST['asset_group'] ) ? $_POST['asset_group'] : '';

	if ( ! empty( $connected_apis ) && $connection !== false && $email_body !== false && $email_subject !== false ) {
		$args        = array(
			'post_type'      => 'tve_lead_asset_group',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'ASC',
			'posts_per_page' => - 1,
		);
		$posts_array = get_posts( $args );

		if ( ! empty( $posts_array ) ) {
			$data = '<div class="tve_lead_asset_delivery"><div class="tve_lightbox_input_holder"><input';
			if ( $asset == 1 ) {
				$data .= ' checked';
			}
			$data .= ' class="tve_change" data-ctrl="function:auto_responder.asset_option_changed" id="tve-asset-connection" type="checkbox" value="' . $connection . '"><label for="tve-asset-connection">' . __( 'Enable Asset Delivery', 'thrive-leads' ) . '</label></div>';
			$data .= '<div class="tve-asset-select-holder"';
			if ( $asset == 0 ) {
				$data .= ' style="display: none" ';
			}
			$data .= '><label>' . __( 'Select the asset group to deliver:', 'thrive-leads' ) . '</label><div class="tve_lightbox_select_holder" style="display: inline-block;"><select id="tve-api-submit-option" class="tve_change " data-ctrl="function:auto_responder.asset_group">';

			foreach ( $posts_array as $post ) {
				if ( ! empty( $asset_group ) && $asset_group == $post->ID ) {
					$data .= '<option value="' . $post->ID . '" selected="selected">' . $post->post_title . '</option>';
				} else {
					$data .= '<option value="' . $post->ID . '">' . $post->post_title . '</option>';
				}
			}
			$data .= '</select></div></div></div>';
			echo $data;
		}
	}
}

/**
 * add custom form options
 */
function tve_leads_add_form_options() {
	if ( $_POST['connection_type'] == 'api' ) {
		$account = ! empty( $_POST['create_account'] ) ? $_POST['create_account'] : '0';

		$data = '<div class="tve_lead_create_account"><div class="tve_lightbox_input_holder"><input';

		if ( $account == 1 ) {
			$data .= ' checked';
		}
		$data .= ' class="tve_change" data-ctrl="function:auto_responder.create_account_change" id="tve-create-account" type="checkbox" value=""><label for="tve-create-account">' . __( "Redirect to Account Creation Page (by checking this, the form will pass the form data to the page where the account creation is made)", "thrive-leads" ) . '</label>';
		$data .= '</div>';
		$data .= '</div>';

		echo $data;
	}

}

/**
 * Tweak the main control panel configuration
 *
 * @param array $config
 *
 * @return array
 */
function tve_leads_main_cpanel_config( $config ) {
	$post_type = get_post_type();
	if ( ! tve_leads_post_type_editable( $post_type ) || empty( $_GET['_key'] ) ) {
		return $config;
	}

	$config['disabled_controls']                      = isset( $config['disabled_controls'] ) ? $config['disabled_controls'] : array();
	$config['disabled_controls']['leads_shortcodes']  = true;
	$config['disabled_controls']['tu_shortcodes']     = true;
	$config['disabled_controls']['page_events']       = 1;
	$config['disabled_controls']['text']['more_link'] = true;
	$config['disabled_controls']['event_manager']     = array();
	$config['thrive_leads']                           = array(
		'_key' => $_GET['_key'],
	);

	return $config;
}

/**
 * Includes the Thrive Leads Shortcode into the list of Elements
 *
 * @param array $element_instances
 *
 * @return array of elements
 */
function tve_leads_add_tcb_elements( $element_instances ) {
	/**
	 * When editing a TL form, no TL element should be in the sidebar
	 */
	if ( tve_leads_post_type_editable( get_post_type() ) ) {
		/* add all the form types as TCB elements */

		require_once plugin_dir_path( __FILE__ ) . 'elements/class-thrive-leads-base-element.php';
		require_once plugin_dir_path( __FILE__ ) . 'elements/class-thrive-leads-slide-in-element.php';
		require_once plugin_dir_path( __FILE__ ) . 'elements/class-thrive-leads-lightbox-element.php';

		$element  = new Thrive_Leads_Base_Element();
		$slide_in = new Thrive_Leads_Slide_In_Element();
		$lightbox = new Thrive_Leads_Lightbox_Element();

		$element_instances[ $element->tag() ]  = $element;
		$element_instances[ $slide_in->tag() ] = $slide_in;
		$element_instances[ $lightbox->tag() ] = $lightbox;

		return $element_instances;
	}

	/**
	 * 1. Shortcode element
	 */
	if ( apply_filters( 'thrive_leads_element_shortcode', true ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'elements/class-thrive-leads-shortcode-element.php';
		$instance                              = new Thrive_Leads_Shortcode_Element();
		$element_instances[ $instance->tag() ] = $instance;
	}

	return $element_instances;
}

/**
 * Include TCB menu elements
 *
 * @param $menu_path
 */
function tve_leads_add_tcb_menu_elements( $menu_path ) {
	$thrive_leads_shortcodes = tve_leads_get_shortcodes();
	require_once dirname( dirname( __FILE__ ) ) . '/editor/elements/menus.php';
}

/**
 * Add Thrive Box to custom link search results
 *
 * @param array $post_types
 *
 * @return array
 */
function tve_leads_search_thrivebox( $post_types ) {

	$post_types [ TVE_LEADS_POST_TWO_STEP_LIGHTBOX ] = array(
		'name'         => __( 'ThriveBox', 'thrive-leads' ),
		'event_action' => 'thrive_leads_2_step',
	);

	return $post_types;
}

/**
 * Enqueue the editor JS extensions for thrive leads
 * This is only called on TCB editor pages - and only for the main frame
 *
 * On Thrive Leads editing pages, an extra js is loaded: tl-editor
 *
 */
function tve_leads_enqueue_editor_extension() {
	$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

	tve_leads_enqueue_script( 'tve-leads-editor-extension', TVE_LEADS_URL . 'js/tcb-editor' . $js_suffix, array( 'tve-main' ), false, true );

	if ( tve_leads_post_type_editable( get_post_type() ) ) {
		tve_leads_enqueue_script( 'tve-leads-editor', TVE_LEADS_URL . 'js/tl-editor' . $js_suffix, array( 'tve-leads-editor-extension' ), false, true );
		tve_leads_enqueue_style( 'tve-leads-main-editor', TVE_LEADS_URL . 'editor-layouts/css/editor-main.css' );
	}
}

/**
 * @param array $i18n
 *
 * @return array
 */
function tve_leads_js_translate( $i18n ) {
	$i18n = array_merge_recursive( $i18n, require plugin_dir_path( dirname( __FILE__ ) ) . 'inc/i18n.php' );

	return $i18n;
}

/**
 * Fetch extra list of templates needed by Thrive Leads on editor pages
 *
 * @param array $templates list of templates from TCB
 *
 * @return array
 */
function tve_get_editor_backbone_templates( $templates = array() ) {
	$templates = array_merge( $templates, tve_dash_get_backbone_templates( plugin_dir_path( dirname( __FILE__ ) ) . 'inc/backbone', 'backbone' ) );

	return $templates;
}

/**
 * Output the HTML for the leads form states
 */
function tve_leads_output_editor_states() {
	if ( ! tve_leads_post_type_editable( get_post_type() ) ) {
		return;
	}

	include plugin_dir_path( dirname( __FILE__ ) ) . 'editor-layouts/_form_states.php';
}

/**
 * Tells TCB from where to include tl_shortcode component menu
 *
 * @param $file
 *
 * @return string
 */
function tve_leads_shortcode_menu_path( $file ) {

	$file = dirname( __FILE__ ) . '/menu/shortcode.php';

	return $file;
}

/**
 * @param $data
 *
 * @return mixed
 */
function tve_leads_localize_shortcodes( $data ) {

	$shortcodes_posts = tve_leads_get_shortcodes();
	$shortcodes       = array();

	foreach ( $shortcodes_posts as $item ) {
		$shortcode          = array();
		$shortcode['id']    = $item->ID;
		$shortcode['label'] = $item->post_title;
		$shortcode['title'] = $item->post_title;

		$shortcodes[] = $shortcode;
	}

	$data['tl_shortcodes'] = $shortcodes;

	return $data;
}

function tve_leads_localize_templates( $data ) {

	if ( ! tve_leads_post_type_editable( get_post_type() ) ) {
		return $data;
	}

	$post_id       = isset( $_REQUEST['p'] ) ? intval( $_REQUEST['p'] ) : 0;
	$variation_key = isset( $_REQUEST['_key'] ) ? intval( $_REQUEST['_key'] ) : 0;

	$last_edited_state_key = get_post_meta( $post_id, 'tve_last_edit_state_' . $variation_key, true );

	$variation      = tve_leads_get_form_variation( $post_id, $last_edited_state_key ? $last_edited_state_key : $variation_key );
	$form_type      = tve_leads_get_form_type_from_variation( $variation, true, false );
	$get_multi_step = ! (bool) $variation['parent_id'];

	$templates = Thrive_Leads_Template_Manager::get_templates( $form_type, $get_multi_step );

	if ( ! empty( $templates ) ) {
		$data['tl_templates']       = $templates['templates'];
		$data['tl_templates_error'] = $templates['error'];
	}

	return $data;
}

/**
 * Enable the template tab on sidebar if the post type is one of TL post types
 *
 * @param $has_template
 *
 * @return bool
 */
function tve_leads_has_templates_tab( $has_template ) {

	if ( tve_leads_post_type_editable( get_post_type() ) ) {
		$has_template = true;
	}

	return $has_template;
}

/**
 * TCB prints the modals' HTML on footer print scripts action and
 * a new file is added to the list
 * to be used when user wants to select a template for current variation
 *
 * @param $files
 *
 * @return array $files
 */
function tve_leads_modal_templates( $files ) {
	if ( ! tve_leads_post_type_editable( get_post_type() ) ) {
		return $files;
	}

	$files[] = TVE_LEADS_PATH . 'tcb-bridge/modals/tl-templates.php';
	$files[] = TVE_LEADS_PATH . 'tcb-bridge/modals/tl-template-saving.php';

	return $files;
}

/**
 * Check if we are currently in a editor page for a Thrive Leads form
 *
 * @param bool $can_use
 *
 * @return bool
 */
function tve_leads_can_use_page_events( $can_use ) {

	return tve_leads_post_type_editable( get_post_type() ) ? false : $can_use;
}

/**
 * On lead generation menu component add html for Asset Delivery Control
 */
function tve_leads_insert_asset_delivery_control() {

	if ( ! tve_leads_asset_delivery_setup_valid() ) {
		return;
	}

	echo tve_leads_template( 'asset-delivery-control', null, true );
}

/**
 * Hook into Elements configuration and add asset group options for LG element and Registration form
 *
 * @param array $config
 *
 * @return mixed
 */
function tve_leads_forms_config( $config ) {

	if ( ! tve_leads_asset_delivery_setup_valid() ) {
		return $config;
	}

	$options = array();
	foreach ( tve_leads_get_asset_delivery_groups() as $group ) {
		$option          = array();
		$option['value'] = $group->ID;
		$option['name']  = $group->post_title;

		$options[] = $option;
	}

	foreach ( array( 'lead_generation', 'registration_form' ) as $key ) {
		if ( isset( $config[ $key ] ) ) {
			$config[ $key ]['components'][ $key ]['config']['AssetDelivery'] = array(
				'config' => array(
					'label'        => __( 'Enable asset delivery', 'thrive-leads' ),
					'tooltip'      => __( 'Asset delivery requires an email address to be submitted in your form.', 'thrive-leads' ),
					'tooltip_side' => 'top',
				),
			);
			$config[ $key ]['components'][ $key ]['config']['AssetGroup']    = array(
				'config' => array(
					'name'          => __( 'Asset group', 'thrive-leads' ),
					'label_col_x'   => 5,
					'options'       => $options,
					'consent_label' => __( 'Asset delivery "{asset_name}"', 'thrive-leads' ),
				),
			);
		}
	}

	return $config;
}

/**
 * @param string $logo_url
 *
 * @return string
 */
function tve_leads_architect_branding( $logo_url ) {
	if ( tve_leads_post_type_editable( get_post_type() ) ) {
		$logo_url = TVE_LEADS_URL . 'admin/img/thrive-leads-logo.png';
	}

	return $logo_url;
}

function tve_leads_output_froala_container() {
	echo '<div class="fr-dropdown-holder"></div>';
}

/**
 * Adds Thrive Leads data to the hook that fetched data for the 3rd party developers
 *
 * @param array $data
 *
 * @return array
 */
function tve_leads_parse_lead_gen_form_data( $data = array() ) {

	if ( ! empty( $data['thrive_leads'] ) && ! empty( $data['thrive_leads']['tl_data'] ) ) {
		$extra_data = $data['thrive_leads']['tl_data'];

		$data['external_plugin_fields'] = array();

		$data['external_plugin_fields']['form_id']         = $extra_data['_key'];
		$data['external_plugin_fields']['form_name']       = $extra_data['form_name'];
		$data['external_plugin_fields']['lead_group_id']   = $extra_data['main_group_id'];
		$data['external_plugin_fields']['lead_group_name'] = $extra_data['main_group_name'];

		if ( ! empty( $extra_data['active_test_data'] ) ) {
			$data['external_plugin_fields']['split_test_id']   = $extra_data['active_test_data']['id'];
			$data['external_plugin_fields']['split_test_name'] = $extra_data['active_test_data']['title'];
		}

		unset( $data['thrive_leads'] );
	}

	return $data;
}
