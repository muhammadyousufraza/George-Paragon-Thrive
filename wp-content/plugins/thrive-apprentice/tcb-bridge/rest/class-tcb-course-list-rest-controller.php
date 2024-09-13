<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Course_List_Rest_Controller
 *
 * @project  : thrive-apprentice
 */
class TCB_Course_List_Rest_Controller {

	/**
	 * Route name
	 *
	 * @var string
	 */
	public static $route = '/course_list_element';

	/**
	 * TCB_Course_List_Rest_Controller constructor.
	 */
	public function __construct() {
		$this->register_routes();

		if ( ! empty( $_POST['content'] ) ) {
			/**
			 * Needed for /html endpoint
			 */
			TCB_Utils::restore_post_waf_content();
		}
	}

	/**
	 * Registers the class routes
	 */
	public function register_routes() {
		register_rest_route( TVA_Const::REST_NAMESPACE, static::$route . '/html', array(
			array(
				/* This should be READABLE, but a lot of data is sent through this request, and it is appended in the request URL string.
				 * Because of the really long URL string, there were 414 errors for some users because the server can block requests like these.
				 * As a solution, we changed this to CREATABLE ( POST ) so the data is added inside the request */
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_html' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'query'   => array(
						'type'     => 'object',
						'required' => false,
					),
					'content' => array(
						'type'     => 'string',
						'required' => false,
					),
				),
			),
		) );

		register_rest_route( TVA_Const::REST_NAMESPACE, static::$route . '/count', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_count' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(
					'query' => array(
						'type'     => 'object',
						'required' => false,
					),
				),
			),
		) );

		register_rest_route( TVA_Const::REST_NAMESPACE, static::$route . '/query_data', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_query_data' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Returns the Course List Markup that satisfy the query
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_html( $request ) {
		$query = (array) $request->get_param( 'query' );

		$content = $request->get_param( 'content' );

		$course_list = \TVA\Architect\Course_List\tcb_course_list_shortcode();

		/* if we receive 'content' in the request, we're going to render the course list by using the given content as a template */
		if ( ! empty( $content ) ) {
			$content = str_replace( array( '{({', '})}' ), array( '[', ']' ), $content );

			$args = $request->get_param( 'args' );

			$query = $course_list->prepare_pagination_query( $args['attr'], $query );
		}

		$courses = $course_list->get_courses( $query )['courses'];

		if ( empty( $content ) ) {
			$response_args = array(
				'courses' => $course_list->localize_dynamic_fields( $courses ),
				'count'   => count( $courses ),
			);
		} else {
			/**
			 * @var \TVA_Course_V2
			 */
			global $tva_active_course;

			$courses_html = array();

			foreach ( $courses as $key => $tva_active_course ) {
				$courses_html[ $key + 1 ] = do_shortcode( $content );
			}

			$response_args = array(
				'html'  => $courses_html,
				'count' => count( $courses_html ),
				'total' => \TVA\Architect\Course_list\tcb_course_list_shortcode()->get_total_course_count( $query ),
			);
		}

		return new WP_REST_Response( $response_args, 200 );
	}

	/**
	 * Counts the number of courses available in the system based on the query
	 *
	 * Calls get_courses function with the count parameter that doesn't create new course objects.
	 * It only returns the number of results
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_count( $request ) {
		$query = (array) $request->get_param( 'query' );

		$number = \TVA\Architect\Course_list\tcb_course_list_shortcode()->get_total_course_count( $query );

		return new WP_REST_Response( $number, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_query_data( $request ) {
		$course_list = \TVA\Architect\Course_List\tcb_course_list_shortcode();

		$courses = $course_list->get_courses( array( 'limit' => 10000 ) )['courses'];

		$labels = tva_get_labels();
		$levels = TVA_Level::get_items();

		/**
		 * Add the all checkboxes
		 */
		array_unshift( $labels, array(
			'ID'    => 'all',
			'title' => esc_attr( __( 'All restricted content levels', 'thrive-apprentice' ) ),
		), array(
			'ID'   => - 1000,
			'name' => esc_attr( __( 'No restriction - free to all visitors', 'thrive-apprentice' ) ),
		) );

		array_unshift( $levels, array(
			'ID'   => 'all',
			'name' => esc_attr( __( 'All difficulty levels', 'thrive-apprentice' ) ),
		) );

		return new WP_REST_Response( array(
			'courses'  => $course_list->localize_dynamic_fields( $courses ),
			'bundles'  => TVA_Course_Bundles_Manager::get_bundles(),
			'topics'   => $course_list->get_topics(),
			'levels'   => $levels,
			'labels'   => $labels,
			'progress' => array(
				array(
					'ID'    => 'all',
					'title' => esc_attr( __( 'All course progress', 'thrive-apprentice' ) ),
				),
				array(
					'ID'    => TVA_Const::TVA_COURSE_PROGRESS_NO_ACCESS,
					'title' => esc_attr( __( 'No access', 'thrive-apprentice' ) ),
				),
				array(
					'ID'    => 'progress-has-access',
					'title' => esc_attr( __( 'Has access - all progress', 'thrive-apprentice' ) ),
				),
				array(
					'ID'    => TVA_Const::TVA_COURSE_PROGRESS_NOT_STARTED,
					'title' => esc_attr( __( 'Not started', 'thrive-apprentice' ) ),
				),
				array(
					'ID'    => TVA_Const::TVA_COURSE_PROGRESS_IN_PROGRESS,
					'title' => esc_attr( __( 'In Progress', 'thrive-apprentice' ) ),
				),
				array(
					'ID'    => TVA_Const::TVA_COURSE_PROGRESS_COMPLETED,
					'title' => esc_attr( __( 'Completed', 'thrive-apprentice' ) ),
				),
			),
		), 200 );
	}
}
