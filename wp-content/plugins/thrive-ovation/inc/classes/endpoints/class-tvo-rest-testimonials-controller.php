<?php

/**
 * Class TVO_REST_Testimonials_Controller
 */
class TVO_REST_Testimonials_Controller extends TVO_REST_Controller {

	public $base = 'testimonials';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		parent::register_routes();

		/*Route for delete multiple testimonials*/
		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_items' ),
				'permission_callback' => array( $this, 'delete_items_permissions_check' ),
				'args'                => array(
					'force' => array(
						'default' => false,
					),
				),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/add', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/email/approval', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'send_confirmation_email' ),
				'permission_callback' => array( $this, 'send_email_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/form', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_form_testimonial' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/copy', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'tvo_copy_testimonial' ),
				'permission_callback' => array( $this, 'copy_item_permission_check' ),
			),
		) );
	}

	/**
	 * Add testimonial from the form
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response|int
	 */
	public function add_form_testimonial( $request ) {

		if ( tve_dash_is_crawler() ) {
			return 0;
		}

		$data = $request;

		if ( isset( $data['_use_captcha'] ) && $data['_use_captcha'] == '1' ) {
			$CAPTCHA_URL = TVO_CAPTCHA_URL;
			$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );

			$_captcha_params = array(
				'response' => $data['g-recaptcha-response'],
				'secret'   => empty( $captcha_api['secret_key'] ) ? '' : $captcha_api['secret_key'],
				'remoteip' => $_SERVER['REMOTE_ADDR'],
			);

			$request_captcha = tve_dash_api_remote_post( $CAPTCHA_URL, array( 'body' => $_captcha_params ) );
			$response        = json_decode( wp_remote_retrieve_body( $request_captcha ) );

			if ( empty( $response ) || $response->success === false ) {
				return new WP_Error( 'code', __( 'Please prove us that you are not a robot!!!', 'thrive-ovation' ) );
			}
		}

		$testimonial = $this->prepare_item_for_database( $request );

		$testimonial['source'] = TVO_SOURCE_DIRECT_CAPTURE;
		$testimonial['status'] = TVO_STATUS_AWAITING_REVIEW;

		$testimonial['content'] = sanitize_textarea_field( $testimonial['content'], true ); // don't allow any html if testimonial is being added from frontend
		/* Replace the we line wit p tag */
		$testimonial['content'] = wpautop( $testimonial['content'] );

		$result = tvo_create_testimonial( $testimonial );
		if ( $result['status'] == 'ok' ) {
			/* Trigger action on testimonial added through capture form */
			do_action( TVO_ACTION_TESTIMONIAL_ADDED_CAPTURE_FORM, $result['testimonial'], array(
				'source' => $testimonial['source'],
				'url'    => admin_url( 'admin.php?page=tvo_admin_dashboard#testimonials/' . $result['testimonial']['id'] ),
			) );
			/* Specify the shortcode source for the testimonial */
			$shortcode_id = $request->get_param( 'shortcode_id' );
			add_post_meta( $result['testimonial']['id'], 'tvo_shortcode_source', $shortcode_id );

			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
			} else {
				$user = get_user_by( 'email', $testimonial['email'] );
			}

			/**
			 * The hook is triggered when a user submits a testimonial through Thrive Ovation. The hook can be fired multiple times, as the user can leave multiple testimonials.
			 * </br>
			 * Example use case:-  Give students access to a bonus course after they have submitted a testimonial.
			 * </br>
			 * <b>Note:</b> This parameter will provide the user details only if the user is logged in. It will not provide the name/email used when the user submits a testimonial.
			 *
			 * @param array Testimonial Details
			 * @param null|array User Details
			 *
			 * @api
			 */
			do_action( 'thrive_ovation_testimonial_submit', tvo_get_testimonial_details( $result['testimonial']['id'], $request->get_param( 'post_id' ) ), $user );

			return new WP_REST_Response( 1, 200 );
		} else {
			return new WP_Error( 'code', __( 'Something went wrong while trying to send data. Please try again.', 'thrive-ovation' ) );
		}
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$tvo_testimonial_meta = $this->prepare_item_for_database( $request );
		$result               = tvo_create_testimonial( $tvo_testimonial_meta );
		if ( $result['status'] == 'ok' ) {
			return new WP_REST_Response( $result['testimonial'], 200 );
		} else {
			return new WP_Error( $result['message'], __( 'Creating Testimonial failed', 'thrive-ovation' ) );
		}
	}

	/**
	 * Copy testimonial
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function tvo_copy_testimonial( $request ) {
		$params      = $this->prepare_item_for_database( $request );
		$testimonial = tvo_get_testimonial_data( $params['id'] );
		unset( $testimonial['id'] );
		$tagsArray = array();
		foreach ( $testimonial['tags'] as $tag ) {
			array_push( $tagsArray, $tag['id'] );
		};
		if ( $testimonial['title'] == '' ) {
			$testimonial['title'] = __( 'Copy ', 'thrive-ovation' );
		} else {
			$testimonial['title'] = __( 'Copy of ', 'thrive-ovation' ) . $testimonial['title'];
		}
		$testimonial['source'] = 'copy';
		$testimonial['tags']   = $tagsArray;

		if ( isset( $testimonial['media_source'] ) && $testimonial['media_source'] ) {
			$testimonial['is_media_source'] = 1;
		} else {
			$testimonial['is_media_source'] = 0;
		}
		$result = tvo_create_testimonial( $testimonial );
		if ( $result['status'] == 'ok' ) {
			return new WP_REST_Response( $result['testimonial'], 200 );
		} else {
			return new WP_Error( $result['message'], __( 'Copy Testimonial failed', 'thrive-ovation' ) );
		}
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$testimonials = array();
		$filter       = array();
		$params       = $request->get_params();

		if ( isset( $params['status'] ) ) {
			$filter = array(
				array(
					'key'   => TVO_STATUS_META_KEY,
					'value' => $params['status'],
				),
			);
		}
		$query              = array(
			'post_type'      => TVO_TESTIMONIAL_POST_TYPE,
			'posts_per_page' => - 1,
			'meta_query'     => $filter,
		);
		$testimonials_posts = get_posts( $query );
		foreach ( $testimonials_posts as $testimonial ) {
			$tvo_testimonial_meta = get_post_meta( $testimonial->ID, TVO_POST_META_KEY, true );
			$testimonials[]       = $this->prepare_testimonial_for_response( $testimonial, $tvo_testimonial_meta );
		}

		return new WP_REST_Response( $testimonials, 200 );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$params = $request->get_params();

		$result = tvo_get_testimonial( $params['id'] );

		if ( $result['status'] == 'ok' ) {
			return new WP_REST_Response( $result['testimonial'], 200 );
		} else {
			return new WP_Error( 'code', __( 'message', 'thrive-ovation' ) );
		}
	}

	/**
	 * Extend activity log
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_activity_log_extension( $request ) {

		$params = $request->get_params();

		if ( ! empty( $params['id'] ) && ! empty( $params['offset'] ) ) {
			$data = tvo_get_testimonial_activity_log( $params['id'], $params['offset'] );
			if ( ! $data ) {
				return new WP_Error( 'code', __( 'message', 'thrive-ovation' ) );
			}
			$data = $this->prepare_item_for_response( $data, $request );

			return new WP_REST_Response( $data, 200 );
		} else {
			return new WP_Error( 'code', __( 'message', 'thrive-ovation' ) );
		}
	}


	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */

	public function get_item_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */

	public function create_item_permissions_check( $request ) {
		return TVO_Product::has_access();
	}


	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Check if a given request has access to copy items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */

	public function copy_item_permission_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Update testimonials
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$params = $this->prepare_item_for_database( $request );

		if ( empty( $params['id'] ) ) {
			return new WP_Error( 'cant-update', __( 'Missing ID from parameter list', 'thrive-ovation' ), array( 'status' => 500 ) );
		}

		$result = tvo_update_testimonial( $params );
		if ( $result['status'] == 'ok' ) {
			return new WP_REST_Response( $result['testimonial'], 200 );
		}

		return new WP_Error( 'cant-update', __( 'Error while updating the testimonial', 'thrive-ovation' ), array( 'status' => 500 ) );

	}


	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {

		$params = $this->prepare_item_for_database( $request );
		if ( ! empty( $params['id'] ) ) {
			$result = tvo_delete_testimonial( $params['id'] );
			if ( $result['status'] == 'ok' ) {
				return new WP_REST_Response( $params['id'], 200 );
			} else {
				return new WP_Error( 'cant-delete', $result['message'], array( 'status' => 500 ) );
			}
		}

		return new WP_Error( 'cant-delete', __( 'message', 'thrive-ovation' ), array( 'status' => 500 ) );
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_items( $request ) {

		$testimonial_ids = $request->get_body_params();

		if ( ! empty( $testimonial_ids['tvo_testimonial_elements'] ) ) {
			foreach ( $testimonial_ids['tvo_testimonial_elements'] as $id ) {
				$testimonial_temp = get_post( $id );
				if ( ! empty( $testimonial_temp ) && $testimonial_temp->post_type == TVO_TESTIMONIAL_POST_TYPE ) {
					wp_trash_post( $id );
				}
			}

			return new WP_REST_Response( $testimonial_ids['tvo_testimonial_elements'], 200 );
		}

		return new WP_Error( 'cant-delete', __( 'No testimonials selected', 'thrive-ovation' ), array( 'status' => 500 ) );

	}

	/**
	 * Send confirmation email template
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function send_confirmation_email( $request ) {
		$testimonial_id = $request->get_param( 'testimonial' );

		if ( empty( $testimonial_id ) ) {
			return new WP_Error( 'cant-update', __( 'Missing ID from parameter list', 'thrive-ovation' ), array( 'status' => 500 ) );
		}

		$testimonial = tvo_get_testimonial_data( $testimonial_id );

		if ( $testimonial ) {
			$connection = get_option( 'tvo_api_delivery_service', false );
			if ( ! $connection ) {
				return new WP_Error( 'cant-update', __( 'No active connection set', 'thrive-ovation' ), array( 'status' => 500 ) );
			}
			$api                  = Thrive_List_Manager::connection_instance( $connection );
			$email_template       = tvo_get_email_template();
			$email_template       = tvo_process_approval_email_content( $email_template, $testimonial );
			$data['html_content'] = $email_template;
			$data['text_content'] = '';
			$subject              = tvo_get_email_template_subject();
			$data['subject']      = tvo_process_approval_email_subject( $subject, $testimonial );
			$data['email']        = $testimonial['email'];
			$sent                 = $api->sendCustomEmail( $data );
			if ( $sent === true ) {
				/*Updates testimonial status changes -> activity log*/
				do_action( 'tvo_log_testimonial_status_activity', array( 'id' => $testimonial_id, 'status' => TVO_STATUS_AWAITING_APPROVAL ) );
				update_post_meta( $testimonial_id, TVO_STATUS_META_KEY, TVO_STATUS_AWAITING_APPROVAL );
				do_action( 'tvo_log_testimonial_email_activity', array( 'id' => $testimonial_id, 'email_address' => $testimonial['email'] ) );

				$activity_log = tvo_get_testimonial_activity_log( $testimonial_id );
				$data         = array(
					'activityLog'      => ! empty( $activity_log['activity_log'] ) ? $activity_log['activity_log'] : array(),
					'activityLogCount' => ! empty( $activity_log['total_count'] ) ? $activity_log['total_count'] : array(),
					'sent_emails'      => tvo_get_emails_from_activity_log( $testimonial_id ),
					'status'           => TVO_STATUS_AWAITING_APPROVAL,
				);
				$data['sent'] = $sent;

				return new WP_REST_Response( $data, 200 );
			}

			return new WP_Error( 'cant-update', __( 'Sending approval email failed', 'thrive-ovation' ), array( 'status' => 500 ) );
		}

		return new WP_Error( 'cant-send', __( 'Sending approval email failed', 'thrive-ovation' ), array( 'status' => 500 ) );
	}


	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function delete_items_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Check if a given request has access to send approval email
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function send_email_permissions_check( $request ) {
		return TVO_Product::has_access();
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object|array $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {

		return array(
			'id'          => $request->get_param( 'id' ),
			'title'       => sanitize_text_field( $request->get_param( 'title' ) ),
			'name'        => sanitize_text_field( $request->get_param( 'name' ) ),
			'email'       => sanitize_email( $request->get_param( 'email' ) ),
			'role'        => sanitize_text_field( $request->get_param( 'role' ) ),
			'website_url' => esc_url_raw( $request->get_param( 'website_url' ) ),
			'picture_url' => esc_url_raw( $request->get_param( 'picture_url' ) ),
			'tags'        => $request->get_param( 'tags' ),
			'status'      => sanitize_text_field( $request->get_param( 'status' ) ),
			'content'     => tvo_sanitize_testimonial_field( $request->get_param( 'content' ) ),
			'source'      => sanitize_text_field( $request->get_param( 'source' ) ),
			'comment_url' => sanitize_text_field( $request->get_param( 'comment_url' ) ),
		);
	}

	/**
	 * Prepares the testimonial for the response
	 *
	 * @param WP_Post $testimonial
	 * @param array   $tvo_testimonial_meta
	 *
	 * @return array
	 */
	private function prepare_testimonial_for_response( $testimonial, $tvo_testimonial_meta = array() ) {
		/* some weird spaces that need to be replaced with spaces -_- csf/ncsf */
		$testimonial->post_content = str_replace( ' ', ' ', $testimonial->post_content );

		return array(
			'id'          => $testimonial->ID,
			'title'       => sanitize_text_field( $testimonial->post_title ),
			'name'        => ! empty( $tvo_testimonial_meta['name'] ) ? sanitize_text_field( $tvo_testimonial_meta['name'] ) : '',
			'date'        => date_i18n( 'jS F, Y', strtotime( $testimonial->post_date ) ),
			'content'     => tvo_sanitize_testimonial_field( $testimonial->post_content ),
			'summary'     => wp_trim_words( wp_strip_all_tags( $testimonial->post_content ), TVO_TESTIMONIAL_CONTENT_WORDS_LIMIT ),
			'email'       => isset( $tvo_testimonial_meta['email'] ) ? sanitize_email( $tvo_testimonial_meta['email'] ) : '',
			'role'        => isset( $tvo_testimonial_meta['role'] ) ? sanitize_text_field( $tvo_testimonial_meta['role'] ) : '',
			'website_url' => ! empty( $tvo_testimonial_meta['website_url'] ) ? esc_url_raw( $tvo_testimonial_meta['website_url'] ) : '',
			'picture_url' => ! empty( $tvo_testimonial_meta['picture_url'] ) && strpos( $tvo_testimonial_meta['picture_url'], 'img/tvo-no-photo.png' ) === false ? esc_url_raw( $tvo_testimonial_meta['picture_url'] ) : tvo_get_default_image_placeholder(),
			'has_picture' => ! empty( $tvo_testimonial_meta['picture_url'] ) && strpos( $tvo_testimonial_meta['picture_url'], 'img/tvo-no-photo.png' ) === false ? 1 : 0,
			'status'      => get_post_meta( $testimonial->ID, TVO_STATUS_META_KEY, true ),
			'tags'        => tvo_get_testimonial_tags( $testimonial->ID ),
			'source'      => tvo_get_testimonial_source_text( get_post_meta( $testimonial->ID, TVO_SOURCE_META_KEY, true ) ),
		);
	}
}
