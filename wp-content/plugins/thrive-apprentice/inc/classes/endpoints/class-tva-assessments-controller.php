<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\Architect\Assessment\Main;
use TVA\Architect\Assessment\Shortcodes;
use TVA\Assessments\Grading\Base as Grading_Base;
use TVA\Assessments\Inbox;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Assessments\Types\Base as Type_Base;
use function TVA\TQB\tva_tqb_integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVA_Assessments_Controller extends TVA_REST_Controller {
	/**
	 * @var string
	 */
	public $base = 'assessments';

	public function register_routes() {

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/localize', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'localize' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/get_data', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_data' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
			],
		] );


		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/users', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_users' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/dropbox_file_data', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'dropbox_file_data' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'file_id' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/html', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_html' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'id'      => [
						'type'     => 'int',
						'required' => true,
					],
					'post_id' => [
						'type'     => 'int',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version,
			$this->base . '/toggle_template_shortcode', [
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'toggle_template_shortcode' ],
					'permission_callback' => [ 'TVA_Product', 'has_access' ],
					'args'                => [
						'id'    => [
							'type'        => 'integer',
							'description' => 'ID of the assessment',
							'required'    => true,
						],
						'value' => [
							'type'        => 'integer',
							'description' => 'Possible values that may have',
							'required'    => true,
							'enum'        => [ 0, 1 ],
						],
					],
				],
			] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'post_title' => [
						'type'     => 'string',
						'required' => true,
					],
					'course_id'  => [
						'type'     => 'int',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<ID>[\d]+)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [],
			],
		] );

		register_rest_route( static::$namespace . 2, '/' . $this->base . '/duplicate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'duplicate' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/settings', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_settings' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/save_grade', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_grade' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'id'            => [
						'type'     => 'integer',
						'required' => true,
					],
					'assessment_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'grade'         => [
						'type'     => 'string',
						'required' => true,
					],
					'notes'         => [
						'type'     => 'string',
						'required' => false,
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/quiz_result_data', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'quiz_result_data' ],
				'permission_callback' => [ 'TVA_Product', 'has_access' ],
				'args'                => [
					'user_id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
		] );
	}

	/**
	 * Retrieves the file URL from dropbox API
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function dropbox_file_data( $request ) {
		$file_id = $request->get_param( 'file_id' );

		$api           = Thrive_Dash_List_Manager::connection_instance( 'dropbox' );
		$result        = $api->get_file_data( $file_id );
		$result['url'] = add_query_arg( 'raw', '1', remove_query_arg( 'dl', $result['url'] ) );

		return new WP_REST_Response(
			$result
		);
	}

	/**
	 * Toggles the template shortcode depending on the content
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function toggle_template_shortcode( $request ) {
		$id       = (int) $request->get_param( 'id' );
		$response = [ 'success' => 0 ];

		if ( ! empty( $id ) && get_post_type( $id ) === TVA_Const::ASSESSMENT_POST_TYPE ) {
			$assessment = new TVA_Assessment( $id );
			$assessment->toggle_assessment_in_content( (int) $request->get_param( 'value' ) );

			$response['success'] = 1;
		}

		return new WP_REST_Response( $response );
	}

	/**
	 * Get the list of users with assessments
	 *
	 * @return WP_REST_Response
	 */
	public function get_users() {
		return new WP_REST_Response(
			Inbox::get_authors()
		);
	}

	/**
	 * Get the list of assessments
	 *
	 *
	 * @return WP_REST_Response
	 */
	public function localize() {
		return new WP_REST_Response(
			Inbox::get_assessments()
		);
	}

	public function get_data( WP_REST_Request $request ) {
		$course_ids        = $request->get_param( 'course_ids' );
		$assessment_ids    = $request->get_param( 'assessment_ids' );
		$student_ids       = $request->get_param( 'student_ids' );
		$assessment_status = $request->get_param( 'assessment_status' );
		$outdated          = $request->get_param( 'outdated' );
		$limit             = (int) $request->get_param( 'limit' );
		$page              = (int) $request->get_param( 'page' );
		$count             = (int) $request->get_param( 'count' );

		if ( ! empty( $assessment_status ) && ! in_array( $assessment_status, [ TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT, TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED, TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED ] ) ) {
			return new WP_REST_Response( [
				'error'   => true,
				'message' => __( 'Invalid assessment status', 'thrive-apprentice' ),
			] );
		}

		$fn = 'get_assessment_' . ( $count ? 'count' : 'submissions' );

		$filters = compact( 'course_ids', 'assessment_ids', 'assessment_status', 'student_ids', 'limit', 'page', 'outdated' );

		return new WP_REST_Response(
			Inbox::$fn( $filters )
		);
	}


	/**
	 * Returns the HTML needed for the assessment shortcode
	 * Triggered from the Editor Page when the assessment is first loaded or assessment post changed
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_html( $request ) {
		$id          = (int) $request->get_param( 'id' );
		$post_id     = (int) $request->get_param( 'post_id' );
		$template_id = (string) $request->get_param( 'template_id' ); //String because it can contain also internal suffix

		if ( empty( $id ) ) {
			return new WP_REST_Response( [ 'message' => 'Invalid Assessment ID' ], 401 );
		}

		$parsed_id = $id;
		if ( $id === - 1 ) {
			$parsed_id = $post_id;
		}

		//We need to set this flag to true before we render the assessment shortcode
		Main::$is_editor_page = is_editor_page_raw( true );

		/**
		 * Allows the system to ignore the cloud default template for apprentice and always render the empty template
		 *
		 * - Used in Template Builder WebSite to start a new template from the default one
		 */
		$get_cloud_template = apply_filters( 'tva_get_cloud_default_template', true );

		if ( $get_cloud_template ) {

			//Make a call to thrive-template-cloud to fetch the defined template
			$default_template_data = tve_get_cloud_template_data(
				'assessment',
				[
					'skip_do_shortcode' => true,
					'id'                => ! empty( $template_id ) && ! defined( 'TCB_CLOUD_API_LOCAL' ) ? $template_id : 'default',
					'type'              => 'assessment',
				]
			);
		}

		if ( empty( $default_template_data ) || $default_template_data instanceof WP_Error ) {
			$content = Shortcodes::assessment( [ 'assessment-id' => $parsed_id ] );
			$css     = '';
		} else {
			$data_ct      = $default_template_data['type'] . '-' . $default_template_data['id'];
			$data_ct_name = esc_attr( $default_template_data['name'] );

			$search  = '[tva_assessment ';
			$replace = "[tva_assessment assessment-id='$parsed_id' ct='$data_ct' ct-name='$data_ct_name' ";

			$content = str_replace( $search, $replace, $default_template_data['content'] );
			$css     = $default_template_data['head_css'];
		}

		return new WP_REST_Response( [
			'html' => do_shortcode( $content ),
			'css'  => $css,
		], 200 );
	}

	public function create_item( $request ) {
		$params     = $request->get_params();
		$assessment = new TVA_Assessment( $params );
		$assessment->set_grading( Grading_Base::factory( $params ) );
		$assessment->set_type_class( Type_Base::factory( $params ) );


		try {
			$assessment->save();
		} catch ( Exception $e ) {
			return new WP_Error( 'tva_assessment_save_error', $e->getMessage() );
		}

		return $assessment;
	}

	public function delete_item( $request ) {

		$assessment = new TVA_Assessment( $request->get_param( 'ID' ) );
		$course     = $assessment->get_course_v2();

		if ( $assessment->delete() ) {
			$course->compute_type();

			return new WP_REST_Response( true, 200 );
		}

		return new WP_Error( 'no-results', __( [], 'thrive-apprentice' ) );
	}

	public function update_item( $request ) {

		$data = array_merge(
			$request->get_params(),
			[
				'edit_date'     => current_time( 'mysql' ),
				'post_date'     => $request->get_param( 'publish_date' ),
				'post_date_gmt' => tva_get_post_date_gmt( $request->get_param( 'publish_date' ) ),
			]
		);

		$assessment = new TVA_Assessment( $data );
		if ( ! empty( $data['grading_method'] ) ) {
			$assessment->set_grading( Grading_Base::factory( $data ) );
		}
		if ( ! empty( $data['assessment_type'] ) ) {
			$assessment->set_type_class( Type_Base::factory( $data ) );
		}


		try {
			$assessment->save();
			$assessment->get_course_v2()->compute_type();

			return $assessment;
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		return new WP_REST_Response( TVA_Manager::get_assessments(), 200 );
	}

	/**
	 * Callback for saving settings
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function save_settings( $request ) {

		tva_assessment_settings()->set_data( $request->get_params() )->save();

		return new WP_REST_Response( [], 200 );
	}

	/**
	 * Callback for saving a grade for a user's assessment
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_grade( $request ) {
		$id         = $request->get_param( 'id' );
		$submission = new TVA_User_Assessment( get_post( $id ) );
		$grade      = $request->get_param( 'grade' );
		$notes      = $request->get_param( 'notes' );
		$response   = new WP_Error( 'error' );

		if ( $submission->save_grade( $grade, $notes ) ) {
			$response = new WP_REST_Response( [
				'status' => $submission->status,
				'grade'  => $submission->grade,
				'notes'  => $submission->notes,
			], 200 );
		}

		return $response;
	}

	/**
	 * Duplicate an assessment and return the new assessment
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function duplicate( WP_REST_Request $request ) {
		$id         = (int) $request->get_param( 'id' );
		$assessment = new TVA_Assessment( $id );

		try {
			$new_lesson = $assessment->duplicate( $assessment->post_parent, 'Clone of ' . $assessment->post_title );
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}

		return rest_ensure_response( $new_lesson );
	}

	/**
	 * Gets user's quiz answers for Tqb preview
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response | WP_Error
	 */
	public function quiz_result_data( $request ) {

		if ( tva_tqb_integration()->is_quiz_builder_active() ) {
			global $tqbdb;

			$users = $tqbdb->get_users( [ 'id' => $request->get_param( 'user_id' ), 'limit' => 1 ] );
		} else {
			return new WP_REST_Response( [ 'message' => 'Thrive Quiz Builder is not active' ], 500 );
		}

		if ( ! empty( $users ) && is_array( $users ) ) {
			$user                          = current( $users );
			$quiz                          = new TQB_Quiz( (int) $user['quiz_id'] );
			$reporting_manager             = new TQB_Reporting_Manager( (int) $user['quiz_id'], 'users' );
			$completion_data               = $reporting_manager->get_users_report()['data'];
			$questions                     = $reporting_manager->get_users_answers( (int) $user['id'] );
			$result_index                  = array_search( $user['random_identifier'], array_column( $completion_data, 'random_identifier' ) );
			$completion_data               = $completion_data[ $result_index ];
			$completion_data->quiz_title   = $quiz->post_title;
			$completion_data->quiz_type    = $quiz->type;
			$completion_data->quiz_results = $quiz->results;
		} else {
			return new WP_REST_Response( [ 'message' => 'Quiz cannot be found' ], 500 );
		}

		return new WP_REST_Response( array(
			'questions'       => $questions,
			'completion_data' => $completion_data,
		) );
	}
}
