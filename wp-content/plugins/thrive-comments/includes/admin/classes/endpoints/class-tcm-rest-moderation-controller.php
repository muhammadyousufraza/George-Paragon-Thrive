<?php

/**
 * FileName  class-tcm-rest-moderation-controller.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */
class TCM_REST_Moderation_Controller extends WP_REST_Comments_Controller {

	/**
	 * Comment post attribute for the rest api
	 */
	const TCM_COMMENT_POST = 'tcm_comment_post';

	/**
	 * Comment post attribute for the rest api
	 */
	const TCM_PARENT_COMMENT = 'tcm_parent_comment';
	/**
	 * Rest version
	 *
	 * @var int
	 */
	public static $version = 1;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		parent::__construct();

		$this->namespace = 'tcm/v' . self::$version;
		$this->rest_base = 'moderation';

		$this->register_meta_fields();
		$this->hooks();
	}

	/**
	 * Registers the extra routes for the objects of the moderation controller.
	 *
	 * @access public
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/bulk_actions', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'bulk_actions' ),
				'permission_callback' => array( $this, 'bulk_actions_permissions_check' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/moderators', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_moderators' ),
				'permission_callback' => array( $this, 'get_moderators_permission_check' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/gravatar', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_gravatar' ),
				'permission_callback' => '__return_true',
			),
		) );

	}

	/**
	 * Add hooks for extra rest functionality
	 */
	public function hooks() {
		if ( tcah()->can_see_moderation() ) {
			add_filter( 'rest_comment_collection_params', array( $this, 'add_to_collection_params' ) );
			add_filter( 'rest_comment_query', array( $this, 'rest_comment_query' ), 10, 2 );
			add_filter( 'rest_prepare_comment', array( $this, 'rest_prepare_comment' ), 10, 3 );
			add_filter( 'user_has_cap', array( $this, 'tcm_has_cap' ), 50, 4 );
		}
	}

	/**
	 * Filter to add capabilities to the current user based on thrive comment moderators
	 *
	 * @param array   $all_caps An array of all the user's capabilities.
	 * @param array   $caps     Actual capabilities for meta capability.
	 * @param array   $args     Optional parameters passed to has_cap(), typically object ID.
	 * @param WP_User $user     The user object.
	 *
	 * @return mixed
	 */
	public function tcm_has_cap( $all_caps, $caps, $args, $user ) {

		if ( tcah()->can_see_moderation( $user ) ) {
			//if the user is a moderator set all the capabilities necessary, so he can do all the actions on a certain comment
			$all_caps['moderate_comments']    = true;
			$all_caps['edit_posts']           = true;
			$all_caps['edit_comment']         = true;
			$all_caps['edit_others_posts']    = true;
			$all_caps['edit_published_posts'] = true;

			//authors which care moderators do not have the capability to edit pages, so only for editing comments from frontend we should give them this cap
			$all_caps['edit_others_pages']    = true;
			$all_caps['edit_published_pages'] = true;
		}

		return $all_caps;
	}


	/**
	 * Modification of the comment right before it is returned.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Comment       $comment  The comment object
	 *
	 * @return WP_REST_Response $response
	 */
	public function rest_prepare_comment( $response, $comment ) {

		tcmh()->populate_default_picture_url( $comment );
		$response->data['author_avatar_urls'] = $comment->photo_src;
		if ( $comment->social_avatar ) {
			$response->data['social_avatar'] = 'true';
		}

		$author_email = isset( $response->data['author_email'] ) ? $response->data['author_email'] : '';

		$response->data['email_hash']                = md5( $author_email );
		$response->data['tcm_date_format_day']       = date( 'd F Y', strtotime( $response->data['date'] ) );
		$response->data['tcm_date_format_hour']      = date( 'H:s', strtotime( $response->data['date'] ) );
		$response->data['tcm_comments_author_count'] = tcamh()->get_comments_author_count( $author_email );

		$user_tcm_achievements                    = tcmdb()->get_log( array( 'achievement' ), array( 'email' => $author_email ) );
		$response->data['tcm_comments_upvotes']   = empty( $user_tcm_achievements['upvotes_received'] ) ? 0 : $user_tcm_achievements['upvotes_received'];
		$response->data['tcm_comments_downvotes'] = empty( $user_tcm_achievements['downvotes_received'] ) ? 0 : $user_tcm_achievements['downvotes_received'];

		$response->data['comment_content']     = tvd_remove_script_tag( $response->data['content']['rendered'] );
		$response->data['content']['raw']      = tvd_remove_script_tag( $response->data['content']['raw'] );
		$response->data['content']['rendered'] = tvd_remove_script_tag( $response->data['content']['rendered'] );

		/**
		 * Filter for changing the comment response from moderation
		 *
		 * @param WP_REST_Response $response the response taken from the db and changed by TC
		 */
		return apply_filters( 'tcm_rest_moderation_response', $response );
	}

	/**
	 * Extra parameters for rest api
	 *
	 * @param array $query_params
	 *
	 * @return mixed
	 */
	public function add_to_collection_params( $query_params ) {
		$query_params['meta_query'] = array(
			'default'     => null,
			'description' => __( 'Include comments with a matching comment meta key' ),
			'type'        => 'object',
			'properties'  => array(
				'featured'    => array(
					'description' => 'If the comments is featured or not',
					'type'        => 'string',
					'context'     => 'edit',
				),
				'needs_reply' => array(
					'description' => 'If the comments needs reply',
					'type'        => 'string',
					'context'     => 'edit',
				),
			),
		);

		return $query_params;
	}

	/**
	 * Set extra arguments for the comment query, used at filters
	 *
	 * @param array           $prepared_args
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	public function rest_comment_query( $prepared_args, $request ) {

		$meta_query = array();

		if ( isset( $request['meta_query'] ) ) {
			$meta_query['relation'] = 'AND';

			foreach ( $request['meta_query'] as $key => $value ) {

				if ( $key === Thrive_Comments_Constants::TCM_DELEGATE ) {

					//for the pending my reply tab the where from query is overwritten in the filter comments clauses
					$meta_query[] = array(
						'relation' => 'OR',
						0          => array(
							'key'     => $key,
							'value'   => '',
							'compare' => 'NOT EXISTS',
						),
						1          => array(
							'key'     => $key,
							'value'   => get_current_user_id(),
							'compare' => 'IN',
						),
					);

					$meta_query[] = array(
						'key'     => Thrive_Comments_Constants::TCM_NEEDS_REPLY,
						'value'   => '1',
						'compare' => '=',
					);

					/**
					 * Added for compatibility with TA
					 *
					 * Allow adding more meta queries to main query
					 */
					$meta_query = apply_filters( 'tcm_delegate_rest_meta_query', $meta_query, $request );

					add_filter( 'comments_clauses', array( $this, 'filter_comments_clauses' ), 1, 2 );

				} else {
					$meta_query[] = array(
						'key'     => $key,
						'value'   => $value,
						'compare' => 'IN',
					);
				}
			}
		}

		$exclude_option = tcms()->tcm_get_setting_by_name( 'tcm_exclude_moderators' );
		if ( $exclude_option ) {
			$moderators_ids                  = tcamh()->tcm_get_moderator_ids();
			$prepared_args['author__not_in'] = $moderators_ids;
		}

		$prepared_args['meta_query'] = $meta_query;

		if ( isset( $request['status'] ) && $request['status'] === Thrive_Comments_Constants::TCM_UNREPLIED ) {
			$unreplied_args = $this->get_unreplied_args( array(), $request );
			$prepared_args  = wp_parse_args( $unreplied_args, $prepared_args );
		}

		if ( isset( $request['post_id'] ) ) {
			$prepared_args['post_id'] = $request['post_id'];
		}

		return $prepared_args;
	}

	/**
	 * Change the last AND from the were condition for the pending my reply tab
	 * We want the comments that are delegated to the current user OR the unreplied comments from the user's posts
	 *
	 * @param array            $pieces
	 * @param WP_Comment_Query $comment_query
	 *
	 * @return array;
	 */
	public function filter_comments_clauses( $pieces, $comment_query ) {
		global $wpdb;
		$table_prefix = $wpdb->prefix;

		// Apply this filter only in the cause of pending my reply tab ( we are sending tcm_delegate ).
		if ( isset( $pieces['where'] ) && strpos( $pieces['where'], Thrive_Comments_Constants::TCM_DELEGATE ) !== false ) {
			$extra_join = '';

			/**
			 * Added for compatibility with TA
			 *
			 * Expand delegate join clauses
			 */
			$extra_join      = apply_filters( 'tcm_delegate_extra_join', $extra_join );
			$pieces['join']  = "LEFT JOIN {$table_prefix}commentmeta ON ({$table_prefix}comments.comment_ID = {$table_prefix}commentmeta.comment_id AND {$table_prefix}commentmeta.meta_key = 'tcm_delegate')
LEFT JOIN {$table_prefix}commentmeta AS mt2 ON ({$table_prefix}comments.comment_ID = mt2.comment_id and mt2.meta_key = 'tcm_needs_reply')" . $extra_join . "";
			$pieces['where'] = $this->build_delegate_where( $comment_query );
		}

		return $pieces;
	}

	/**
	 * Build custom where for pending my reply tab
	 *
	 * @param WP_Comment_Query $comment_query
	 */
	public function build_delegate_where( $comment_query ) {
		global $wpdb;
		$table_prefix = $wpdb->prefix;

		$posts_ids      = get_posts( array(
			'author'         => get_current_user_id(),
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_type'      => 'any',
		) );
		$unreplied_args = $this->get_unreplied_args( $posts_ids );

		$comment_not_in = implode( ',', $unreplied_args['comment__not_in'] );
		$user_not_in    = implode( ',', $unreplied_args['author__not_in'] );
		$delegate_id    = get_current_user_id();

		$delegate_args       = $this->get_delegate_args( $posts_ids );
		$comments_unassigned = implode( ',', $delegate_args );
		if ( empty( $comments_unassigned ) ) {
			$comments_unassigned = "''";
		}

		if ( ! empty( $comment_not_in ) ) {
			$comment_not_in = trim( $comment_not_in, '\"' );
		} else {
			$comment_not_in = "'" . $comment_not_in . "'";
		}

		if ( ! empty( $comments_unassigned ) ) {
			$comments_unassigned = trim( $comments_unassigned, '\"' );
		} else {
			$comments_unassigned = "'" . $comments_unassigned . "'";
		}

		if ( ! empty( $delegate_id ) ) {
			$delegate_id = trim( $delegate_id, '\"' );
		} else {
			$delegate_id = "'" . $delegate_id . "'";
		}

		if ( ! empty( $comment_query->query_vars['post_id'] ) ) {
			$comment_post_id = $wpdb->prepare( 'comment_post_ID = %d', $comment_query->query_vars['post_id'] );
			$where           = $comment_post_id . ' AND ';
		} else {
			$where = '';
		};

		if ( ! empty( $comment_query->date_query ) ) {
			$date_sql = $comment_query->date_query->get_sql();
			$where    .= str_replace( 'AND', '', $date_sql ) . ' AND';
		}

		/**
		 * Starting with wp 5.5 the comment_type field is set by default to be comment so we need to change it accordingly
		 * and also be backwards compatible
		 */
		global $wp_version;
		$comment_type = version_compare( $wp_version, '5.5-beta', '>=' ) ? 'comment' : '';

		$extra_where = '';
		/**
		 * Used for compatibility with TA
		 *
		 * Allow expanding tcm_delegate where clause
		 */
		$extra_where = apply_filters( 'tcm_delegate_extra_where', $extra_where, $delegate_id, $comment_query );

		$where .= "( comment_approved = '1' OR comment_approved = '0' ) AND {$table_prefix}comments.comment_ID NOT IN (" . $comment_not_in . ")"
		          . " AND comment_type IN ('" . $comment_type . "') AND user_id NOT IN (" . $user_not_in . ") AND "
		          . "((( {$table_prefix}commentmeta.comment_id IS NULL AND {$table_prefix}comments.comment_id IN (" . $comments_unassigned . ") AND ( mt2.comment_id IS NULL OR (mt2.meta_key = 'tcm_needs_reply' AND mt2.meta_value = '1'))) OR " . "( (mt2.meta_key = 'tcm_needs_reply' AND mt2.meta_value = '1') AND ({$table_prefix}commentmeta.meta_key = 'tcm_delegate' AND {$table_prefix}commentmeta.meta_value IN (" . $delegate_id . ")))) OR"
		          . "( mt2.meta_key = 'tcm_needs_reply' AND mt2.meta_value = '1' AND ( {$table_prefix}commentmeta.meta_key = 'tcm_delegate' AND {$table_prefix}commentmeta.meta_value IN (" . $delegate_id . ")) AND {$table_prefix}comments.comment_id IN (" . $comments_unassigned . ") AND (comment_approved = '1' OR comment_approved = '0')" . $extra_where . "))";

		return $where;
	}

	/**
	 * Permission check for accesing rest api in thrive comments
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function tcm_rest_comments_permission_check() {
		return tcah()->can_see_moderation();
	}

	public function get_delegate_args( $posts_ids ) {
		//if the user doesn't have posts => there no comments
		if ( empty( $posts_ids ) ) {
			return array();
		}

		$comments_ids   = array();
		$comments_query = new WP_Comment_Query();
		$comments       = $comments_query->query( array(
			'post__in' => $posts_ids,
			'status'   => array( 'approve', 'hold' ), //only from the comment approved or unapproved
		) );

		if ( ! empty( $comments ) ) {
			$comments_ids = wp_list_pluck( $comments, 'comment_ID' );
		}

		return $comments_ids;
	}

	/**
	 * Get the necessary arguments for unreplied filter
	 *
	 * @return array
	 */
	public function get_unreplied_args( $posts = array(), $request = null ) {
		$moderators_ids = tcamh()->tcm_get_moderator_ids();
		$meta_query     = array();

		$comments_query = new WP_Comment_Query();
		$comments       = $comments_query->query(
			array(
				'author__in'     => $moderators_ids,
				'parent__not_in' => array( 0 ),
			)
		);

		$replied = array();
		foreach ( $comments as $comment ) {
			$replied[] = (int) $comment->comment_parent;
		}

		$unreplied_args = array(
			'comment__not_in' => $replied,
			'author__not_in'  => $moderators_ids,
			'status'          => array( 'approve', 'hold' ), // Only from the comment approved or unapproved.
		);

		$meta_query['relation'] = 'AND';
		$meta_query[]           = array(
			'relation' => 'OR',
			0          => array(
				'key'     => 'tcm_needs_reply',
				'value'   => '',
				'compare' => 'NOT EXISTS',
			),
			1          => array(
				'key'     => 'tcm_needs_reply',
				'value'   => 1,
				'compare' => 'IN',
			),
		);

		$unreplied_args['meta_query'] = $meta_query;


		//take the unreplied comments for certain posts
		if ( ! empty( $posts ) ) {
			$unreplied_args['post__in'] = $posts;
		}

		/**
		 * Currently used for compatibility with TA
		 * It can be used to expand query args for unreplied comments
		 */
		return apply_filters( 'tcm_get_unreplied_args', $unreplied_args, $request );
	}

	/**
	 * Checks if a given REST request has access to update a comment.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|bool True if the request has access to update the item, error object otherwise.
	 */
	public function bulk_actions_permissions_check( $request ) {

		$comment_ids = $request->get_param( 'comment_ids' );

		foreach ( $comment_ids as $comment_id ) {
			$comment = $this->get_comment( $comment_id );

			if ( is_wp_error( $comment ) ) {
				return $comment;
			}

			if ( ! $this->check_edit_permission( $comment ) ) {
				return new WP_Error( 'rest_cannot_edit', __( 'Sorry, you are not allowed to edit this comment.' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}

		return true;
	}

	/**
	 * Apply bulk actions on selected comments
	 *
	 * @param WP_REST_Request $request Data from request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or error object on failure.
	 */
	public function bulk_actions( $request ) {

		$updated_ids = array();
		$comment_ids = $request->get_param( 'comment_ids' );
		$status      = $request->get_param( 'status' );
		$value       = $request->get_param( 'value' );

		foreach ( $comment_ids as $comment_id ) {
			if ( 'status' === $status ) {

				$old_status = wp_get_comment_status( $comment_id );
				//wordpress returns unapproved if the status is 0, but we need to have 'hold' in this case because this is what we are sending
				if ( $old_status === 'unapproved' ) {
					$old_status = 'hold';
				}

				if ( $value === $old_status ) { //if the old status is the same as the new one, than we do not need to update
					continue;
				}

				$changed = $this->moderation_handle_status_param( $value, intval( $comment_id ) );

				if ( ! $changed ) {
					$data_fail = array(
						'comment_id' => $comment_id,
						'status'     => 500,
					);

					return new WP_Error( 'rest_comment_failed_edit', __( 'Bulk Actions for comments failed.' ), $data_fail );
				}

				//if the comment is unapproved we need to make it unfeatured also
				if ( $value === 'hold' ) {
					$featured = get_comment_meta( $comment_id, Thrive_Comments_Constants::TCM_FEATURED, true );
					if ( intval( $featured ) !== 0 ) {
						tcamh()->update_comment_meta( Thrive_Comments_Constants::TCM_FEATURED, 0, $comment_id );
					}
				}
			} else {
				tcamh()->update_comment_meta( $status, $value, $comment_id );

				if ( $status === Thrive_Comments_Constants::TCM_DELEGATE ) {
					$user = get_user_by( 'id', $value );
					if ( $user ) {
						$delegate_user = $user->display_name;
					}
				}

				//if we make a comment featured, it needs to be approved also
				if ( $status === Thrive_Comments_Constants::TCM_FEATURED ) {
					$comment_status = wp_get_comment_status( $comment_id );
					if ( $comment_status !== Thrive_Comments_Constants::TCM_APPROVE ) {
						wp_set_comment_status( $comment_id, 1 );
					}
				}
			}
			$updated_ids[] = array(
				'id'            => $comment_id,
				'attribute'     => $status,
				'new_value'     => $value,
				'delegate_user' => ( isset( $delegate_user ) ? $delegate_user : '' ),
			);
		}

		$response = rest_ensure_response( array( 'data' => $updated_ids ) );

		return $this->set_count_headers( $response, $request );
	}

	/**
	 * Sets the comment_status of a given comment object when creating or updating a comment.
	 *
	 * @access protected
	 *
	 * @param string|int $new_status New comment status.
	 * @param int        $comment_id Comment ID.
	 *
	 * @return bool Whether the status was changed.
	 */
	protected function moderation_handle_status_param( $new_status, $comment_id ) {

		switch ( $new_status ) {
			case 'approved' :
			case 'approve':
			case '1':
				$changed = wp_set_comment_status( $comment_id, 'approve' );
				break;
			case 'hold':
			case '0':
				$changed = wp_set_comment_status( $comment_id, 'hold' );
				break;
			case 'spam' :
				$changed = wp_spam_comment( $comment_id );
				break;
			case 'unspam' :
				$changed = wp_unspam_comment( $comment_id );
				break;
			case 'trash' :
				$changed = wp_trash_comment( $comment_id );
				break;
			case 'untrash' :
				$changed = wp_untrash_comment( $comment_id );
				break;
			case 'delete':
				$changed = wp_delete_comment( $comment_id, true );
				break;
			default :
				$changed = false;
				break;
		}

		return $changed;
	}

	/**
	 * Retrieves a list of comment items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or error object on failure.
	 * @since  4.7.0
	 * @access public
	 *
	 */
	public function get_items( $request ) {
		$response = parent::get_items( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->set_count_headers( $response, $request );
	}

	/**
	 * Updates a comment and sets the response headers with the number of comments
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$filter_status = $request->get_param( 'status' );
		$request->set_param( 'status', $request->get_param( 'action_status' ) );
		$response = parent::update_item( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$request->set_param( 'status', $filter_status );

		return $this->set_count_headers( $response, $request );
	}

	/**
	 * Delete comment
	 *
	 * @param WP_REST_Request $request Request from moderation.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {

		$response = parent::delete_item( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->set_count_headers( $response, $request );
	}

	/**
	 * Checks if a given request has access to create a comment.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|bool True if the request has access to create items, error object otherwise.
	 * @since 4.7.0
	 *
	 */
	public function create_item_permissions_check( $request ) {

		$this->before_create_check( $request );

		return parent::create_item_permissions_check( $request );

	}

	/**
	 * We are making a check if a reply is added from moderation at a landing page comment
	 * If this is the case, we enable comments_open
	 *
	 * @param WP_REST_Request $request
	 */
	public function before_create_check( $request ) {
		$post_id = $request->get_param( 'post' );

		// If we have a tc element on a landing page than show comments only if the settings from TC allow that.

		if ( function_exists( 'tve_post_is_landing_page' ) && tve_post_is_landing_page( $post_id ) ) {

			$tc_comments_closed = tcms()->tcm_get_setting_by_name( 'activate_comments' );

			if ( $tc_comments_closed && ! tcms()->close_comments( $post_id ) ) {
				add_filter( 'comments_open', '__return_true' );
			};
		}
	}

	/**
	 * Creates a comment and sets the response headers with the number of comments
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$filter_status = $request->get_param( 'status' );
		$request->set_param( 'status', $request->get_param( 'action_status' ) );

		$response = parent::create_item( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$request->set_param( 'status', $filter_status );

		$comment = $this->get_comment( $response->data['id'] );
		tcmh()->tcm_send_notification( $comment );
		update_comment_meta( $response->data['id'], 'author_subscribed', 0 );

		return $this->set_count_headers( $response, $request );
	}


	/**
	 * @param WP_REST_Response $response
	 * @param WP_REST_Request  $request
	 *
	 * @return mixed
	 */
	public function set_count_headers( $response, $request ) {

		//get counter for each tab
		$no_pending   = $this->get_comments_counter( Thrive_Comments_Constants::TCM_UNAPPROVE, $request );
		$no_unreplied = $this->get_comments_counter( Thrive_Comments_Constants::TCM_UNREPLIED, $request );
		$no_my_reply  = $this->get_comments_counter( Thrive_Comments_Constants::TCM_DELEGATE, $request );

		$no_featured = $this->get_comments_counter( Thrive_Comments_Constants::TCM_FEATURED, $request );
		$no_spam     = $this->get_comments_counter( Thrive_Comments_Constants::TCM_SPAM, $request );
		$no_trash    = $this->get_comments_counter( Thrive_Comments_Constants::TCM_TRASH, $request );

		$response->header( 'X-WP-PENDING', $no_pending );
		$response->header( 'X-WP-UNREPLIED', $no_unreplied );
		$response->header( 'X-WP-PENDING-MY-REPLY', $no_my_reply );
		$response->header( 'X-WP-FEATURED', $no_featured );
		$response->header( 'X-WP-SPAM', $no_spam );
		$response->header( 'X-WP-TRASH', $no_trash );

		$no_total = $this->get_comments_counter( 'all', $request );
		$response->header( 'X-WP-Total', $no_total );

		return $response;
	}

	/**
	 * Get how many comments are for each tab
	 *
	 * @param string          $tab
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return int
	 */
	public function get_comments_counter( $tab, $request ) {

		$args = array(
			'no_found_rows' => false,
			'number'        => ( isset( $request['per_page'] ) ) ? $request['per_page'] : 10,
			'post_id'       => ( isset( $request['post_id'] ) ) ? $request['post_id'] : 0,
			'search'        => ( isset( $request['search'] ) ) ? $request['search'] : '',
			'type__not_in'  => array( 'pingback', 'trackback', 'review' ),
		);

		//maybe implement some caching for the values
		switch ( $tab ) {
			case Thrive_Comments_Constants::TCM_UNAPPROVE:
			case Thrive_Comments_Constants::TCM_SPAM:
			case Thrive_Comments_Constants::TCM_TRASH:
				$args['status'] = $tab;
				break;
			case Thrive_Comments_Constants::TCM_UNREPLIED:
				$unreplied_args = $this->get_unreplied_args( array(), $request );
				$args           = wp_parse_args( $unreplied_args, $args );
				break;
			case Thrive_Comments_Constants::TCM_DELEGATE:
				//for the pending my reply tab the where from query is overwritten in the filter comments clauses
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query'][]           = array(
					'relation' => 'OR',
					array(
						'key'     => Thrive_Comments_Constants::TCM_DELEGATE,
						'value'   => '',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => Thrive_Comments_Constants::TCM_DELEGATE,
						'value'   => get_current_user_id(),
						'compare' => 'IN',
					),
				);
				$args['meta_query'][]           = array(
					'key'     => Thrive_Comments_Constants::TCM_NEEDS_REPLY,
					'value'   => '1',
					'compare' => '=',
				);

				$args['meta_query'] = apply_filters( 'tcm_delegate_rest_meta_query', $args['meta_query'], $request );

				add_filter( 'comments_clauses', array( $this, 'filter_comments_clauses' ), 1, 2 );
				break;
			case Thrive_Comments_Constants::TCM_FEATURED:
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query'][]           = array(
					'key'     => Thrive_Comments_Constants::TCM_FEATURED,
					'value'   => 1,
					'compare' => 'IN',
				);
				break;
			default:
				break;
		}

		$exclude_option = tcms()->tcm_get_setting_by_name( 'tcm_exclude_moderators' );
		if ( $exclude_option ) {
			$moderators_ids         = tcamh()->tcm_get_moderator_ids();
			$args['author__not_in'] = $moderators_ids;
		}

		/**
		 * Added for compatibility with TA
		 *
		 * Filter query args
		 */
		$args  = apply_filters( 'tcm_header_comment_count', $args, $request );
		$query = new WP_Comment_Query;
		$query->query( $args );

		return (int) $query->found_comments;
	}

	/**
	 * Add custom meta fields for comments to use them with the rest api
	 */
	public function register_meta_fields() {
		register_rest_field( $this->get_object_type(), Thrive_Comments_Constants::TCM_FEATURED, array(
			'get_callback'    => array( $this, 'get_comment_featured' ),
			'update_callback' => array( $this, 'update_comment_featured' ),
		) );

		register_rest_field( $this->get_object_type(), Thrive_Comments_Constants::TCM_NEEDS_REPLY, array(
			'get_callback'    => array( $this, 'get_comment_needs_reply' ),
			'update_callback' => array( $this, 'update_comment_needs_reply' ),
		) );

		register_rest_field( $this->get_object_type(), Thrive_Comments_Constants::TCM_DELEGATE, array(
			'get_callback'    => array( $this, 'get_comment_delegate' ),
			'update_callback' => array( $this, 'update_comment_delegate' ),
		) );

		register_rest_field( $this->get_object_type(), Thrive_Comments_Constants::TCM_DELEGATE_AUTHOR, array(
			'get_callback'    => array( $this, 'get_comment_delegate_author' ),
			'update_callback' => array( $this, 'update_comment_delegate_author' ),
		) );

		register_rest_field( $this->get_object_type(), self::TCM_COMMENT_POST, array(
			'get_callback' => array( $this, 'get_comment_post' ),
		) );

		register_rest_field( $this->get_object_type(), self::TCM_PARENT_COMMENT, array(
			'get_callback' => array( $this, 'get_parent_comment' ),
		) );
	}

	/**
	 * @param array $comment_data
	 *
	 * @return array|null|WP_Comment
	 */
	public function get_parent_comment( $comment_data ) {
		$parent = array();

		if ( $this->get_moderators_permission_check() && $comment_data['parent'] !== 0 ) {
			$parent = get_comment( $comment_data['parent'] );

			$parent->comment_content = tvd_remove_script_tag( $parent->comment_content );
		}

		return $parent;
	}

	/**
	 * Get the post for the comment
	 *
	 * @param WP_REST_Request $request Information about request
	 *
	 * @return array|null|WP_Post
	 */
	public function get_comment_post( $comment_data ) {

		//get the post where the comment was placed
		$pos_id = $comment_data['post'];
		$post   = get_post( $pos_id );

		if ( ! $post ) {
			return null;
		}

		$post->edit_link = html_entity_decode( get_edit_post_link( $post->ID ) );

		$user                    = $this->get_post_author( $post );
		$post->user_nicename     = ( $user ) ? $user->user_nicename : '';
		$post->user_display_name = ( $user ) ? $user->display_name : '';
		$post->guid              = html_entity_decode( $post->guid );
		$post->link              = get_permalink( $post );

		/**
		 * Filter the post where the comment was placed
		 *
		 * @param WP_Post $post         where the comment is
		 * @param array   $comment_data The comment details
		 */
		$post = apply_filters( 'tcm_get_post_for_comment', $post, $comment_data );

		return $post;
	}

	/**
	 * Returns the user that created the post
	 *
	 * @param WP_Post $post
	 *
	 * @return false|WP_User
	 */
	public function get_post_author( $post ) {
		$user = get_user_by( 'ID', $post->post_author );

		return $user;
	}

	/**
	 * Get if a comments is featured or not
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|mixed
	 */
	public function get_comment_featured( $request ) {

		if ( ! empty( $request['id'] ) ) {
			return get_comment_meta( $request['id'], Thrive_Comments_Constants::TCM_FEATURED, true );
		}

		return false;
	}

	/**
	 * Update the value for comment featured status
	 *
	 * @param WP_REST_Request $request
	 * @param WP_Comment      $comment_obj
	 *
	 * @return bool|WP_Error
	 */
	public function update_comment_featured( $meta_value, $comment_obj, $meta_key, $request ) {
		$is_insert = false;
		// We update only the meta key that we need.
		if ( $request->get_param( 'update_meta' ) !== Thrive_Comments_Constants::TCM_FEATURED ) {
			return true;
		}
		//Before updating the meta, lets save the featured count for user
		$user_tcm_achievements = tcmdb()->get_log( array( 'achievement' ), array( 'email' => $comment_obj->comment_author_email ) );
		//If user doesn't have achievements array, set it
		if ( null === $user_tcm_achievements ) {
			$user_tcm_achievements = Thrive_Comments_Constants::$_default_achievements;
			$is_insert             = true;
		}
		if ( 1 === $meta_value ) {
			$user_tcm_achievements['featured_comments'] += 1;
		} else if ( $user_tcm_achievements['featured_comments'] > 0 ) {
			$user_tcm_achievements['featured_comments'] -= 1;
		}
		if ( $is_insert ) {
			tcmdb()->insert_log( array( 'email' => $comment_obj->comment_author_email, 'achievement' => json_encode( $user_tcm_achievements ) ) );
		} else {
			tcmdb()->update_log(
				array(
					'achievement' => json_encode( $user_tcm_achievements ),
				),
				array(
					'email' => $comment_obj->comment_author_email,
				)
			);

		}

		return tcamh()->update_comment_meta( Thrive_Comments_Constants::TCM_FEATURED, $meta_value, $comment_obj );
	}

	/**
	 * Get the delegated moderators name
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|mixed
	 */
	public function get_comment_delegate_author( $request ) {

		if ( ! empty( $request['id'] ) ) {
			$tcm_delegate = $this->get_comment_delegate( $request );

			if ( '0' === $tcm_delegate ) {
				return __( 'Unassigned', Thrive_Comments_Constants::T );
			}

			$author_info = get_userdata( $tcm_delegate );
			$author_name = $author_info->display_name;

			return $author_name;
		}

		return false;
	}

	/**
	 * We don't need to update this. The function is just a callback for the rest api to call
	 *
	 * @return bool
	 */
	public function update_comment_delegate_author() {
		return false;
	}

	/**
	 * Get the delegated moderator for the comment
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|mixed
	 */
	public function get_comment_delegate( $request ) {

		if ( ! empty( $request['id'] ) ) {
			$tcm_delegate = get_comment_meta( $request['id'], Thrive_Comments_Constants::TCM_DELEGATE, true );

			if ( empty( $tcm_delegate ) ) {
				$post = get_post( $request['post'] );
				if ( ! $post ) {
					$tcm_delegate = '0';
				} else {
					$author = get_user_by( 'id', $post->post_author );

					if ( $author && tcah()->can_see_moderation( $author ) ) {
						$tcm_delegate = $author->ID;
					} else {
						$tcm_delegate = '0';
					}
				}
				/**
				 * Added for compatibility with TA
				 * Expend comment delegate query
				 */
				$tcm_delegate = apply_filters( 'tcm_comment_delegate', $tcm_delegate, $request );
			}

			return $tcm_delegate;
		}

		return false;
	}

	/**
	 * Update the delegated moderator for the comment
	 *
	 * @param WP_REST_Request $request
	 * @param WP_Comment      $comment_obj
	 *
	 * @return bool|WP_Error
	 */
	public function update_comment_delegate( $meta_value, $comment_obj, $meta_key, $request ) {
		// We update only the meta key that we need.
		if ( $request->get_param( 'update_meta' ) !== Thrive_Comments_Constants::TCM_DELEGATE ) {
			return true;
		}

		return tcamh()->update_comment_meta( Thrive_Comments_Constants::TCM_DELEGATE, $meta_value, $comment_obj );
	}


	/**
	 * Returns if a comments needs reply
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|mixed
	 */
	public function get_comment_needs_reply( $request ) {
		if ( ! empty( $request['id'] ) ) {
			$tcm_needs_reply = get_comment_meta( $request['id'], Thrive_Comments_Constants::TCM_NEEDS_REPLY, true );

			if ( empty( $tcm_needs_reply ) ) {
				$tcm_needs_reply = $this->set_needs_reply_status( $request['id'] );
			}

			return $tcm_needs_reply;
		}

		return false;
	}

	/**
	 * Update if a comment needs or not a reply
	 *
	 * @param WP_REST_Request $request
	 * @param WP_Comment      $comment_obj
	 *
	 * @return bool|WP_Error
	 */
	public function update_comment_needs_reply( $meta_value, $comment_obj, $meta_key, $request ) {

		//we update only the meta key that we need
		if ( $request->get_param( 'update_meta' ) !== Thrive_Comments_Constants::TCM_NEEDS_REPLY ) {
			return true;
		}

		return tcamh()->update_comment_meta( Thrive_Comments_Constants::TCM_NEEDS_REPLY, $meta_value, $comment_obj );
	}

	/**
	 * Set comment meta for reply needed status and returns the new update comment meta with all the information
	 *
	 * @param int   $comment_id   Comment ID.
	 * @param array $comment_meta Comment Meta.
	 *
	 * @return mixed
	 */
	public function set_needs_reply_status( $comment_id ) {
		$tcm_needs_reply = 1;
		$comment_obj     = $this->get_comment( $comment_id );

		if ( is_wp_error( $comment_obj ) ) {
			return 0;
		}

		// first let's check if this is infact an admin comment
		$author = get_user_by( 'email', $comment_obj->comment_author_email );

		if ( $author && tcah()->can_see_moderation( $author ) ) {
			//if it's an admin comment results, it doesn't need an admin reply
			$tcm_needs_reply = 2;
			update_comment_meta( $comment_obj->comment_ID, Thrive_Comments_Constants::TCM_NEEDS_REPLY, $tcm_needs_reply );

			return $tcm_needs_reply;
		}

		$comment_children = $comment_obj->get_children();
		if ( empty( $comment_children ) ) {
			//update comment meta with needs reply
			$tcm_needs_reply = 1;
			update_comment_meta( $comment_obj->comment_ID, Thrive_Comments_Constants::TCM_NEEDS_REPLY, $tcm_needs_reply );
		} else {
			//check if there is reply from an admin
			$needs_reply = true;
			foreach ( $comment_children as $child ) {
				$author = get_user_by( 'email', $child->comment_author_email );
				if ( $author && tcah()->can_see_moderation( $author ) ) {
					//we have a comment from and admin, so it's not reply needed
					$tcm_needs_reply = 0;
					update_comment_meta( $comment_obj->comment_ID, Thrive_Comments_Constants::TCM_NEEDS_REPLY, $tcm_needs_reply );
					$needs_reply = false;
					break;
				}
			}
			if ( $needs_reply ) {
				//update comment meta with needs reply
				$tcm_needs_reply = 1;
				update_comment_meta( $comment_obj->comment_ID, Thrive_Comments_Constants::TCM_NEEDS_REPLY, $tcm_needs_reply );
			}
		}

		return $tcm_needs_reply;
	}

	/**
	 * Check if the caller can request all the moderators from the website
	 *
	 * @return bool
	 */
	public function get_moderators_permission_check() {
		return tcah()->can_see_moderation();
	}

	/**
	 * Return all the moderators from the website
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function get_moderators() {
		$moderators = tcamh()->tcm_get_moderators();
		$response   = rest_ensure_response( $moderators );

		return $response;
	}

	/**
	 * Get gravatar for moderation dashboard
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_gravatar( $request ) {

		$email        = $request->get_param( 'email' );
		$gravatar_url = tcmh()->tcm_get_avatar_url( $email );

		$response = array( 'gravatar_url' => $gravatar_url );

		return new WP_REST_Response( $response, 200 );
	}
}
