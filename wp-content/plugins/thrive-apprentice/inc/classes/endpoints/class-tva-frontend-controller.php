<?php

use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

class TVA_Frontend_Controller extends TVA_REST_Controller {

	/**
	 * Controller base
	 *
	 * @var string
	 */
	public $base = 'frontend';

	/**
	 * Count course comments
	 *
	 * @var int
	 */
	public $comment_count = 0;

	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/process_conversion/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'process_conversion' ),
				'permission_callback' => array( $this, 'frontend_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/mark_lesson/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mark_lesson' ),
				'permission_callback' => array( $this, 'frontend_permissions_check' ),
				'args'                => array(
					'lesson_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/filters/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'filters' ),
				'permission_callback' => array( $this, 'frontend_permissions_check' ),
				'args'                => array(
					'page' => array(
						'type' => 'integer',
					),
				),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/check_email/', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'check_email_exists' ),
				'permission_callback' => array( $this, 'frontend_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/create_comment/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_comment' ),
				'permission_callback' => array( $this, 'frontend_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/comments/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_comments' ),
				'permission_callback' => array( $this, 'frontend_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Check if email exists
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request|WP_REST_Response
	 */
	public function check_email_exists( $request ) {

		$email = $request->get_param( 'email' );
		if ( empty( $email ) ) {
			$exists = false;
		} else {
			$user   = get_user_by( 'email', $email );
			$exists = ! empty( $user );
		}

		return new WP_REST_Response( $exists, 200 );
	}

	/**
	 * Create a new comment
	 *
	 * @param $request
	 *
	 * @return array
	 */
	public function create_comment( $request ) {
		$comment_fields            = $this->prepare_comment_for_database( $request );
		$comment_id                = wp_new_comment( $comment_fields );
		$request['new_cooment_id'] = $comment_id;

		$prepare_comment_cookie = array(
			'comment_ID'    => $comment_id,
			'comment_email' => $comment_fields['comment_author_email'],
		);

		/**
		 * Set a cookie for comments in approval stage, so we can display them for the user who post them
		 */
		if ( $comment_fields['comment_approved'] == 0 ) {
			setcookie( 'tva_comment_cookie_' . $comment_id, json_encode( $prepare_comment_cookie ), 0, '/' );
		}

		/**
		 * Set guest user data so we can autofill the comment form
		 */
		if ( ! is_user_logged_in() ) {
			if ( ! array_key_exists( 'tva_cookie_user_name', $_COOKIE ) ) {
				setcookie( 'tva_cookie_user_name', json_encode( $comment_fields['comment_author'] ), 0, '/' );
				setcookie( 'tva_cookie_user_email', json_encode( $comment_fields['comment_author_email'] ), 0, '/' );
			}

			if ( ! empty( $comment_fields['comment_author_url'] ) ) {
				setcookie( 'tva_cookie_user_url', json_encode( esc_url( $comment_fields['comment_author_url'] ) ), 0, '/' );
			}
		}

		return $this->get_comments( $request );
	}

	/**
	 * Parse comment before db insert
	 *
	 * @param WP_REST_Request $request Comment request.
	 *
	 * @return array
	 */
	public function prepare_comment_for_database( $request ) {
		$comment_data = $request->get_param( 'comment_data' );
		$current_user = wp_get_current_user();

		return array(
			'comment_post_ID'      => $comment_data['comment_post_ID'],
			'comment_author'       => $comment_data['comment_author'],
			'comment_author_email' => $comment_data['comment_author_email'],
			'comment_author_url'   => $comment_data['comment_author_url'],
			'comment_content'      => $comment_data['comment_content'],
			'comment_parent'       => $comment_data['comment_parent'],
			'comment_type'         => $comment_data['comment_type'],
			'comment_term_ID'      => $comment_data['comment_term_ID'],
			'user_id'              => $comment_data['user_id'],
			'comment_approved'     => $current_user->has_cap( 'moderate_comments' ) ? 1 : 0,
		);
	}

	/**
	 * Get comments
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_comments( $request ) {
		$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
		$comment_data   = $request->get_param( 'comment_data' );
		$args           = array(
			'post_id'    => $comment_data['comment_post_ID'],
			'parent'     => 0,
			'meta_key'   => $comment_data['comment_post_ID'] == $hidden_post_id ? 'tva_course_comment_term_id' : '',
			'meta_value' => $comment_data['comment_post_ID'] == $hidden_post_id ? $comment_data['comment_term_ID'] : '',
		);
		$new_comment_id = $request->get_param( 'new_cooment_id' );

		return $this->get_comments_from_post( $args, $new_comment_id );
	}

	/**
	 * Get all comments from a post based on query comments
	 *
	 * @param array $query_comments parameteres for taking comments from db.
	 *
	 * @return array
	 */
	public function get_comments_from_post( $query_comments, $new_comment_id ) {
		$comments          = get_comments( $query_comments );
		$filtered_comments = $this->filter_comments( $comments, $new_comment_id );
		$all_comments      = $this->comments_parents_with_children( $filtered_comments, $query_comments, $new_comment_id );

		$current_user = wp_get_current_user();
		$result       = array(
			'comments'      => $all_comments,
			'nextPage'      => isset( $next_page ) ? $next_page : null,
			'approve'       => $current_user->has_cap( 'moderate_comments' ) ? true : false,
			'comment_count' => $this->comment_count,
		);

		return $result;
	}

	/**
	 * Returns only the comments that are allowed to be viewed by the user
	 *
	 * @param array $comments comments to be filtered.
	 *
	 * @return mixed
	 */
	public function filter_comments( $comments, $new_comment_id ) {
		foreach ( $comments as $key => $comment ) {
			if ( ! $this->check_comment( $comment ) && $new_comment_id != $comment->comment_ID ) {
				unset( $comments[ $key ] );
			}
		}

		return $comments;
	}

	/**
	 * Check if a comment can be shown on frontend
	 * Returns true if the comment cannot be showed on frontend
	 *
	 * @param WP_Comment $comment comment to be checked.
	 *
	 * @return bool
	 */
	public function check_comment( $comment ) {
		$current_user = wp_get_current_user();
		// The admins get respect.
		if ( $current_user->has_cap( 'moderate_comments' ) ) {
			return true;
		}
		$user_email = ( $current_user->user_email ) ? $current_user->user_email : 'default_email';

		$comment_cookie = isset( $_COOKIE[ 'tva_comment_cookie_' . $comment->comment_ID ] ) ? $_COOKIE[ 'tva_comment_cookie_' . $comment->comment_ID ] : '';
		$cookie_data    = ( ! empty( $comment_cookie ) )
			? json_decode( stripslashes( $comment_cookie ), true )
			: array_fill_keys( array(
				'comment_ID',
				'comment_email',
			), 'default_cookie' );

		if ( 1 === intval( $comment->comment_approved ) || $comment->comment_ID === $cookie_data['comment_ID'] || $comment->comment_author_email === $cookie_data['comment_email'] || $comment->comment_author_email === $user_email ) {
			return true;
		}

		return false;
	}

	/**
	 * Get comments with their children also
	 *
	 * @param array $comments All comments that do not have any parents.
	 *
	 * @return array
	 */
	public function comments_parents_with_children( $comments, $query_comments, $new_comment_id ) {
		$result = array();

		$children_query = array(
			'post_id'        => $query_comments['post_id'],
			'parent__not_in' => array( 0 ),
		);

		$all_children = get_comments( $children_query );
		foreach ( $comments as $comment ) {
			if ( $comment->comment_approved == 1 ) {
				$this->comment_count ++;
			}
			$comment_array = $comment->to_array();
			$children      = $this->tva_get_children( $comment, $all_children );
			if ( ! empty( $children ) ) {
				$comment_array['children'] = $this->return_children( $comment, 1, $all_children, $new_comment_id );
			} else {
				$comment_array['children'] = array();
			}
			$result[] = $comment_array;
		}

		return $result;
	}

	/**
	 * @param $comment
	 * @param $all_children
	 * Get comment's childrens
	 *
	 * @return array
	 */
	public function tva_get_children( $comment, $all_children ) {
		$children = array();
		foreach ( $all_children as $child ) {
			if ( $comment->comment_ID === $child->comment_parent ) {
				$children[] = $child;
			}
		}

		return $children;
	}

	/**
	 * Return all children recursively
	 *
	 * @param WP_Comment $comment Comment object parent of the children.
	 * @param int        $level   how deep is the children comment situated.
	 *
	 * @return array
	 */
	public function return_children( $comment, $level, $all_children, $new_comment_id ) {
		$children       = $this->tva_get_children( $comment, $all_children );
		$children_array = array();

		foreach ( $children as $child ) {
			if ( ! $this->check_comment( $child ) && $new_comment_id != $child->comment_ID ) {
				continue;
			}
			$other_children = $this->tva_get_children( $child, $all_children );
			if ( $child->comment_approved == 1 ) {
				$this->comment_count ++;
			}
			$child_array = $child->to_array();
			if ( $other_children ) {
				$child_array['children'] = $this->return_children( $child, $level + 1, $all_children, $new_comment_id );
			}
			$children_array[] = $child_array;
		}

		/* sorting children from oldest to newest */
		if ( ! empty( $children_array ) ) {

			foreach ( $children_array as $key => $part ) {
				$sort[ $key ] = strtotime( $part['comment_date'] );
			}

			array_multisort( $sort, SORT_ASC, $children_array );
		}

		return $children_array;
	}

	/**
	 * Count comments for a course
	 *
	 * @param $comments
	 */
	public function count_course_comments( $comments ) {
		foreach ( $comments as $comment ) {
			if ( $this->check_comment( $comment ) && ( $comment['comment_approved'] == 1 ) ) {
				$this->comment_count ++;
			}
		}
	}

	/**
	 * Filters the content that will be on frontend.
	 *
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function filter_comment( $content ) {
		// if the content contains HTML
		if ( $content != strip_tags( $content ) ) {
			if ( preg_match( '@(</?script.)|(</?style.)@', $content ) ) {
				$content = '<pre>' . strip_tags( $content ) . '</pre>';
			}
		}

		$content = apply_filters( 'comment_text', $content );

		return $content;
	}

	/**
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function process_conversion( $request ) {
		$course_id = $request->get_param( 'course_id' );

		$conversion_cookie = isset( $_COOKIE['tva_conversions'] ) ? $_COOKIE['tva_conversions'] : array();

		if ( ! empty( $conversion_cookie ) ) {
			$data              = stripslashes( $conversion_cookie );
			$conversion_cookie = json_decode( $data );
			if ( in_array( $course_id, $conversion_cookie ) ) {
				return new WP_REST_Response( 'no-conversion', 200 );

			}
		}

		$conversion_cookie[] = $course_id;
		$time                = strtotime( date( 'Y-m-d', time() ) . ' + 365 day' );
		setcookie( 'tva_conversions', json_encode( $conversion_cookie ), $time, '/' );

		$conversions = get_option( 'tva_conversions', array() );
		$logged_in   = (int) get_term_meta( $course_id, 'tva_logged_in', true );

		if ( $logged_in && is_user_logged_in() ) {
			$enrolled_users = get_option( 'tva_enrolled_users', array() );
			$user_id        = get_current_user_id();

			if ( ! array_key_exists( $course_id, $enrolled_users ) ) {
				$enrolled_users[ $course_id ] = array( $user_id );
			} elseif ( ! in_array( $user_id, $enrolled_users[ $course_id ] ) ) {
				array_push( $enrolled_users[ $course_id ], $user_id );
			}

			update_option( 'tva_enrolled_users', $enrolled_users );
		} elseif ( array_key_exists( $course_id, $conversions ) ) {
			$conversions[ $course_id ] ++;
		} else {
			$conversions[ $course_id ] = 1;
		}

		update_option( 'tva_conversions', $conversions );

		return new WP_REST_Response( 'conversion', 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function mark_lesson( $request ) {
		/**
		 * Lessons are marked as 0 for in progress and 1 for learned
		 */
		$lesson_id       = (int) $request->get_param( 'lesson_id' );
		$lesson_instance = new TVA_Lesson( $lesson_id );

		if ( ! $lesson_instance->ID ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'Invalid param currency', 'thrive-apprentice' )
			);
		}

		$terms           = wp_get_post_terms( $lesson_id, TVA_Const::COURSE_TAXONOMY );
		$course_id       = $terms[0]->term_id;
		$completed       = (bool) $request->get_param( 'completed' );
		$learned_lessons = isset( $_COOKIE['tva_learned_lessons'] ) ? $_COOKIE['tva_learned_lessons'] : array();
		$allowed         = tva_access_manager()->has_access_to_object( $lesson_instance->get_the_post() );

		if ( $allowed ) {

			$user_id = get_current_user_id();

			if ( 0 !== $user_id ) {
				/**
				 * Try to get the learned lessons from the user meta if the cookie does not exist
				 */
				if ( empty( $learned_lessons ) ) {
					$learned_lessons = get_user_meta( $user_id, 'tva_learned_lessons', true );
					$learned_lessons = empty( $learned_lessons ) ? array() : $learned_lessons;
				}
			}

			/**
			 * Drop a cookie in case the user is not logged in and we need to show the progress bar
			 * (we always need to do this because the user may see the lesson as logged in but then
			 * come back as not logged in. if so then we won't have any data to show)
			 */

			if ( ! empty( $learned_lessons ) ) {

				if ( is_string( $learned_lessons ) ) {
					$data = stripslashes( $learned_lessons );
				}

				if ( ! is_array( $learned_lessons ) ) {
					$learned_lessons = json_decode( $data, JSON_OBJECT_AS_ARRAY );
				}

				if ( isset( $learned_lessons[ $course_id ] ) && array_key_exists( $lesson_id, $learned_lessons[ $course_id ] ) && $completed ) {
					return new WP_REST_Response( 'lesson-already-marked', 200 );
				}

				/**
				 * Mark all viewed lessons which are in progress (it should be just one but we never know)
				 * as completed so we can mark the one we're on as in progress
				 *
				 * @var  $lesson
				 * @var  $data
				 */
				if ( isset( $learned_lessons[ $course_id ] ) ) {
					foreach ( $learned_lessons[ $course_id ] as $lesson => $data ) {
						if ( $data == 0 ) {
							$learned_lessons[ $course_id ][ $lesson ] = 1;
						}
					}
				}
			}

			/**
			 * Check if any course has all lessons viewed and mark it as completed if so
			 */
			$courses = tva_get_courses( array( 'published' => true ) );

			foreach ( $courses as $course ) {
				if ( $course->term_id != $course_id && isset( $learned_lessons[ $course->term_id ] ) && count( $course->lessons ) == count( $learned_lessons[ $course->term_id ] ) ) {
					foreach ( $learned_lessons[ $course->term_id ] as $lesson => $data ) {
						if ( $data == 0 ) {
							$learned_lessons[ $course->term_id ][ $lesson ] = 1;
						}
					}
				}
			}

			$learned_lessons[ $course_id ][ $lesson_id ] = $completed ? 1 : 0;
			/** Replace or set the cookie */
			$time = strtotime( date( 'Y-m-d', time() ) . ' + 365 day' );
			setcookie( 'tva_learned_lessons', json_encode( $learned_lessons ), $time, '/' );
			$_COOKIE['tva_learned_lessons'] = json_encode( $learned_lessons );

			if ( is_user_logged_in() ) {
				/**
				 * we should also mark the user meta with the lessons he's seen so even if the
				 * user changes browsers we'll ba able to show the correct data
				 */
				$user_id              = get_current_user_id();
				$prev_learned_lessons = get_user_meta( $user_id, 'tva_learned_lessons', true );
				$prev_learned_lessons = is_array( $prev_learned_lessons ) ? $prev_learned_lessons : array();

				if ( isset( $prev_learned_lessons[ $course_id ] ) ) {
					foreach ( $learned_lessons[ $course_id ] as $key => $learned_lesson ) {
						if ( ! array_key_exists( $key, $prev_learned_lessons[ $course_id ] ) ) {
							$prev_learned_lessons[ $course_id ][ $key ] = $learned_lesson;
						}
					}
				} else {
					$prev_learned_lessons[ $course_id ] = $learned_lessons[ $course_id ];
				}

				update_user_meta( $user_id, 'tva_learned_lessons', $prev_learned_lessons );
			}

			/**
			 * Sends the Actions on mark lesson
			 *
			 * It sends the course, module, lesson, actions depending on the status of the lesson
			 */
			tva_send_hooks( $lesson_id, 'end', $lesson_instance->get_course_v2() );
		}
		$course = $lesson_instance->get_course_v2();

		$shortcodes = new TVA_Shortcodes();

		$shortcodes->set_course( $course->get_id() );

		/** @var TVA_Shortcodes */
		global $tva_shortcodes;

		return new WP_REST_Response(
			[
				'lessons_list'     => $tva_shortcodes->tva_lesson_list(),
				'progress_bar'     => $tva_shortcodes->tva_progress_bar(),
				'message'          => TVA_Dynamic_Labels::get( 'course_progress' )['lesson_completed']['title'],
				'next_lesson_url'  => $this->after_mark_lesson_complete_link( $lesson_instance, $course ),
				'next_lesson_text' => $this->after_mark_lesson_complete_text( $lesson_instance, $course ),
			],
			200
		);
	}

	/**
	 * After a lesson is marked as complete, check if the user should be redirected to the completion page
	 *
	 * @param TVA_Lesson    $lesson
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	private function after_mark_lesson_complete_link( $lesson, $course ) {
		if ( tcb_tva_dynamic_actions()->set_data( $lesson )->maybe_go_to_completed_page() ) {
			return $course->get_completed_post()->url;
		}

		return tcb_tva_dynamic_actions()->set_data( $lesson )->get_next_lesson_link();
	}

	/**
	 * After a lesson is marked as complete, check if the user should be redirected to the completion page
	 *
	 * @param TVA_Lesson    $lesson
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	private function after_mark_lesson_complete_text( $lesson, $course ) {
		if ( tcb_tva_dynamic_actions()->set_data( $lesson )->maybe_go_to_completed_page() ) {
			return tcb_tva_dynamic_actions()->set_data( $lesson )->get_course_nav_label( 'to_completion_page' );
		}

		return tcb_tva_dynamic_actions()->set_data( $lesson )->get_next_item_text();
	}

	/**
	 * Do the filter request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function filters( $request ) {
		$template_settings = tva_get_setting( 'template' );

		/**
		 * used in templates
		 */
		$topics = tva_get_topics( array( 'by_courses' => true ) );
		$levels = tva_get_levels();

		$template_id = isset( $template_settings['ID'] ) ? $template_settings['ID'] : 1;

		$page = null;
		if ( ! empty( $request->get_param( 'page' ) ) ) {
			$page = (int) $request->get_param( 'page' );
		}

		$arguments['topics']    = $request->get_param( 'terms' );
		$arguments['page']      = $page;
		$arguments['s']         = sanitize_text_field( $request->get_param( 's' ) );
		$arguments['published'] = true;
		$arguments['per_page']  = tva_get_setting( 'per_page' );

		if ( empty( $page ) ) {
			$page = 1;
		}

		$courses = tva_get_courses( $arguments );

		ob_start();
		if ( empty( $courses ) ) {
			include( TVA_Const::plugin_path( '/templates/err-no-data.php' ) );
		} else {
			include( TVA_Const::plugin_path( '/templates/template_' . $template_id . '/course-list.php' ) );
		}
		$html = ob_get_clean();

		if ( $html === false ) {
			return new WP_Error( 'cant-update', __( 'Error while updating the meta data', 'thrive' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $html, 200 );
	}

	/**
	 * Check if the user has permission to execute this ajax call
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function frontend_permissions_check( $request ) {
		return true;
	}
}
