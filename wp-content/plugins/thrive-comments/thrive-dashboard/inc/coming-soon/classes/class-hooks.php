<?php
/**
 * Thrive Dashboard - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Coming_Soon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package TVD\Coming_Soon
 */
class Hooks {

	public static function actions() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );

		add_action( 'wp_loaded', array( __CLASS__, 'editor_enqueue_scripts' ) );

		add_action( 'template_redirect', array( __CLASS__, 'redirect_to_coming_soon' ), 1 );

		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_item' ) );

		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'render_backbone_templates' ) );

		add_filter( 'option_' . Main::OPTION, array( __CLASS__, 'disable_coming_soon' ) );
	}

	public static function filters() {

		add_filter( 'tve_dash_filter_features', array( __CLASS__, 'tve_dash_filter_features' ) );

		add_filter( 'tve_main_js_dependencies', array( __CLASS__, 'tve_main_js_dependencies' ) );

		add_filter( 'tcb_get_page_wizard_items', array( __CLASS__, 'tcb_set_page_wizard_items' ) );

		add_filter( 'tcb_post_types', array( __CLASS__, 'allow_tcb_edit' ) );

		add_filter( 'tcb_landing_page_templates_list', array( __CLASS__, 'filter_templates' ) );
	}

	/* ###################################### ACTIONS ###################################### */


	public static function render_backbone_templates() {
		if ( tve_get_current_screen_key() === 'admin_page_tve_dash_coming_soon' ) {
			$templates = tve_dash_get_backbone_templates( plugin_dir_path( __DIR__ ) . 'views', 'coming-soon' );
			tve_dash_output_backbone_templates( $templates, 'coming-soon-' );
		}
	}

	/**
	 * Settings for the Coming Soon label in the Admin Bar
	 *
	 * @param $wp_admin_bar
	 */
	public static function admin_bar_item( $wp_admin_bar ) {
		$item_visibility = Main::get_page_id() ? 'block' : 'none';
		$wp_admin_bar->add_menu( array(
			'id'     => 'coming-soon',
			'parent' => 'top-secondary',
			'group'  => null,
			'title'  => '<span style="width:18px;height:12px;display:inline-block;background-image:url(' . TVE_DASH_URL . '/css/images/thrive-leaf.png);margin-right:5px !important;" class="thrive-adminbar-icon"></span>' . __( 'Coming Soon Mode Active', 'thrive-dash' ),
			'href'   => admin_url( 'admin.php?page=tve_dash_coming_soon' ),
			'meta'   => array(
				'class' => 'thrive-coming-soon',
				'html'  => '<style>#wpadminbar .thrive-coming-soon {background: orange; display:' . $item_visibility . '}</style>',
				'title' => __( 'Go to Coming Soon Dashboard', 'thrive-dash' ),
			),
		) );
	}

	/*
    * Display admin dashboard
    */
	public static function admin_dashboard() {
		$coming_soon = Main::is_coming_soon_enabled();

		include __DIR__ . '/../views/admin.php';
	}

	/**
	 * Redirect all pages/posts to the selected Coming Soon page
	 */
	public static function redirect_to_coming_soon() {
		if ( ! is_user_logged_in() && Main::is_coming_soon_enabled() && ( get_the_ID() !== (int) Main::get_page_id() ) ) {
			wp_redirect( Main::get_preview_url() );
			exit();
		}
	}

	/**
	 * Create menu page for the Coming Soon functionality
	 */
	public static function admin_menu() {
		add_submenu_page(
			'',
			Main::title(),
			Main::title(),
			'manage_options',
			Main::MENU_SLUG,
			array( __CLASS__, 'admin_dashboard' )
		);
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param $screen
	 */
	public static function admin_enqueue_scripts( $screen ) {
		if ( ! empty( $screen ) && $screen === 'admin_page_tve_dash_coming_soon' ) {
			$coming_soon_page_id = Main::get_page_id();
			tve_dash_enqueue();
			tve_dash_enqueue_script( 'tvd-coming-soon', TVE_DASH_URL . '/inc/coming-soon/assets/dist/main.min.js', array( 'jquery' ) );
			tve_dash_enqueue_style( 'tvd-coming-soon-main-frame', TVE_DASH_URL . '/inc/coming-soon/assets/css/admin.css' );

			wp_localize_script( 'tvd-coming-soon', 'TVD_CS_CONST', array(
				'nonce'                  => wp_create_nonce( 'wp_rest' ),
				'baseUrl'                => get_rest_url( get_current_blog_id(), 'wp/v2/pages/' ),
				'is_coming_soon_enabled' => empty( $coming_soon_page_id ) ? 0 : 1,
				'edit_url'               => Main::get_edit_url(),
				'preview_url'            => Main::get_preview_url(),
				'page_name'              => Main::get_page_name(),
				'base_url'               => admin_url(),
				'is_empty_page'          => Main::is_empty_page(),
				'is_ttb_active'          => tve_dash_is_ttb_active(),
				'is_admin_page'          => $screen === 'admin_page_tve_dash_coming_soon',
                't'                      => array(
                    'search_page'        => __( 'Search an existing page', 'thrive-dash' ),
                    'add_page_title'     => __( 'Please add the page title!', 'thrive-dash' ),
                    'saved_successfully' => __( 'Changes saved successfully', 'thrive-dash' ),
                ),
			) );
		}
	}

	/**
	 * Enqueue scripts in the editor
	 */
	public static function editor_enqueue_scripts() {
		if ( Main::is_edit_screen() ) {
			tve_dash_enqueue_script( 'tvd-coming-soon-editor', TVE_DASH_URL . '/inc/coming-soon/assets/dist/editor.min.js', array( 'jquery' ) );

			wp_localize_script( 'tvd-coming-soon-editor', 'TVD_CS_CONST', array(
				'is_empty_page' => Main::is_empty_page(),
				'is_ttb_active' => tve_dash_is_ttb_active(),
			) );
		}
	}

	/**
	 * Disable CS if it is not enabled or if the selected page is not published
	 */
	public static function disable_coming_soon( $value ) {
		$features = apply_filters( 'tve_dash_features', array() );

		if ( ! isset( $features['coming-soon'] ) || get_post_status( $value ) !== 'publish' ) {
			$value = 0;
		}

		return $value;
	}

	/* ###################################### FILTERS ###################################### */

	/**
	 * In the editor, load the main js only after our files are loaded
	 *
	 * @param array $dependencies
	 *
	 * @return array
	 */
	public static function tve_main_js_dependencies( $dependencies ) {
		if ( Main::is_edit_screen() ) {
			$dependencies[] = 'tvd-coming-soon-editor';
		}

		return $dependencies;
	}

	/**
	 * Add dashboard card for the Coming Soon functionality
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public static function tve_dash_filter_features( $features ) {
		$features['coming-soon'] = array(
			'icon'        => 'tvd-coming-soon',
			'title'       => Main::title(),
			'description' => __( 'Display a "Coming Soon" page to let visitors know that you are currently working on your website.', 'thrive-dash' ),
			'btn_link'    => add_query_arg( 'page', Main::MENU_SLUG, admin_url( 'admin.php' ) ),
			'btn_text'    => __( 'Coming Soon Mode', 'thrive-dash' ),
		);

		return $features;
	}

	/**
	 * Filter the items that are loaded in the Page Wizard Modal
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function tcb_set_page_wizard_items( $items = array() ) {
		/* Only load custom items when we are editing the Coming Soon Page */
		if ( Main::is_edit_screen() ) {
			foreach ( $items as $key => $item ) {
				if ( ! in_array( $item['layout'], array( 'completely_blank', 'lp' ) ) ) {
					unset( $items[ $key ] );
				}
			}
		}

		return $items;
	}

	/**
	 * Filter the templates that are loaded
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public static function filter_templates( $templates ) {
		/* Only load custom templates when we are editing the Coming Soon Page */
		if ( ! empty( $_POST['post_id'] ) && $_POST['post_id'] === Main::get_page_id() ) {
			$result = array();
			$skin   = '';

			if ( function_exists( 'thrive_skin' ) ) {
				$skin = thrive_skin()->get_tag();
			}

			/* Only load the 'Coming Soon' templates and the current skin's blank template */
			foreach ( $templates as $key => $tpl ) {
				$tags = implode( ' ', $tpl['tags'] );

				if ( $tpl['set'] === 'Coming Soon' || ( ! empty( $tpl['skin_tag'] ) && $tpl['skin_tag'] === $skin && stripos( $tags, 'blank' ) !== false ) ) {
					$tpl['locked']  = 0;
					$result[ $key ] = $tpl;
				}
			}

			return $result;

		}

		return $templates;
	}

	/**
	 * Allow tcb to edit the Coming Soon page
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function allow_tcb_edit( $post_types ) {
		if ( Main::is_edit_screen() ) {
			if ( ! isset( $post_types['force_whitelist'] ) ) {
				$post_types['force_whitelist'] = array();
			}

			$post_types['force_whitelist'][] = get_post_type();
		}

		return $post_types;
	}
}
