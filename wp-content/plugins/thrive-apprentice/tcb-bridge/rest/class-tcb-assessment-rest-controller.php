<?php

namespace TVA\Architect\Assessment;

use TCB\inc\helpers\FileUploadConfig;
use Thrive_Dash_List_Manager;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Assessments\Types\Upload;
use TVA_Assessment;
use TVA_Const;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_User;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Assessment_Rest_Controller
 *
 * @project  : thrive-apprentice
 */
class TCB_Assessment_Rest_Controller {

	/**
	 * Route name
	 *
	 * @var string
	 */
	public static $route = '/user/assessment';

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Registers the class routes
	 */
	public function register_routes() {
		register_rest_route( TVA_Const::REST_NAMESPACE, static::$route, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create' ],
				'permission_callback' => [ $this, 'has_access' ],
				'args'                => [
					'assessment_id'  => [
						'type'     => 'number',
						'required' => true,
					],
					'type'           => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ Main::TYPE_QUIZ, Main::TYPE_YOUTUBE_LINK, Main::TYPE_EXTERNAL_LINK, Main::TYPE_UPLOAD ],
					],
					'result_content' => [
						'required' => false,
						'type'     => 'string',
					],
				],
			],
		] );


		register_rest_route( TVA_Const::REST_NAMESPACE, static::$route . '/file-upload', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'file_action' ],
				'permission_callback' => [ $this, 'has_access' ],
				'args'                => [
					'action'        => [
						'required' => true,
						'type'     => 'string',
						'enum'     => [ 'tcb_file_upload', 'tcb_file_remove' ],
					],
					'assessment_id' => [
						'required' => false,
						'type'     => 'number',
					],
				],
			],
		] );
	}

	/**
	 * Create an assessment for a user
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function create( $request ) {

		$user_assessment = new TVA_User_Assessment( array_merge( [
			'post_parent' => (int) $request->get_param( 'assessment_id' ),
		], $request->get_params() ) );

		$user_assessment->create();

		$result_content = (string) $request->get_param( 'result_content' );
		if ( ! empty( $result_content ) ) {
			//Needed for do_shortcode logic
			Shortcodes::$assessment_from_request = new TVA_Assessment( $request->get_param( 'assessment_id' ) );

			$result_content = str_replace( [ '{({', '})}' ], [ '[', ']' ], $result_content );
			$result_content = do_shortcode( $result_content );

			Shortcodes::$assessment_from_request = null;
		}

		return new WP_REST_Response( [
			'user_assessment' => $user_assessment,
			'result_content'  => $result_content,
		], 200 );
	}

	/**
	 * File action upload / remove
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function file_action( $request ) {
		$action = $request->get_param( 'action' );

		if ( $action === 'tcb_file_upload' ) {
			$assessment_id = $request->get_param( 'assessment_id' );

			if ( empty( $assessment_id ) ) {
				static::upload_file_error( 'Missing assessment_id' );
			}

			$assessment = new TVA_Assessment( (int) $request->get_param( 'assessment_id' ) );

			if ( empty( $_FILES ) ) {
				static::upload_file_error( 'Missing file, or file is too large' );
			}

			if ( ! empty( $_FILES['file']['error'] ) ) {
				static::upload_file_error( 'Error uploading file', 500 );
			}

			if ( ! tva_assessment_settings()->can_upload_assessments() ) {
				static::upload_file_error( 'Missing storage API settings' );
			}

			$info = pathinfo( $_FILES['file']['name'] );

			if ( empty( $info['extension'] ) || empty( $info['filename'] ) ) {
				/* something is wrong here */
				static::upload_file_error( 'Invalid file name or extension' );
			}

			if ( ! in_array( strtolower( $info['extension'] ), Upload::get_extensions( $assessment ) ) ) {
				static::upload_file_error( 'Invalid file extension' );
			}

			if ( filesize( $_FILES['file']['tmp_name'] ) > wp_max_upload_size() ) {
				/**
				 * Filesize security check
				 */
				static::upload_file_error( 'FILE: The selected file is larger than the allowed file size for this website', 400 );
			}

			$api    = Thrive_Dash_List_Manager::connection_instance( tva_assessment_settings()->upload_connection_key );
			$folder = filter_var( tva_assessment_settings()->folder_id, FILTER_VALIDATE_URL ) === false ? tva_assessment_settings()->folder_id : basename( tva_assessment_settings()->folder_id );
			$result = $api->upload( file_get_contents( $_FILES['file']['tmp_name'] ), $folder, [
				'originalName' => $_FILES['file']['name'],
				'name'         => static::get_upload_filename( $_FILES['file']['name'], $assessment, wp_get_current_user() ) . '.' . $info['extension'],
			] );

			wp_send_json( [
				'success' => true,
				'nonce'   => FileUploadConfig::create_nonce( $result ), // generate nonce so that it can be used in file delete requests and validate the subsequent POST
				'file_id' => $result,
			] );
		} elseif ( $action === 'tcb_file_remove' ) {
			if ( empty( $request->get_param( 'nonce' ) ) || empty( $request->get_param( 'file_id' ) ) ) {
				/* don't generate any error messages */
				exit();
			}

			if ( ! FileUploadConfig::verify_nonce( sanitize_text_field( $_POST['nonce'] ), sanitize_text_field( $_POST['file_id'] ) ) ) {
				exit();
			}

			$api = Thrive_Dash_List_Manager::connection_instance( tva_assessment_settings()->upload_connection_key );
			$api->delete( sanitize_text_field( $request->get_param( 'file_id' ) ) );

			wp_send_json( [ 'success' => 1 ] );
		}
	}

	/**
	 * The assessments routes should be accessible if the following cases are true
	 * - user is logged in
	 * - user has access to the post the assessment element is located
	 *
	 * @return bool
	 */
	public function has_access() {
		$has_access = is_user_logged_in();
		$has_access = $has_access && ! empty( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] );
		$has_access = $has_access && tva_access_manager()->has_access_to_object( get_post( $_POST['post_id'] ) );

		return $has_access;
	}

	/**
	 * Used in file upload method.
	 * Returns the error string to frontend
	 *
	 * @param string $error
	 * @param int    $code
	 *
	 * @return void
	 */
	private static function upload_file_error( $error, $code = 400 ) {
		status_header( $code );
		echo esc_html( $error );
		die();
	}

	/**
	 * @param string         $original_name
	 * @param TVA_Assessment $assessment
	 * @param WP_User        $user
	 *
	 * @return string
	 */
	private static function get_upload_filename( $original_name, $assessment, $user ) {
		$templates = [
			'{match}'      => sanitize_file_name( $original_name ),
			'{assessment}' => sanitize_file_name( $assessment->post_title ),
			'{course}'     => sanitize_file_name( $assessment->get_course_v2()->name ),
			'{email}'      => sanitize_file_name( $user->user_email ),
			'{date}'       => current_time( 'm-d-Y' ),
			'{time}'       => current_time( 'Hi' ),
		];

		return str_replace( array_keys( $templates ), $templates, tva_assessment_settings()->get_upload_filename() ) . '_' . mt_rand( 1000000, 9999999 );
	}
}
