<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Admin_View_Test_Page
 * Used for viewing a test details(report)
 *
 * - enqueues the scripts/styles
 * - localizes required data based on test id from query string
 */
class Thrive_AB_Admin_View_Test_Page {

	/**
	 * @var int
	 */
	protected $_test_id;

	/**
	 * @var Thrive_AB_Test
	 */
	protected $test;


	public function __construct() {

		$test_id = ! empty( $_GET['test_id'] ) ? sanitize_key( $_GET['test_id'] ) : null;

		if ( $test_id ) {

			$this->_test_id = (int) $test_id;

			/**
			 * Enqueue Scripts
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			/**
			 * Enqueue Styles
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ), PHP_INT_MAX );

			add_action( 'admin_print_footer_scripts', array( $this, 'print_backbone_templates' ) );
			add_action( 'admin_print_footer_scripts', array( $this, 'include_svg' ) );

			add_filter( 'admin_title', array( $this, 'get_title' ), 10, 2 );
		}
	}

	public function get_title() {

		$admin_title = thrive_ab()->plugin_name() . ' - ' . $this->_get_test()->title;

		return $admin_title;
	}

	/**
	 * enqueue scripts
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'backbone' );

		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		wp_enqueue_script( 'thrive-ab-dashboard', thrive_ab()->url( 'assets/js/dist/app' . $js_suffix ), array(
			'backbone',
			'jquery-ui-autocomplete',
			'tve-dash-main-js',
		), Thrive_AB::V, true );

		$this->localize_data();

		/**
		 * Enqueue dash js file cos it is needed for Modals, Views and Materialize
		 */
		tve_dash_enqueue_script( 'tve-dash-main-js', TVE_DASH_URL . '/js/dist/tve-dash.min.js', array(
			'jquery',
			'backbone',
		), false, true );

		tve_dash_enqueue_script( 'tve-dash-highcharts', TVE_DASH_URL . '/js/util/highcharts/highcharts.js', array(
			'jquery',
		), false, true );

		tve_dash_enqueue_script( 'tve-dash-highcharts-more', TVE_DASH_URL . '/js/util/highcharts/highcharts-more.js', array(
			'jquery',
			'tve-dash-highcharts',
		), false, true );
	}

	/**
	 * enqueue styles
	 */
	public function enqueue_styles() {

		/**
		 * Inherit CSS from dashboard
		 */
		tve_dash_enqueue_style( 'tve-dash-styles-css', TVE_DASH_URL . '/css/styles.css' );

		wp_enqueue_style( 'thrive-ab', thrive_ab()->url( 'assets/css/dashboard.css' ), array(
			'tve-dash-styles-css',
		), Thrive_AB::V );

		/**
		 * Use this css file to overwrite the css for this page only
		 */
		wp_enqueue_style( 'thrive-ab-view-test-page', thrive_ab()->url( 'assets/css/admin/tab-view-test-page.css' ), array(
			'tve-dash-styles-css',
		), Thrive_AB::V );
	}

	/**
	 * put the backbone templates into page for later usage
	 */
	public function print_backbone_templates() {

		$templates = tve_dash_get_backbone_templates( thrive_ab()->path( 'includes/views/backbone' ), 'backbone' );
		tve_dash_output_backbone_templates( $templates );
	}

	protected function _get_test( $test_id = null ) {

		if ( ! ( $this->test instanceof Thrive_AB_Test ) ) {
			$this->test = new Thrive_AB_Test( (int) $this->_test_id );
		}

		return $this->test;
	}

	private function localize_data() {

		$data = array();

		try {

			$test = $this->_get_test();
			$test->get_items();

			$page           = new Thrive_AB_Page( (int) $test->page_id );
			$report_manager = new Thrive_AB_Report_Manager();

			$data['current_test'] = $test->get_data();
			$data['page']         = $page->get_data();
			$data['t']            = include( thrive_ab()->path( 'includes/i18n.php' ) );
			$data['chart_colors'] = array(
				'#20a238',
				'#2f82d7',
				'#fea338',
				'#dd383d',
				'#ab31a4',
				'#95d442',
				'#36c4e2',
				'#525252',
				'#f3643e',
				'#e26edd',
			);

			$data['license'] = Thrive_AB::license_data();

			$data['test_chart'] = $report_manager->get_test_chart_data(
				array(
					'test_id' => (int) $this->_test_id,
					'type'    => 'conversion_rate',
				)
			);

			$data['ajax'] = array(
				'url'               => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( Thrive_AB_Ajax::NONCE_NAME ),
				'action'            => Thrive_AB_Ajax::$action,
				'controller_action' => Thrive_AB_Ajax::$controller_action,
			);

		} catch ( Exception $e ) {
			die( $e->getMessage() );
		}


		wp_localize_script( 'thrive-ab-dashboard', 'ThriveAB', $data );
	}

	/**
	 * puts the page html required for viewing a test
	 */
	public function render() {
		echo '<div id="tab-dashboard-wrapper"></div>';
	}

	/**
	 * include svg file with all required icons
	 * usually used on admin_print_footer_scripts
	 */
	public function include_svg() {

		include thrive_ab()->path( '/assets/fonts/icons.svg' );
	}
}

