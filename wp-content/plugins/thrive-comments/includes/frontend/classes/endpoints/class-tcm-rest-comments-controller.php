<?php
/**
 * FileName  class-tcm-rest-comments-controller.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TCM_REST_Comments_Controller
 */
class TCM_REST_Comments_Controller extends WP_REST_Comments_Controller {

	/**
	 * Base string to form a route
	 *
	 * @var string $base
	 */
	public static $base = 'comments';

	/**
	 * Rest version
	 *
	 * @var int
	 */
	public static $version = 1;

	/**
	 * Comment field which are required when creating a comment
	 *
	 * @var array $required_fields
	 */
	protected $required_fields = array(
		'comment_content',
		'comment_author_email',
		'comment_author',
	);

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		parent::__construct();

		$this->namespace = 'tcm/v' . self::$version;
		$this->rest_base = self::$base;
		$this->hooks();

	}

	/**
	 * Add hooks for extra rest functionality
	 */
	public function hooks() {
		if ( tcah()->can_see_moderation() ) {
			add_filter( 'rest_prepare_comment', array( $this, 'rest_prepare_comment' ), 10, 3 );
		}
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<post_id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_post_comments' ),
				'permission_callback' => array( $this, 'get_post_comments_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_comment' ),
				'permission_callback' => array( $this, 'create_comment_permission_check' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_comment' ),
				'permission_callback' => array( $this, 'update_comment_permission_check' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/live_update', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'live_update' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'         => array(
						'required' => true,
						'type'     => 'integer',
					),
					'update_interval' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/update_post_subscriber', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_post_subscriber' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/vote', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'vote' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'secret_spam' => array(
						'required' => true,
						'type'     => 'string',
					),
					'comment_id'  => array(
						'required' => true,
						'type'     => 'integer',
					),
					'vote_type'   => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => array( 'upvote', 'downvote' ),
					),
				),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/gravatar', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_gravatar' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'comment_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/unsubscribe' . '/(?P<comment_id>[\d]+)' . '/(?P<post_id>[\d]+)' . '/(?P<user_email_hash>[\S]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'unsubscribe_email' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/unsubscribe' . '/(?P<post_id>[\d]+)' . '/(?P<user_email_hash>[\S]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'unsubscribe_post_email' ),
				'permission_callback' => '__return_true',
			),
		) );
	}

	/**
	 * Checks if a comment has been added
	 *
	 * @param WP_REST_Request $request Data from request.
	 *
	 * @return WP_REST_Response
	 */
	public function live_update( $request ) {

		$live_update_setting = tcms()->tcm_get_setting_by_name( 'tcm_live_update' );

		//extra check server side if the live update setting is inactive
		$response = ( $live_update_setting ) ? tcmh()->live_update( $request ) : array();

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Save post subscribers
	 *
	 * @param WP_REST_Request $request Data from request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_post_subscriber( $request ) {
		$subscriber_email     = $request->get_param( 'email' );
		$post_id              = $request->get_param( 'post_id' );
		$subscribe            = $request->get_param( 'subscribe' );
		$all_subscriber_email = get_post_meta( $post_id, 'tcm_post_subscribers', true );

		if ( empty( $all_subscriber_email ) ) {
			$all_subscriber_email = array();
		}

		if ( $subscribe ) {

			$user_hash = tcmdb()->get_email_hash( 'email_hash', array( 'email' => $subscriber_email ) );
			if ( null === $user_hash ) {
				tcmdb()->insert_email_hash( array( 'email' => $subscriber_email, 'email_hash' => md5( $subscriber_email ) ) );
			}

			if ( ! in_array( $subscriber_email, $all_subscriber_email ) ) {
				$all_subscriber_email[] = $subscriber_email;
			}

			update_post_meta( $post_id, 'tcm_post_subscribers', $all_subscriber_email );

			/**
			 * Action created for TA compatibility.
			 */
			do_action( 'tcm_post_subscribe', $request );

			return new WP_REST_Response( __( 'Subscribed with success! ' ), 200 );
		} elseif ( '0' === $subscribe ) {

			if ( ( $key = array_search( $subscriber_email, $all_subscriber_email ) ) !== false ) {
				unset( $all_subscriber_email[ $key ] );
			} else {
				return new WP_Error( 'UnSubscribing Failed', __( 'UnSubscribing Failed', Thrive_Comments_Constants::T ) );

			}
			update_post_meta( $post_id, 'tcm_post_subscribers', $all_subscriber_email );

			/**
			 * Action created for TA compatibility.
			 */
			do_action( 'tcm_post_unsubscribe', $request );

			return new WP_REST_Response( __( 'UnSubscribed with success! ' ), 200 );
		} else {
			return new WP_Error( 'Subscribing Failed', __( 'Subscribing Failed', Thrive_Comments_Constants::T ) );
		}
	}


	/**
	 * Get post comments by post id
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_post_comments( $request ) {

		$post_id = $request->get_param( 'post_id' );

		if ( empty( $post_id ) ) {
			return new WP_Error( 'error-code', __( 'Post id is missing from the request', Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}

		$query              = $this->prepare_query_args( $request, $post_id );
		$res                = tcmh()->get_comments_from_post( $query );
		$comment_count      = get_comment_count( $post_id );
		$user_comment_count = $this->get_user_comment_count( $request );

		return new WP_REST_Response( array(
			'comments'           => $res['comments'],
			'count'              => apply_filters( 'tcm_comment_count', $comment_count['approved'], $request ),
			'nextPage'           => $res['nextPage'],
			'user_comment_count' => $user_comment_count,
		), 200 );

	}

	/**
	 * Get the number of comments that each user needs to see
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return array|bool|int|mixed
	 */
	public function get_user_comment_count( $request ) {
		$current_user        = wp_get_current_user();
		$comments_unapproved = 0;
		$post_id             = $request->get_param( 'post_id' );

		if ( ! $current_user->ID ) {
			//user is not logged
			$args = array(
				'status'     => array( 'approve' ),
				'parent__in' => array( 0 ),
			);
		} else {

			$total_no = wp_cache_get( "comments-count-{$current_user->ID}", 'tc-comment-counts' );
			if ( false !== $total_no ) {
				return $total_no;
			}

			if ( user_can( $current_user, 'manage_options' ) ) {
				//user is admin
				$args = array(
					'status'     => array( 'approve', 'hold' ), //only from the comment approved or unapproved
					'parent__in' => array( 0 ),
				);
			} else {
				//user is logged
				$comments_query      = new WP_Comment_Query();
				$comments_unapproved = $comments_query->query(
					array(
						'user_id'    => $current_user->ID,
						'parent__in' => array( 0 ),
						'status'     => array( 'hold' ),
						'count'      => true,
						'post__in'   => array( $post_id ),
					)
				);

				$args = array(
					'status'     => array( 'approve' ), //only from the comment approved or unapproved
					'parent__in' => array( 0 ),
				);
			}
		}

		$args['count']    = true;
		$args['post__in'] = array( $post_id );

		/**
		 * Filter for adding arguments when getting user comment count
		 *
		 * @param array   $args         The default arguments
		 * @param WP_User $current_user The currently logged user
		 */
		$args = apply_filters( 'tcm_user_comment_count', $args, $request );

		$query       = new WP_Comment_Query();
		$comments_no = $query->query( $args );

		//this is the total number of parent comments that a user has access on a post
		$total_no = $comments_no + $comments_unapproved;
		if ( $current_user->ID ) {
			wp_cache_set( "comments-count-{$current_user->ID}", $total_no, 'tc-comments-counts' );
		}

		return $total_no;
	}

	/**
	 * Prepare arguments for reading from db
	 *
	 * @param WP_REST_Request $request request from frontend.
	 * @param integer         $post_id Id of the post for which the comments are taken.
	 *
	 * @return array
	 */
	public function prepare_query_args( $request, $post_id ) {

		$page_comments = tcms()->tcm_get_setting_by_name( 'page_comments' );

		$page           = $request->get_param( 'page' );
		$items_per_page = ( $page_comments ) ? $request->get_param( 'itemsPerPage' ) : 0;
		$offset         = $items_per_page * ( $page - 1 );
		$sort_by        = $request->get_param( 'sortBy' );
		$order          = $request->get_param( 'order' );
		$go_to_comment  = $request->get_param( 'go_to_id' );

		$meta_query['relation'] = 'OR';
		$meta_query[]           = array(
			'key'     => Thrive_Comments_Constants::TCM_FEATURED,
			'value'   => 1,
			'compare' => 'NOT IN',
		);
		$meta_query[]           = array(
			'key'     => Thrive_Comments_Constants::TCM_FEATURED,
			'compare' => 'NOT EXISTS',
			'value'   => '',
		);

		$query = array(
			'post_id'    => $post_id,
			'offset'     => $offset,
			'number'     => $items_per_page,
			'orderby'    => $sort_by,
			'order'      => $order,
			'parent'     => 0,
			'tcm_page'   => $page,
			'go_to_id'   => $go_to_comment,
			'meta_query' => $meta_query,
		);

		/**
		 * Filter for adding arguemnts when the comments are retrived on frontend
		 *
		 * @param array           $query   The query built by thrive comments
		 * @param WP_REST_Request $request Request from frontend
		 */
		return apply_filters( 'tcm_get_comments', $query, $request );
	}

	/**
	 * Get permissions for view posts
	 *
	 * @param WP_REST_Request $request request from frontend.
	 *
	 * @return bool
	 */
	public function get_post_comments_permissions_check( $request ) {
		// TODO if seeing the comments won't be linked to some admin setting, we can remove this function.
		return true;
	}

	/**
	 * Delete comments
	 *
	 * @param WP_REST_Request $request Request from frontend.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_comment( $request ) {

		$comment_id = $request->get_param( 'comment_id' );

		if ( ! $comment_id ) {
			return new WP_Error( 'cant-delete', __( 'No comments selected', Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}

		if ( wp_delete_comment( $comment_id ) ) {
			return new WP_REST_Response( 'Comment deleted successfully', 200 );
		}

		// TODO: the user can get here only if something goes wrong when deleting the comment, so maybe we should change the message.
		return new WP_Error( 'cant-delete', __( 'No comments selected', Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
	}

	/**
	 * Check permission for delete action
	 *
	 * @param WP_REST_Request $request The request from frontend.
	 *
	 * @return bool
	 */
	public function delete_comment_permissions_check( $request ) {
		// TODO if deleting the comments won't be linked to some admin setting, we can remove this function.
		return true;
	}

	/**
	 * Create comment
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_comment( $request ) {
		/* add request params into post to solve possible conflict with other plugins that use the post data*/

		$_POST                = array_merge( $_POST, $request->get_params() );
		$verify_spam          = tve_dash_check_secret( $request->get_param( 'secret_spam' ) );
		$comment_registration = tcms()->tcm_get_setting_by_name( 'comment_registration' );

		if ( $comment_registration && ! get_current_user_id() ) {
			return new WP_Error( 'need_register', __( 'The comment could not be saved. You must be registered in order to comment', Thrive_Comments_Constants::T ) );
		}

		$response = $this->validate_input( $request );

		if ( empty( $response['status'] ) ) {
			return new WP_REST_Response( $response, 200 );
		}

		if ( ! $verify_spam ) {
			return new WP_Error( 'spam_comment', __( 'Your comment was marked as spam', Thrive_Comments_Constants::T ) );
		}

		$comment_fields = $this->prepare_comment_for_database( $request );

		$comment = tcmdb()->save_comment( $comment_fields );

		// Added option for moderators to NOT receive notifications if unchecked.
		$allow_notifications = $request->get_param( 'tcm_receive_notifications' ) ? 1 : 0;

		if ( '' === get_option( 'tcm_moderators_notifications' ) ) {
			if ( ! tcah()->can_see_moderation() ) {
				update_comment_meta( $comment->comment_ID, 'author_subscribed', $allow_notifications );
			}
		} else {
			update_comment_meta( $comment->comment_ID, 'author_subscribed', $allow_notifications );
		}

		// Create a hash for each email for unSubscription.
		$user_hash = tcmdb()->get_email_hash( 'email_hash', array( 'email' => $comment->comment_author_email ) );
		if ( null === $user_hash ) {
			tcmdb()->insert_email_hash( array( 'email' => $comment->comment_author_email, 'email_hash' => md5( $comment->comment_author_email ) ) );
		}

		if ( '1' === $comment->comment_approved ) {
			tcmh()->tcm_send_notification( $comment );
		}

		$remember_me = $request->get_param( 'tcm_remember_me' ) ? 1 : 0;

		if ( $comment ) {
			$comment->level = min( Thrive_Comments_Constants::TCM_MAX_NESTING_LEVEL, $comment_fields['level'] );

			// If the comment is approved by default, increment logs.
			$status = wp_get_comment_status( $comment->comment_ID );
			if ( 'approved' === $status ) {
				tcmh()->update_badges_log( $comment );
			}

			$this->set_after_create_cookies( $comment, $remember_me );

			return new WP_REST_Response( $comment, 200 );
		} else {
			return new WP_Error( 'The comment could not be saved', __( 'Creating Comment failed', Thrive_Comments_Constants::T ) );
		}
	}

	public function set_after_create_cookies( $comment, $remember_me ) {

		//cookie so that the user can see his comment if it's not approved
		$prepare_comment_cookie = array(
			'comment_ID' => $comment->comment_ID,
		);

		// Cookie that makes it possible to see previous unapproved comments
		if ( ! tcah()->can_see_moderation() ) {
			setcookie( 'tcm_cookie_' . $comment->comment_ID, json_encode( $prepare_comment_cookie ), time() + ( 30 * 24 * 3600 ), '/' );
		}

		// if Remember me is enabled, set 30 day cookie or delete the session one.
		$time = 0;
		if ( tcms()->tcm_get_setting_by_name( 'remember_me' ) ) {
			// destroy cookie or set it for 30 days.
			$time = $remember_me ? time() + ( 3600 * 30 * 24 ) : 1;
		}

		$encoded = json_encode( $comment->comment_author );

		$unescaped = preg_replace_callback( '/\\\u(\w{4})/', array( $this, 'cyrillic_decoding' ), $encoded );

		if ( ! array_key_exists( 'tcm_cookie_user_name', $_COOKIE ) || tcms()->tcm_get_setting_by_name( 'remember_me' ) ) {

			setcookie( 'tcm_cookie_user_name', $unescaped, $time, '/' );
			setcookie( 'tcm_cookie_user_email', json_encode( $comment->comment_author_email ), $time, '/' );
			setcookie( 'tcm_cookie_user_website', json_encode( $comment->comment_author_url ), $time, '/' );
		}
		//keep a record of how many times a user commented to a post
		tcmc()->cookie_counts_comments( $comment );
	}

	public function cyrillic_decoding( $matches ) {
		return html_entity_decode( '&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * Parse comment before db insert
	 *
	 * @param WP_REST_Request $request Comment request.
	 *
	 * @return array
	 */
	public function prepare_comment_for_database( $request ) {

		$comment_fields = array(
			'comment_post_ID'        => sanitize_text_field( $request->get_param( 'post_id' ) ),
			'comment_approved'       => sanitize_text_field( $request->get_param( 'comment_approved' ) ),
			'comment_author'         => sanitize_text_field( $request->get_param( 'comment_author' ) ),
			'comment_author_email'   => sanitize_email( $request->get_param( 'comment_author_email' ) ),
			'comment_author_url'     => esc_url_raw( $request->get_param( 'comment_author_url' ) ),
			'comment_content'        => tvd_remove_script_tag( $request->get_param( 'comment_content' ) ),
			'comment_date'           => sanitize_text_field( $request->get_param( 'comment_date' ) ),
			'comment_karma'          => sanitize_text_field( $request->get_param( 'comment_karma' ) ),
			'comment_parent'         => sanitize_text_field( $request->get_param( 'comment_parent' ) ),
			'comment_type'           => sanitize_text_field( $request->get_param( 'comment_type' ) ),
			'user_id'                => get_current_user_id(),
			//Meta fields for comments
			'level'                  => wp_filter_kses( $request->get_param( 'level' ) ),
			'comment_author_picture' => esc_url_raw( $request->get_param( 'comment_author_picture' ) ),
			'upvote'                 => wp_filter_kses( $request->get_param( 'upvote' ) ),
			'downvote'               => wp_filter_kses( $request->get_param( 'downvote' ) ),
		);

		/* We sanitize by default the fields that we don't know */
		$extra_fields = $request->get_param( 'tcm_extra_fields' );
		if ( ! empty( $extra_fields ) ) {
			foreach ( $extra_fields as $key => $value ) {
				$extra_fields[ $key ] = sanitize_text_field( $value );
			}
			$request->set_param( 'tcm_extra_fields', $extra_fields );
		}

		/**
		 * Possibility to add more fields before a comments is registered in the database
		 *
		 * @param array           $comment_fields The fields from thrive comments
		 * @param WP_REST_Request $request        The comment request
		 */
		return apply_filters( 'tcm_comments_fields', $comment_fields, $request );
	}

	/**
	 * Check if the user can create a comment
	 *
	 * @param WP_REST_Request $request Comment request.
	 *
	 * @return bool
	 */
	public function create_comment_permission_check( $request ) {
		return true;
	}

	/**
	 * @param WP_REST_Request $request         Comment request
	 * @param array           $required_fields The required fields
	 *
	 * @return array
	 */
	public function validate_input( $request, $required_fields = array() ) {

		$required_fields = wp_parse_args( $required_fields, $this->required_fields );
		$message         = __( 'There are comment fields which are empty. All comment fields are required', Thrive_Comments_Constants::T );

		$status = 1;
		foreach ( $required_fields as $field ) {
			$input = $request->get_param( $field );
			if ( empty( $input ) ) {
				$status = 0;
			}
		}

		//validate gdpr consent
		$storing_consent         = $request->get_param( 'tcm_storing_consent' ) ? 1 : 0;
		$storing_consent_setting = tcms()->tcm_get_setting_by_name( 'storing_consent' );

		if ( $storing_consent_setting && ! $storing_consent && ! get_current_user_id() && ! array_key_exists( 'tcm_cookie_user_name', $_COOKIE ) && ! isset( $_COOKIE['social-login'] ) ) {
			$status  = 0;
			$message = __( 'Please accept the privacy checkbox', Thrive_Comments_Constants::T );
		}


		return array(
			'status'  => $status,
			'message' => $message,
		);

	}

	/**
	 * Check edit permissions
	 *
	 * @param WP_REST_Request $request Comment request.
	 *
	 * @return bool
	 */
	public function update_comment_permission_check( $request ) {
		return $this->update_item_permissions_check( $request );
	}

	/**
	 * Edit comment action
	 *
	 * @param WP_REST_Request $request Comment request.
	 */
	public function update_comment( $request ) {
		return parent::update_item( $request );
	}

	/**
	 * Prepare comment for frontend after moderation
	 *
	 * @param WP_REST_Response $response
	 * @param WP_Comment       $comment
	 * @param WP_REST_Request  $request
	 *
	 * @return mixed
	 */
	public function rest_prepare_comment( $response, $comment, $request ) {
		tcmh()->populate_default_picture_url( $comment, array() );
		$response->data['author_avatar_urls'] = $comment->photo_src;
		if ( $comment->social_avatar ) {
			$response->data['social_avatar'] = 'true';
		}

		$raw          = isset( $response->data['content']['raw'] ) ? $response->data['content']['raw'] : '';
		$author_email = isset( $response->data['author_email'] ) ? $response->data['author_email'] : '';

		$response->data['formatted_date']       = tcmh()->format_comment_date( $response->data['id'] );
		$response->data['comment_content']      = tcmh()->filter_comment( $raw, $comment );
		$response->data['email_hash']           = md5( $author_email );
		$response->data['replace_keyword']      = tcmh()->tcm_replace_keywords( $response->data['author'] );
		$response->data['upvote']               = tcmh()->get_votes( $response->data['id'], 'upvote' );
		$response->data['downvote']             = tcmh()->get_votes( $response->data['id'], 'downvote' );
		$response->data['comment_karma']        = tcmh()->get_karma( $response->data['id'] );
		$response->data['user_achieved_badges'] = tcmh()->get_badges( $response->data['id'] );
		$response->data['show_badge']           = tcmh()->comment_show_badge( $response->data['author'] );

		return $response;
	}

	/**
	 *
	 * Validate recaptcha v3 for a "Vote" request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return void
	 */
	protected function validate_captcha( $request ) {
		$recaptcha = tcm()->recaptcha();
		if ( $recaptcha ) {
			$captcha_api = $recaptcha->get_credentials();
			$captcha_url = 'https://www.google.com/recaptcha/api/siteverify';

			$params = array(
				'response' => $request->get_param( 'g_recaptcha_response' ),
				'secret'   => empty( $captcha_api['secret_key'] ) ? '' : $captcha_api['secret_key'],
				'remoteip' => ! empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			);

			$request  = tve_dash_api_remote_post( $captcha_url, array( 'body' => $params ) );
			$response = json_decode( wp_remote_retrieve_body( $request ), false );

			if ( empty( $response ) || $response->success === false || $response->score <= $captcha_api['connection']['threshold'] ) {
				return new WP_Error( 'recaptcha_failed', __( 'Invalid recaptcha', Thrive_Comments_Constants::T ), array( 'status' => 403 ) );
			}
		}
	}

	/**
	 * Add vote to comment and set a cookie for vote type
	 *
	 * @param WP_REST_Request $request Comment request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function vote( $request ) {

		/* validate for logged in users */
		$needs_login = tcms()->tcm_get_setting_by_name( 'tcm_voting_only_register' );
		if ( $needs_login && ! get_current_user_id() ) {
			return new WP_Error( 'tcm_vote_disabled', __( 'No voting for visitors', Thrive_Comments_Constants::T ), array( 'status' => 403 ) );
		}

		$allowed_vote_type = tcms()->tcm_get_setting_by_name( 'tcm_vote_type' );
		if ( empty( $allowed_vote_type ) || 'no_vote' === $allowed_vote_type ) {
			return new WP_Error( 'tcm_vote_disabled', __( 'Voting is disabled', Thrive_Comments_Constants::T ), array( 'status' => 403 ) );
		}

		$err = $this->validate_captcha( $request );
		if ( is_wp_error( $err ) ) {
			return $err;
		}

		//Checking for no spam
		$verify_spam = tve_dash_check_secret( $request->get_param( 'secret_spam' ) );
		if ( ! $verify_spam ) {
			return new WP_Error( 'The comment could not be saved', __( 'Your comment was marked as spam', Thrive_Comments_Constants::T ) );
		}

		$comment_id = $request->get_param( 'comment_id' );
		$comment    = get_comment( $comment_id );
		if ( ! $comment ) {
			return new WP_Error( 'not_found', 'Not found', [ 'status' => 404 ] );
		}

		$author_email          = get_comment_author_email( $comment_id );
		$is_insert             = false;
		$user_tcm_achievements = tcmdb()->get_log( array( 'achievement' ), array( 'email' => $author_email ) );

		//If user doesn't have achievements array, set it
		if ( null === $user_tcm_achievements ) {
			$user_tcm_achievements = Thrive_Comments_Constants::$_default_achievements;
			$is_insert             = true;
		}
		$vote_type = $request->get_param( 'vote_type' );
		if ( $vote_type === 'downvote' && $allowed_vote_type === 'up_only' ) {
			return new WP_Error( 'tcm_vote_disabled', __( 'No downvoting', Thrive_Comments_Constants::T ), array( 'status' => 403 ) );
		}
		//Getting current comment votes meta from db
		$voting = array(
			'upvote'   => (int) get_comment_meta( $comment_id, 'upvote', true ),
			'downvote' => (int) get_comment_meta( $comment_id, 'downvote', true ),
		);
		//Init cookie time
		$time = time() + ( 60 * 60 * 24 * 365 );
		//Get vote cookie of current comment
		if ( isset( $_COOKIE[ 'vote-comment-' . $comment_id ] ) ) {
			$cookie = $_COOKIE[ 'vote-comment-' . $comment_id ];
		}

		if ( ! isset( $user_tcm_achievements[ $vote_type . 's_received' ] ) ) {
			$user_tcm_achievements[ $vote_type . 's_received' ] = 0;
		}
		if ( ! isset( $cookie ) ) {
			//Increment current vote value if no cookie
			$voting[ $vote_type ] += 1;
			//Adding vote to user count
			$user_tcm_achievements[ $vote_type . 's_received' ] += 1;
			tcmh()->set_needs_reply_after_vote( $vote_type, $comment_id );
		} else if ( $cookie === $vote_type ) {
			//Decrease current vote value if same options was pressed twice
			$voting[ $vote_type ] -= 1;
			//Remove current vote from achievements count
			$user_tcm_achievements[ $vote_type . 's_received' ] -= 1;
			//Remove current cookie by setting negative time
			$time = time() - 60 * 60;
		} else if ( $cookie !== $vote_type ) {
			//Increment current vote value if vote exchange from different type
			$voting[ $vote_type ] += 1;
			//Adding vote to user count
			$user_tcm_achievements[ $vote_type . 's_received' ] += 1;
			( 'upvote' === $vote_type ) ? $user_tcm_achievements['downvotes_received'] -= 1 : $user_tcm_achievements['upvotes_received'] -= 1;
			//The last vote is removed because it was changed
			$voting[ 'upvote' === $vote_type ? 'downvote' : 'upvote' ] -= 1;
			tcmh()->set_needs_reply_after_vote( $vote_type, $comment_id );
		}
		//Update votes values for this comment in db
		update_comment_meta( $comment_id, 'downvote', $voting['downvote'] );
		update_comment_meta( $comment_id, 'upvote', $voting['upvote'] );
		$commet = array(
			'comment_ID'    => $comment_id,
			'comment_karma' => ( $voting['upvote'] - $voting['downvote'] ),
		);
		wp_update_comment( $commet );
		//Set cookie or remove cookie based on $time variable
		setcookie( 'vote-comment-' . $comment_id, $vote_type, $time, '/' );
		if ( $is_insert ) {
			tcmdb()->insert_log( array( 'email' => $author_email, 'achievement' => json_encode( $user_tcm_achievements ) ) );
		} else {
			//Update user achievements meta
			tcmdb()->update_log(
				array(
					'achievement' => json_encode( $user_tcm_achievements ),
				),
				array(
					'email' => $author_email,
				)
			);
		}

		/**
		 * At the end of the vote call this action in case someone wants to log something
		 */
		do_action( 'tcm_vote', $comment_id, $voting );

		//Return vote values for mapping them on frontend
		return new WP_REST_Response( $voting, 200 );
	}

	/**
	 * Unsubscribe a user from his own comment.
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function unsubscribe_email( $request ) {
		$comment_id = $request->get_param( 'comment_id' );
		$post_id    = $request->get_param( 'post_id' );
		$user_hash  = $request->get_param( 'user_email_hash' );

		$user_email = tcmdb()->get_email_hash( 'email', array( 'email_hash' => $user_hash ) );
		if ( null !== $user_email ) {
			update_comment_meta( $comment_id, 'author_subscribed', 0 );
		}
		header( 'Location: ' . get_permalink( $post_id ) );
		die;
	}

	/**
	 * Unsubscribe user from post notifications.
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function unsubscribe_post_email( $request ) {
		$user_hash = $request->get_param( 'user_email_hash' );
		$post_id   = $request->get_param( 'post_id' );

		$user_email = tcmdb()->get_email_hash( 'email', array( 'email_hash' => $user_hash ) );

		if ( null !== $user_email ) {
			$post_subscribers = tcmh()->tcm_get_post_subscribers( $post_id );
			// Remove the mail from the post meta.
			if ( ( $key = array_search( $user_email, $post_subscribers ) ) !== false ) {
				unset( $post_subscribers[ $key ] );
			}
			update_post_meta( $post_id, 'tcm_post_subscribers', $post_subscribers );
		}
		header( 'Location: ' . get_permalink( $post_id ) );
		die;
	}

	/**
	 * Get gravatr url
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_gravatar( $request ) {
		$comment_id = $request->get_param( 'comment_id' );
		$comment    = get_comment( $comment_id );
		if ( ! $comment ) {
			return new WP_Error( 'not_found', 'Not found', array( 'status' => 404 ) );
		}
		$email = get_comment_author_email( $comment_id );

		$gravatar_url = tcmh()->tcm_get_avatar_url( $email );

		$response = array( 'gravatar_url' => $gravatar_url );

		return new WP_REST_Response( $response, 200 );
	}
}

