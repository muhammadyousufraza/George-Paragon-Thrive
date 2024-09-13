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
 * Class Thrive_AB_Variation_Manager
 *
 * Applies logic based on info: page, variation(s)
 */
class Thrive_AB_Variation_Manager {

	protected static $_instance;

	/**
	 * Flag for overwriting the current query
	 *
	 * @var bool
	 */
	protected static $_querying_variations;

	/**
	 * @var Thrive_AB_Test|null
	 */
	protected $_running_test;

	protected function __construct() {

		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
	}

	public static function instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Overwrite the queried post if the post is_singular(if it's page)
	 *
	 * @param $posts
	 * @param $wp_query WP_Query
	 *
	 * @return mixed
	 */
	public function the_posts( $posts, $wp_query ) {

		self::$_querying_variations = false;

		if ( $this->should_determine_variation( $wp_query ) ) {

			if ( function_exists( 'tve_do_not_cache_page' ) ) {
				tve_do_not_cache_page();
			}
			$variation = $this->determine_variation();

			/**
			 * enqueue the frontend script to allow
			 * - making conversions for html forms if the test type is 'optins' only
			 * - register lazy impressions on DOM Ready
			 */
			$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';
			thrive_ab()->enqueue_script( 'tab-frontend', thrive_ab()->url( 'assets/js/dist/tab-frontend' . $js_suffix ), array(
				'tve_frontend',
				'tve-dash-frontend'
			), true, true );
			$localize_data = array(
				'impression_data' => array(
					'action'       => Thrive_AB_Ajax::REGISTER_IMPRESSION_ACTION_NAME,
					'test_id'      => $this->_running_test->id,
					'page_id'      => $this->_running_test->page_id,
					'variation_id' => (int) $variation->ID,
				),
				'test_type'       => $this->_running_test->type,

			);

			if ( $this->_running_test->page_id != $variation->ID ) {
				$variation_data['page_id']      = $this->_running_test->page_id;
				$variation_data['variation_id'] = $variation->ID;

				add_filter( 'tcb_lp_body_class', function ( $classes ) use ( $variation_data ) {
					$classes .= ' page-id-' . $variation_data['variation_id'];

					return $classes;
				}, 10 );
			}

			wp_localize_script( 'tab-frontend', 'ThriveAB', $localize_data );

			try {
				/**
				 * let this here in case we need to implement option for user to register stats on server or lazy loading
				 * if there has to be on server side just uncomment next line
				 */
				//Thrive_AB_Event_Manager::do_impression( $this->_running_test->page_id, $this->_running_test->id, $variation->ID );

			} catch ( Exception $e ) {

			}
		}

		if ( self::$_querying_variations === false && isset( $variation ) ) {
			$wp_query->post = $variation;
			$posts          = array( $variation );
		}

		return $posts;
	}

	/**
	 * Read the variations from DB and determine one to be displayed
	 * Call this function if there is a running test only so we are sure there is a test running on this context/instance
	 *
	 * @return WP_Post always
	 */
	protected function determine_variation() {
		/**
		 * If a cookie is set with a certain variation, the same variation should be displayed
		 */
		$cookie_variation_id = Thrive_AB_Cookie_Manager::get_cookie( $this->_running_test->id, $this->_running_test->page_id, 1 );

		$variation = $cookie_variation_id ? get_post( $cookie_variation_id ) : $this->_determine_traffic_variation();

		if ( $variation === null ) {
			$variation = get_post( $this->_running_test->page_id );
		}

		return $variation;
	}

	/**
	 * Checks if the current request should display variations content
	 *
	 * @param $wp_query WP_Query
	 *
	 * @return bool
	 */
	protected function should_determine_variation( $wp_query ) {

		$should = function_exists( 'tve_dash_is_crawler' ) && ! tve_dash_is_crawler( true );

		$should = $should && $wp_query->is_singular;
		/**
		 * when user wants to edit a page with TAr which has variations
		 */
		$should = $should && defined( 'TVE_EDITOR_FLAG' ) && ! isset( $_GET[ TVE_EDITOR_FLAG ] );
		/**
		 * if user wants to see the page variations dashboard
		 */
		$should = $should && ! thrive_ab()->is_dashboard();

		/**
		 * if current user is not admin
		 */
		$should = $should && ! ( current_user_can( 'edit_posts' ) || Thrive_AB_Product::has_access() );

		if ( $should ) {
			$post = isset( $wp_query->posts[0] ) ? $wp_query->posts[0] : null;

			Thrive_AB_Event_Manager::check_thank_you_page( $post );
			$this->_init_test( $wp_query->queried_object_id ? $wp_query->queried_object_id : ( $post instanceof WP_Post ? $post->ID : null ) );
		}

		/**
		 * The control variation should be displayed if the AB Test is not running
		 */
		$should = $should && $this->_running_test instanceof Thrive_AB_Test;

		return $should;
	}

	/**
	 * Based on traffic allocated on each variations return the corresponding one
	 * Please complete this doc based on todo
	 *
	 * @return WP_POST|null
	 */
	protected function _determine_traffic_variation() {

		$variations  = $this->query_variations();
		$_rand       = function_exists( 'mt_rand' ) ? mt_rand( 0, 101 ) : rand( 0, 101 );
		$measurement = 0;

		/**@var Thrive_AB_Variation $variation */
		foreach ( $variations as $variation ) {
			$traffic = $variation->get_meta()->get( 'traffic' );
			if ( ( $_rand >= $measurement ) && ( $_rand < ( $measurement + $traffic ) ) ) {

				return $variation->get_post();
			}
			$measurement += $traffic;
		}

		return null;
	}

	/**
	 * @param $filters array
	 *
	 * @return array|null
	 */
	protected function query_variations( $filters = array() ) {

		$page = new Thrive_AB_Page( (int) $this->_running_test->page_id );

		self::$_querying_variations = true;

		return $page->get_variations( $filters, 'obj' );
	}

	protected function _init_test( $page_id ) {

		if ( empty( $page_id ) ) {
			return $this;
		}

		$test_manager        = new Thrive_AB_Test_Manager();
		$this->_running_test = $test_manager->get_running_test( $page_id );

		return $this;
	}
}

return Thrive_AB_Variation_Manager::instance();
