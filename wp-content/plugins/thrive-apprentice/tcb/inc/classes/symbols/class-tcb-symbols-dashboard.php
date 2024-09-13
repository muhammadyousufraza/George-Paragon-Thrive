<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Symbols_Dashboard
 */
class TCB_Symbols_Dashboard {

	/**
	 * @var string
	 */
	private $_symbols_dashboard_page = 'tcb_symbols_dashboard';

	private $_tcb_admin_dashboard = 'tcb_admin_dashboard';

	/**
	 * Setup everything for the symbols dashboard
	 *
	 * TCB_Symbols_Dashboard constructor.
	 */
	public function __construct() {
		$this->hooks();
		$this->includes();
	}

	public function includes() {
	}

	/**
	 * Hooks used for the symbol dashboard
	 */
	public function hooks() {
		//add dashboard page so we can access it
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		//add symbols card in thrive dashboard
		add_action( 'current_screen', [ $this, 'dash_features' ] );
	}

	/**
	 * Add symbols card in thrive dashboard
	 */
	public function dash_features() {
		/**
		 * if screen = main dashboard then enable and display the feature
		 */
		if ( tve_get_current_screen_key() === 'toplevel_page_tve_dash_section' ) {
			add_filter( 'tve_dash_filter_features', [ $this, 'admin_symbols_feature' ] );
			add_filter( 'tve_dash_features', [ $this, 'admin_enable_feature' ] );
		}

	}

	/**
	 * Add new feature ( card ) to thrive dashboard
	 *
	 * @param array $features
	 *
	 * @return mixed
	 */
	public function admin_symbols_feature( $features ) {
		if ( tcb_has_external_cap() ) {
			$features['symbols_manager'] = array(
				'icon'        => 'tvd-ct-symbols-icon',
				'title'       => __( 'Global Elements', 'thrive-cb' ),
				'description' => __( 'Create and manage templates, symbols, notification toasts, headers and footers', 'thrive-cb' ),
				'btn_link'    => add_query_arg( 'page', $this->_tcb_admin_dashboard . '#templatessymbols', admin_url( 'admin.php' ) ),
				'btn_text'    => __( 'Manage Global Elements', 'thrive-cb' ),
			);
		}

		return $features;
	}

	/**
	 * Enable feature ( card ) in thrive dashboard
	 *
	 * @param array $features
	 *
	 * @return mixed
	 */
	public function admin_enable_feature( $features ) {
		$features['symbols_manager'] = true;

		return $features;
	}

	/**
	 * Create page for symbols dashboard
	 */
	public function admin_menu() {
		add_submenu_page( '', __( 'Symbols', 'thrive-cb' ), __( 'Symbols', 'thrive-cb' ), tcb_has_external_cap( true ), $this->_symbols_dashboard_page, [
			$this,
			'admin_symbols_dashboard',
		] );
	}

	/**
	 * Include the file for the symbols dashboard
	 */
	public function admin_symbols_dashboard() {
		include TVE_TCB_ROOT_PATH . 'inc/views/symbols/symbols-dashboard.php';
	}
}

global $tcb_symbol_dashboard;
/**
 * Main instance of TCB Symbols Dashboard
 *
 * @return TCB_Symbols_Dashboard
 */
function tcb_symbol_dashboard() {
	global $tcb_symbol_dashboard;

	if ( ! $tcb_symbol_dashboard ) {
		$tcb_symbol_dashboard = new TCB_Symbols_Dashboard();
	}

	return $tcb_symbol_dashboard;
}

tcb_symbol_dashboard();
