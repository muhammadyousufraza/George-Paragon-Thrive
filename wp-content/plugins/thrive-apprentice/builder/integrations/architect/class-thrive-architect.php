<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use Thrive\Theme\AMP\Settings as AMP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Architect
 */
class Thrive_Architect {

	public static function init() {

		defined( 'LIGHT_ARCHITECT' ) || define( 'LIGHT_ARCHITECT', ! defined( 'TVE_IN_ARCHITECT' ) );

		static::includes();
		add_action( 'init', [ static::class, 'register_post_types' ] );

		static::filters();

		static::actions();
	}

	/**
	 * Check if we have the light Architect or the full one
	 *
	 * @return bool
	 */
	public static function is_light() {
		return LIGHT_ARCHITECT;
	}

	private static function includes() {
		/* in certain builds the following file might not exist */
		if ( file_exists( THEME_PATH . '/architect/external-architect.php' ) ) {
			/* set a custom architect URL to allow for symlink-based setups */
			$current_architect_url = get_stylesheet_directory_uri() . '/architect/';
			include THEME_PATH . '/architect/external-architect.php';
		}

		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-template.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-architect-utils.php';

		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-palette.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-section.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-hf-section.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-layout.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-dynamic-list-helper.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-list.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-content-switch.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-demo-content.php';

		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-skin.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-skin-taxonomy.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-default-data.php';

		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-lightspeed.php';
	}

	private static function actions() {
		add_action( 'after_setup_theme', [ __CLASS__, 'after_setup_theme' ] );

		add_action( 'updated_' . THRIVE_TEMPLATE . '_meta', [ __CLASS__, 'updated_template_style' ], 10, 3 );

		/* Enqueue scripts needed in the editor */
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'editor_enqueue_scripts' ], 9 );

		add_action( 'tcb_main_frame_enqueue', [ __CLASS__, 'tcb_main_frame_enqueue' ], 9 );

		/* register rest routes used by Architect */
		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );

		add_action( 'parse_request', [ __CLASS__, 'parse_request' ] );

		add_action( 'tcb_output_components', [ __CLASS__, 'tcb_output_components' ] );

		add_action( 'wp_print_footer_scripts', [ __CLASS__, 'wp_print_footer_scripts' ], 9 );

		add_action( 'tcb_editor_enqueue_scripts', [ __CLASS__, 'tcb_editor_enqueue_scripts' ] );

		add_action( 'tcb_editor_iframe_after', [ __CLASS__, 'tcb_add_editor_svgs' ] );

		add_action( 'tcb_sidebar_extra_links', [ __CLASS__, 'add_extra_links' ] );

		add_action( 'tcb_cpanel_top_content', [ __CLASS__, 'add_top_content' ] );

		add_action( 'tcb_sidebar_elements_notice', [ __CLASS__, 'add_tar_light_notice' ] );

		add_action( 'tcb_output_extra_editor_svg', [ __CLASS__, 'tcb_add_extra_svg_in_iframe' ] );

		add_action( 'tcb_ajax_save_post', [ __CLASS__, 'tcb_ajax_save_post' ], 10, 2 );

		add_action( 'pre_delete_term', [ __CLASS__, 'pre_delete_term' ], 10, 2 );

		add_action( 'updated_postmeta', [ __CLASS__, 'updated_postmeta' ], 10, 4 );

		add_action( 'tcb_ajax_before_cloud_content_template_download', [ __CLASS__, 'set_global_post_before_cloud_ajax' ] );

		add_action( 'pre_get_posts', [ __CLASS__, 'search_filter_post_types' ] );

		add_action( 'tcb_set_lp_cloud_template', [ __CLASS__, 'theme_set_cloud_landing_page' ], 10, 2 );

		add_action( 'tcb_extra_postlist_links', [ __CLASS__, 'add_extra_dynamic_links' ] );

		add_action( 'tcb_extra_landing_page_lightbox_set_icons', [ __CLASS__, 'add_extra_lp_lightbox_set_icons' ] );
		add_action( 'tcb_extra_landing_page_lightbox_icons', [ __CLASS__, 'add_extra_lp_lightbox_icons' ] );

		add_action( 'tve_after_load_custom_css', [ __CLASS__, 'after_load_custom_css' ] );

		add_action( 'rest_delete_tcb_symbol', [ __CLASS__, 'unlink_hf_from_templates' ], 10, 1 );

		add_action( 'tcb_before_get_content_template', [ __CLASS__, 'change_section_menu' ], 10, 3 );

		add_action( 'thrive_theme_template_copied_data', [ 'Thrive_Post_List', 'thrive_theme_template_copied_data' ], 10, 2 );

		add_action( 'wp', static function () {
			/* We still need some of the shortcodes to modify content inside template editing*/
			if ( Thrive_Utils::is_inner_frame() ) {
				remove_filter( 'tcb_clean_frontend_content', 'tcb_clean_frontend_content' );
			}
		}, PHP_INT_MAX );

		if ( static::is_light() ) {
			/* Enqueue scripts needed in frontend */
			add_action( 'wp_enqueue_scripts', 'tve_frontend_enqueue_scripts', 9 );

			/* load custom css for custom post types edited with architect */
			add_action( 'wp_head', 'tve_load_custom_css', 100, 0 );
		}

		if ( Thrive_Theme::is_active() ) {
			add_action( 'tcb_get_extra_global_variables', [ __CLASS__, 'output_skin_variables' ] );
		}
	}

	private static function filters() {
		/* Include what post types should be editable with Theme Architect  */
		add_filter( 'tcb_post_types', [ __CLASS__, 'tcb_post_types' ] );

		add_filter( 'tcb_allow_landing_page_set_data', [ __CLASS__, 'tcb_allow_smart_lp_set_data' ], 10, 2 );

		add_filter( 'tcb_get_page_palettes', [ __CLASS__, 'tcb_get_smart_lp_palettes' ], 10, 2 );

		add_filter( 'tcb_get_page_variables', [ __CLASS__, 'tcb_get_smart_lp_variables' ], 10, 3 );

		add_filter( 'tcb_lp_transfer_meta', [ __CLASS__, 'tcb_lp_transfer_meta' ], 10, 2 );

		/* Filter the layout to be displayed when editing a template with Theme Architect */
		add_filter( 'tcb_custom_post_layouts', [ __CLASS__, 'tcb_custom_post_layouts' ], 10, 3 );

		/* Elements to be displayed  */
		add_filter( 'tcb_remove_instances', [ __CLASS__, 'tcb_remove_instances' ], 100 );

		add_filter( 'tcb_element_instances', [ __CLASS__, 'add_theme_element_instance' ], 10, 2 );

		/* add extra classes for body */
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );

		add_filter( 'tcb_hide_post_list_element', [ __CLASS__, 'tcb_hide_post_list_element' ] );

		/* parse inner frame uri */
		add_filter( 'tcb_frame_request_uri', [ __CLASS__, 'tcb_frame_request_uri' ] );

		add_filter( 'tcb_main_frame_localize', [ __CLASS__, 'tcb_main_frame_localize' ] );

		add_filter( 'tve_main_js_dependencies', [ __CLASS__, 'tve_main_js_dependencies' ] );

		add_filter( 'tcb_backbone_templates', [ __CLASS__, 'tcb_backbone_templates' ] );

		add_filter( 'tcb_divider_prefix', [ __CLASS__, 'tcb_divider_prefix' ], 10, 2 );

		add_filter( 'tcb_overwrite_scripts_enqueue', '__return_true' );

		add_filter( 'tcb_overwrite_event_scripts_enqueue', [ __CLASS__, 'tcb_overwrite_event_scripts_enqueue' ] );

		add_filter( 'tcb_categories_order', [ __CLASS__, 'tcb_categories_order' ] );

		add_filter( 'preview_post_link', [ __CLASS__, 'tcb_frame_request_uri' ] );

		add_filter( 'thrive_post_attributes', [ __CLASS__, 'thrive_post_attributes' ], 10, 2 );

		add_filter( 'post_class', [ __CLASS__, 'post_class' ] );

		add_filter( 'tcb_close_url', [ __CLASS__, 'tcb_close_url' ] );

		add_filter( 'architect.branding', [ __CLASS__, 'architect_branding' ], 10, 2 );

		add_filter( 'tcb_can_use_landing_pages', [ __CLASS__, 'can_use_landing_pages' ] );

		add_filter( 'tcb_modal_templates', [ __CLASS__, 'tcb_modal_templates' ] );

		add_filter( 'tcb_global_styles_before_save', [ __CLASS__, 'assign_global_styles' ], 10, 3 );

		add_filter( 'tcb_global_styles', [ __CLASS__, 'tcb_global_styles' ], 10, 2 );

		add_filter( 'tcb_post_list_query_args', [ __CLASS__, 'change_featured_list_args' ], 10, 2 );

		add_filter( 'tcb_cloud_templates', [ __CLASS__, 'tcb_cloud_templates' ], 10, 2 );

		add_filter( 'tcb_landing_page_templates_list', [ __CLASS__, 'tcb_landing_page_templates_list' ], 10, 2 );

		add_filter( 'tcb_editor_title', [ __CLASS__, 'tcb_editor_title' ] );

		add_filter( 'tcb_localize_existing_post_list', [ __CLASS__, 'tcb_localize_existing_post_list' ], 10, 2 );

		if ( Thrive_Theme::is_active() ) {
			/* extends the config of TCB_Post_Element from Architect by adding extra components */
			add_filter( 'tcb_post_element_extend_config', [ __CLASS__, 'tcb_post_element_extend_config' ] );
			/* extends the config of TCB_Landing_Page_Element */
			add_filter( 'tcb_lp_element_extend_config', [ __CLASS__, 'tcb_lp_element_extend_config' ] );
			/**
			 * Adds the theme block set to the blocks cloud call if the requirements are met
			 */
			add_filter( 'tcb_get_special_blocks_set', [ __CLASS__, 'tcb_get_special_blocks_set' ] );

			/* always include theme css */
			add_filter( 'tcb_theme_dependency', '__return_false' );

			add_filter( 'tve_get_seo_content', [ __CLASS__, 'tve_get_seo_content' ], 10, 2 );
		}

		add_filter( 'tcb_allow_central_style_panel', [ __CLASS__, 'tcb_skin_allow_central_style_panel' ] );
		add_filter( 'tcb_has_central_style_panel', [ __CLASS__, 'tcb_skin_allow_central_style_panel' ] );

		add_filter( 'tcb.template_path', [ __CLASS__, 'tcb_change_template_path' ], 10, 4 );


		add_filter( 'tcb_editor_javascript_params', [ __CLASS__, 'tcb_editor_localize' ] );

		add_filter( 'tcb_js_translate', [ __CLASS__, 'tcb_js_translate' ] );

		add_filter( 'tcb_remove_theme_css', [ __CLASS__, 'tcb_remove_theme_css' ], 10, 2 );

		add_filter( 'tcb_ajax_response_load_content_template', [ __CLASS__, 'tcb_ajax_response_load_content_template' ], 10, 2 );

		/* Show TAR button on the post edit page when gutenberg is used */
		add_filter( 'tcb_gutenberg_switch', [ __CLASS__, 'gutenberg_switch' ], 11 );

		add_filter( 'tcb_post_breadcrumb_data', [ __CLASS__, 'post_breadcrumb_data' ] );

		add_filter( 'get_search_query', [ __CLASS__, 'alter_search_element_query_string' ] );

		/* populate sections when editing a theme template */
		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'add_lazy_load_data' ], 10, 2 );

		add_filter( 'tcb_lazy_load_dynamic_colors', [ __CLASS__, 'add_lazy_load_dynamic_colors' ], 10, 2 );

		add_filter( 'tcb_post_element_name', [ __CLASS__, 'post_element_name' ] );

		add_filter( 'tcb_post_list.disable_related', [ 'Thrive_Post_List', 'disable_query_builder_related_posts' ] );

		add_filter( 'tcb_post_list.related_text', [ 'Thrive_Post_List', 'query_builder_related_posts_text' ] );

		add_filter( 'tcb_post_list.show_exclude', [ 'Thrive_Post_List', 'query_builder_show_exclude_current_post' ] );

		add_filter( 'tcb_post_list_pagination_types', [ 'Thrive_Post_List', 'add_pagination_types' ] );

		add_filter( 'tcb_element_post_config', [ 'Thrive_Utils', 'tcb_element_post_config' ] );

		add_filter( 'tcb_element_footer_config', [ 'Thrive_Utils', 'tcb_element_hf_config' ] );
		add_filter( 'tcb_element_header_config', [ 'Thrive_Utils', 'tcb_element_hf_config' ] );

		add_filter( 'thrive_theme_template_content', [ __CLASS__, 'thrive_theme_template_content' ], 10, 2 );

		/**
		 * Modify default style provider - TTB overwrites TAr's style provider
		 */
		add_filter( 'tcb_default_style_provider_class', function () {
			require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-style-provider.php';

			return Thrive_Theme_Style_Provider::class;
		} );

		/**
		 * Adds extra functionality to page wizard when TTB is active
		 */
		add_filter( 'tcb_get_page_wizard_items', [ __CLASS__, 'tcb_add_page_wizard_items' ] );

		add_filter( 'tcb_cloud_request_params', [ __CLASS__, 'add_ttb_skin_tag_to_params' ] );

		add_filter( 'tcb_get_cloud_templates_default_args', [ __CLASS__, 'add_ttb_skin_tag_to_params' ] );

		/**
		 * When editing theme typography, default styles should be output in a reachable style node
		 */
		add_filter( 'tcb_output_default_styles', static function ( $output_styles ) {
			if ( Thrive_Utils::is_theme_typography() ) {
				$output_styles = false;
			}

			return $output_styles;
		} );

		/**
		 * Hook into the page fonts area and include fonts used in the current template
		 */
		add_filter( 'tcb_css_imports', static function ( $imports ) {
			/* current template + all sections and header / footer */
			return array_merge( $imports, thrive_template()->get_css_imports() );
		} );

		/**
		 * Print default Theme styles in landing pages if the "disable theme CSS" option has NOT been selected
		 */
		add_filter( 'tcb_should_print_unified_styles', [ __CLASS__, 'tcb_should_print_unified_styles' ] );

		/* All editing landing pages for all custom posts */
		add_filter( 'tcb_allow_landing_page_edit', static function ( $allow ) {
			if ( ! $allow && Thrive_Theme::is_active() ) {
				$allow = is_singular();
			}

			return $allow;
		} );

		/* Makes sure that when TTB is active, there are no left-over cached transients from TAr for landing pages */
		add_filter( 'tve_cloud_templates_transient_name', static function ( $transient_name ) {
			return $transient_name . '_ttb_' . thrive_skin()->ID;
		} );

		/* while in editor, don't let thrive leads shortcodes render */
		add_filter( 'tve_leads_allow_shortcodes', static function () {
			return ! is_editor_page_raw( true );
		} );

		/**
		 * Post visibility options blacklist
		 */
		add_filter( 'tcb_post_visibility_options_availability', static function ( $post_types ) {
			$post_types[] = THRIVE_TEMPLATE;

			return $post_types;
		} );

		add_filter( 'tcb_can_import_content', [ __CLASS__, 'tcb_prevent_content_export' ], 10, 2 );
		add_filter( 'tcb_can_export_content', [ __CLASS__, 'tcb_prevent_content_export' ], 10, 2 );
		add_filter( 'tve_aut_banned_post_types', [ __CLASS__, 'tcb_hide_templates' ] );

		/**
		 * Overwrite tcb attributes on ovation post types
		 */
		add_filter( 'tve_lcns_attributes', static function ( $attributes, $post_type ) {
			$tag = 'ttb';
			if ( in_array( $post_type, [
					THRIVE_TEMPLATE,
					THRIVE_SECTION,
					THRIVE_LAYOUT,
					THRIVE_TYPOGRAPHY,
					SKIN_TAXONOMY,
				], true ) || ( in_array( $post_type, [ 'post', 'page', 'tcb_symbol' ], true ) && Thrive_Architect::is_light() ) ) {
				return [
					'source'        => $tag,
					'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( $tag ),
					'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $tag ),
					'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( $tag ),
					'link'          => tvd_get_individual_plugin_license_link( $tag ),
					'product'       => 'Thrive Theme Builder',
				];
			}

			return $attributes;
		}, 10, 2 );

		if ( static::is_light() ) {
			add_filter( 'tve_landing_page_content', 'tve_editor_content' );

			/* for the case when he only has the theme license */
			add_filter( 'tcb_skip_license_check', '__return_true' );
		}
	}

	/**
	 * Prevent import/export of content on TTB templates
	 *
	 * @param $allow
	 * @param $post
	 *
	 * @return false|mixed
	 */
	public static function tcb_prevent_content_export( $allow, $post ) {
		if ( in_array( $post->post_type, [ THRIVE_TEMPLATE, THRIVE_TYPOGRAPHY ], true ) ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Data related to template should not be displayed since the frontend data would be based on actual post
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function tcb_hide_templates( $post_types ) {
		return array_merge( $post_types, [ THRIVE_TEMPLATE, THRIVE_TYPOGRAPHY ] );
	}

	/**
	 * Fired when variables are being fetched for a smart landing page
	 *
	 * For a landing page associated with the theme, the variables must come from the theme itself
	 *
	 * @param array            $page_variables
	 * @param TCB_Landing_Page $landing_page
	 * @param string           $key
	 *
	 * @return array
	 */
	public static function tcb_get_smart_lp_variables( $page_variables = [], $landing_page = null, $key = '' ) {
		if ( ! empty( $landing_page ) && ! empty( $landing_page->meta( 'theme_skin_tag' ) ) ) {
			$skin_variables = thrive_skin()->get_variables();
			if ( ! empty( $skin_variables ) ) {
				if ( 'colours' === $key ) {
					$key = 'colors';
				}
				$page_variables = $skin_variables[ $key ];
			}
		}

		return $page_variables;
	}

	/**
	 * Fired when palettes are being fetched for a smart landing page
	 *
	 * For a landing page associated with the theme, the palettes must come from the theme itself
	 *
	 * @param array            $page_palettes
	 * @param TCB_Landing_Page $landing_page
	 *
	 * @return array
	 */
	public static function tcb_get_smart_lp_palettes( $page_palettes = [], $landing_page = null ) {
		if ( ! empty( $landing_page ) && ! empty( $landing_page->meta( 'theme_skin_tag' ) ) ) {
			$page_palettes = thrive_skin()->get_palettes();
		}

		return $page_palettes;
	}

	/**
	 * Fired when Landing Page set data is being set.
	 *
	 * For Landing Pages associated with the theme, the set data must come from the theme not form the page itself
	 *
	 * @param bool             $return
	 * @param TCB_Landing_Page $landing_page
	 *
	 * @return bool
	 */
	public static function tcb_allow_smart_lp_set_data( $return = true, $landing_page = null ) {
		if ( ! empty( $landing_page ) && ! empty( $landing_page->meta( 'theme_skin_tag' ) ) ) {
			$return = false;
		}

		return $return;
	}

	/**
	 * When exporting a skin landingpage from the end user website, include the skin_tag into the lp.json configuration file
	 *
	 * @param array   $config
	 * @param integer $post_id
	 *
	 * @return array
	 */
	public static function tcb_lp_transfer_meta( $config, $post_id ) {

		if ( tve_post_is_landing_page( $post_id ) && Thrive_Utils::is_end_user_site() ) {

			$landing_page = tcb_landing_page( $post_id );

			if ( ! empty( $landing_page->meta( 'theme_skin_tag' ) ) ) {
				$config['skin_tag'] = $landing_page->meta( 'theme_skin_tag' );
			}
		}

		return $config;
	}

	/**
	 * Fired when a landing page is set from cloud
	 *
	 * If a landing page is associated to a skin, it sets the skin tag inside post meta
	 *
	 * @param TCB_Landing_Page $tcb_landing_page
	 * @param array            $config
	 */
	public static function theme_set_cloud_landing_page( $tcb_landing_page, $config ) {

		$tcb_landing_page->meta_delete( 'theme_skin_tag' );

		if ( ! empty( $config['skin_tag'] ) ) {

			$tcb_landing_page->meta( 'theme_skin_tag', $config['skin_tag'] );

			if ( ! empty( $config['silo'] ) ) {
				$tcb_landing_page->meta( '_tve_header', thrive_skin()->get_default_data( THRIVE_HEADER_SECTION ) );
				$tcb_landing_page->meta( '_tve_footer', thrive_skin()->get_default_data( THRIVE_FOOTER_SECTION ) );
			}
		}
	}

	/**
	 * Add a theme instance to the instances from TAR
	 *
	 * @param $instances
	 * @param $element_type
	 *
	 * @return mixed
	 */
	public static function add_theme_element_instance( $instances, $element_type ) {

		if ( ! empty( $element_type ) && empty( Thrive_Architect_Utils::$theme_elements[ $element_type ] ) ) {
			Thrive_Architect_Utils::set_theme_element( $element_type );
		}

		if ( ! empty( Thrive_Architect_Utils::$theme_elements[ $element_type ] ) ) {
			$instances[ $element_type ] = Thrive_Architect_Utils::$theme_elements[ $element_type ];
		}

		Thrive_Architect_Utils::overwrite_elements( $instances );

		return $instances;
	}

	/**
	 * Register post types used in theme
	 */
	public static function register_post_types() {
		Thrive_Template::register_post_type();
		Thrive_Section::register_post_type();
		Thrive_Layout::register_post_type();
		Thrive_Typography::register_post_type();
	}

	/**
	 * Actions done after the theme has been loaded. We load some elements only after just to make sure the dependencies are loaded
	 */
	public static function after_setup_theme() {
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-post-list.php';
		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-tcb-pagination-infinite-scroll.php';

		add_action( 'pre_get_posts', [ 'Thrive_Post_List', 'list_pre_get_posts' ], 10, 1 );

		Thrive_Theme_Lightspeed::init();
	}

	/**
	 * Depending on the template we're editing, load the layout for Architect
	 *
	 * @param $layouts
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function tcb_custom_post_layouts( $layouts, $post_id, $post_type ) {

		switch ( $post_type ) {
			case THRIVE_TEMPLATE:
				$layout = thrive_template()->editor_layout();
				break;

			case THRIVE_TYPOGRAPHY:
				$layout = thrive_typography()->prepare_layout();
				break;

			default:
				$layout = null;
		}

		if ( $layout ) {
			/* added here to prevent google indexing */
			if ( ! is_user_logged_in() ) {
				wp_redirect( home_url() );
				exit();
			}

			$layouts['template'] = $layout;
		}

		return $layouts;
	}

	/**
	 * Set Architect to edit the theme page templates
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public static function tcb_post_types( $post_types ) {

		if ( static::is_light() ) {
			if ( ! isset( $post_types['force_whitelist'] ) ) {
				$post_types['force_whitelist'] = [];
			}

			$post_types['force_whitelist'][] = THRIVE_TYPOGRAPHY;
			$post_types['force_whitelist'][] = THRIVE_TEMPLATE;

			if ( Thrive_Theme::is_active() ) {
				$post_types['force_whitelist'] = array_merge(
					$post_types['force_whitelist'],
					[ Thrive_Demo_Content::POST_TYPE, Thrive_Demo_Content::PAGE_TYPE ],
					array_keys( Thrive_Utils::get_content_types() )
				);
			}

			/* we do this in order for tve_is_post_type_editable() to return true (post_type of 404 is null) */
			if ( thrive_template()->is404() ) {
				$post_types['force_whitelist'][] = null;
			}
		}

		return $post_types;
	}

	/**
	 * Depending on the template we're editing, load the elements inside Architect
	 *
	 * @param $used_elements
	 *
	 * @return array
	 */
	public static function tcb_remove_instances( $used_elements ) {
		$elements_to_hide = [];

		/* first, add the elements used by other elements (example: post list, pagination button ) */
		$inherited_theme_elements = Thrive_Architect_Utils::get_architect_theme_elements( ARCHITECT_INTEGRATION_PATH . '/classes/inherited-elements' );

		$only_theme_elements = Thrive_Architect_Utils::get_architect_theme_elements();
		$only_theme_elements = array_merge( $only_theme_elements, $inherited_theme_elements );

		if ( Thrive_Utils::allow_theme_scripts() ) {
			/* these are replaced by theme elements (more tag is no longer needed because we have a read more element) */
			$elements_to_hide = [ 'moretag', 'postgrid' ];

			/* display only the elements needed for the template editing */
			$used_elements = array_merge( $used_elements, $only_theme_elements );

			if ( thrive_template()->is_singular() ) {
				unset( $used_elements['blog_list'] );
			}
		}

		/* if the user doesn't have full TAR, add some elements to the list of elements to hide */
		if ( static::is_light() ) {
			$elements_to_hide = array_merge( $elements_to_hide, Thrive_Defaults::unavailable_elements() );
		}

		foreach ( $elements_to_hide as $tag ) {
			unset( $used_elements[ $tag ] );
		}

		Thrive_Architect_Utils::$theme_elements = $used_elements;

		$used_elements = Thrive_Typography::tcb_remove_instances( $used_elements );

		return $used_elements;
	}

	/**
	 * Enqueue editor script
	 */
	public static function editor_enqueue_scripts() {
		if ( Thrive_Utils::is_inner_frame() ) {
			/* template editor js and css */
			tve_dash_enqueue_script( 'thrive-theme-editor', THEME_ASSETS_URL . '/editor.min.js', [ 'jquery', 'underscore' ], THEME_VERSION );
			wp_enqueue_style( 'thrive-theme-editor-styles', THEME_ASSETS_URL . '/editor-styles.css', [], THEME_VERSION );

			wp_localize_script( 'thrive-theme-editor', 'thrive_page_params', apply_filters( 'theme_editor_page_params_localize', static::localization_params() ) );
		}
	}

	/**
	 * Localize data in the architect editor inner frame.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function tcb_editor_localize( $data ) {
		/* Only localize this data if we're on a TAr post or page */
		if ( Thrive_Utils::is_architect_editor() ) {
			$data['theme'] = [
				'template_data' => [
					'url'  => tcb_get_editor_url( thrive_template()->ID ),
					'name' => thrive_template()->title(),
				],
			];

			/* Get template section visibility info. We can't do this in the main frame because we don't have the template data there yet. */
			$template_visibility = [];

			foreach ( Thrive_Post::get_visibility_config( 'sections' ) as $type => $config ) {
				/* calculate the 'real' visibility value for the template ( without checking page flags, etc ) */
				$template_visibility[ $type ] = Thrive_Utils::get_template_visibility( $type ) ? 'show' : 'hide';
			}

			/* add the template section visibility data to the inner frame localize */
			$data['theme']['template_visibility'] = $template_visibility;
		}

		return $data;
	}

	/**
	 * Main frame localize parameters
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function tcb_main_frame_localize( $data ) {
		$needs_localization = Thrive_Theme::is_active() || Thrive_Utils::is_theme_template() || Thrive_Utils::is_theme_typography();
		/**
		 * Filter whether or not to include localization data from TTB
		 *
		 * @param bool $needs_localization
		 */
		$needs_localization = apply_filters( 'thrive_theme_needs_localization', $needs_localization );
		if ( ! $needs_localization ) {
			return $data;
		}
		$template = thrive_template();
		$skin     = thrive_skin();

		$data['theme'] = [
			'sidebars'              => Thrive_Utils::get_sidebars(),
			'demo_content_url'      => Thrive_Demo_Content::url(),
			'demo_content_preview'  => Thrive_Demo_Content::url( true ),
			'routes'                => Thrive_Utils::get_rest_routes(),
			'element_selectors'     => Thrive_Architect_Utils::get_architect_elements_selector(),
			'template'              => $template->export(),
			'layouts'               => $skin->get_layouts( 'array', [ 'sidebar_on_left', 'hide_sidebar', 'content_width' ] ),
			'post_types'            => [
				'all' => array_merge( Thrive_Utils::get_post_types(), [ 'attachment' => 'Media' ] ),
			],
			'comments_form'         => [
				'error_defaults' => thrive_theme_comments::get_comment_form_error_labels(),
			],
			'is_theme_template'     => Thrive_Utils::is_theme_template(),
			'content_switch'        => thrive_content_switch()->get_localized_data(),
			'taxonomies'            => get_object_taxonomies( $template->meta( THRIVE_SECONDARY_TEMPLATE ), 'object' ),
			'dynamic_list_types'    => Thrive_Dynamic_Styled_List_Element::get_list_type_options(),
			'skin_id'               => $skin->ID,
			'skin_tag'              => $skin->get_tag(),
			'skin_styles'           => $skin->get_global_styles(),
			'logo_url'              => Thrive_Branding::get_logo_url( site_url() ),
			'templates_layouts_map' => $skin->get_layouts_templates_map(),
			'breadcrumbs_labels'    => $skin->get_breadcrumbs_labels(),
			'compatibility'         => self::version_compatibility(),
		];

		if ( ! empty( $data['landing_page'] ) && ! empty( tcb_landing_page( $data['post']->ID )->meta( 'theme_skin_tag' ) ) ) {
			$skin_variables = $skin->get_variables();

			$data['colors']['lp_set_prefix'] = THEME_SKIN_COLOR_VARIABLE_PREFIX;
			if ( thrive_palettes()->has_palettes() ) {
				$data['colors']['templates'] = array_values( thrive_palettes()->get_palette() );
			} else {
				$data['colors']['templates'] = empty( $skin_variables['colors'] ) ? [] : $skin_variables['colors'];
			}
			$data['gradients']['templates'] = empty( $skin_variables['gradients'] ) ? [] : $skin_variables['gradients'];
			$data['template_palettes']      = $skin->get_palettes();
			$data['external_palettes']      = 1;
		} elseif ( ! empty( $data['theme']['is_theme_template'] ) ) {
			$data['external_palettes'] = 1;
		}

		/**
		 * Include The skin Variables only for the end user. So not for Theme Builder Site
		 */
		if ( Thrive_Utils::is_end_user_site() ) {
			$data['theme'] = array_merge( $data['theme'], [
				'skin_palettes'  => $skin->get_palettes(),
				'skin_variables' => $skin->get_variables( true ),
			] );

			if ( thrive_palettes()->has_palettes() ) {
				$data['theme']['palette_colors'] = thrive_palettes()->get_palette();
			}
		}

		/* only localize this data if we're on a TAr post or page */
		if ( Thrive_Utils::is_architect_editor() && array_key_exists( get_post_type(), Thrive_Utils::get_content_types() ) ) {
			$thrive_post = thrive_post();

			$data['theme'] = array_merge( $data['theme'], [
				'element_visibility'        => [
					'config' => Thrive_Post::get_visibility_config(),
					'values' => $thrive_post->localize_visibility_meta(),
				],
				'amp_status'                => thrive_post()->is_amp_disabled() ? 'disabled' : '',
				/* add a list of template IDs, names, and other information needed in JS for templates */
				'templates'                 => $thrive_post->get_all_templates(),
				'post_format_options_video' => thrive_video_post_format( Thrive_Video_Post_Format_Main::get_type() )->get_video_options(),
				'post_format_options_audio' => thrive_audio_post_format( Thrive_Audio_Post_Format_Main::get_type() )->get_audio_options(),
				'post_featured_image'       => thrive_image_post_format()->get_image(),
				'post_formats'              => array_merge( [ THRIVE_STANDARD_POST_FORMAT ], Thrive_Theme::post_formats() ),
				'scripts'                   => tcb_scripts()->get_all(),
			] );
		}

		if ( ! empty( $data['landing_page'] ) || get_post_type() === 'page' ) {
			/* localize a default header and footer to use in a landing page */
			$data['default_header_id'] = thrive_skin()->get_default_data( 'header' );
			$data['default_footer_id'] = thrive_skin()->get_default_data( 'footer' );
		}

		return $data;
	}

	/**
	 * Load scripts for main frame
	 */
	public static function tcb_main_frame_enqueue() {
		if ( Thrive_Utils::allow_theme_scripts() ) {
			tve_dash_enqueue_script( 'thrive-theme-main', THEME_ASSETS_URL . '/main.min.js', [ 'jquery', 'underscore' ], THEME_VERSION );
		}

		if ( Thrive_Utils::is_theme_template() ) {
			wp_enqueue_style( 'thrive-theme-main', THEME_ASSETS_URL . '/main-frame.css', [], THEME_VERSION );
		}

		if ( Thrive_Utils::is_theme_typography() ) {
			tve_dash_enqueue_script( 'thrive-theme-typography', THEME_ASSETS_URL . '/typography.min.js', [ 'jquery', 'underscore' ], THEME_VERSION );
		}

		/* enqueue this JS & CSS only on architect posts and pages */
		if ( array_key_exists( get_post_type(), Thrive_Utils::get_content_types() ) && Thrive_Utils::is_architect_editor() ) {
			tve_dash_enqueue_script( 'thrive-theme-tar-editor', THEME_ASSETS_URL . '/tar-editor.min.js', [ 'jquery', 'underscore' ], THEME_VERSION );

			/* some main frame CSS only for the architect editor */
			wp_enqueue_style( 'thrive-editor-tar-main-frame', THEME_ASSETS_URL . '/editor-main-frame.css', [], THEME_VERSION );
		}
	}

	/**
	 * Set the theme js file a dependency for the main file so it will load before
	 *
	 * @param $dependencies
	 *
	 * @return array
	 */
	public static function tve_main_js_dependencies( $dependencies ) {
		if ( Thrive_Utils::allow_theme_scripts() ) {
			$dependencies[] = 'thrive-theme-main';
		}

		if ( Thrive_Utils::is_theme_typography() ) {
			$dependencies[] = 'thrive-theme-typography';
		}

		if ( Thrive_Utils::is_architect_editor() ) {
			$dependencies[] = 'thrive-theme-tar-editor';
		}

		return $dependencies;
	}

	/**
	 * Params needed in the editor js
	 *
	 * @return array
	 */
	private static function localization_params() {
		global $post;

		return [
			'ID'                   => get_the_ID(),
			'query_vars'           => Thrive_Utils::get_query_vars(),
			'body_class'           => thrive_template()->body_class( false, 'string', true ),
			'posts'                => [],
			'post_image'           => [
				'featured' => THRIVE_FEATURED_IMAGE_PLACEHOLDER,
				'author'   => THRIVE_AUTHOR_IMAGE_PLACEHOLDER,
			],
			'featured_image_sizes' => Thrive_Featured_Image::get_image_sizes( get_option( THRIVE_FEATURED_IMAGE_OPTION ) ),
			'default_sizes'        => array_keys( Thrive_Featured_Image::filter_available_sizes() ),
			'social_urls'          => null === $post ? '' : get_the_author_meta( THRIVE_SOCIAL_OPTION_NAME, $post->post_author ),
			'comments'             => Thrive_Theme_Comments::get_comments_meta(),
			'is_demo_content'      => Thrive_Demo_Content::on_demo_content_page(),
			'archive_description'  => thrive_template()->is_archive() ? Thrive_Shortcodes::taxonomy_term_description() : '',
			'taxonomy'             => thrive_template()->is_archive() ? [
				'thrive_archive_name'        => Thrive_Shortcodes::archive_name(),
				'thrive_archive_description' => Thrive_Shortcodes::archive_description(),
				'thrive_archive_parent_name' => Thrive_Shortcodes::archive_parent_name(),
			] : [],
		];
	}

	/**
	 * For thrive templates, add a specific body class so we can better handle css
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public static function body_class( $classes ) {
		if ( Thrive_Utils::is_inner_frame() ) {
			$classes[] = 'tve_editor_page';
		}

		return $classes;
	}

	/**
	 * Register REST Routes for Architect
	 */
	public static function rest_api_init() {
		$dir = ARCHITECT_INTEGRATION_PATH . '/classes/endpoints/';
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( $file, [ '.', '..' ] ) ) {
				continue;
			}

			require_once $dir . $file;
		}

		Thrive_Post_Rest::register_routes();
		Thrive_Layout_Rest::register_routes();
		Thrive_Sidebar_Rest::register_routes();
		Thrive_Section_Rest::register_routes();
		Thrive_Options_Rest::register_routes();
		Thrive_Plugins_Rest::register_routes();
		Thrive_Templates_Rest::register_routes();
		Thrive_Dynamic_List_Rest::register_routes();

		if ( Thrive_Theme::is_active() ) {
			Thrive_Skins_Rest::instance();
			Thrive_Palette_Rest::register_routes();
			Thrive_Amp_Rest::register_routes();
			Thrive_Wizard_REST::register_routes();
			Thrive_Demo_Content_Rest::register_routes();
		}

		Thrive_Typography::rest_api_init();

		Thrive_Demo_Content::init( true );
	}

	/**
	 * Parse tcb inner frame url. Add Theme Editor Flag with the template id
	 *
	 * @param $uri
	 *
	 * @return string
	 */
	public static function tcb_frame_request_uri( $uri ) {

		if ( ! Thrive_Utils::is_theme_template() ) {
			return $uri;
		}

		$new = thrive_template()->url( true );

		if ( empty( $new ) ) {
			return $uri;
		}

		$args = [
			TVE_EDITOR_FLAG   => 'true',
			THRIVE_THEME_FLAG => thrive_template()->ID,
		];

		/* add an extra param for the preview link, so we will know how to display stuff */
		if ( doing_filter( 'preview_post_link' ) ) {
			$args[ THRIVE_PREVIEW_FLAG ] = 'true';
			unset( $args[ TVE_EDITOR_FLAG ] );

			/**
			 * Filters the arguments right before constructing the preview URL for a template
			 *
			 * @param array $args current url query string params
			 * @param int   $post_id
			 *
			 * @return array the list of query string params
			 */
			$args = apply_filters( 'thrive_theme_preview_url_args', $args, get_the_ID() );
		}

		return add_query_arg( $args, $new );
	}

	/**
	 * When we're in the inner frame, we let Architect know that he has the power
	 */
	public static function parse_request() {
		if ( Thrive_Utils::is_inner_frame() ) {
			add_filter( 'tcb_is_inner_frame_override', [ __CLASS__, 'tcb_is_inner_frame_override' ] );
			add_filter( 'tcb_is_editor_page', [ __CLASS__, 'tcb_is_inner_frame_override' ] );
		}
	}

	/**
	 * While doing the content filter, we tell Architect that he's not in editor mode
	 *
	 * @return bool
	 */
	public static function tcb_is_inner_frame_override() {
		return ! doing_filter( 'the_content' );
	}

	/**
	 * Load our custom components
	 */
	public static function tcb_output_components() {
		$files = [];

		/* load these components only on theme templates */
		if ( Thrive_Utils::allow_theme_scripts() ) {
			$path  = ARCHITECT_INTEGRATION_PATH . '/views/components/theme/';
			$files += array_diff( scandir( $path ), [ '.', '..' ] );
		}

		/* only load these components on TAr posts or pages */
		if ( apply_filters( 'thrive_theme_load_content_components', Thrive_Utils::is_architect_editor() ) ) {
			$path  = ARCHITECT_INTEGRATION_PATH . '/views/components/editor/';
			$files += array_diff( scandir( $path ), [ '.', '..' ] );
		}

		/* include all the files we collected */
		foreach ( $files as $file ) {
			include $path . $file;
		}
	}

	/**
	 * Include theme architect backbone templates
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public static function tcb_backbone_templates( $templates ) {
		$theme_templates = tve_dash_get_backbone_templates( ARCHITECT_INTEGRATION_PATH . '/views/backbone/theme-main', 'backbone' );

		/* add these templates only in the architect editor */
		if ( Thrive_Utils::is_architect_editor() ) {
			$architect_templates = tve_dash_get_backbone_templates( ARCHITECT_INTEGRATION_PATH . '/views/backbone/architect-main' );

			$theme_templates = array_merge( $architect_templates, $theme_templates );
		}

		return array_merge( $theme_templates, $templates );
	}

	/**
	 * Specify the themes divider prefix
	 *
	 * @param $prefix
	 *
	 * @return string
	 */
	public static function tcb_divider_prefix( $prefix ) {

		if ( Thrive_Utils::is_theme_template() ) {
			$prefix = '.thrv-divider';
		}

		return $prefix;
	}

	/**
	 * Add some backbone templates for the editor.
	 */
	public static function wp_print_footer_scripts() {
		if ( Thrive_Utils::is_inner_frame() ) {
			$templates = tve_dash_get_backbone_templates( ARCHITECT_INTEGRATION_PATH . '/views/backbone/theme-editor', 'theme-editor' );
			tve_dash_output_backbone_templates( $templates, 'tve-theme-' );
		}
	}

	/**
	 * Add Article Section order inside the elements sidebar
	 *
	 * @param $order
	 *
	 * @return mixed
	 */
	public static function tcb_categories_order( $order ) {
		$order[4] = Thrive_Defaults::theme_group_label();

		return $order;
	}

	public static function tcb_editor_enqueue_scripts() {
		if ( isset( $_GET[ THRIVE_PREVIEW_FLAG ] ) ) {
			wp_dequeue_style( 'tve_inner_style' );
			wp_dequeue_style( 'tve_editor_style' );
		}
	}

	/**
	 * Add custom class to article wrapper
	 *
	 * @param array $post_class
	 *
	 * @return array
	 */
	public static function post_class( $post_class = [] ) {

		$post_class[] = THRIVE_POST_WRAPPER_CLASS;
		$post_class[] = THRIVE_WRAPPER_CLASS;

		return $post_class;
	}

	/**
	 * Exit url for theme builder
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public static function tcb_close_url( $url ) {
		if ( Thrive_Utils::is_theme_template() ) {
			$url = thrive_template()->url();
		}

		return $url;
	}

	/**
	 * Add the SVGs for the editor.
	 */
	public static function tcb_add_extra_svg_in_iframe() {
		include_once THEME_PATH . '/inc/assets/svg/iframe.svg';
	}

	/**
	 * Add the SVGs for the editor.
	 */
	public static function tcb_add_editor_svgs() {
		include_once THEME_PATH . '/inc/assets/svg/editor.svg';
	}

	/**
	 * Add attributes to the post wrapper
	 *
	 * @param array   $attributes
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	public static function thrive_post_attributes( $attributes, $post ) {
		if ( Thrive_Utils::is_inner_frame() ) {
			$attributes['data-id'] = $post->ID;
		}

		return $attributes;
	}

	/**
	 * Set branding elements for the theme
	 *
	 * @param $string
	 * @param $type
	 *
	 * @return string
	 */
	public static function architect_branding( $string, $type = 'text' ) {
		if ( Thrive_Utils::is_theme_template() ) {
			switch ( $type ) {
				case 'text':
					$string = 'Thrive Theme Builder';
					break;
				case 'logo_src':
					$string = THEME_URL . '/inc/assets/images/theme-logo.png';
					break;
				default:
					break;
			}
		}

		return $string;
	}

	/**
	 * Disable landing page options for architect light.
	 *
	 * @param $allow
	 *
	 * @return mixed
	 */
	public static function can_use_landing_pages( $allow ) {

		if ( Thrive_Utils::is_theme_template() || Thrive_Utils::is_theme_typography() ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Include theme modals inside architect
	 *
	 * @param $files
	 *
	 * @return mixed
	 */
	public static function tcb_modal_templates( $files ) {

		$path   = ARCHITECT_INTEGRATION_PATH . '/views/modals/';
		$modals = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $modals as $file ) {
			$files[] = $path . $file;
		}

		return $files;
	}

	/**
	 * Include extra links in the editor's right sidebar
	 * Allow this only on the theme templates or in the allowed theme post types
	 */
	public static function add_extra_links() {
		if ( Thrive_Utils::is_theme_template() || Thrive_Utils::is_allowed_post_type( get_the_ID() ) ) {
			include_once THEME_PATH . '/inc/templates/parts/extra-links.php';
		}
	}

	/**
	 * Include extra content in the editor's left sidebar
	 * Allow this only on the theme templates or in the allowed theme post types
	 */
	public static function add_top_content() {
		if ( Thrive_Utils::is_theme_template() || Thrive_Utils::is_allowed_post_type( get_the_ID() ) ) {
			include_once THEME_PATH . '/inc/templates/parts/top-content.php';
		}
	}

	/**
	 * Include TAR light notice for elements
	 */
	public static function add_tar_light_notice() {
		if ( static::is_light() && ! Thrive_Utils::is_theme_template() ) {
			include_once THEME_PATH . '/inc/templates/parts/tar-light-notice.php';
		}
	}

	/**
	 * Don't hide the post list element if TTB is active.
	 *
	 * @param $hide
	 *
	 * @return bool
	 */
	public static function tcb_hide_post_list_element( $hide ) {
		return $hide && ! Thrive_Utils::is_theme_template();
	}

	/**
	 * Assign a global style to a specific skin
	 *
	 * @param $global_styles
	 * @param $request
	 *
	 * @return mixed
	 */
	public static function assign_global_styles( $global_styles, $is_create, $request ) {
		$identifier = $request['identifier'];

		/* If we are sending a skin tag and we are creating the style we assign it also to a skin */
		if ( ! empty( $request['skin_tag'] ) && $is_create ) {
			$global_styles[ $identifier ]['skin_tag'] = thrive_skin()->get_tag();
		}

		return $global_styles;
	}

	/**
	 * We don't need the skin styles in the TAR localization array. We will take those separately
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function tcb_global_styles( $items = [] ) {

		$items = empty( $items ) ? [] : $items;

		$items = array_filter( $items, static function ( $value ) {
			return empty( $value['skin_tag'] );
		} );

		return $items;
	}

	/**
	 * Reorder template from cloud based on the ones are from a theme skin
	 *
	 * @param $templates
	 *
	 * @return mixed
	 */
	public static function tcb_cloud_templates( $templates, $type ) {
		/* Content blocks / headers / footers have a different template structure, so they shouldn't be affected by skin logic*/
		if ( ! in_array( $type, [ 'contentblock', THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ] ) ) {
			$templates = thrive_skin()->filter_templates( $templates, $type );
		}

		return $templates;
	}

	/**
	 * Handle tar light landing pages lp_templates
	 *
	 * @param $lp_templates
	 *
	 * @return array
	 */
	public static function tcb_landing_page_templates_list( $lp_templates ) {
		return thrive_skin()->filter_landing_pages( $lp_templates );
	}

	/**
	 * Display custom title based on what we're editing
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public static function tcb_editor_title( $title ) {

		switch ( get_post_type() ) {
			case THRIVE_TEMPLATE:
				$title = 'Thrive Theme Builder';
				break;
			case THRIVE_TYPOGRAPHY:
				$title = 'Typography';
				break;
		}

		return $title;
	}


	public static function tve_get_seo_content( $content, $id ) {
		if ( ! tve_post_is_landing_page( $id ) ) {
			$post     = new Thrive_Post( $id );
			$template = $post->get_template();

			$post_template = thrive_template( $template['ID'] );

			add_filter( 'the_content', 'tve_editor_content', PHP_INT_MAX );
			/* We do not need to enter in the_post because we already set the query and we only need one post */
			add_filter( 'thrive_theme_do_the_post', '__return_false' );

			$content = $post_template->template();
		}

		return $content;
	}

	/**
	 * Hook for the architect post save - save the element visibility data or the theme typography.
	 *
	 * @param $post_id
	 * @param $post_request_data
	 */
	public static function tcb_ajax_save_post( $post_id, $post_request_data ) {
		$post = new Thrive_Post( $post_id );

		if ( isset( $post_request_data['element_visibility'] ) ) {
			$post->set_visibility_meta( $post_request_data['element_visibility'] );
		} elseif ( isset( $post_request_data['theme_typography_style'] ) ) {
			foreach ( $post_request_data['theme_typography_style'] as $type => & $json_data ) {
				$json_data = json_decode( stripslashes( $json_data ), true );
			}
			thrive_typography( $post_id )->set_style( $post_request_data['theme_typography_style'] );
		}

		if ( isset( $post_request_data[ THRIVE_META_POST_AMP_STATUS ] ) ) {
			if ( empty( $post_request_data[ THRIVE_META_POST_AMP_STATUS ] ) ) {
				$post->delete_meta( THRIVE_META_POST_AMP_STATUS );
			} else {
				$post->set_meta( THRIVE_META_POST_AMP_STATUS, $post_request_data[ THRIVE_META_POST_AMP_STATUS ] );
			}
		}

		if ( isset( $post_request_data['tve_video_attributes']['type'] ) ) {
			$type        = $post_request_data['tve_video_attributes']['type'];
			$post_format = thrive_video_post_format( $type, $post_id );

			if ( $post_format !== null ) {
				$settings = $post_format->process_options( $post_request_data['tve_video_attributes'], $type );
				$post_format->save_options( $settings );
			}
		}

		if ( isset( $post_request_data['tve_audio_attributes']['type'] ) ) {
			$type        = $post_request_data['tve_audio_attributes']['type'];
			$post_format = thrive_audio_post_format( $type, $post_id );

			if ( $post_format !== null ) {

				$settings = $post_format->process_options( $post_request_data['tve_audio_attributes'], $type );
				$post_format->save_options( $settings );
			}
		}

		if ( isset( $post_request_data['scripts'] ) ) {
			tcb_scripts( $post_id )->save( $post_request_data['scripts'] );
		}
	}

	/**
	 * Action called when deleting a term. If the term is a skin, make sure we delete everything from the skin
	 *
	 * @param $term_id
	 * @param $taxonomy
	 */
	public static function pre_delete_term( $term_id, $taxonomy ) {
		if ( $taxonomy === SKIN_TAXONOMY ) {
			$skin = new Thrive_Skin( $term_id );

			$skin->remove();
		}
	}

	/**
	 * When updating css for typography, we also recreate the style file.
	 *
	 * @param $meta_id
	 * @param $object_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public static function updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( $meta_key === 'style' && get_post_type( $object_id ) === THRIVE_TYPOGRAPHY ) {
			thrive_skin()->generate_style_file();
		}
	}

	/**
	 * Outputs skin variables
	 */
	public static function output_skin_variables() {
		if ( thrive_post()->is_landing_page() && ! empty( thrive_post()->get_meta( 'theme_skin_tag' ) ) ) {
			/**
			 * For Landing Pages associated with the theme, we shouldn't include the landing page variables that comes with the landing page
			 * The Landing Page variables are called on output_skin_variables, like this function is also called but with priority PHP_INT_MAX
			 */
			remove_action( 'tcb_get_extra_global_variables', [ 'TCB_Landing_Page', 'output_landing_page_variables' ], PHP_INT_MAX );
		}

		echo thrive_skin()->get_variables_for_css();
	}

	/**
	 * Change tcb template path in some instances
	 *
	 * @param $file_path
	 * @param $file
	 * @param $data
	 * @param $namespace
	 *
	 * @return string
	 */
	public static function tcb_change_template_path( $file_path, $file, $data, $namespace ) {
		if ( Thrive_Utils::is_theme_template() && Thrive_Utils::has_skin_style_panel() && strpos( $file, 'central-style-panel' ) !== false ) {
			$file_path = THEME_PATH . '/inc/templates/parts/theme-style-panel.php';
		}

		return $file_path;
	}

	/**
	 * Function that allows the central style panel to be displayed on a content edited with TAR
	 *
	 * @param bool $return
	 *
	 * @return bool
	 */
	public static function tcb_skin_allow_central_style_panel( $return = false ) {
		if ( Thrive_Utils::has_skin_style_panel() ) {
			$return = true;
		}

		return $return;
	}

	/**
	 * At page load we require info for the posts from the page. For demo content posts we need a separate query
	 * Use cases for demo content posts:
	 * 1) blog with sample posts
	 * 2) single templates with demo content posts that also contain post lists with normal posts
	 *
	 * @param array $posts
	 * @param array $post_ids
	 *
	 * @return array|int[]|WP_Post[]
	 */
	public static function tcb_localize_existing_post_list( $posts = [], $post_ids = [] ) {
		/* if we're localizing demo content posts, those are private so we have to search them in a specific way. */
		$demo_content_posts = get_posts( [
			'posts_per_page' => count( $post_ids ),
			'post__in'       => $post_ids,
			'post_type'      => Thrive_Demo_Content::POST_TYPE,
		] );

		if ( ! empty( $demo_content_posts ) ) {
			$posts = array_merge( $posts, $demo_content_posts );
		}

		return $posts;
	}

	/**
	 * Changes the Query attributes for the Featured List in order to correspond to the ones form blog list
	 *
	 * @param $query
	 * @param $post_list TCB_Post_List
	 *
	 * @return mixed
	 */
	public static function change_featured_list_args( $query, $post_list ) {
		/* If we are on an Archive(all except search and date) we should change the query for the Featured List*/
		$template_id = $post_list->get_attr( 'template-id' );

		if ( $post_list->is_featured() && is_numeric( $template_id ) ) {
			$query_vars = Thrive_Utils::get_query_vars();
			$template   = new Thrive_Template( $template_id );


			if ( ! empty( $query_vars['post_type'] ) ) {
				$query['post_type'] = $query_vars['post_type'];
			}

			if ( $template->is_archive() ) {
				$query['tax_query'] = [];
				if ( is_date() ) {
					/*When the Post List is on a date archive page, the Featured List should also be updated*/
					if ( ! empty( $query_vars['year'] ) && ! empty( $query_vars['monthnum'] ) ) {
						global $wp_query;

						$query['year'] = (int) $wp_query->query['year'];

						if ( isset( $wp_query->query['monthnum'] ) && is_numeric( $wp_query->query['monthnum'] ) ) {
							$query['monthnum'] = (int) $wp_query->query['monthnum'];
						}

						if ( isset( $wp_query->query['day'] ) && is_numeric( $wp_query->query['day'] ) ) {
							$query['day'] = (int) $wp_query->query['day'];
						}
					}
				} elseif ( isset( $query_vars['rules'] ) ) {
					if ( is_author() ) {
						$query['author__in'] = $query_vars['rules'][0]['terms'];
					} else {
						$query['tax_query'][] = $query_vars['rules'][0];
					}
				}

			}

			if ( $template->is_search() ) {
				$query['tax_query'] = [];
				/*When the Post List is on a search page, add the serarch term in Featured List also*/
				if ( ! empty( $query_vars['s'] ) ) {
					global $wp_query;

					$query['s'] = $wp_query->query['s'];
				}
			}
		}

		return $query;
	}

	/**
	 * Extend the config of the LP element
	 *
	 * @param $lp_config
	 *
	 * @return array
	 */
	public static function tcb_lp_element_extend_config( $lp_config ) {
		if ( Thrive_Utils::is_architect_editor() && AMP_Settings::enabled_on_post_type( get_the_ID() ) ) {
			$lp_config = array_merge( $lp_config, static::get_amp_component() );
		}

		return $lp_config;
	}

	/**
	 * Add more components to the post element config.
	 *
	 * @param $post_config
	 *
	 * @return mixed
	 */
	public static function tcb_post_element_extend_config( $post_config ) {
		$is_architect_editor = Thrive_Utils::is_architect_editor();

		if ( $is_architect_editor && class_exists( 'AMP_Settings', false ) && AMP_Settings::enabled_on_post_type( get_the_ID() ) ) {
			$post_config = array_merge( $post_config, static::get_amp_component() );
		}

		/* don't add anything on landing pages */
		if ( ! thrive_post()->is_landing_page() ) {
			/* only add this component on page templates; it has no TAr controls, only a custom-made list */
			if ( $is_architect_editor ) {
				$post_config['post-type-template-settings'] = [];
				$post_config['page_content_settings']       = [];
			}

			$visibility_config  = [];
			$visibility_options = [
				[
					'name'  => __( 'Inherit', 'thrive-theme' ),
					'value' => 'inherit',
				],
				[
					'name'  => __( 'Show', 'thrive-theme' ),
					'value' => 'show',
				],
				[
					'name'  => __( 'Hide', 'thrive-theme' ),
					'value' => 'hide',
				],
			];

			/* the section visibility has select controls because they also have an inherit option */
			foreach ( Thrive_Post::get_visibility_config( 'sections' ) as $data ) {
				$visibility_config[ $data['view'] ] = [
					'config'  => [
						'label'   => $data['label'],
						'options' => $visibility_options,
						'default' => 'inherit',
					],
					'extends' => 'Select',
				];
			}
			$controls['Visibility']['config']['default'] = 'inherit';

			/* the normal elements have toggles, not selects */
			foreach ( Thrive_Post::get_visibility_config( 'elements' ) as $data ) {
				/* add the config for each view */
				$visibility_config[ $data['view'] ] = [
					'config'  => [
						'label' => $data['label'],
					],
					'extends' => 'Switch',
				];
			}

			/* add the config for showing the hidden elements in the editor ( this works like that toggle in the responsive component )*/
			$visibility_config['ShowAllHidden'] = [
				'config'  => [
					'label' => __( 'Show all hidden modules', 'thrive-theme' ),
				],
				'extends' => 'Switch',
			];

			/* add everything we have just set up to the post element config */
			$post_config['visibility_settings'] = [
				'config' => $visibility_config,
			];

			$post_config['scripts_settings'] = [];
		}

		return $post_config;
	}

	/**
	 * Get the AMP settings component
	 *
	 * @return array
	 */
	public static function get_amp_component() {
		return [
			'amp-settings' => [
				'config' => [
					'DisableAMP' => [
						'config'  => [
							'label' => __( 'Disable AMP for this post', 'thrive-theme' ),
						],
						'extends' => 'Switch',
					],
				],
				'order'  => 999,
			],
		];
	}

	/**
	 * Add extra elements to the translate array from the editor
	 *
	 * @param $translate
	 *
	 * @return mixed
	 */
	public static function tcb_js_translate( $translate ) {

		$translate['elements'] = array_merge( $translate['elements'], [
			'thrive_author_box' => __( 'About the Author', 'thrive-theme' ),
		] );

		return $translate;
	}

	/**
	 * Adds extra items to Page Wizard from TAR when the Theme in active
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function tcb_add_page_wizard_items( $items = [] ) {

		/* Blank page with h/f is available only for non default skins and when the theme is active. */
		if ( Thrive_Theme::is_active() && ! thrive_skin()->is_default() ) {
			$items = array_merge( $items, [
				[
					'title'   => __( 'Blank Page with Header and Footer', 'thrive-theme' ),
					'layout'  => 'blank_hf',
					'order'   => 10,
					'picture' => THEME_URL . '/inc/assets/images/page-wizard/blank-h-f.png',
					'text'    => [
						__( 'Start with a blank page that  includes your header and footer.', 'thrive-theme' ),
						__( 'Use this template to design full landing pages from scratch using blocks.', 'thrive-theme' ),
						__( 'This is mostly useful if you want to build a marketing page from scratch (sales pages, lead generation pages, webinar registrations).', 'thrive-theme' ),
					],
				],
				[
					'title'   => __( 'Completely Blank Page', 'thrive-theme' ),
					'layout'  => 'completely_blank',
					'order'   => 20,
					'picture' => THEME_URL . '/inc/assets/images/page-wizard/blank.png',
					'text'    => [
						__( 'Start with a completely empty canvas.', 'thrive-theme' ),
						__( 'Use our page blocks feature to build a page from nothing. Build anything you want - your imagination is your only limit.', 'thrive-theme' ),
					],
				],
			] );
		}

		return $items;
	}

	/**
	 * Added the ttb_skin param to the request that fetches the Cloud Template List.
	 *
	 * For the theme, the cloud templates are skin based
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public static function add_ttb_skin_tag_to_params( $params = [] ) {
		if ( Thrive_Theme::is_active() ) {
			/* Add skin tag only when the theme is active */
			if ( empty( $params['ttb_skin'] ) ) {
				$params['ttb_skin'] = thrive_skin( 0, false )->get_tag();
			}

			if ( isset( $_REQUEST['post_id'] ) && in_array( get_post_type( $_REQUEST['post_id'] ), [ THRIVE_TEMPLATE, THRIVE_SECTION ], true ) ) {
				if ( empty( $params['filters'] ) ) {
					$params['filters'] = [];
				}
				/* theme scope for templates and sections */
				$params['filters']['scope'] = 'ttb';
			}
		}

		return $params;
	}

	/**
	 * Extra check if we want to remove the theme css in landing pages
	 *
	 * @param bool   $remove to remove or not the theme css
	 * @param string $src
	 *
	 * @return bool
	 */
	public static function tcb_remove_theme_css( $remove, $src ) {

		/**
		 * We don't need to remove the css that comes from the TAR within the theme builder when TAR is not active on the user's site
		 * That css is actually necessary for the editor
		 */
		if ( strpos( $src, TVE_EDITOR_URL ) !== false ) {
			$remove = false;
		}
		/**
		 * If on a TAr Landing page and user has setup "remove theme CSS" - do not include the theme's css
		 */
		if ( ! $remove && strpos( $src, UPLOAD_DIR_URL_NO_PROTOCOL ) !== false ) {
			$remove = true;
		}

		return $remove;
	}

	/**
	 * Just before downloading a template, set the global post so we have post data available when do_shortcode() is called on the template content.
	 */
	public static function set_global_post_before_cloud_ajax() {
		if ( isset( $_GET['post_id'] ) ) {
			$existing_post = get_post( $_GET['post_id'] );

			if ( ! empty( $existing_post ) ) {
				global $post;

				$post = $existing_post;
			}
		}
	}

	/**
	 * Make sure shortcodes are rendered in templates
	 *
	 * @param $response
	 * @param $ajax_handler
	 *
	 * @return mixed
	 */
	public static function tcb_ajax_response_load_content_template( $response, $ajax_handler ) {

		$response['html_code'] = do_shortcode( $response['html_code'] );

		return $response;
	}

	/**
	 * If thrive theme is not active, remove the "Edit with TAR" view post.php?action=edit page
	 *
	 * @param bool $hide
	 *
	 * @return bool
	 */
	public static function gutenberg_switch( $hide ) {
		if ( static::is_light() ) {
			/* display edit with architect only if the user can edit posts */
			$hide = ! current_user_can( 'edit_post', get_the_ID() );
		}

		return $hide;
	}

	/**
	 * Change breadcrumb data based on post type
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function post_breadcrumb_data( $data ) {
		$post_type_name = Thrive_Utils::get_post_type_name();

		if ( ! empty( $post_type_name ) ) {
			$data['label'] = $post_type_name;
		}

		return $data;
	}

	/**
	 * For editor page and preview page the query string of the search element should not be shown in the search input
	 *
	 * @param string $query_string
	 *
	 * @return string
	 */
	public static function alter_search_element_query_string( $query_string = '' ) {
		if ( is_editor_page_raw() || Thrive_Utils::is_preview() ) {
			$query_string = '';
		}

		return $query_string;
	}

	/**
	 * Exclude Landing Pages from being displayed on the search archive list ( in the template editor and in preview ).
	 * When a LP is displayed there, it prevents the regular archive list from rendering and glitches the editor.
	 *
	 * @param WP_Query $query
	 *
	 * @return mixed
	 */
	public static function search_filter_post_types( $query ) {
		/* make sure we never return landing pages when displaying the search template in the template editor */
		if ( $query->is_search() && ( Thrive_Utils::is_inner_frame() || Thrive_Utils::is_preview() ) ) {
			$query->set( 'meta_query', Thrive_Utils::meta_query_no_landing_pages() );
		}

		return $query;
	}

	/**
	 * Appends all sections needed for a theme template.
	 * General sections & headers/footers
	 * Also add all the custom fields for this post type.
	 *
	 * @param array $data
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public static function add_lazy_load_data( $data, $post_id ) {
		if ( Thrive_Utils::is_theme_template() ) {
			$data['headers_and_footers'] = Thrive_HF_Section::get_all();
			$data['theme_sections']      = thrive_skin()->get_sections();
			$data['custom_fields']       = Thrive_Architect_Utils::get_filtered_custom_fields_data( $post_id );
		}

		return $data;
	}

	/**
	 * Add the ACF dynamic colors for the current post
	 *
	 * @param $data
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function add_lazy_load_dynamic_colors( $data, $post_id ) {
		$custom_fields = Thrive_Architect_Utils::get_filtered_custom_fields_data( $post_id );

		$data['custom_fields']['colors']         = $custom_fields['colors'];
		$data['custom_fields']['has_acf_colors'] = $custom_fields['has_acf_colors'];

		return $data;
	}

	/**
	 * Change the menu from the header and replace it with the default one
	 *
	 * @param WP_Post $post
	 * @param array   $meta
	 */
	public static function change_section_menu( $post, $meta ) {

		if ( $meta['type'] === THRIVE_HEADER_SECTION ) {
			$menu_id = thrive_skin()->get_default_data( 'header_menu' ) ?: thrive_skin()->get_default_data( 'menu' );
		} elseif ( $meta['type'] === THRIVE_FOOTER_SECTION ) {
			$menu_id = thrive_skin()->get_default_data( 'footer_menu' );
		}

		if ( ! empty( $menu_id ) ) {
			$html = Thrive_Utils::replace_menu_in_html( $menu_id, $post->post_content );
			if ( ! empty( $html ) ) {
				$post->post_content = is_editor_page_raw( true ) ? tve_thrive_shortcodes( $html, true ) : $html;
			}
		}
	}

	/**
	 * When a header or footer is deleted from the admin area ( Global Elements ), unlink it from all the templates where it's used
	 *
	 * @param $post
	 */
	public static function unlink_hf_from_templates( $post ) {
		if ( ! empty( $post ) ) {
			/* determine the type of the symbol */
			if ( has_term( 'headers', TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY, $post ) ) {
				$type = THRIVE_HEADER_SECTION;
			} elseif ( has_term( 'footers', TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY, $post ) ) {
				$type = THRIVE_FOOTER_SECTION;
			}

			if ( ! empty( $type ) && ! empty( $post->ID ) ) {
				$section_to_delete = new Thrive_HF_Section( $post->ID, $type );

				$section_to_delete->unlink_from_templates();
			}
		}
	}

	/**
	 * Change post element name based on post type
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function post_element_name( $name ) {
		$post_type_name = Thrive_Utils::get_post_type_name();

		if ( ! empty( $post_type_name ) ) {
			$name = $post_type_name;
		}

		return $name;
	}

	/**
	 * Get version of bundled TAr plugin
	 *
	 * @return string
	 */
	public static function internal_version() {
		$architect_version_file = trailingslashit( THEME_PATH ) . 'architect/version.php';
		if ( file_exists( $architect_version_file ) ) {
			$version = include $architect_version_file;
		} else {
			$version = TVE_VERSION;
		}

		return $version;
	}

	/**
	 * Checks version compatibility with standalone version of TAr
	 * Only relevant if TAr plugin is installed and activated, and if TTB is active
	 *
	 * @return array having the following structure:
	 *      $compatible boolean whether or not TTB and TAr are both up-to-date
	 *      $needs_update string which of the 2 needs updating ('theme', 'plugin')
	 */
	public static function version_compatibility() {
		$status = [
			'compatible'   => true,
			'needs_update' => '',
		];

		/* only if TAr is active as a standalone plugin */
		if ( defined( 'TVE_IN_ARCHITECT' ) && TVE_IN_ARCHITECT && defined( 'TVE_VERSION' ) && Thrive_Theme::is_active() ) {
			/* check inner TAr version against TAr plugin version */
			$result = version_compare( static::internal_version(), TVE_VERSION );

			if ( $result !== 0 ) {
				$status['compatible']   = false;
				$status['needs_update'] = $result < 0 ? 'theme' : 'plugin';
			}
		}

		return $status;
	}

	/**
	 * Include extra dynamic links in the editor
	 *
	 * Allow this only on the theme templates or in the allowed theme post types
	 */
	public static function add_extra_dynamic_links() {
		if ( apply_filters( 'thrive_theme_show_extra_dynamic_links', Thrive_Utils::is_theme_template() ) ) {
			include_once THEME_PATH . '/inc/templates/parts/extra-dynamic-links.php';
		}
	}

	/**
	 * Adds the landing page lightbox extra icons
	 */
	public static function add_extra_lp_lightbox_set_icons() {
		include_once THEME_PATH . '/inc/templates/parts/tar-extra-lp-lightbox-set-icons.php';
	}

	/**
	 * Adds extra Landing Page Lightbox icons into the Templates Preview View
	 */
	public static function add_extra_lp_lightbox_icons() {
		echo '<span>' . tcb_icon( 'ttb-skin', false, 'sidebar', 'set-skin' ) . '</span>';
	}

	public static function after_load_custom_css() {
		/**
		 * For 404 pages load global styles because those are not rendered outside posts
		 */
		if ( is_404() ) {
			echo tve_get_shared_styles( '' );
		}
	}

	/**
	 * Only overwrite the scrips for single page views
	 *
	 * @param bool $overwrite
	 *
	 * @return mixed
	 */
	public static function tcb_overwrite_event_scripts_enqueue( $overwrite ) {
		return is_singular();
	}

	/**
	 * Get special blocks specific to the theme
	 *
	 * @param string $special_set
	 *
	 * @return mixed|string
	 */
	public static function tcb_get_special_blocks_set( $special_set = '' ) {
		$post_id = get_the_ID();

		if ( wp_doing_ajax() && empty( $post_id ) && isset( $_POST['post_id'] ) ) {
			$post_id = $_POST['post_id'];
		}

		$landing_page = tve_post_is_landing_page( $post_id );

		if ( $landing_page === false || $landing_page === 'blank_v2' || strtolower( $special_set ) === 'blank' ) {
			$skin_tag    = thrive_skin()->get_meta( Thrive_Skin::TAG );
			$result      = explode( '_', $skin_tag );
			$special_set = $result[0];
		}

		return $special_set;
	}

	/**
	 * Create a page were we would preview a section.
	 * So if the post type is a thrive section, overwrite the template content and display just this section.
	 *
	 * @param string          $content
	 * @param Thrive_Template $template
	 *
	 * @return string
	 */
	public static function thrive_theme_template_content( $content, $template ) {

		if ( get_post_type() === THRIVE_SECTION ) {
			$section = new Thrive_Section( get_the_ID() );

			$content = TCB_Utils::wrap_content( $section->render(), 'div', 'wrapper', [
				Thrive_Utils::is_inner_frame() ? THRIVE_WRAPPER_CLASS : '',
				'tcb-style-wrap',
			] );
		}

		return $content;
	}

	/**
	 * When we update the style of a template, rewrite template file
	 *
	 * @param $meta_id
	 * @param $template_id
	 * @param $meta_key
	 */
	public static function updated_template_style( $meta_id, $template_id, $meta_key ) {
		if ( $meta_key === 'style' ) {
			$lightspeed = \TCB\Lightspeed\Css::get_instance( $template_id );

			if ( $lightspeed->get_css_location( 'template' ) === 'file' ) {
				$template = new Thrive_Template( $template_id );
				$lightspeed->write_css_file( 'template', $template->style( false ) );
			}
		}
	}

	/**
	 * Decide if we want to print all styles together or not
	 *
	 * @param $print
	 *
	 * @return bool|mixed
	 */
	public static function tcb_should_print_unified_styles( $print ) {
		if ( is_singular() && tve_post_is_landing_page() && ! tcb_landing_page( get_the_ID() )->should_remove_theme_css() ) {
			$print = true;
		}

		if ( Thrive_Utils::is_theme_typography() ) {
			$print = false;
		}

		return $print;
	}
}
