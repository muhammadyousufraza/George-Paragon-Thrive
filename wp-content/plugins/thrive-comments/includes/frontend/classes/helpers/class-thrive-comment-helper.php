<?php
/**
 * FileName  class-thrive-comments-model.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TCM_Comment_Helper
 *
 * Helper with the comment CRUD
 */
class Thrive_Comments_Helper {

	/**
	 * Hide comment date on frontend
	 */
	const TCM_COMMENT_HIDE_DATE = 0;

	/**
	 * Absolute dates ex. 01.03.2017
	 */
	const TCM_COMMENT_ABSOLUTE_DATE = 1;

	/**
	 * Relative dates ex. two days ago
	 */
	const TCM_COMMENT_RELATIVE_DATE = 2;

	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Comments_Helper singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Comment Extra fields
	 *
	 * @var array fileds from comment object that we do not use
	 */
	protected static $_extra_fields
		= array(
			'comment_date_gmt',
			'comment_author_IP',
			'comment_author_email',
		);

	/**
	 * Thrive_Comments_Helper constructor.
	 */
	public function __construct() {

	}

	/**
	 * Main Thrive Comments Instance.
	 * Ensures only one instance of Thrive Comments Helper is loaded or can be loaded.
	 *
	 * @return Thrive_Comments_Helper
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get all comments from a post based on query comments
	 *
	 * @param array $query_comments parameteres for taking comments from db.
	 *
	 * @return array
	 */
	public function get_comments_from_post( $query_comments ) {

		//take all the comments that are not featured and add featured comments afterwards
		$comments_array    = array();
		$initial_offset    = $query_comments['offset'];
		$initial_page      = isset( $query_comments['tcm_page'] ) ? $query_comments['tcm_page'] : 0;
		$total_comments    = get_comment_count( $query_comments['post_id'] );
		$featured_comments = $this->get_featured_comments( $query_comments );

		// If we have the case where the user goes directly to the link.
		if ( isset( $query_comments['go_to_id'] ) ) {
			$top_parent = $this->get_comment_top_parent( $query_comments['go_to_id'] );

			if ( ! empty( $top_parent ) && $query_comments['post_id'] === $top_parent->comment_post_ID ) {
				$parent_found = false;
				// Take a page full of comments until we find the top most parent of the comment.
				do {
					$comments                   = get_comments( $query_comments );
					$filtered_comments          = $this->filter_comments( $comments );
					$parent_found               = $parent_found || $this->search_filter_comments( $filtered_comments, $top_parent );
					$comments_array             = array_merge( $comments_array, $this->comments_parents_with_children( $filtered_comments, $query_comments ) );
					$query_comments['offset']   = $query_comments['number'] * ( $query_comments['tcm_page'] ); // Take the next page itemsPerPage * ( $page + 1 - 1).
					$query_comments['tcm_page'] = $query_comments['tcm_page'] + 1;
					$next_page                  = $query_comments['tcm_page'];
				} while ( ( ! $parent_found || count( $comments_array ) < $query_comments['number'] ) && ! empty( $comments ) );
			}
		}

		if ( empty( $parent_found ) ) {

			unset( $query_comments['go_to_id'] );
			$query_comments['offset'] = $initial_offset;


			do {
				// Take another page of comments if the user is unable to see the first page or if the page is incomplete.
				$comments                   = get_comments( $query_comments );
				$filtered_comments          = $this->filter_comments( $comments );
				$comments_count             = count( $comments );
				$comments_array             = array_merge( $comments_array, $this->comments_parents_with_children( $filtered_comments, $query_comments ) );
				$query_comments['offset']   = $query_comments['number'] * ( $query_comments['tcm_page'] ); // Take the  next page itemsPerPage * ( $page + 1 - 1).
				$query_comments['tcm_page'] = $query_comments['tcm_page'] + 1;
				$next_page                  = $query_comments['tcm_page'];

				$total_comments['all'] = $total_comments['all'] - $comments_count;

			} while (
				! empty( $comments )
				&&  //load next page only if there are comments
				$total_comments['all'] > 0
				&& //same as above
				( empty( $comments_array )
				  ||  //if everything got filtered out, load the next page
				  count( $comments_array ) < $query_comments['number'] ) //if something got filtered out, load the next page and add those comments to what we have
			);

		}

		if ( ! empty( $featured_comments ) && intval( $initial_page ) <= 1 ) {
			//if there are featured comments and we are on the first page, we have to add them in the page
			$comments_array = array_merge( $featured_comments, $comments_array );
		}

		$result = array(
			'comments' => $comments_array,
			'nextPage' => isset( $next_page ) ? $next_page : null,
		);

		return $result;
	}

	/**
	 * Get featured comments for a specific post
	 *
	 * @param $query_comments query comments for getting comments from the db
	 *
	 * @return array|int
	 */
	public function get_featured_comments( $query_comments ) {

		$args['meta_query']['relation'] = 'AND';
		$args['meta_query'][]           = array(
			'key'     => Thrive_Comments_Constants::TCM_FEATURED,
			'value'   => 1,
			'compare' => 'IN',
		);
		$args['post_id']                = $query_comments['post_id'];
		$args['parent']                 = 0;


		/**
		 * Filter for adding extra params for getting featured comments
		 *
		 * @param array $args           The default arguments
		 * @param array $query_comments query comments for getting comments from the db
		 */
		$args = apply_filters( 'tcm_get_featured_comments', $args, $query_comments );

		$featured_comments = get_comments( $args );
		if ( ! empty( $featured_comments ) ) {
			$featured_comments = $this->comments_parents_with_children( $featured_comments, $query_comments );
		}

		return $featured_comments;
	}

	/**
	 * Get comments with their children also
	 *
	 * @param array $comments All comments that do not have any parents.
	 *
	 * @return array
	 */
	public function comments_parents_with_children( $comments, $query_comments ) {
		$result = array();

		$children_query = array(
			'post_id'        => $query_comments['post_id'],
			'parent__not_in' => array( 0 ),
		);

		$all_children = get_comments( $children_query );

		foreach ( $comments as $comment ) {
			$comment_array = $this->parse_comment( $comment, 0 );
			$children      = $this->tcm_get_children( $comment, $all_children );
			if ( ! empty( $children ) ) {
				$comment_array['children'] = $this->return_children( $comment, 1, $all_children );
			} else {
				$comment_array['children'] = array();
			}
			$result[] = $comment_array;
		}

		return $result;
	}

	/**
	 * Get from comment only the fields that we need
	 *
	 * @param WP_Comment $comment the comment to be transformed in array.
	 * @param int        $level   the level where the comment finds itself in the children tree.
	 *
	 * @return array
	 */
	public function parse_comment( $comment, $level ) {
		$comment_aux = $comment->to_array();
		$this->populate_default_picture_url( $comment_aux );

		$comment_aux['comment_ID']                              = (int) $comment_aux['comment_ID'];
		$comment_aux['level']                                   = min( $level, Thrive_Comments_Constants::TCM_MAX_NESTING_LEVEL );
		$comment_aux['email_hash']                              = md5( strtolower( trim( $comment->comment_author_email ) ) );
		$comment_aux['formatted_date']                          = $this->format_comment_date( $comment_aux['comment_ID'] );
		$comment_aux['comment_content']                         = $this->filter_comment( $comment->comment_content, $comment );
		$comment_aux['conversion_settings']                     = tcms()->tcm_get_setting_by_name( 'tcm_conversion' );
		$comment_aux['replace_keyword']                         = tcmh()->tcm_replace_keywords( $comment->user_id );
		$comment_aux['upvote']                                  = $this->get_votes( $comment_aux['comment_ID'], 'upvote' );
		$comment_aux['downvote']                                = $this->get_votes( $comment_aux['comment_ID'], 'downvote' );
		$comment_aux['comment_karma']                           = $this->get_karma( $comment_aux['comment_ID'] );
		$comment_aux['user_achieved_badges']                    = $this->get_badges( $comment_aux['comment_ID'] );
		$comment_aux[ Thrive_Comments_Constants::TCM_FEATURED ] = get_comment_meta( $comment_aux['comment_ID'], Thrive_Comments_Constants::TCM_FEATURED, true );
		$comment_aux['show_badge']                              = $this->comment_show_badge( $comment_aux['user_id'] );
		$comment_aux['display_name']                            = $this->get_comment_display_name( $comment_aux['comment_ID'], $comment->user_id );

		foreach ( self::$_extra_fields as $extra_field ) {
			unset( $comment_aux[ $extra_field ] );
		}

		return $comment_aux;
	}

	/**
	 * Get picture url for comment authors
	 *
	 * @return mixed|string
	 */
	public function get_picture_url() {
		$default_picture = tcms()->tcm_get_setting_by_name( Thrive_Comments_Constants::TCM_DEFAULT_PICTURE_OPTION );

		return ( empty( $default_picture ) ) ? tcm()->plugin_url( 'assets/images/' . Thrive_Comments_Constants::TCM_DEFAULT_PICTURE ) : $default_picture;
	}

	/**
	 * Handles the default comment author picture. If needed, this will be replaced on frontend with a lazyloaded gravatar
	 *
	 * @param WP_Comment|array $comment
	 * @param array            $args arguments for the `get_avatar_url` filter
	 *
	 * @return string
	 */
	public function populate_default_picture_url( &$comment, $args = array() ) {
		$arr_comment = (array) $comment;
		$fields      = array(
			'photo_src'     => '',
			'social_avatar' => '',
		);
		$avatar      = get_comment_meta( $arr_comment['comment_ID'], 'comment_author_picture', true );
		if ( $avatar ) {
			$fields['photo_src']     = $avatar;
			$fields['social_avatar'] = 'true';
		} else {
			$fields['photo_src'] = $this->get_picture_url();
		}
		$defaults = array(
			'size'           => Thrive_Comments_Constants::AVATAR_SIZE,
			'height'         => null,
			'width'          => null,
			'default'        => $fields['photo_src'],
			'force_default'  => false,
			'rating'         => get_option( 'avatar_rating' ),
			'scheme'         => null,
			'processed_args' => null, // If used, should be a reference.
			'extra_attr'     => '',
		);

		$fields['photo_src'] = apply_filters( 'get_avatar_url', $fields['photo_src'], $arr_comment['comment_author_email'], wp_parse_args( $args, $defaults ) );

		foreach ( $fields as $key => $value ) {
			if ( is_array( $comment ) ) {
				$comment[ $key ] = $value;
			} else {
				$comment->{$key} = $value;
			}
		}
	}

	/**
	 * Format the date of a specific comment
	 *
	 * @param int $comment_id comment id.
	 *
	 * @return int|string
	 */
	public function format_comment_date( $comment_id ) {

		$comment_format = tcms()->tcm_get_setting_by_name( 'comment_date' );

		switch ( $comment_format ) {
			case self::TCM_COMMENT_HIDE_DATE:
				$comment_date = 0;
				break;
			case self::TCM_COMMENT_ABSOLUTE_DATE:
				$comment_date = get_comment_date( '', $comment_id );
				break;
			case self::TCM_COMMENT_RELATIVE_DATE:
				$comment_date = $this->get_absolute_date( $comment_id );
				break;
			default:
				$comment_date = get_comment_date( 'd-m-Y', $comment_id );
				break;
		}

		return $comment_date;
	}

	/**
	 * Get an absolute date for a comment
	 *
	 * @param int $comment_id comment id.
	 *
	 * @return string
	 */
	public function get_absolute_date( $comment_id ) {

		$comment_date  = strtotime( get_comment_date( 'd-m-Y H:i:s', $comment_id ) );
		$now           = strtotime( current_time( 'mysql' ) );
		$absolute_date = human_time_diff( $comment_date, $now ) . ' ' . __( 'ago', Thrive_Comments_Constants::T );

		return $absolute_date;
	}

	/**
	 * Filters the content that will be on frontend.
	 *
	 * @param string     $content content.
	 * @param WP_Comment $comment wp comment.
	 *
	 * @return string $content
	 */
	public function filter_comment( $content, $comment ) {

		// if the content contains HTML
		if ( $content != strip_tags( $content ) ) {
			if ( preg_match( '@(</?script.)|(</?style.)@', $content ) ) {
				$content = '<pre>' . strip_tags( $content ) . '</pre>';
			}
		}

		$content = apply_filters( 'comment_text', $content, $comment, array() );

		return $content;
	}

	/**
	 * Replace keywords in frontend
	 *
	 * @param int $user_id User Id.
	 *
	 * @return bool
	 */
	public function tcm_replace_keywords( $user_id ) {
		if ( '0' === $user_id ) {
			return false;
		}

		$user_info = get_userdata( $user_id );
		if ( ! $user_info || empty( $user_info->roles ) ) {
			return false;
		}
		$role = $user_info->roles[0];

		if ( '1' === get_option( 'tcm_mod_' . $role ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return number of downvotes or upvotes for comment with id $comment_id
	 *
	 * @param $comment_id
	 * @param $vote_type
	 *
	 * @return int|mixed
	 */
	public function get_votes( $comment_id, $vote_type ) {
		$comment_votes = get_comment_meta( $comment_id, $vote_type, true );
		if ( ! empty( $comment_votes ) ) {
			return intval( $comment_votes );
		}

		return 0;
	}

	/** Return the karma based on comments upvotes for sorting comments based on popular ones
	 *
	 * @param $comment_id
	 *
	 * @return int|mixed
	 */
	public function get_karma( $comment_id ) {
		$vote_type = get_option( 'tcm_vote_type', true );

		return ( $vote_type !== 'no_vote' ) ? $this->get_votes( $comment_id, 'upvote' ) : 0;
	}

	/**
	 * Return a list of badges that user have received
	 *
	 * @param $comment
	 *
	 * @return array|bool
	 */
	public function get_badges( $comment_id ) {
		$achieved_badges       = array();
		$badges                = tcms()->tcm_get_setting_by_name( 'tcm_badges' );
		$author_email          = get_comment_author_email( $comment_id );
		$user_tcm_achievements = tcmdb()->get_log( array( 'achievement' ), array( 'email' => $author_email ) );
		if ( empty( $badges ) ) {
			return false;
		}

		if ( empty( $user_tcm_achievements ) ) {

			$all_approved_comments = get_comments(
				array(
					'status'       => 'approve',
					'author_email' => $author_email,
					'count'        => true,
				)
			);

			$best_approved_comments = get_comments(
				array(
					'parent'       => 0,
					'status'       => 'approve',
					'author_email' => $author_email,
					'count'        => true,
				)
			);

			$best_featured_comments = get_comments(
				array(
					'status'       => 'approve',
					'author_email' => $author_email,
					'count'        => true,
					'meta_query'   => array(
						array(
							'key'   => Thrive_Comments_Constants::TCM_FEATURED,
							'value' => 1,
						),
					),
				)
			);

			$best_approved_replies = $all_approved_comments - $best_approved_comments;
			$best_upvotes          = 0;

			$user_tcm_achievements['upvotes_received']  = 0;
			$user_tcm_achievements['approved_comments'] = $best_approved_comments;
			$user_tcm_achievements['approved_replies']  = $best_approved_replies;
			$user_tcm_achievements['featured_comments'] = $best_featured_comments;

			tcmdb()->insert_log( array( 'email' => $author_email, 'achievement' => json_encode( $user_tcm_achievements ) ) );

		} else {
			$best_upvotes           = empty( $user_tcm_achievements['upvotes_received'] ) ? 0 : $user_tcm_achievements['upvotes_received'];
			$best_approved_comments = empty( $user_tcm_achievements['approved_comments'] ) ? 0 : $user_tcm_achievements['approved_comments'];
			$best_approved_replies  = empty( $user_tcm_achievements['approved_replies'] ) ? 0 : $user_tcm_achievements['approved_replies'];
			$best_featured_comments = empty( $user_tcm_achievements['featured_comments'] ) ? 0 : $user_tcm_achievements['featured_comments'];
		}
		$history_upvotes_received  = 0;
		$history_approved_comments = 0;
		$history_approved_replies  = 0;
		$history_featured_comments = 0;

		foreach ( $badges as $badge ) {
			$change = false;
			switch ( $badge['awarded'] ) {
				case 'upvotesreceived':
					if ( $best_upvotes >= intval( $badge['reaches'] ) && $history_upvotes_received < intval( $badge['reaches'] ) ) {
						$history_upvotes_received = intval( $badge['reaches'] );
						$change                   = true;
					}
					break;
				case 'approvedcomments':
					if ( $best_approved_comments >= intval( $badge['reaches'] ) && $history_approved_comments < intval( $badge['reaches'] ) ) {
						$history_approved_comments = intval( $badge['reaches'] );
						$change                    = true;
					}
					break;
				case 'approvedreplies':
					if ( $best_approved_replies >= intval( $badge['reaches'] ) && $history_approved_replies < intval( $badge['reaches'] ) ) {
						$history_approved_replies = intval( $badge['reaches'] );
						$change                   = true;
					}
					break;
				case 'featuredcomments':
					if ( $best_featured_comments >= intval( $badge['reaches'] ) && $history_featured_comments < intval( $badge['reaches'] ) ) {
						$history_featured_comments = intval( $badge['reaches'] );
						$change                    = true;
					}
					break;
			}
			if ( true == $change ) {
				$achieved_badges[ $badge['awarded'] ] = array( 'name' => $badge['name'], 'image' => $badge['image'], 'image_url' => $badge['image_url'] );
			}
		}

		return $achieved_badges;
	}

	/**
	 * Returns if we need to show badges for this specific comment
	 *
	 * @param array $comment
	 */
	public function comment_show_badge( $user_id ) {

		//check if we have the value in cache
		$moderators_badges = wp_cache_get( 'thrive-comments-moderatos-badges' );
		if ( $moderators_badges && isset( $moderators_badges[ $user_id ] ) ) {
			return $moderators_badges[ $user_id ];
		}

		if ( empty( $moderators_badges ) ) {
			$moderators_badges = [];
		}

		$badges_to_moderators = tcms()->tcm_get_setting_by_name( 'badges_to_moderators' );

		//if the setting is checked or the user is not registered than show the badges
		if ( $badges_to_moderators || $user_id === '0' ) {
			$moderators_badges[ $user_id ] = true;
			wp_cache_set( 'thrive-comments-moderatos-badges', $moderators_badges );

			return true;
		}

		$search_roles = tcamh()->get_search_roles();

		$user = get_user_by( 'ID', $user_id );

		//if the user exists and he's a moderator than do not show the badges on the comment
		if ( $user && array_intersect( $search_roles, $user->roles ) ) {
			$moderators_badges[ $user_id ] = false;
			wp_cache_set( 'thrive-comments-moderatos-badges', $moderators_badges );

			return false;
		}

		//add the value to cache
		$moderators_badges[ $user_id ] = true;
		wp_cache_set( 'thrive-comments-moderatos-badges', $moderators_badges );

		return true;
	}

	public function tcm_get_children( $comment, $all_children ) {
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
	public function return_children( $comment, $level, $all_children ) {

		$children       = $this->tcm_get_children( $comment, $all_children );
		$children_array = array();

		foreach ( $children as $child ) {
			if ( ! $this->check_comment( $child ) ) {
				continue;
			}
			$other_children = $this->tcm_get_children( $child, $all_children );
			$child_array    = $this->parse_comment( $child, $level );
			if ( $other_children ) {
				$child_array['children'] = $this->return_children( $child, $level + 1, $all_children );
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

		if ( 'default_email' === $user_email && isset( $_COOKIE['social-login'] ) ) {
			$user_email = json_decode( stripslashes( $_COOKIE['social-login'] ) )->email;
		}

		$comment_cookie = isset( $_COOKIE[ 'tcm_cookie_' . $comment->comment_ID ] ) ? $_COOKIE[ 'tcm_cookie_' . $comment->comment_ID ] : '';
		$cookie_data    = ( ! empty( $comment_cookie ) )
			? json_decode( stripslashes( $comment_cookie ), true )
			: array_fill_keys( array(
				'comment_ID',
			), 'default_cookie' );

		if ( 1 === intval( $comment->comment_approved ) || $comment->comment_ID === $cookie_data['comment_ID'] || $comment->comment_author_email === $user_email ) {
			return true;
		}

		return false;
	}

	/**
	 * Get top parent of comment
	 *
	 * @param integer $comment_id id of the comment of which parent we are searching for.
	 *
	 * @return array|null|bool|WP_Comment
	 */
	public function get_comment_top_parent( $comment_id ) {
		$comment = get_comment( $comment_id );
		$level   = 0;

		// See if the comment can be shown to the user.
		if ( ! $this->check_comment( $comment ) ) {
			return false;
		}

		while ( isset( $comment ) && 0 !== intval( $comment->comment_parent ) ) {
			$level ++;
			$comment = get_comment( $comment->comment_parent );
		}

		$comment->level = min( $level, Thrive_Comments_Constants::TCM_MAX_NESTING_LEVEL );

		return $comment;
	}

	/**
	 * Returns only the comments that are allowed to be viewed by the user
	 *
	 * @param array $comments comments to be filtered.
	 *
	 * @return mixed
	 */
	public function filter_comments( $comments ) {

		foreach ( $comments as $key => $comment ) {

			if ( ! $this->check_comment( $comment ) ) {
				unset( $comments[ $key ] );
			}
		}

		return $comments;
	}

	/**
	 * Search comment in filtered comments
	 *
	 * @param array $comments filtered comments.
	 * @param array $comment  the comments that we are searching for.
	 *
	 * @return bool
	 */
	public function search_filter_comments( $comments, $comment ) {

		foreach ( $comments as $value ) {
			if ( $value->comment_ID === $comment->comment_ID ) {
				return true;
			}
		}

		return false;
	}

	/** Return the number of total upvotes that a comment with $comment_id received
	 *
	 * @param $comment_id
	 *
	 * @return int|mixed
	 */
	public function get_upvotes( $comment_id ) {
		$comment_upvote = get_comment_meta( $comment_id, 'upvote', true );
		if ( ! empty( $comment_upvote ) ) {
			return $comment_upvote;
		}

		return 0;
	}

	/** Return the number of total downvotes that a comment with $comment_id received
	 *
	 * @param $comment_id
	 *
	 * @return int|mixed
	 */
	public function get_downvotes( $comment_id ) {
		$comment_downvote = get_comment_meta( $comment_id, 'downvote', true );
		if ( ! empty( $comment_downvote ) ) {
			return $comment_downvote;
		}

		return 0;
	}

	/**
	 * Search for comment in all comments
	 *
	 * @param array   $comments A collection of comments.
	 * @param integer $id       Id of the comment that are we searching for.
	 *
	 * @return bool
	 */
	public function search_comment( $comments, $id ) {
		$found = false;

		foreach ( $comments as $comment ) {
			if ( $found ) {
				break;
			}

			if ( $comment['comment_ID'] === $id ) {
				$found = $comment;
			} else {
				if ( count( $comment['children'] ) ) {
					$found = $this->search_comment( $comment['children'], $id );
				}
			}
		}

		return $found;
	}

	/**
	 * Get the number of comments for a specific post
	 *
	 * @param integer $post_id Id of the post for which we take the number of comments.
	 *
	 * @return array|int
	 */
	public function get_comment_count( $post_id ) {
		return get_comments( array( 'parent' => 0, 'count' => true, 'post_id' => $post_id ) );
	}

	/**
	 * Return current logged user and the photo for the avatar
	 *
	 * @return array
	 */
	public function tcm_get_current_user() {
		$current_user = wp_get_current_user();
		if ( $current_user ) {
			$result = $current_user->to_array();
			//check if a user is moderator only of there is a logged user on the website
			if ( $current_user->ID !== 0 ) {
				$result['is_moderator'] = $this->is_user_moderator( $current_user->ID );
			}
		}
		$user_email          = isset( $result['user_email'] ) ? $result['user_email'] : '';
		$result['photo_url'] = tcmh()->tcm_get_avatar_url( $user_email );
		$result['is_admin']  = TCM_Product::has_access();

		//do not send sensitive data in frontend
		unset( $result['user_pass'], $result['user_activation_key'], $result['user_status'] );

		return $result;
	}

	/**
	 * Get photo avatar url based on the email
	 *
	 * @param string $email for the avatar.
	 * @param array  $args  extra arguments.
	 *
	 * @return string
	 */
	public function tcm_get_avatar_url( $email, $args = array() ) {
		$default_picture = tcms()->tcm_get_setting_by_name( Thrive_Comments_Constants::TCM_DEFAULT_PICTURE_OPTION );

		$args['size']        = Thrive_Comments_Constants::AVATAR_SIZE;
		$args['default_pic'] = $default_picture;
		if ( isset( $args['comment_id'] ) ) {
			$args['author_avatar'] = get_comment_meta( $args['comment_id'], 'comment_author_picture', true );
		}

		if ( $this->tcm_validate_gravatar( $email ) ) {
			$picture = get_avatar_url( $email, $args );
		} else if ( isset( $args['author_avatar'] ) && '' !== $args['author_avatar'] ) {
			$picture = $args['author_avatar'];
		} else {
			$picture = ( empty( $default_picture ) ) ? tcm()->plugin_url( 'assets/images/' . Thrive_Comments_Constants::TCM_DEFAULT_PICTURE ) : $default_picture;
		}

		return apply_filters( 'get_avatar_url', $picture, $email, $args );
	}

	/**
	 * Check if an email has gravatar and return if true
	 *
	 * @param string $email user email.
	 *
	 * @return bool|string
	 */
	public function tcm_validate_gravatar( $email ) {
		// Craft a potential url and test its headers.
		$protocol    = is_ssl() ? 'https' : 'http';
		$hash        = md5( strtolower( trim( $email ) ) );
		$uri         = $protocol . '://www.gravatar.com/avatar/' . $hash . '?s=512&d=404';
		$response    = tve_dash_api_remote_get( $uri );
		$header_type = wp_remote_retrieve_header( $response, 'content-type' );
		if ( ! $header_type || strpos( $header_type, 'image' ) === false ) {
			$valid_avatar = false;
		} else {
			$valid_avatar = $uri;
		}

		return $valid_avatar;
	}

	/**
	 * Check if a user is a moderator
	 *
	 * @param int|string $user_id
	 *
	 * @return bool
	 */
	public function is_user_moderator( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_user_by( 'ID', (int) $user_id );
		if ( empty( $user ) ) {
			return false;
		}

		/* user has at least one of the moderator roles */

		return (bool) array_intersect( tcamh()->get_search_roles(), $user->roles );
	}

	/**
	 * Check if there are any comments created in the last 5 seconds and if there are, it adds them to the list if there aren't already there.
	 *
	 * @param WP_REST_Request $request request data from frontend.
	 *
	 * @return mixed
	 */
	public function live_update( $request ) {

		$post_id         = $request->get_param( 'post_id' );
		$update_interval = $request->get_param( 'update_interval' );

		// Calculate the date when the last ajax was fired.
		$sub_date   = (int) ( $update_interval / 1000 );
		$after_time = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) - $sub_date );

		// Take the comments after the last ajax was fired until now.
		$args = array(
			'post_id'    => $post_id,
			'date_query' => array(
				array(
					'after' => $after_time,
				),
			),
		);

		$comments_query = new WP_Comment_Query;
		$comments       = $comments_query->query( $args );


		$found = ( count( $comments ) ) ? 1 : 0;


		if ( $found ) {
			foreach ( $comments as $key => $comment ) {
				$top_parent = $this->get_comment_top_parent( $comment->comment_ID );
				if ( ! empty( $top_parent ) ) {
					$response['comments'][ $key ]                  = $this->parse_comment( $comment, $top_parent->level );
					$response['comments'][ $key ]['top_parent_id'] = $top_parent->comment_ID;
					$response['comments'][ $key ]['children']      = array();
				}
			}
		}

		$response['comment_found'] = $found;

		return $response;
	}

	/**
	 * Send notification to subscribed users
	 *
	 * @param WP_Comment $comment comment.
	 */
	public function tcm_send_notification( $comment ) {

		// Send post notification template.
		$post_subscribers = tcmh()->tcm_get_post_subscribers( $comment->comment_post_ID );

		$post_subscribers = apply_filters( 'tcm_post_subscribers', $post_subscribers, $comment );

		if ( $post_subscribers ) {
			foreach ( $post_subscribers as $post_subscriber ) {
				$this->tcm_send_mail( $comment, $post_subscriber, 'post' );
			}
		}

		// Send mail to parent comment.
		$parent_id = $comment->comment_parent;
		if ( $parent_id && intval( get_comment_meta( $parent_id, 'author_subscribed', true ) ) ) {
			$email = get_comment_author_email( $parent_id );
			$this->tcm_send_mail( $comment, $email, 'comment' );
		}
	}

	/**
	 * Retrieve a list of subscribers for current post
	 *
	 * @param int $post_id post ID.
	 *
	 * @return mixed
	 */
	public function tcm_get_post_subscribers( $post_id ) {
		return get_post_meta( $post_id, 'tcm_post_subscribers', true );
	}

	/**
	 * @param WP_Comment $comment         comment.
	 * @param string     $email           email.
	 * @param string     $subscriber_type $subscriber_type subscriber type ( post / comment ).
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or error object on failure.
	 */
	public function tcm_send_mail( $comment, $email, $subscriber_type = '' ) {
		$data          = array();
		$data['email'] = $email;
		$data          = $this->replace_placeholders( $subscriber_type, $data, $comment );

		$active_connection = get_option( 'tcm_email_service', true );
		$api               = Thrive_Dash_List_Manager::connection_instance( $active_connection );

		if ( $api && $api->get_credentials() ) {
			$result = $api->sendCustomEmail( $data );

			if ( true === $result ) {
				return new WP_REST_Response( json_encode( $result ), 200 );
			} else {
				return new WP_Error( 'cant-update', __( 'Sending email failed', Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
			}
		}
	}

	/**
	 * @param string     $subscriber_type $subscriber_type subscriber type ( post / comment ).
	 * @param array      $data            data.
	 * @param WP_Comment $comment         comment.
	 *
	 * @return mixed
	 */
	public function replace_placeholders( $subscriber_type, $data, $comment ) {
		$labels           = tcms()->tcm_get_setting_by_name( 'tcm_notification_labels' );
		$commenter_avatar = tcmh()->tcm_get_avatar_url( $comment->comment_author_email );
		$mailer_avatar    = tcmh()->tcm_get_avatar_url( $data['email'] );
		$parent_comment   = get_comment( $comment->comment_parent );
		$user_hash        = tcmdb()->get_email_hash( 'email_hash', array( 'email' => $data['email'] ) );
		$unsub_link       = tcm()->tcm_get_route_url( 'comments' ) . '/unsubscribe/';
		$site_title       = get_option( 'blogname', true );

		$color_picker_value = get_option( 'tcm_color_picker_value' );
		empty( $color_picker_value ) ? $color_picker_value = '#03a9f4' : true;

		$post_url = get_permalink( $comment->comment_post_ID );
		$post_url = apply_filters( 'tcm_comment_notification_email', $post_url, $comment );

		/**
		 * Filter - allows overriding the post_title displayed in email content
		 * @param string $post_title
		 * @param WP_Comment $comment
		 *
		 * @return string
		 */
		$post_title = apply_filters( 'tcm_comment_mail_post_title', get_the_title( $comment->comment_post_ID ), $comment );

		$extension = substr( $commenter_avatar, strrpos( $commenter_avatar, '.' ) + 1 );
		if ( 'svg' === $extension ) {
			$commenter_avatar = str_replace( $extension, 'png', $commenter_avatar );
		}

		$extension2 = substr( $mailer_avatar, strrpos( $mailer_avatar, '.' ) + 1 );
		if ( 'svg' === $extension2 ) {
			$mailer_avatar = str_replace( $extension2, 'png', $mailer_avatar );
		}

		if ( 'post' === $subscriber_type ) {
			$data['subject'] = $labels['post_email_subject']['text'];
			$unsub_link      .= $comment->comment_post_ID . '/' . $user_hash;

			ob_start();
			include tcm()->plugin_path( 'includes/frontend/views/notifications/post-email-notification.php' );
			$data['html_content'] = ob_get_contents();
			ob_end_clean();

			$data['html_content'] = str_replace( '{unsubscribe_link}', '<a href="' . $unsub_link . '">' . $labels['post_unsubscribe_text']['text'] . '</a>', $data['html_content'] );

		} else {
			$comment_excerpt = implode( ' ', array_slice( explode( ' ', $comment->comment_content ), 0, 10 ) );
			$comment_excerpt = strip_tags( $comment_excerpt );
			$data['subject'] = $labels['email_subject']['text'];
			$data['subject'] = str_replace( '{comment_start}', $comment_excerpt, $data['subject'] );

			$unsub_link .= $parent_comment->comment_ID . '/' . $comment->comment_post_ID . '/' . $user_hash;

			ob_start();
			include tcm()->plugin_path( 'includes/frontend/views/notifications/comment-email-notification.php' );
			$data['html_content'] = ob_get_contents();
			ob_end_clean();

			$data['html_content'] = str_replace( '{comment_start}', $comment_excerpt, $data['html_content'] );
			$data['html_content'] = str_replace( '{comment_author}', $parent_comment->comment_author, $data['html_content'] );
			$data['subject']      = str_replace( '{comment_author}', $parent_comment->comment_author, $data['subject'] );
			$data['html_content'] = str_replace( '{unsubscribe_link}', '<a href="' . $unsub_link . '">' . $labels['unsubscribe_text']['text'] . '</a>', $data['html_content'] );
		}

		$data['html_content'] = str_replace( '{source_page}', '<a href="' . $post_url . '">' . $post_title . '</a>', $data['html_content'] );
		$data['html_content'] = str_replace( '{site_title}', '<a href="' . get_home_url() . '">' . $site_title . '</a>', $data['html_content'] );
		$data['html_content'] = str_replace( '{source_commenter_name}', $comment->comment_author, $data['html_content'] );
		$data['subject']      = str_replace( '{source_commenter_name}', $comment->comment_author, $data['subject'] );
		$data['subject']      = str_replace( '{source_page}', $post_title, $data['subject'] );

		/* Handle the case where the subject contains hyphens and quotes */
		$data['subject'] = stripslashes( html_entity_decode( $data['subject'], ENT_QUOTES, 'UTF-8' ) );

		return $data;
	}

	/* includes a file containing svg declarations */

	/**
	 * If a moderator upvotes a comment, we are setting the needs reply status to 0 for that comment
	 *
	 * @param string $vote_type
	 * @param string $author_email
	 * @param int    $comment_id
	 */
	public function set_needs_reply_after_vote( $vote_type, $comment_id ) {
		$tcm_mark_upvoted = tcms()->tcm_get_setting_by_name( 'tcm_mark_upvoted' );

		if ( $tcm_mark_upvoted && $vote_type === 'upvote' ) {

			$moderators_ids = tcamh()->tcm_get_moderator_ids();
			$user_id        = get_current_user_id();

			//we update the comment meta only if the user exists and he's a moderator
			if ( $user_id && in_array( $user_id, $moderators_ids ) ) {
				tcamh()->update_comment_meta( Thrive_Comments_Constants::TCM_NEEDS_REPLY, 2, $comment_id );
			}
		}
	}

	/**
	 * @param        $icon   : the icon name (without the 'tcm-', that gets added here)
	 * @param string $class  : for adding extra classes
	 * @param bool   $return : if you want the function to return instead of printing
	 *
	 * @return : nothing if there is no $return param, a string if there is a $return param.
	 */
	function tcm_icon( $icon, $class = '', $return = false ) {
		$html = '<svg class="' . $class . '"><use xlink:href="#tcm-' . $icon . '"></use></svg>';

		if ( false !== $return ) {
			return $html;
		}
		echo $html;
	}

	function include_svg_file( $file ) {
		include tcm()->plugin_path( 'assets/images/' . $file );
	}

	public function update_badges_log( $comment ) {
		$author_email          = $comment->comment_author_email;
		$user_tcm_achievements = tcmdb()->get_log( array( 'achievement' ), array( 'email' => $author_email ) );
		$is_insert             = false;

		if ( null === $user_tcm_achievements ) {
			$user_tcm_achievements = Thrive_Comments_Constants::$_default_achievements;
			$is_insert             = true;
		}

		//check if comment is a reply or a stand alone comment
		$badge_type = ( '0' == $comment->comment_parent ) ? 'approved_comments' : 'approved_replies';

		$user_tcm_achievements[ $badge_type ] += 1;

		if ( $is_insert ) {
			tcmdb()->insert_log( array( 'email' => $author_email, 'achievement' => json_encode( $user_tcm_achievements ) ) );
		} else {
			tcmdb()->update_log(
				array(
					'achievement' => json_encode( $user_tcm_achievements ),
				),
				array(
					'email' => $author_email,
				)
			);
		}

	}

	/**
	 * Get the begin and end day for the given date interval.
	 *
	 * @param $date_interval
	 * @param $date_after
	 * @param $date_before
	 *
	 * @return array $begin and $end dates.
	 */
	public function get_interval_days( $date_interval, $date_after, $date_before ) {
		// $end = $today by default, special cases ( Last month/year )
		$end = new DateTime( date( 'Y-m-d' ) );

		switch ( $date_interval ) {
			case 'this_month':
				$begin = new DateTime( date( 'Y-m-01' ) );
				break;
			case 'this_year':
				$begin = new DateTime( date( 'Y-01-01' ) );
				break;
			case 'last_month':
				$begin = new DateTime( date( 'Y-m-01' ) );
				$end   = clone $begin;
				$begin->modify( '-1 month' );
				break;
			case 'last_year':
				$begin = new DateTime( date( 'Y-01-01' ) );
				$end   = clone $begin;
				$begin->modify( '-1 year' );
				break;
			case 'custom_range':
				$begin = DateTime::createFromFormat( 'Y-m-d', $date_after );
				$end   = DateTime::createFromFormat( 'Y-m-d', $date_before );
				break;
			default:
				$begin = new DateTime( date( 'Y-m-d' ) );
				$begin->modify( '-' . $date_interval );
		}

		return array( $begin, $end );
	}

	/**
	 * Special rules for showing the name beside the comment
	 *
	 * @param $comment_id
	 * @param $user_id
	 *
	 * @return string
	 */
	public function get_comment_display_name( $comment_id, $user_id ) {
		$comment_author = get_user_by( 'ID', $user_id );

		if ( $comment_author ) {
			$fname        = get_the_author_meta( 'first_name', $comment_author->ID );
			$lname        = get_the_author_meta( 'last_name', $comment_author->ID );
			$author_name  = get_the_author_meta( 'display_name', $comment_author->ID );
			$display_name = empty( $author_name ) ? $fname . " " . $lname : $author_name;
		}

		if ( ! isset( $display_name ) || $display_name == "" ) {
			$display_name = get_comment_author( $comment_id );
		}

		$url = get_comment_author_url( $comment_id );
		if ( ! empty( $url ) ) {
			$display_name = $this->get_comment_author_link( $url, $display_name, $comment_id );
		}

		return $display_name;
	}

	/**
	 * Include url for the author if the comment was submitted with url
	 *
	 * @param $url
	 * @param $author
	 * @param $comment_id
	 *
	 * @return mixed
	 */
	public function get_comment_author_link( $url, $author, $comment_id ) {

		if ( 'http://' === $url ) {
			$return = $author;
		} else {
			$return = "<a href='$url' rel='external nofollow'>$author</a>";
		}

		return apply_filters( 'get_comment_author_link', $return, $author, $comment_id );
	}

	/**
	 * Given a url path this function returns if the url exists on the server
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public function picture_exists( $path ) {
		$response = wp_remote_head( $path );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}

/**
 *  Main instance of Thrive Comments Helpers
 *
 * @return Thrive_Comments_Helper
 */
function tcmh() {
	return Thrive_Comments_Helper::instance();
}

tcmh();
