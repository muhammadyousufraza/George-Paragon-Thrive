<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

//include "functions.php";

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main class for handling the editor page related stuff
 *
 * Class TCB_Editor_Page
 */
class TCB_Editor {
	/**
	 * Instance
	 *
	 * @var TCB_Editor
	 */
	private static $instance;

	/**
	 * Post being edited
	 *
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * If the current post can be edited
	 *
	 * @var bool
	 */
	protected $can_edit_post = null;

	/**
	 * TCB Elements Class
	 *
	 * @var TCB_Elements
	 */
	public $elements;

	/**
	 * TCB_Editor constructor.
	 */
	final private function __construct() {
		$this->elements = tcb_elements();
	}

	/**
	 * Singleton instance method
	 *
	 * @return TCB_Editor
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Called on "init" action hook - only in admin
	 *
	 */
	public function on_admin_init() {
		/**
		 * Enfold Theme enqueues scripts in the admin_menu hook
		 */
		remove_all_actions( 'admin_menu' );
	}

	/**
	 * Setup actions for the main editor frame
	 */
	public function setup_main_frame() {
		remove_all_actions( 'wp_head' );

		remove_all_actions( 'wp_enqueue_scripts' );
		remove_all_actions( 'wp_print_scripts' );
		remove_all_actions( 'wp_print_footer_scripts' );
		remove_all_actions( 'wp_footer' );
		remove_all_actions( 'wp_print_styles' );

		add_action( 'wp_head', 'wp_enqueue_scripts' );
		add_action( 'wp_head', 'wp_print_styles' );
		add_action( 'wp_print_footer_scripts', '_wp_footer_scripts' );

		add_action( 'wp_footer', [ $this, 'print_footer_templates' ], 1 );
		add_action( 'wp_footer', 'wp_auth_check_html' );

		add_action( 'wp_footer', '_wp_footer_scripts' );
		add_action( 'wp_footer', 'wp_print_footer_scripts' );
		add_action( 'wp_enqueue_scripts', [ $this, 'main_frame_enqueue' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'main_frame_dequeue' ], PHP_INT_MAX );

		/**
		 * Remove all tinymce buttons
		 */
		remove_all_filters( 'mce_buttons' );
		remove_all_filters( 'mce_external_plugins' );
	}

	/**
	 * Template redirect hook for the main window ( containing the control panel and the post content iframe )
	 */
	public function post_action_architect() {

		if ( ! $this->has_license() ) {
			wp_redirect( admin_url( 'admin.php?page=tve_dash_section' ) );
			exit();
		}

		if ( ! $this->is_main_frame() ) {
			wp_redirect( admin_url( 'post.php?action=edit&post=' . get_post()->ID ) );
			exit();
		}

		$this->setup_main_frame();

		/**
		 * Action hook.
		 * Allows executing 3rd party code in this point. Example: dequeue any necessary resources from the editor main page
		 */
		do_action( 'tcb_hook_template_redirect' );

		tcb_template( 'layouts/editor', $this );
		exit();
	}

	/**
	 * Check if the current screen is the main frame for the editor ( containing the control panel and the content frame )
	 */
	public function is_main_frame() {
		if ( ! apply_filters( 'tcb_is_editor_page', ! empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) ) ) {
			return false;
		}
		/**
		 * If we are in the iframe request, we are not in the main editor page request
		 */
		if ( isset( $_REQUEST[ TVE_FRAME_FLAG ] ) ) {
			return false;
		}

		if ( ! $this->can_edit_post() ) { // If this isn't a TCB editable post.
			return false;
		}

		return true;
	}

	/**
	 * Check capabilities and regular conditions for the editing screen
	 *
	 * @return bool
	 */
	public function can_edit_post() {
		if ( isset( $this->can_edit_post ) ) {
			return $this->can_edit_post;
		}
		// @codingStandardsIgnoreStart
		$this->set_post( get_post() );

		if ( ! $this->post ) {
			return $this->can_edit_post = false;
		}

		if ( ! tve_is_post_type_editable( $this->post->post_type ) || ! current_user_can( 'edit_posts' ) ) {
			return $this->can_edit_post = false;
		}

		$page_for_posts = get_option( 'page_for_posts' );
		if ( $page_for_posts && (int) $this->post->ID === (int) $page_for_posts ) {
			return $this->can_edit_post = false;
		}

		if ( ! tve_tcb__license_activated() && ! apply_filters( 'tcb_skip_license_check', false ) ) {
			return $this->can_edit_post = false;
		}

		if ( ! TCB_Product::has_external_access( (int) $this->post->ID ) ) {
			/**
			 * If Architect and plugin or just the plugin can't be used the post isn't available to eidt
			 */
			return $this->can_edit_post = false;
		}

		return $this->can_edit_post = apply_filters( 'tcb_user_can_edit', true, $this->post->ID );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Check if the current screen (request) if the inner contents iframe ( the one displaying the actual post content )
	 */
	public function is_inner_frame() {
		if ( apply_filters( 'tcb_is_inner_frame_override', false ) ) {
			return true;
		}

		if ( empty( $_REQUEST[ TVE_FRAME_FLAG ] ) || ! apply_filters( 'tcb_is_editor_page', ! empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) ) ) {
			return false;
		}
		if ( ! $this->can_edit_post() ) {
			return false;
		}

		/**
		 * The iframe receives a query string variable
		 */
		if ( ! wp_verify_nonce( sanitize_text_field( $_REQUEST[ TVE_FRAME_FLAG ] ), TVE_FRAME_FLAG ) ) { //phpcs:ignore
			return false;
		}

		add_filter( 'body_class', [ $this, 'inner_frame_body_class' ] );

		return true;
	}

	/**
	 * Adds the required CSS classes to the body of the inner html document
	 *
	 * @param array $classes Classes to be added on the iframe body.
	 *
	 * @return array
	 */
	public function inner_frame_body_class( $classes ) {
		$classes [] = 'tve_editor_page';
		$classes [] = 'preview-desktop';

		return $classes;
	}

	/**
	 * Enqueue scripts and styles for the main frame
	 */
	public function main_frame_enqueue() {
		$js_suffix = TCB_Utils::get_js_suffix();

		$this->enqueue_media();
		// WP colour picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-masonry', [ 'jquery' ] );
		wp_enqueue_script( 'tcb-velocity', TVE_DASH_URL . '/js/dist/velocity.min.js' );
		tve_dash_enqueue_script( 'tcb-dropbox-picker', TVE_DASH_URL . '/js/dist/dropbox-picker.min.js' );
		wp_enqueue_script( 'tcb-leanmodal', TVE_DASH_URL . '/js/dist/leanmodal.min.js' );

		wp_enqueue_script( 'tcb-scrollbar', TVE_DASH_URL . '/js/util/jquery.scrollbar.min.js' );

		wp_enqueue_script( 'tcb-moment', tve_editor_url( 'editor/js/libs/moment.min.js' ) );

		$locale = tve_get_locale();
		if ( file_exists( TVE_TCB_ROOT_PATH . '/editor/js/libs/moment-locale/' . $locale . '.js' ) ) {
			wp_enqueue_script( 'tcb-moment-locale', tve_editor_url( 'editor/js/libs/moment-locale/' . $locale . '.js' ) );
		}

		if ( function_exists( 'wp_enqueue_code_editor' ) ) {
			/**
			 * @since 4.9.0
			 */
			wp_enqueue_code_editor( [ 'type' => 'text/html' ] );
		}

		$main_deps = apply_filters( 'tve_main_js_dependencies', [
			'jquery',
			'jquery-ui-draggable',
			'jquery-ui-position',
			'jquery-ui-autocomplete',
			'jquery-ui-datepicker',
			'jquery-effects-core',
			'jquery-effects-slide',
			'tcb-leanmodal',
			'tcb-velocity',
			'tcb-dropbox-picker',
			'backbone',
		] );

		tve_enqueue_script( 'tve-main', tve_editor_js( '/main' . $js_suffix ), $main_deps, false, true );

		tve_enqueue_style( 'tve2_editor_style', tve_editor_css( 'main/style.css' ) );

		/* wp-auth-login */
		wp_enqueue_script( 'wp-auth-check' );
		wp_enqueue_style( 'wp-auth-check' );

		/* widget styles */
		wp_enqueue_style( 'widgets' );
		wp_enqueue_style( 'media-views' );

		wp_enqueue_editor();

		TCB_Icon_Manager::enqueue_icon_pack();
		TCB_Icon_Manager::enqueue_fontawesome_styles();

		Tvd_Auth_Check::auth_enqueue_scripts();

		/* Font family */
		tve_enqueue_style( 'tve-editor-font', 'https://fonts.googleapis.com/css?family=Rubik:400,500,700' );

		//Default datepicker design
		wp_enqueue_style( 'jquery-ui-datepicker', tve_editor_css( 'jquery-ui-1.10.4.custom.min.css' ) );

		if ( tve_check_if_thrive_theme() ) {
			/* include the css needed for the shortcodes popup (users are able to insert Thrive themes shortcode inside the WP editor on frontend) - using the "Insert WP Shortcode" element */
			tve_enqueue_style( 'tve_shortcode_popups', tve_editor_css( 'thrive_shortcodes_popup.css' ) );
		}

		/*Include Select2*/
		wp_enqueue_script( 'tcb-select2-script', TVE_DASH_URL . '/js/dist/select2.min.js' );

		/**
		 * Action filter.
		 * Used to enqueue scripts from other products
		 */
		do_action( 'tcb_main_frame_enqueue' );

		wp_localize_script( 'tve-main', 'tcb_main_const', $this->main_frame_localize() );
	}

	/**
	 * Dequeue conflicting scripts from the main frame
	 */
	public function main_frame_dequeue() {
		wp_dequeue_script( 'membermouse-blockUI' );
		wp_deregister_script( 'membermouse-blockUI' );
		/* TAR-5246 - floating preview in editor is not working because of the mm scripts */
		wp_dequeue_script( 'mm-common-core.js' );
		wp_deregister_script( 'mm-common-core.js' );
		wp_dequeue_script( 'mm-preview.js' );
		wp_deregister_script( 'mm-preview.js' );
		wp_dequeue_script( 'membermouse-socialLogin' );
		wp_deregister_script( 'membermouse-socialLogin' );

		/* Uncode theme CSS incorrectly loading CSS all over admin and messing up TAr editor page */
		wp_dequeue_style( 'ot-admin' );
		wp_deregister_style( 'ot-admin' );
		wp_dequeue_style( 'admin-uncode-icons' );
		wp_deregister_style( 'admin-uncode-icons' );
		wp_dequeue_style( 'uncode-custom-style' );
		wp_deregister_style( 'uncode-custom-style' );

		/**
		 * This saves QueryString params as cookies and in some cases will make admin menu disappear
		 */
		wp_dequeue_script( 'inbound-analytics' );
		wp_deregister_script( 'inbound-analytics' );

		/* indeed membership-pro loading styles in editor sidebar */
		wp_dequeue_style( 'indeed_sweetalert_css' );
		wp_deregister_style( 'indeed_sweetalert_css' );
		wp_dequeue_style( 'ihc_bootstrap-slider' );
		wp_deregister_style( 'ihc_bootstrap-slider' );
		wp_dequeue_style( 'ihc_admin_style' );
		wp_deregister_style( 'ihc_admin_style' );
		wp_dequeue_style( 'ihc_public_style' );
		wp_deregister_style( 'ihc_public_style' );

		/* Woocommerce */
		wp_dequeue_style( 'woocommerce_admin' );
		wp_deregister_style( 'woocommerce_admin' );

		wp_dequeue_style( 'woocommerce_admin_menu_styles' );
		wp_deregister_style( 'woocommerce_admin_menu_styles' );

		/* Woocommerce Etsy plugin */
		wp_dequeue_style( 'ced-boot-css' );
		wp_deregister_style( 'ced-boot-css' );
		wp_dequeue_style( 'woocommmerce-etsy-integration' );
		wp_deregister_style( 'woocommmerce-etsy-integration' );

		/* ACF */
		wp_dequeue_style( 'acf-global' );
		wp_deregister_style( 'acf-global' );
		wp_dequeue_style( 'acf-input' );
		wp_deregister_style( 'acf-input' );
		wp_dequeue_style( 'acf-datepicker' );
		wp_deregister_style( 'acf-datepicker' );
		wp_dequeue_style( 'acf-timepicker' );
		wp_deregister_style( 'acf-timepicker' );

		wp_dequeue_script( 'acf' );
		wp_deregister_script( 'acf' );
		wp_dequeue_script( 'acf-input' );
		wp_deregister_script( 'acf-input' );
		wp_dequeue_script( 'acf-timepicker' );
		wp_deregister_script( 'acf-timepicker' );

		/**
		 * Dequeue custom font js from main frame
		 */
		wp_dequeue_script( 'bsf-custom-fonts-js' );
		wp_deregister_script( 'bsf-custom-fonts-js' );
	}

	/**
	 * Include backbone templates and let other add their own stuff
	 */
	public function print_footer_templates() {
		$templates = tve_dash_get_backbone_templates( TVE_TCB_ROOT_PATH . 'inc/backbone', 'backbone' );

		$templates = apply_filters( 'tcb_backbone_templates', $templates );

		tve_dash_output_backbone_templates( $templates, 'tve-' );
		do_action( 'tve_editor_print_footer_scripts' );
		$this->add_footer_modals();
	}

	/**
	 * Print editor modals
	 */
	private function add_footer_modals() {

		$path  = TVE_TCB_ROOT_PATH . 'inc/views/modals/';
		$files = array_diff( scandir( $path ), [ '.', '..' ] );
		foreach ( $files as $key => $file ) {
			$files[ $key ] = $path . $file;
		}

		$files = apply_filters( 'tcb_modal_templates', $files );

		tcb_template( 'modals', [
			'post'  => $this->post,
			'files' => $files,
		] );
	}

	/**
	 * Javascript localization for the main TCB frame
	 *
	 * @return array
	 */
	public function main_frame_localize() {
		$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
		// For some reason, the above line does not work in some instances.
		if ( is_ssl() ) {
			$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
		}

		$fm = new TCB_Font_Manager();

		$post            = tcb_post();
		$is_landing_page = $post->is_landing_page();
		$current_user    = wp_get_current_user();

		/**
		 * The names of the global styles wp options
		 */
		$global_style_options = tve_get_global_styles_option_names();

		/**
		 * Fixes an issue where the editor crashes because tve_globals is not an object
		 */
		$globals = $post->meta( 'tve_globals', null, true, [ 'e' => 1 ] );
		if ( ! is_array( $globals ) ) {
			$globals = [ 'e' => 1 ];
		}

		$tcb_user_settings = get_user_option( 'tcb_u_settings' );
		if ( empty( $tcb_user_settings ) || ! is_array( $tcb_user_settings ) ) {
			$tcb_user_settings = [];
		}

		$post_constants = get_post_meta( $this->post->ID, '_tve_post_constants', true );
		if ( ! is_array( $post_constants ) ) {
			$post_constants = [ 'e' => 1 ];
		}

		/* build api connections localization */
		$api_connections      = [];
		$api_connections_data = [];
		foreach (
			Thrive_Dash_List_Manager::get_available_apis( true, [
				'exclude_types' => [
					'email',
					'social',
					'storage',
					'collaboration',
					'sellings',
				],
			] ) as $key => $connection_instance
		) {
			$api_connections[ $key ]      = $connection_instance->get_title();
			$api_connections_data[ $key ] = $connection_instance->get_data_for_setup();
		}

		$data = array(
			'global_css_prefix'             => tcb_selection_root(),
			'frame_uri'                     => tcb_get_editor_url( $this->post->ID, false ),
			'plugin_url'                    => tve_editor_url() . '/',
			'nonce'                         => wp_create_nonce( TCB_Editor_Ajax::NONCE_KEY ),
			'rest_nonce'                    => TCB_Utils::create_nonce(),
			'dash_nonce'                    => wp_create_nonce( 'tve-dash' ),
			'ajax_url'                      => $admin_base_url . 'admin-ajax.php',
			'post'                          => $this->post,
			'post_format'                   => get_post_format(),
			'elements'                      => $this->elements->localize(),
			'tpl_categ'                     => $this->elements->user_templates_category(),
			'theme_css_disabled'            => get_post_meta( $this->post->ID, 'tve_disable_theme_dependency', true ),
			'options'                       => $this->elements->component_options(),
			'fonts'                         => $fm->all_fonts(),
			'landing_page'                  => $is_landing_page,
			'templates_path'                => TVE_LANDING_PAGE_TEMPLATE,
			'dash_url'                      => TVE_DASH_URL,
			'pinned_category'               => $this->elements->pinned_category,
			'social_fb_app_id'              => tve_get_social_fb_app_id(),
			'google_maps_embeded_app_id'    => tve_get_google_maps_embedded_app_id(),
			'disable_google_fonts'          => tve_dash_is_google_fonts_blocked(),
			'allow_video_src'               => tve_dash_allow_video_src(),
			'lcns'                          => $this->lcns_attributes( $this->post->post_type ),
			'lightspeed'                    => [
				'js_modules'         => \TCB\Lightspeed\JS::get_module_data( '', 'identifier' ),
				'styles_to_optimize' => \TCB\Lightspeed\CSS::get_instance( $this->post->ID )->get_styles_to_optimize(),
				'gutenberg_modules'  => \TCB\Lightspeed\Gutenberg::get_gutenberg_assets( null, 'identifier' ),
			],
			'api_connections'               => $api_connections,
			'api_connections_data'          => $api_connections_data,
			'storage_apis'                  => array_map( static function ( $connection ) {
				/**
				 * Search for a square version of the logo. if not found, use the default one
				 */
				$base_path = TVE_DASH_PATH . '/inc/auto-responder/views/images/';
				$base_url  = TVE_DASH_URL . '/inc/auto-responder/views/images/';
				$png       = $connection->get_key() . '.png';

				$credentials = $connection->get_credentials();

				return array(
					'name'      => $connection->get_title(),
					'client_id' => isset( $credentials['client_id'] ) ? $credentials['client_id'] : '',
					'logo'      => file_exists( $base_path . 'square/' . $png ) ? ( $base_url . 'square/' . $png ) : ( $base_url . $png ),
				);
			}, Thrive_Dash_List_Manager::get_available_apis( true, [ 'include_types' => [ 'storage' ] ] ) ),
			'connected_apis_custom_fields'  => Thrive_Dash_List_Manager::get_available_custom_fields(),
			'apis_custom_fields_mapper'     => Thrive_Dash_List_Manager::get_custom_fields_mapper(),
			'apis_default_form_fields'      => Thrive_Dash_List_Manager::get_custom_fields_mapper( true ),
			'colors'                        => array(
				'favorites'      => tve_convert_favorite_colors(),
				'globals'        => array_reverse( tcb_color_manager()->get_list() ),
				'global_prefix'  => TVE_GLOBAL_COLOR_VAR_CSS_PREFIX,
				'local_prefix'   => TVE_LOCAL_COLOR_VAR_CSS_PREFIX,
				'lp_set_prefix'  => TVE_LP_COLOR_VAR_CSS_PREFIX,
				'dynamic_prefix' => TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX,
				'main'           => [
					'h' => TVE_MAIN_COLOR_H,
					's' => TVE_MAIN_COLOR_S,
					'l' => TVE_MAIN_COLOR_L,
				],
			),
			'gradients'                     => array(
				'favorites'     => get_option( 'thrv_custom_gradients', [] ),
				'globals'       => array_reverse( get_option( apply_filters( 'tcb_global_gradients_option_name', 'thrv_global_gradients' ), [] ) ),
				'global_prefix' => TVE_GLOBAL_GRADIENT_VAR_CSS_PREFIX,
				'local_prefix'  => TVE_LOCAL_GRADIENT_VAR_CSS_PREFIX,
				'lp_set_prefix' => TVE_LP_GRADIENT_VAR_CSS_PREFIX,
			),
			'global_cls_prefix'             => TVE_GLOBAL_STYLE_CLS_PREFIX,
			'dynamic_prefix'                => TVE_DYNAMIC_VAR_CSS_PREFIX,
			'dynamic_background_url_prefix' => TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX,
			'global_styles'                 => array(
				'prefix'            => TVE_GLOBAL_STYLE_CLS_PREFIX,
				'button'            => tve_get_global_styles( 'button', $global_style_options['button'] ),
				'prefix_button'     => TVE_GLOBAL_STYLE_BUTTON_CLS_PREFIX,
				'section'           => tve_get_global_styles( 'section', $global_style_options['section'] ),
				'prefix_section'    => TVE_GLOBAL_STYLE_SECTION_CLS_PREFIX,
				'contentbox'        => tve_get_global_styles( 'contentbox', $global_style_options['contentbox'] ),
				'prefix_contentbox' => TVE_GLOBAL_STYLE_CONTENTBOX_CLS_PREFIX,
				'link'              => tve_get_global_styles( 'link', $global_style_options['link'] ),
				'prefix_link'       => TVE_GLOBAL_STYLE_LINK_CLS_PREFIX,
				'text'              => tve_get_global_styles( 'text', $global_style_options['text'] ),
				'prefix_text'       => TVE_GLOBAL_STYLE_TEXT_CLS_PREFIX,
				'has_c_s_p'         => $this->has_central_style_panel(),
			),
			'user_settings'                 => $tcb_user_settings,
			/**
			 * Filter tcb_js_translate allows adding javascript translations to the editor page ( main editor panel ).
			 */
			'i18n'                          => apply_filters( 'tcb_js_translate', require TVE_TCB_ROOT_PATH . 'inc/i18n.php' ),
			/**
			 * Page events
			 */
			'page_events'                   => $post->meta( 'tve_page_events', null, true, [] ),
			/**
			 * Globals for the current post / page
			 */
			'tve_globals'                   => $globals,
			'tve_post_constants'            => $post_constants,
			'icon_pack_css'                 => $this->icon_pack_css(),
			'editor_selector'               => apply_filters( 'editor_selector', $post->is_lightbox() || $is_landing_page ? 'body' : '' ),
			'current_user'                  => [
				'email' => $current_user->user_email,
				'name'  => $current_user->first_name . ' ' . $current_user->last_name,
			],
			'site_title'                    => get_bloginfo( 'name' ),
			'debug_mode'                    => tve_dash_is_debug_on(),
			'has_templates'                 => $this->can_use_landing_pages(),
			'custom_menu'                   => array(
				'use_positional_selectors' => tcb_custom_menu_positional_selectors(),
				/** required to solve backwards compatibility issues */
				'typography_old_prefix'    => tcb_selection_root() . ' ',
				/* Menu Descriptions template (applicable in Mega Menus) */
				'mega_desc_tpl'            => TCB_Menu_Walker::$mega_description_template,
				'mega_image_tpl'           => TCB_Menu_Walker::$mega_image_template,
				'menu_item_image_tpl'      => TCB_Menu_Walker::$menu_item_image_template,
			),
			'display_save_notification'     => (int) get_option( 'tve_display_save_notification', 1 ),
			'lead_generation'               => array(
				/**
				 * Allows turning the default file upload validation on or off (default = true)
				 *
				 * @param boolean
				 *
				 */
				'file_upload_validation' => apply_filters( 'tcb_file_upload_validation', true ),
				'custom_tag_apis'        => TCB_Utils::get_api_list_with_tag_support(),
			),
			'froalaMode'                    => get_user_meta( $current_user->ID, 'froalaMode', true ),
			'default_styles'                => tve_get_default_styles( false ),
			'post_login_actions'            => TCB_Login_Element_Handler::get_post_login_actions(),
			'post_register_actions'         => TCB_Login_Element_Handler::get_post_register_actions(),
			'is_woo_active'                 => \TCB\Integrations\WooCommerce\Main::active() ? 1 : 0,
			'lg_email_shortcodes'           => $this->get_lg_email_shortcodes(),
			'dismissed_tooltips'            => (array) get_user_meta( $current_user->ID, 'tcb_dismissed_tooltips', true ),
			'post_parent'                   => empty( $post->post->post_parent ) ? '' : get_post( $post->post->post_parent ),
			'categories'                    => TCB_Post_List_Filter::localize_filter_categories(),
		);

		/** Do not localize anything that's not necessary */

		$data['show_more_tag'] = apply_filters( 'tcb_show_more_tag', ! $data['landing_page'] && ! $this->is_lightbox() && ! $this->is_page() );
		if ( empty( $data['show_more_tag'] ) ) {
			unset( $data['elements']['moretag'] );
		}

		if ( $is_landing_page ) {
			$landing_page = tcb_landing_page( $this->post->ID );

			$data['colors']['templates']    = $landing_page->template_vars['colours'];
			$data['gradients']['templates'] = $landing_page->template_vars['gradients'];
			$data['template_palettes']      = $landing_page->palettes;
			$data['external_palettes']      = 0;

			if ( $data['global_styles']['has_c_s_p'] ) {
				$data['global_styles']['tpl_button']     = $landing_page->template_styles['button'];
				$data['global_styles']['tpl_section']    = $landing_page->template_styles['section'];
				$data['global_styles']['tpl_contentbox'] = $landing_page->template_styles['contentbox'];
			}

			/* Only localize when TTB is not active */
			if ( ! tve_dash_is_ttb_active() ) {
				$lp_palettes_instance = $landing_page->get_palette_instance();
				if ( $lp_palettes_instance ) {
					$data['template_palettes'] = $lp_palettes_instance->get_smart_lp_palettes_v2();
					$data['skin_colors']       = array(
						'skin_palettes'      => $lp_palettes_instance->get_smart_lp_palettes_v2(),
						'skin_main_variable' => '--tcb-theme-main-master',
						'palette_colors'     => $lp_palettes_instance->tcb_get_palettes_from_config(),
					);
				}

			}

			/* For TTB LPs we save the skin tag written in meta */
			$skin_tag = $landing_page->meta( 'theme_skin_tag' );

			if ( ! empty( $skin_tag ) ) {
				$data['lp_skin_tag'] = $skin_tag;
			}

			/* For TTB LPs we save if it has inherited Typography */
			$inherit_typography = $landing_page->meta( 'ttb_inherit_typography', null, true );

			if ( ! empty( $inherit_typography ) ) {
				$data['ttb_inherit_typography'] = (int) $inherit_typography;
			}
		}

		/**
		 * Filter tcb_main_frame_localize. Allows manipulating the javascript data from the main editor frame.
		 */
		$data = apply_filters( 'tcb_main_frame_localize', $data );

		return $data;
	}

	/**
	 * Render sidebar menu that contains the elements, components and settings
	 */
	public function render_menu() {
		tcb_template( 'control-panel', $this );
	}

	/**
	 * Returns an array of shortcodes to be used in LG email message
	 *
	 * @return array
	 */
	public function get_lg_email_shortcodes() {

		$shortcodes = array(
			array(
				'key'        => 'standard',
				'order'      => 0,
				'label'      => __( 'Standard fields', 'thrive-cb' ),
				'shortcodes' => array(
					'all_form_fields' => array(
						'label' => __( 'List all the fields and data captured in the form', 'thrive-cb' ),
						'value' => '[all_form_fields]',
					),
					'first_name'      => array(
						'label' => __( 'The first name of visitor', 'thrive-cb' ),
						'value' => '[first_name]',
					),
					'user_email'      => array(
						'label' => __( 'The email of visitor', 'thrive-cb' ),
						'value' => '[user_email]',
					),
					'phone'           => array(
						'label' => __( 'The phone of visitor', 'thrive-cb' ),
						'value' => '[phone]',
					),
					'uploaded_files'  => array(
						'label' => __( 'Lists all files uploaded by the user (if any).', 'thrive-cb' ),
						'value' => '[uploaded_files]',
					),
				),
			),
			array(
				'key'        => 'other',
				'order'      => 1,
				'label'      => __( 'Other', 'thrive-cb' ),
				'shortcodes' => array(
					'date'            => array(
						'label' => __( 'Date of submission', 'thrive-cb' ),
						'value' => '[date]',
					),
					'time'            => array(
						'label' => __( 'Time of submission', 'thrive-cb' ),
						'value' => '[time]',
					),
					'site_title'      => array(
						'label' => __( 'The title of your WordPress site', 'thrive-cb' ),
						'value' => '[wp_site_title]',
					),
					'page_url'        => array(
						'label' => __( 'Page containing the form', 'thrive-cb' ),
						'value' => '[page_url]',
					),
					'ip_address'      => array(
						'label' => __( 'TIP address of visitor', 'thrive-cb' ),
						'value' => '[ip_address]',
					),
					'device_settings' => array(
						'label' => __( '"Chrome 3.3.2" for example', 'thrive-cb' ),
						'value' => '[device_settings]',
					),
					'form_url_slug'   => array(
						'label' => __( 'The slug of form e.g "/form/123"', 'thrive-cb' ),
						'value' => '[form_url_slug]',
					),
				),
			),
		);

		return apply_filters( 'tve_lg_email_shortcodes', $shortcodes );
	}

	/**
	 * Returns true if the editor has the POST breadcrumb option
	 *
	 * @return bool
	 */
	public function has_post_breadcrumb_option() {
		$post_type = get_post_type( get_the_ID() );

		return ! in_array( $post_type, apply_filters( 'tcb_post_visibility_options_availability', [
			'attachment',
			'content_template',
			'tcb_lightbox',
			TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			'tve_notifications',
		] ) );
	}

	/**
	 * Returns the post breadcrumb data
	 *      - label
	 *      - selector
	 *
	 * @return array
	 */
	public function post_breadcrumb_data() {
		$return = [];

		$return['selector'] = addslashes( "<div class='tve-post-options-element'>" );

		if ( tcb_post( $this->post )->is_landing_page() ) {
			$return['label']    = __( 'Landing Page', 'thrive-cb' );
			$return['selector'] = 'body.tve_lp';
		} elseif ( $this->post->post_type === 'page' ) {
			$return['label'] = __( 'Page', 'thrive-cb' );
		} else {
			$return['label'] = __( 'Post', 'thrive-cb' );
		}

		/**
		 * Change breadcrumb data selector and label
		 */
		return apply_filters( 'tcb_post_breadcrumb_data', $return );
	}

	/**
	 * Output the inner control panel menus for elements ( menus for each element )
	 */
	public function inner_frame_menus() {
		/**
		 * This is called in the footer. There are some plugins that query posts in the footer,
		 * changing the global query. This makes sure the global query is reset to its initial state
		 * - apply filters for this in case there is no need of a reset on wp_query
		 */
		if ( apply_filters( 'tcb_reset_query_for_inner_frame', true ) ) {
			wp_reset_query();
		}

		if ( ! $this->is_inner_frame() ) {
			return;
		}

		tcb_template( 'inner.php' );

		/**
		 * Output the editor page SVG icons
		 */
		$this->output_editor_svg();
	}

	/**
	 * Clean up inner frame ( e.g. remove admin menu )
	 */
	public function clean_inner_frame() {
		if ( ! $this->is_inner_frame() ) {
			return;
		}
		add_filter( 'show_admin_bar', '__return_false' );

		// membermouse admin bar
		global $mmplugin;
		if ( ! empty( $mmplugin ) && is_object( $mmplugin ) ) {
			remove_action( 'wp_head', [ $mmplugin, 'loadPreviewBar' ] );
		}
	}

	/**
	 * Output the SVG file for the editor page - to have icons in the inner frame also.
	 */
	public function output_editor_svg() {
		include TVE_TCB_ROOT_PATH . 'editor/css/fonts/editor-page.svg';

		do_action( 'tcb_output_extra_editor_svg' );
	}

	/**
	 * Enqueue wp media scripts / styles and solve some issues with 3rd party plugins.
	 */
	public function enqueue_media() {
		wp_enqueue_style( 'media' );
		/** some themes have hooks defined here, which rely on functions defined only in the admin part - these will not be defined on frontend */
		remove_all_filters( 'media_view_settings' );
		remove_all_actions( 'print_media_templates' );
		// enqueue scripts for tapping into media thickbox
		wp_enqueue_media();
		/* Compatibility fix for Media Modal when Unsplash plugin is active */
		wp_dequeue_script( 'unsplash-media-selector' );
	}

	/**
	 * Checks if the current post / page can have page events
	 *
	 * @return bool
	 */
	public function can_use_page_events() {
		if ( ! $this->post ) {
			return false;
		}

		return apply_filters( 'tcb_can_use_page_events', $this->post->post_type !== 'tcb_lightbox' );
	}

	/**
	 * Allows other plugins to hook into this and allowing the central style panel to be displayed
	 *
	 * @return mixed|void
	 */
	private function allow_central_style_panel() {
		return apply_filters( 'tcb_allow_central_style_panel', $this->is_landing_page() );
	}

	/**
	 * Checks if the editor allows to add elements
	 *
	 * @return boolean
	 */
	public function can_add_elements() {
		/**
		 * Allows other plugins that have the ability to edit content with TAR to disable/enable the "Add Elements" button in the sidebar
		 *
		 * @param boolean
		 */
		return apply_filters( 'tcb_can_add_elements', true );
	}

	/**
	 * Checks if the editor allows the preview button
	 *
	 * @return boolean
	 */
	public function has_preview_button() {

		/**
		 * Allows other plugins that have the ability to edit content with TAR to disable/enable the "Preview" button in the bottom sidebar
		 *
		 * @return boolean
		 */
		return apply_filters( 'tcb_has_preview_button', true );
	}

	/**
	 * Returns true if the page has centralized style panel
	 *
	 * @return boolean
	 */
	public function has_central_style_panel() {
		if ( ! $this->allow_central_style_panel() ) {
			return false;
		}

		$landing_page = tcb_landing_page( $this->post->ID );

		return apply_filters( 'tcb_has_central_style_panel', true, $landing_page );
	}

	/**
	 * @param string $icon
	 * @param string $display show | hide | unavailable
	 *
	 * @return string
	 */
	public function get_sidebar_icon_availability( $icon = '', $display = 'hide' ) {
		$post_type = get_post_type( $this->post );
		switch ( $icon ) {
			case 'central-style':
				if ( $this->has_central_style_panel() ) {
					$display = 'show';
				} /* Only in TAr, the icon can be 'unavailable' */
				else if ( $post_type === 'post' || $post_type === 'page' ) {
					$display = 'unavailable';
				} else {
					$display = 'hide';
				}
				break;
			case 'cloud-templates':
				if ( $this->has_templates_tab() ) {
					$display = 'show';
				} else if ( $post_type === 'post' || $post_type === 'page' ) {
					/* Only in TAr, the icon can be 'unavailable' */
					$display = 'unavailable';
				} else {
					$display = 'hide';
				}

				break;
			/* The '$display=show' case is treated in TOP*/
			case 'ab-test':
				/* Icon can be unavailable only on pages that normally allow the Optimize icon*/
				if ( $this->is_cpt_allowed( $post_type ) ) {
					$display = 'unavailable';
					break;
				}
				$display = 'hide';
				break;
			default:
				break;
		}

		return apply_filters( 'tcb_sidebar_icon_availability', $display, $icon, $this );
	}


	public function is_cpt_allowed( $post_type ) {
		return ! empty( $post_type ) && apply_filters( 'tve_allowed_post_type', true, $post_type );
	}

	/**
	 * Returns the template styles data
	 *
	 * template styles
	 * template vars
	 *
	 * Used in inc/views/sidebar-right.php
	 *
	 * @return array
	 */
	public function get_template_styles_data() {
		if ( ! $this->allow_central_style_panel() ) {
			return [];
		}

		$landing_page = tcb_landing_page( $this->post->ID );

		return apply_filters( 'tcb_alter_template_data', [
			'styles'        => $landing_page->template_styles,
			'vars'          => $landing_page->template_vars,
			'skin_palettes' => $landing_page->palettes,
		], $landing_page );
	}

	/**
	 * Checks if the current item being edited allows having the "Template setup" tab in the sidebar
	 */
	public function has_templates_tab() {
		if ( ! $this->post ) {
			return false;
		}
		/**
		 * Checking if the post type can have templates tab
		 */
		$is_allowed = $this->can_use_landing_pages();

		return apply_filters( 'tcb_has_templates_tab', $is_allowed );
	}

	/**
	 * Whether or not the settings tab icon should be displayed
	 *
	 * @return bool
	 */
	public function has_settings_tab() {
		if ( ! $this->post ) {
			return false;
		}

		return apply_filters( 'tcb_has_settings', $this->post->post_type !== TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );
	}

	/**
	 * Allows the plugins to enable / disable Revision Manager Setting
	 * By default is enabled.
	 *
	 * @return bool
	 */
	public function has_revision_manager() {
		if ( ! $this->post ) {
			return false;
		}

		/**
		 * Filter that allows plugins to enable / disable revision manager
		 */
		return apply_filters( 'tcb_has_revision_manager', true );
	}

	/**
	 * Check if Architect License is Activated
	 *
	 * @return bool
	 */
	public function has_license() {
		return tve_tcb__license_activated() || apply_filters( 'tcb_skip_license_check', false );
	}

	/**
	 * Checks if the item being edited allows landing page templates. For now, this is only true for pages
	 */
	public function can_use_landing_pages() {
		if ( ! $this->post ) {
			return false;
		}

		/**
		 * Filter that allows others plugins to use a landing page template on their custom post type
		 */
		$is_allowed = apply_filters( 'tve_allowed_post_type', true, $this->post->post_type );

		return apply_filters( 'tcb_can_use_landing_pages', $is_allowed );
	}

	/**
	 * Whether or not the current post can use external content
	 *
	 * @return false|mixed|void
	 */
	public function allow_import_content() {
		if ( ! $this->post ) {
			return false;
		}

		return apply_filters( 'tcb_can_import_content', true, $this->post );
	}

	/**
	 * Whether or not the current post can export its content
	 *
	 * @return false|mixed|void
	 */
	public function allow_export_content() {
		if ( ! $this->post ) {
			return false;
		}

		return apply_filters( 'tcb_can_export_content', ! $this->is_landing_page(), $this->post );
	}

	/**
	 * Sets post after given post ID
	 *
	 * @param int|WP_POST $post
	 * @param boolean     $set_global_post
	 */
	public function set_post( $post, $set_global_post = false ) {
		if ( is_numeric( $post ) ) {
			$this->post = get_post( $post );
		} elseif ( $post instanceof WP_Post ) {
			$this->post = $post;
		}
		/**
		 * Set the current edited post as global in the case the GLOBAL['post'] is overwritten by 3rd party plugins so we have a fallback
		 */
		global $tve_post;
		$tve_post = $this->post;

		if ( $set_global_post ) {
			global $post;
			$post = $tve_post;
		}
	}

	/**
	 * Checks if the user is currently editing a Thrive Lightbox
	 */
	public function is_lightbox() {
		if ( ! $this->post ) {
			return false;
		}

		return $this->post->post_type === 'tcb_lightbox';
	}

	/**
	 * Checks if the user is currently editing a page
	 *
	 * @return bool
	 */
	public function is_page() {
		if ( ! $this->post ) {
			return false;
		}

		return $this->post->post_type === 'page';
	}

	/**
	 * Checks if the user is currently editing a landing page
	 *
	 * @return bool
	 */
	public function is_landing_page() {
		if ( ! $this->post ) {
			return false;
		}

		return tcb_post( $this->post )->is_landing_page();
	}

	/**
	 * Get the URL for the installed icon pack, if any
	 *
	 * @return string
	 */
	public function icon_pack_css() {
		$icon_pack = get_option( 'thrive_icon_pack' );

		return ! empty( $icon_pack['css'] ) ? tve_url_no_protocol( $icon_pack['css'] ) . '?ver=' . ( isset( $icon_pack['css_version'] ) ? $icon_pack['css_version'] : TVE_VERSION ) : '';
	}

	/**
	 * Prepare the global scripts ( head, body ) for a (possible) landing page
	 *
	 * @param null|TCB_Post $post
	 *
	 * @return array
	 */
	public function post_global_scripts( $post = null ) {
		if ( ! $post ) {
			$post = tcb_post();
		}
		/* landing page template - we need to allow the user to setup head and footer scripts */
		$tve_global_scripts = $post->meta( 'tve_global_scripts' );
		if ( empty( $tve_global_scripts ) || ! $post->is_landing_page() ) {
			$tve_global_scripts = [
				'head'   => '',
				'footer' => '',
			];
		}
		$tve_global_scripts['head']   = preg_replace( '#<style(.+?)</style>#s', '', $tve_global_scripts['head'] );
		$tve_global_scripts['footer'] = preg_replace( '#<style(.+?)</style>#s', '', $tve_global_scripts['footer'] );

		return $tve_global_scripts;
	}

	/**
	 * Get the correct name / title of the "Choose template" functionality from the small sidebar
	 *
	 * @return string
	 */
	public function get_templates_tab_title() {
		return apply_filters( 'tcb_templates_menu_title', __( 'Change Template', 'thrive-cb' ) );
	}

	/**
	 * Whether or not to show a "Save Template" button in the settings menu
	 * User in TAr for post / regular pages (NOT landing pages or lightboxes)
	 *
	 * @return boolean
	 */
	public function has_save_template_button() {
		return apply_filters( 'tcb_has_save_template_button', $this->post->post_type === 'post' || ( $this->is_page() && ! $this->is_landing_page() ) );
	}


	/**
	 * Adding Architect TVE=true to the cache plugin's exclude pages in order to disable cache on editor pages
	 */
	public function disable_content_cache() {
		/**
		 * This constant is used by many cache plugins
		 */
		defined( 'DONOTCACHEPAGE' ) || define( 'DONOTCACHEPAGE', true );

		$architect_query_string = 'tve=true';
		switch ( tve_dash_detect_cache_plugin() ) {
			case 'wp-super-cache':
				global $wp_cache_config_file, $cache_rejected_uri;
				if ( isset( $wp_cache_config_file ) ) {
					if ( ! is_array( $cache_rejected_uri ) ) {
						$cache_rejected_uri = [];
					}

					if ( ! in_array( $architect_query_string, $cache_rejected_uri, true ) && function_exists( 'wp_cache_sanitize_value' ) && function_exists( 'wp_cache_replace_line' ) ) {
						$cache_rejected_uri[] = $architect_query_string;
						$cache_string         = implode( ' ', $cache_rejected_uri );
						$text                 = wp_cache_sanitize_value( str_replace( '\\\\', '\\', $cache_string ), $cache_rejected_uri );
						wp_cache_replace_line( '^ *\$cache_rejected_uri', "\$cache_rejected_uri = $text;", $wp_cache_config_file );
					}
				}


				break;
			case 'w3-total-cache':
				if ( class_exists( 'W3_Config' ) ) {
					$cfg       = new W3_Config();
					$cfg_array = $cfg->get_array( 'pgcache.reject.custom' );

					if ( ! in_array( $architect_query_string, $cfg_array, true ) ) {
						$cfg_array[] = $architect_query_string;
						$cfg->set( 'pgcache.reject.custom', $cfg_array );
						$cfg->save();
					}
				}
				break;
			case 'wp-fastest-cache':
				$architect_query_string =
					array(
						'prefix'  => 'contain',
						'content' => 'tve=true',
						'type'    => 'page',

					);
				if ( $cache_option = json_decode( get_option( 'WpFastestCacheExclude' ), true ) ) {
					if ( ! in_array( $architect_query_string, $cache_option, true ) ) {
						$cache_option[] = $architect_query_string;
						update_option( 'WpFastestCacheExclude', json_encode( $cache_option ) );
					}
				} else {
					add_option( 'WpFastestCacheExclude', json_encode( [ $architect_query_string ] ), null, 'yes' );
				}
				break;
			case 'litespeed-cache':
				if ( $cache_option = get_option( 'litespeed-excludes_uri' ) ) {
					if ( strpos( $cache_option, $architect_query_string ) === false ) {
						$cache_option .= "\n{$architect_query_string}";
						update_option( 'litespeed-excludes_uri', $cache_option );
					}
				} else {
					add_option( 'litespeed-excludes_uri', $architect_query_string, null, 'yes' );
				}
				break;
			default:
		}

	}

	/**
	 * Add architect license attributes and overwrite them from other plugins
	 *
	 * @return bool
	 */
	public function lcns_attributes( $post_type = null ) {
		if ( empty ( $post_type ) ) {
			$post_type = get_post_type();
		}

		return apply_filters( 'tve_lcns_attributes', [
			'source'        => 'tcb',
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tcb' ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tcb' ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'tcb' ),
			'link'          => tvd_get_individual_plugin_license_link( 'tcb' ),
			'product'       => 'Thrive Architect',
		], $post_type );
	}
}
