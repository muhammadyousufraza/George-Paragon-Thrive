<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Dashboard {

	static protected $_instance;

	private function __construct() {

		$this->_init();
	}

	/**
	 * The only point to entry in this class
	 * and it is instantiated only if the query var is true
	 */
	public static function instance() {

		if ( thrive_ab()->get_query()->get_var( 'thrive-variations' ) !== 'true' ) {
			return null;
		}

		if ( ! current_user_can( 'edit_posts' ) || ! Thrive_AB_Product::has_access() ) {
			return null;
		}

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		if ( function_exists( 'tve_do_not_cache_page' ) ) {
			tve_do_not_cache_page();
		}

		return self::$_instance;
	}

	private function _clear_scripts() {

		global $wp_filter;

		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_footer' );

		remove_all_actions( 'wp_enqueue_scripts' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_footer_scripts' );
		remove_all_actions( 'print_footer_scripts' );
		remove_all_actions( 'admin_bar_menu' );

		remove_all_filters( 'template_redirect' );
		remove_all_filters( 'page_template' );

		add_action( 'wp_head', 'wp_enqueue_scripts' );
		add_action( 'wp_head', 'wp_print_styles' );
		add_action( 'wp_head', 'wp_print_head_scripts' );

		add_action( 'wp_head', '_wp_render_title_tag', 1 );

		add_action( 'wp_footer', '_wp_footer_scripts' );
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'wp_footer', 'print_footer_scripts', 1000 );
	}

	/**
	 * Clear the styles and scripts and add required ones
	 */
	private function _init() {

		if ( ! thrive_ab()->license_activated() ) {
			return;
		}

		$this->_clear_scripts();

		/**
		 * Layout
		 */
		add_filter( 'page_template', array( $this, 'layout' ) );
		add_filter( 'home_template', array( $this, 'layout' ) );
		add_action( 'template_redirect', array( $this, 'layout' ) );

		/**
		 * Enqueue Scripts
		 */
		add_action( 'wp_print_footer_scripts', array( $this, 'enqueue_scripts' ) );

		/**
		 * Enqueue Styles
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), PHP_INT_MAX );

		add_action( 'wp_print_footer_scripts', array( $this, 'print_backbone_templates' ) );

		add_action( 'wp_print_footer_scripts', 'tve_dash_backbone_templates' );

		/**
		 * Works only if the theme supports title-tag
		 * Thrive Themes do not support title-tag
		 *
		 * @see _wp_render_title_tag
		 *
		 */
		add_filter( 'document_title_parts', array( $this, 'get_title' ) );
	}

	/**
	 * HTML title to be displayed in AB Dashboard
	 * Works only if current_theme_supports( 'title-tag' )
	 *
	 * @param $title_tags
	 *
	 * @return mixed
	 */
	public function get_title( $title_tags ) {

		array_unshift( $title_tags, thrive_ab()->plugin_name() );

		return $title_tags;
	}

	/**
	 * Specify the template file to be used on dashboard
	 *
	 * @return string
	 */
	public function layout() {

		include dirname( __FILE__ ) . '/views/layouts/dashboard.php';
		die;
	}

	/**
	 * Enqueues the necessary script files for AB Dashboard
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

	private function localize_data() {

		global $post;

		try {
			if ( is_home() && 'page' === get_option( 'show_on_front' ) && ( $post = get_option( 'page_on_front' ) ) ) {
				$post = get_post( $post );
			}

			$page = new Thrive_AB_Page( $post );
		} catch ( Exception $e ) {
			return;
		}


		$data = array(
			'page'         => $page->get_data(),
			'ajax'         => array(
				'url'               => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( Thrive_AB_Ajax::NONCE_NAME ),
				'action'            => Thrive_AB_Ajax::$action,
				'controller_action' => Thrive_AB_Ajax::$controller_action,
			),
			't'            => include( thrive_ab()->path( 'includes/i18n.php' ) ),
			'chart_colors' => array(
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
			),
		);

		$test_id = thrive_ab()->get_query()->get_var( 'test-id' );

		if ( $test_id ) {
			$current_test = $page->get_tests( array(
				'id' => $test_id,
			), 'obj' );
			if ( count( $current_test ) ) {
				$current_test = current( $current_test );
			}
			if ( $current_test instanceof Thrive_AB_Test ) {

				if ( thrive_ab()->get_query()->get_var( 'generate-stats' ) ) {
					$this->generate_random_stats( 12, $current_test );
				}

				$current_test->get_items();
				$current_test = $current_test->get_data();
			}
			$data['current_test'] = $current_test;
		}

		if ( empty( $data['current_test'] ) && $page->get_running_test() instanceof Thrive_AB_Test ) {
			$data['running_test'] = $page->get_running_test()->get_data();
		} else {
			$all_variations     = $this->get_variations_for_page( $page );
			$data['variations'] = $all_variations['published'];
			$data['archived']   = $all_variations['archived'];
		}

		if ( ! empty( $data['current_test'] ) || ! empty( $data['running_test'] ) ) {

			$test_id = ! isset( $test_id ) ? $data['running_test']['id'] : $data['current_test']['id'];

			$report_manager = new Thrive_AB_Report_Manager();

			$data = array_merge(
				$data,
				array(
					'test_chart' => $report_manager->get_test_chart_data(
						array(
							'test_id' => (int) $test_id,
							'type'    => 'conversion_rate',
						)
					),
				)
			);
		}

		$visit_page_monetary                                       = array(
			'name'  => 'Visit Page',
			'label' => __( 'A customer visits certain pages on my site', 'thrive-ab-page-testing' ),
			'slug'  => 'visit_page',
		);
		$data['monetary_services'][ $visit_page_monetary['slug'] ] = $visit_page_monetary;

		$data['monetary_services'] = apply_filters( 'thrive_ab_monetary_services', $data['monetary_services'] );

		$data['license'] = Thrive_AB::license_data();

		wp_localize_script( 'thrive-ab-dashboard', 'ThriveAB', $data );
	}

	/**
	 * Generate event logs for a test
	 *
	 * @param $days int
	 * @param $test Thrive_AB_Test
	 *
	 * @throws
	 */
	protected function generate_random_stats( $days, $test ) {

		$days = intval( $days );

		if ( $days <= 0 || $days > 100 || ! ( $test instanceof Thrive_AB_Test ) ) {
			return;
		}

		$variation_ids   = array();
		$variation_items = array();

		$test_items = $test->get_items();

		/** @var Thrive_AB_Test_Item $test_item */
		foreach ( $test_items as $test_item ) {
			$variation_ids[]                             = $test_item->variation_id;
			$variation_items[ $test_item->variation_id ] = $test_item;
		}

		$goal_page     = null;
		$goal_page_ids = array_keys( $test->goal_pages );
		$goal_pages    = $test->goal_pages;

		if ( count( $goal_page_ids ) === 1 ) {
			$goal_page = $goal_pages[ $goal_page_ids[0] ];
		} else {
			$step      = 1000;
			$index     = (int) rand( 0, count( $goal_page_ids ) * $step );
			$goal_page = $goal_pages[ $goal_page_ids[ $index ] ];
		}

		/**
		 * Loop through dates
		 */
		for ( $d = $days; $d > 0; $d -- ) {
			$date = date( 'Y-m-d H:i:s', time() - ( $d * 60 * 60 * 24 ) );

			/**
			 * loop variations
			 */
			foreach ( $variation_ids as $variation_id ) {

				$test_item = $variation_items[ $variation_id ];

				$step              = 1000;
				$total_impressions = rand( 0, 30 * $step );
				$total_impressions = intval( $total_impressions / $step );

				$total_conversions = rand( 0, $total_impressions * $step );
				$total_conversions = intval( $total_conversions / $step );

				$event_model = array(
					'page_id'      => $test->page_id,
					'variation_id' => $variation_id,
					'test_id'      => $test->id,
					'date'         => $date,
				);

				/**
				 * log impressions
				 */
				for ( $i = 0; $i < $total_impressions; $i ++ ) {

					$impression               = $event_model;
					$impression['event_type'] = 1;

					$event_log = new Thrive_AB_Event( $impression );
					$event_log->save();

					$test_item->impressions ++;
					$test_item->unique_impressions ++;
				}

				/**
				 * log conversions
				 */
				for ( $c = 0; $c < $total_conversions; $c ++ ) {
					$conversion               = $event_model;
					$conversion['event_type'] = 2;
					$conversion['revenue']    = isset( $goal_page['revenue'] ) ? $goal_page['revenue'] : 0;
					$conversion['goal_page']  = isset( $goal_page['post_id'] ) ? $goal_page['post_id'] : null;

					$event_log = new Thrive_AB_Event( $conversion );
					$event_log->save();

					$test_item->conversions ++;
					$test_item->revenue = $test_item->revenue + ( isset( $goal_page['revenue'] ) ? $goal_page['revenue'] : 0 );
				}

				$test_item->save();

				if ( $d === $days ) {
					$test->date_started = $date;
					$test->save();
				}
			}
		}
	}

	/**
	 * If the page does not have any variations automatically creates a new once as control
	 * On failure of creating the control variation empty array of variations is returned
	 *
	 * @param $page Thrive_AB_Page
	 *
	 * @return array
	 */
	public function get_variations_for_page( $page ) {
		$result = array(
			'published' => array(),
			'archived'  => array(),
			'deleted'   => array(),
		);
		try {
			$variations = $page->get_variations( array( 'all' => true ) );

			foreach ( $variations as $variation ) {
				$variation_object    = new Thrive_AB_Variation( (int) $variation['ID'] );
				$meta                = $variation_object->get_meta();
				$variation['status'] = $meta->get( 'status' );
				if ( isset( $result[ $variation['status'] ] ) ) {
					array_push( $result[ $variation['status'] ], $variation );
				}
			}

		} catch ( Exception $e ) {
			die( $e );
		}

		return $result;
	}

	/**
	 * Enqueue css files used on Dashboard
	 */
	public function enqueue_styles() {

		/**
		 * Inherit CSS from dashboard
		 */
		tve_dash_enqueue_style( 'tve-dash-styles-css', TVE_DASH_URL . '/css/styles.css' );

		wp_enqueue_style( 'thrive-ab', thrive_ab()->url( 'assets/css/dashboard.css' ), array(
			'tve-dash-styles-css',
		), Thrive_AB::V );
	}

	/**
	 * Echoes in HTML the backbone templates used on Dashboard
	 * Uses Thrive Dashboard functions
	 */
	public function print_backbone_templates() {

		$templates = tve_dash_get_backbone_templates( thrive_ab()->path( 'includes/views/backbone' ), 'backbone' );
		tve_dash_output_backbone_templates( $templates );
	}
}
