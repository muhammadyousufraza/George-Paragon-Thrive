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
 * Certificate controller
 */
class TVA_Certificate_Controller extends TVA_REST_Controller {
	public $base = 'certificate';

	/**
	 * Registers REST routes
	 *
	 * @return void
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/download', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'download' ],
				'permission_callback' => 'is_user_logged_in', //Only logged in users have access to download functionality
				'args'                => [
					'course_id' => [
						'required' => true,
						'type'     => 'int',
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'course_id' => [
						'required' => true,
						'type'     => 'int',
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'args' => [
						'id' => [
							'required' => true,
							'type'     => 'int',
						],
					],
				],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/clear-cache', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'clear_cache' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			],
		] );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/search', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array(
					$this,
					'search_certificate',
				),
				'permission_callback' => '__return_true',
				'args'                => array(
					'number' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			),
		) );
	}

	/**
	 * Deletes all the certificate PDF files from the /uploads
	 *
	 * @return void
	 */
	public function clear_cache() {
		\TVD_PDF_From_URL::delete_by_prefix( \TVA_Course_Certificate::FILE_NAME_PREFIX );
	}

	/**
	 * Search for a TVA Certificate and applies the shortcodes
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function search_certificate( $request ) {
		$code          = '';
		$message       = '';
		$number        = sanitize_text_field( $request->get_param( 'number' ) );
		$skin_template = \TVA\TTB\Main::skin()->get_default_template( \TVA_Const::CERTIFICATE_VALIDATION_POST );

		if ( ! ( $skin_template instanceof \TVA\TTB\Skin_Template ) ) {
			return rest_ensure_response( new WP_Error( 'missing_validation_page', esc_html__( 'Certificate validation page not set', 'thrive-apprentice' ) ) );
		}

		global $certificate;
		$certificate = tva_course_certificate()->search_by_number( $number );

		if ( empty( $certificate ) ) {
			$code    = 'no_certificate_found';
			$message = esc_html__( 'Certificate not found', 'thrive-apprentice' );
		} elseif ( ! $certificate['course']->get_wp_term() instanceof WP_Term ) {
			$code    = 'attached_course_deleted';
			$message = esc_html__( 'The certificate is not available anymore as the course was removed', 'thrive-apprentice' );
		}

		if ( ! empty( $code ) && ! empty ( $message ) ) {
			return new WP_REST_Response( array(
				'code'    => $code,
				'message' => $message,
				'data'    => null,
			), 400 );
		}

		$html = '';

		foreach ( $skin_template->meta( 'sections' ) as $section ) {
			if ( strpos( $section['content'], 'tva-certificate-verification-element' ) !== false ) {
				$html = do_shortcode( $section['content'] );
				break;
			}
		}

		$data = array(
			'certificate' => $certificate,
			'html'        => $html,
		);

		$certificate_data = [
			'certificate_number' => $number,
		];
		$user_data        = [
			'user_id'    => $certificate['recipient']->ID,
			'user_email' => $certificate['recipient']->user_email,
		];
		$course_data      = [
			'course_id'   => $certificate['course']->term_id,
			'course_name' => $certificate['course']->name,
		];

		/**
		 * This hook is triggered when a certificate has been verified
		 *
		 * @param array $certificate_data
		 * @param array $user_data
		 * @param array $course_data
		 */
		do_action( 'tva_certificate_verified', $certificate_data, $user_data, $course_data );

		return rest_ensure_response( $data, is_wp_error( $data ) ? 400 : 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_REST_Response
	 */
	public function download( $request ) {

		$course   = new TVA_Course_V2( (int) $request->get_param( 'course_id' ) );
		$user_id  = (int) $request->get_param( 'user_id' );
		$customer = tva_customer();

		if ( ! empty( $user_id ) ) {
			$customer = new TVA_Customer( $user_id );
		}

		if ( empty( $course->get_id() ) ) {
			return new WP_REST_Response(
				array(
					'message' => __( 'Invalid parameters!', 'thrive-apprentice' ),
				),
				404
			);
		}

		if ( ! $course->has_certificate() ) {
			return new WP_REST_Response(
				array(
					'message' => __( 'No certificate found!', 'thrive-apprentice' ),
				),
				404
			);
		}

		$certificate = $course->get_certificate();
		$response    = $certificate->download( $customer );

		if ( ! empty( $response['error'] ) ) {
			return new WP_REST_Response( $response, 404 );
		}

		$certificate_data = [
			'certificate_number' => $certificate->number,
		];
		$user_data        = [
			'user_id'    => $customer->get_id(),
			'user_email' => $customer->get_user()->user_email,
		];
		$course_data      = [
			'course_id'   => $course->get_id(),
			'course_name' => $course->name,
		];

		/**
		 * This hook is triggered when a certificate is downloaded by a user
		 *
		 * @param array $certificate_data
		 * @param array $user_data
		 * @param array $course_data
		 */
		$is_admin_action = (bool) $request->get_param( 'tva_admin_download' );
		if ( ! $is_admin_action ) {
			do_action( 'tva_certificate_downloaded', $certificate_data, $user_data, $course_data );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Length: ' . filesize( $response['file'] ) );
		if ( ob_get_contents() ) {
			ob_end_clean();
		}

		readfile( $response['file'] );
		exit();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$course_id = (int) $request->get_param( 'course_id' );
		$course    = new TVA_Course_V2( $course_id );

		if ( ! TD_TTW_Connection::get_instance()->is_connected() ) {
			/**
			 * If TPM is not connected. disable certificate functionality
			 */
			return new WP_REST_Response( [ 'tpm_not_connected' => 1 ] );
		}

		return new WP_REST_Response( $course->get_certificate()->set_status_publish() );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$id      = (int) $request->get_param( 'id' );
		$success = wp_update_post( [
			'ID'          => $id,
			'post_status' => 'draft',
		] );

		return new WP_REST_Response( $success );
	}
}
