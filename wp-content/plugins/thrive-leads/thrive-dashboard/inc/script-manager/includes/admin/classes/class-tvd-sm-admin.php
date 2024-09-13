<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TVD_SM_Admin
 */
class TVD_SM_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'current_screen', array( $this, 'conditional_hooks' ) );
	}

	public function includes() {
		include_once 'class-tvd-sm-rest-scripts-controller.php';
	}

	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'admin_create_rest_routes' ) );
	}

	public function init() {
		$this->includes();
		$this->hooks();
	}

	public function enqueue_scripts() {
		if ( tve_get_current_screen_key() === 'admin_page_tve_dash_script_manager' ) {
			tve_dash_enqueue();
			tve_dash_enqueue_style( 'tvd-sm-admin-css', TVD_SM_Constants::url( '/assets/css/admin.css' ) );
			tve_dash_enqueue_script( 'tvd-sm-admin-js', TVD_SM_Constants::url( 'assets/js/dist/admin.min.js' ), array(
				'tve-dash-main-js',
				'jquery',
				'jquery-ui-sortable',
				'backbone',
			), false, true );

			$params = array(
				'routes'                => array(
					'scripts'                  => get_rest_url() . 'script-manager/v1/scripts',
					'scripts_order'            => get_rest_url() . 'script-manager/v1/scripts-order',
					'clear_page_level_scripts' => get_rest_url() . 'script-manager/v1/clear-old-scripts',
				),
				'is_ttb_active'         => TVD_SM_Constants::is_ttb_active(),
				'is_tar_active'         => TVD_SM_Constants::is_architect_active(),
				'nonce'                 => wp_create_nonce( 'wp_rest' ),
				'scripts'               => tah()->tvd_sm_get_scripts(),
				'script_placement_text' => array(
					TVD_SM_Constants::HEAD_PLACEMENT       => 'Before ' . htmlentities( '</head>' ),
					TVD_SM_Constants::BODY_OPEN_PLACEMENT  => 'After ' . htmlentities( '<body>' ),
					TVD_SM_Constants::BODY_CLOSE_PLACEMENT => 'Before ' . htmlentities( '</body>' ),
				),
				'translations'          => include TVD_SM_Constants::path( 'includes/i18n.php' ),
				'dash_url'              => admin_url( 'admin.php?page=tve_dash_section' ),
				'url'                   => TVD_SM_Constants::url(),
				'recognized_scripts'    => array(
					'keywords' => TVD_SM_Constants::get_recognized_scripts_keywords(),
					'data'     => TVD_SM_Constants::get_recognized_scripts_data(),
				),
			);

			wp_localize_script( 'tvd-sm-admin-js', 'TVD_SM_CONST', $params );
		}
	}

	/**
	 * Hook based on the current screen
	 */
	public function conditional_hooks() {
		/**
		 * if screen = script_manager feature then load all the templates for the SM admin side
		 */
		if ( tve_get_current_screen_key() === 'admin_page_tve_dash_script_manager' ) {
			add_action( 'admin_print_scripts', array( $this, 'admin_backbone_templates' ), 9 );
			add_filter( 'admin_title', array( $this, 'change_title' ) );
		}
	}

	public function change_title( $title ) {
		return __( 'Script Manager', 'thrive-dash' ) . $title;
	}

	/**
	 * Add page to admin menu so the page could be accessed
	 */
	public function admin_menu() {
		add_submenu_page( '', __( 'Landing Pages Analytics & Scripts', 'thrive-dash' ), __( 'Landing Pages Analytics & Scripts', 'thrive-dash' ), 'manage_options', 'tve_dash_script_manager', array(
			$this,
			'admin_dashboard',
		) );
	}

	/**
	 * Main TVD_SM page content
	 */
	public function admin_dashboard() {
		include TVD_SM_Constants::path( 'includes/admin/views/dashboard.php' );
	}

	public function admin_create_rest_routes() {
		$controller = new TVD_SM_REST_Scripts_Controller();
		$controller->register_routes();
	}

	/**
	 * Add templates as scripts in the footer.
	 */
	public function admin_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( TVD_SM_Constants::path( 'includes/admin/views/templates' ), 'templates' );
		tve_dash_output_backbone_templates( $templates );
	}
}

return new TVD_SM_Admin();
