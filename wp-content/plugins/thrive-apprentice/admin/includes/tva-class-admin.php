<?php

use TVA\TTB\Main as TTB_Main;

/**
 * Main point for the admin
 * - enqueues scripts and styles
 * - localise what is necessary
 * - defines Thrive Apprentice admin page with its Thrive Dashboard product hooks
 *
 * Class TVA_Admin
 */
class TVA_Admin {

	/**
	 * full ID of the current screen on the main admin apprentice page
	 */
	const SCREEN_ID = 'thrive-dashboard_page_thrive_apprentice';

	/**
	 * General constant for how many items should be displayed on any page of any list
	 * - this constant is localized to JS
	 */
	const ITEMS_PER_PAGE             = 10;
	const OVERVIEW_PAGE_COURSE_COUNT = 4;

	/**
	 * @var TVA_Admin
	 */
	private static $_instance;

	/**
	 * Whether or not TA code can be executed
	 *
	 * @var bool
	 */
	public static $can_run = false;

	/**
	 * In case version deps are not met, this contains an error message
	 *
	 * @var string
	 */
	protected static $dependency_error = null;

	/**
	 * TVA_Admin constructor.
	 */
	private function __construct() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_init', [ $this, 'add_access_to_generation' ] );
		add_filter( 'tve_dash_admin_product_menu', array( $this, 'add_admin_menu' ) );
		add_filter( 'tve_dash_menu_products_order', array( $this, 'set_admin_menu_order' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/**
		 * set the folded class on body on page render, to avoid flickering from javascript on domready
		 */
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );

		/**
		 * Allows the content sets scripts on apprentice admin screen
		 */
		add_filter( 'tvd_content_sets_allow_enqueue_scripts', array( $this, 'allow_content_sets_resources' ), 10, 2 );
	}

	/**
	 * Admin init hook
	 */
	public function admin_init() {
		global $wp_version;

		/* check that all deps are met */
		if ( floatval( $wp_version ) < 4.6 ) {
			static::$dependency_error = tva_get_file_contents( 'admin/includes/templates/incompatible/wordpress.php' );
		} elseif ( ! tva_license_activated() ) {
			static::$dependency_error = tva_get_file_contents( 'admin/views/license_inactive.php' );
		} elseif ( ! tva_check_tcb_version() ) {
			static::$dependency_error = tva_get_file_contents( 'admin/includes/templates/incompatible/architect.php' );
		} elseif ( ! tva_check_ttb_version() ) {
			static::$dependency_error = tva_get_file_contents( 'admin/includes/templates/incompatible/theme-builder.php' );
		} else {
			/* everything looks good, continue */
			static::$can_run = true;
		}
	}

	/**
	 * Push Thrive Apprentice submenu item into Thrive Dashboard Admin menu
	 *
	 * @param array $menus
	 *
	 * @return array
	 */
	public function add_admin_menu( $menus = array() ) {

		$menus['apprentice'] = array(
			'parent_slug' => 'tve_dash_section',
			'page_title'  => esc_html__( 'Thrive Apprentice', 'thrive-apprentice' ),
			'menu_title'  => esc_html__( 'Thrive Apprentice', 'thrive-apprentice' ),
			'capability'  => TVA_Product::cap(),
			'menu_slug'   => 'thrive_apprentice',
			'function'    => array( $this, 'page_callback' ),
		);

		return $menus;
	}

	/**
	 * Push the new Thrive Apprentice submenu item into an array at a specific order
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function set_admin_menu_order( $items ) {

		$items[11] = 'apprentice';

		return $items;
	}

	/**
	 * Set the tva_generate user meta to the value specified in the params
	 *
	 * @return void
	 */
	public function add_access_to_generation() {
		$tva_generate = isset( $_GET['tva_generate'] ) ? $_GET['tva_generate'] : null;
		$user         = wp_get_current_user();

		if ( ! is_null( $tva_generate ) && ( (int) $tva_generate === 1 || (int) $tva_generate === 0 ) ) {
			update_user_meta( $user->ID, 'tva_generate', (int) $tva_generate );
		}
	}

	/**
	 * Displays Admin page content html
	 */
	public function page_callback() {
		if ( ! static::$can_run ) {
			$content = static::$dependency_error;
		} else {
			$content = tva_get_file_contents( 'admin/includes/templates/new-dashboard.php' );
		}

		echo $content;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $screen_id
	 */
	public function enqueue_scripts( $screen_id ) {

		if ( static::SCREEN_ID !== $screen_id ) {
			return;
		}

		tve_dash_enqueue();

		/* apprentice main admin style should be loaded anyway, together with styles from thrive dashboard */
		wp_enqueue_style(
			'thrive-admin-apprentice',
			$this->url( 'dist/tva-admin-styles.css' ),
			array(),
			TVA_Const::PLUGIN_VERSION
		);

		//TODO find a solution in the future for this, maybe use the html default datepicker?
		global $wp_version;

		if ( (float) $wp_version >= 6.1 ) {
			wp_enqueue_style(
				'thrive-apprentice-timepicker',
				$this->url( 'dist/timepicker_new.css' ),
				array(),
				TVA_Const::PLUGIN_VERSION
			);
		} else {
			wp_enqueue_style(
				'thrive-apprentice-timepicker',
				$this->url( 'dist/timepicker_old.css' ),
				array(),
				TVA_Const::PLUGIN_VERSION
			);
		}

		/* avoid loading extra resources on the apprentice admin page when they are not needed */
		if ( static::$can_run ) {

			wp_enqueue_media();

			tve_dash_enqueue_script( 'tva-dropbox-picker', TVE_DASH_URL . '/js/dist/dropbox-picker.min.js' );

			$apprentice_js_file      = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? 'apprentice.js' : 'apprentice.min.js';
			$apprentice_js_file_deps = array(
				'jquery',
				'backbone',
				'wp-components',
				'tva-dropbox-picker',
				'wp-date', // Ensure wp date settings
			);
			wp_enqueue_script( 'thrive-admin-apprentice', $this->url( 'dist/' . $apprentice_js_file ), $apprentice_js_file_deps, TVA_Const::PLUGIN_VERSION, true );
			wp_localize_script( 'thrive-admin-apprentice', 'TVA', $this->get_localize_data() );

			/**
			 * Enqueue jQuery Scrollbar script
			 */
			wp_enqueue_script( 'thrive-dash-jquery-scrollbar', TVE_DASH_URL . '/js/util/jquery.scrollbar.min.js', array( 'jquery' ) );
			/**
			 * Include the spectrum Script & Style
			 */
			wp_enqueue_script( 'thrive-apprentice-spectrum-script', $this->url( 'libs/spectrum.js' ), array( 'jquery' ), TVA_Const::PLUGIN_VERSION, true );
			wp_enqueue_style( 'thrive-apprentice-spectrum-style', $this->url( 'libs/spectrum.css' ), array(), TVA_Const::PLUGIN_VERSION );

			if ( function_exists( 'wp_enqueue_code_editor' ) ) {
				/**
				 * Set on true before code editor enqueue.
				 * Fixes issue when syntax_highlighting is disabled from user profile.php
				 */
				wp_get_current_user()->syntax_highlighting = 'true';

				/**
				 * @since 4.9.0
				 */
				wp_enqueue_code_editor( array( 'type' => 'text/plain' ) );
			}

			/**
			 * Enqueue WP File Uploader
			 */
			wp_enqueue_script( 'plupload' );
			wp_enqueue_style( 'wp-components' );

			/**
			 * To enable post search functionality
			 */
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'jquery-masonry', [ 'jquery' ] );
			wp_enqueue_script( 'tar-lazyload', tve_editor_url() . '/editor/js/libs/lazyload.min.js', [
				'jquery',
				'backbone',
				'underscore',
				'jquery-ui-tooltip',
			] );

			/**
			 * Icomoon icon styles, if any
			 */
			if ( class_exists( 'TCB_Icon_Manager' ) ) {
				TCB_Icon_Manager::enqueue_icon_pack();
			}

			/**
			 * Output the skin variables also inside dashboard (main frame)
			 */
			wp_add_inline_style( 'thrive-admin-apprentice', ':root{' . TTB_Main::requested_skin()->css( true ) . '}' );

			add_action( 'admin_print_footer_scripts', array( $this, 'print_backbone_templates' ) );
		}

		/* icons need to be loaded - they are needed in the version warnings */
		add_action( 'admin_print_footer_scripts', array( $this, 'print_icons' ) );
	}

	/**
	 * Gets data to be localized
	 *
	 * @return array
	 */
	public function get_localize_data() {

		$logged_in_user = wp_get_current_user();

		return array(
			'debug'                => defined( 'TVE_DEBUG' ) && TVE_DEBUG,
			'items_per_page'       => self::ITEMS_PER_PAGE,
			'home_course_count'    => self::OVERVIEW_PAGE_COURSE_COUNT,
			'routes'               => array(
				'admin'              => tva_get_route_url( 'admin' ),
				'email_template'     => tva_get_route_url( 'emailTemplate' ),
				'logs'               => tva_get_route_url( 'logs' ),
				'customer'           => tva_get_route_url( 'customer' ),
				'products'           => tva_get_route_url( 'products' ),
				'settings'           => tva_get_route_url( 'settings' ),
				'settings_v2'        => tva_get_route_url( 'settings-v2' ),
				'sendowl'            => tva_get_route_url( 'so_settings' ),
				'token'              => tva_get_route_url( 'token' ),
				'topics'             => tva_get_route_url( 'topics' ),
				'labels'             => tva_get_route_url( 'labels' ),
				'courses'            => tva_get_route_url( 'courses' ),
				'chapters'           => tva_get_route_url( 'chapters' ),
				'modules'            => tva_get_route_url( 'modules' ),
				'access_restriction' => tva_get_route_url( 'access-restriction' ),
				'resources'          => tva_get_route_url( 'resources' ),
				'skins'              => tva_get_route_url( 'skins' ),
				'wizard'             => tva_get_route_url( 'wizard' ),
				'templates'          => tva_get_route_url( 'templates' ),
				'campaigns'          => tva_get_route_url( 'campaigns' ),
				'certificate'        => tva_get_route_url( 'certificate' ),
				'protected_files'    => tva_get_route_url( 'protected-files' ),
				'assessments'        => tva_get_route_url( 'assessments' ),
				'slug_routes'        => tva_get_route_url( 'routes' ),
				'stripe'             => tva_get_route_url( 'stripe' ),
			),
			'apiSettings'          => array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'root'  => get_rest_url(),
				'v1'    => TVA_Const::REST_NAMESPACE,
				'v2'    => 'tva/v2',
			),
			'menuItems'            => include __DIR__ . '/configs/menu.php',
			't'                    => include __DIR__ . '/../../i18n.php',
			'tar_active'           => (int) is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ),
			'theme_active'         => (int) Thrive_Theme::is_active(),
			'defaults'             => array(
				'course_topic_icon' => TVA_Const::get_default_course_icon_url(),
			),
			'defaultAuthor'        => new TVA_Author( $logged_in_user->ID ),
			'lessonTypes'          => TVA_Lesson::$types,
			'assessmentTypes'      => TVA_Assessment::$types,
			'postAcceptedStatuses' => TVA_Post::$accepted_statuses,
			'licenseActivated'     => tva_license_activated(),
			'accessRestriction'    => array(
				'options'  => TVA_Access_Restriction::get_possible_options(),
				'defaults' => TVA_Access_Restriction::$defaults,
				'settings' => tva_access_restriction_settings()->ensure_data_exists( null )->admin_localize(),
			),
			'resourceIcons'        => TVA_Resource::$icons,
			'dismissedTooltips'    => get_user_meta( get_current_user_id(), 'tva_dismissed_tooltips', true ),
			'menus'                => tve_get_custom_menus(),
			'visualEditingEnabled' => TTB_Main::uses_builder_templates(),
			'tpm_connected'        => TD_TTW_Connection::get_instance()->is_connected(),
			'tpm_access_ok'        => (int) TVA_Product::is_ready(),
			'max_upload_size'      => tve_get_max_upload_size(),
			'current_routes'       => TVA_Routes::get_all(),
		);
	}

	/**
	 * Gets the singleton instance
	 *
	 * @return TVA_Admin
	 */
	public static function instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Calculates url to $file for admin context
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function url( $file = '' ) {

		return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * Calculates file path tp $file for admin context
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function path( $file = '' ) {

		return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * Prints backbone templates onto print footer script action
	 */
	public function print_backbone_templates() {

		/**
		 * Add extra templates
		 *
		 * Used in tqb_bridge to add templates needed for the drip - quiz builder integration
		 */
		$templates = apply_filters( 'tva_admin_get_backbone_templates', tve_dash_get_backbone_templates( $this->path( '/templates' ), 'templates' ) );
		tve_dash_output_backbone_templates( $templates );
	}

	/**
	 * Prints admin SVG icons in admin footer before body end tag
	 */
	public function print_icons() {
		include __DIR__ . '/assets/admin-icons.svg';
		include THEME_PATH . '/inc/assets/svg/dashboard.svg';
		include TVE_DASH_PATH . '/css/font/dashboard-icons.svg';

		/**
		 * Add extra icons to apprentice admin dashboard
		 *
		 * Used in tqb_bridge to add extra icons for Quiz Builder integration
		 */
		do_action( 'tva_admin_print_icons' );
	}

	/**
	 * Setup the `folded` body class for the main apprentice admin app page
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function body_class( $classes ) {
		global $current_screen;

		if ( $current_screen && static::SCREEN_ID === $current_screen->id ) {
			$classes = trim( 'folded ' . $classes );
		}

		return $classes;
	}

	/**
	 * Allow resources to load for apprentice admin screen
	 *
	 * @param boolean $allow
	 * @param string  $screen_id
	 *
	 * @return bool
	 */
	public function allow_content_sets_resources( $allow, $screen_id ) {

		if ( static::SCREEN_ID === $screen_id ) {
			$allow = true;
		}

		return $allow;
	}
}

/**
 * Shortcut for getting the admin instance
 *
 * @return TVA_Admin
 */
function tva_admin() {
	return TVA_Admin::instance();
}

/**
 * Initialise the admin
 */
tva_admin();
