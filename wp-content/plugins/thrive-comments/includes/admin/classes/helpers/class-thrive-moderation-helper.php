<?php
/**
 * FileName  class-thrive-moderation-helper.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TCM_Admin_Helper
 */
class Thrive_Moderation_Helper {
	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Admin_Helper singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Thrive Admin Instance.
	 * Ensures only one instance of Thrive Comments Helper is loaded or can be loaded.
	 *
	 * @return Thrive_Admin_Helper
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get all posts / pages which have comments
	 *
	 * @return array
	 */
	public function tcm_get_posts_with_comments() {
		$posts = get_posts( array( 'posts_per_page' => - 1, 'post_type' => 'any' ) );

		$posts_with_comments = array();
		foreach ( $posts as $post ) {
			$posts_with_comments[] = array(
				'id'   => $post->ID,
				'text' => $post->post_title,
			);
		}

		return $posts_with_comments;
	}

	/**
	 * General update for a comment meta
	 *
	 * @param string                $meta_key
	 * @param string                $meta_value
	 * @param WP_Comment|string|int $comment_id Comment to retrieve.
	 *
	 * @return bool|WP_Error
	 */
	public function update_comment_meta( $meta_key, $meta_value, $comment_id ) {
		$comment_obj = get_comment( $comment_id );

		if ( empty( $comment_obj ) ) {
			return new WP_Error( 'rest_comment_invalid_id', __( 'Invalid comment ID.' ), array( 'status' => 404 ) );
		}

		//First check if we really need to update. Compare existing value with what we want to insert
		$old_value = get_comment_meta( $comment_obj->comment_ID, $meta_key );

		if ( count( $old_value ) == 1 && intval( $old_value[0] ) === intval( $meta_value ) ) {
			return true;
		}

		$ret = update_comment_meta( $comment_obj->comment_ID, $meta_key, $meta_value );

		if ( false === $ret ) {
			return new WP_Error(
				'rest_meta_database_error',
				__( 'Could not update meta value in database.', Thrive_Comments_Constants::T ),
				array( 'key' => $meta_key, 'status' => WP_Http::INTERNAL_SERVER_ERROR )
			);
		}


		return true;
	}

	/**
	 * Returns an array with all the users that can moderate comments
	 *
	 * @return array
	 */
	public function tcm_get_moderators( $get_avatars = true ) {

		$searchroles = $this->get_search_roles();
		$users       = array();

		$all_users = get_users( array( 'role__in' => $searchroles ) );
		foreach ( $all_users as $user ) {
			$userdata = array(
				'display_name' => $user->display_name,
				'roles'        => $user->roles,
				'ID'           => $user->ID,
			);
			if ( $get_avatars ) {
				$userdata['avatar'] = tcmh()->tcm_get_avatar_url( $user->user_email );
			}
			array_push( $users, $userdata );
		}

		return $users;
	}

	/**
	 * Get roles that are set in TC as administrators
	 *
	 * @return array
	 */
	public function get_search_roles() {
		$roles       = tcah()->get_all_roles();
		$searchroles = array();

		foreach ( $roles as $role => $check ) {
			if ( '1' === $check ) {
				array_push( $searchroles, substr( $role, 8 ) );
			}
		}

		return $searchroles;
	}

	/**
	 * Returns an array with the IDs of all moderators
	 *
	 * @return array $moderator_ids
	 */
	public function tcm_get_moderator_ids() {
		$moderators     = $this->tcm_get_moderators( false );
		$moderators_ids = array();
		foreach ( $moderators as $moderator ) {
			$moderators_ids[] = $moderator['ID'];
		}

		return $moderators_ids;
	}

	/**
	 * Get for each user the data that we need
	 *
	 * @return array
	 */
	public function tcm_get_all_users() {
		$users = get_users( array( 'role__not_in' => array( 'subscriber' ) ) );

		$result = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$result[ $user->ID ] = array( 'user_nicename' => $user->user_nicename );
			}
		}

		return $result;
	}

	/**
	 * Get the number of comment for authors
	 *
	 * @param $author_id
	 *
	 * @return int
	 */
	public function get_comments_author_count( $author_email ) {

		$args = array(
			'author_email'  => $author_email,
			'no_found_rows' => false,
			'number'        => 10,
			'status'        => 'all,spam,trash',
		);

		$query = new WP_Comment_Query;
		$query->query( $args );

		return (int) $query->found_comments;
	}

	/**
	 * Return user achievements of if they do not exist the default one
	 *
	 * @param int $user_id
	 *
	 * @return array|mixed
	 */
	public function get_user_achievements( $user_id ) {

		$user_achievements = get_user_meta( $user_id, 'tcm_achievements', true );

		return ( ! empty( $user_achievements ) ? $user_achievements : Thrive_Comments_Constants::$_default_achievements );
	}

	/**
	 * Get connected email services
	 *
	 * @return array
	 */
	public function get_email_services() {
		$email_services    = Thrive_Dash_List_Manager::get_available_apis( true, [ 'include_types' => [ 'email' ] ] );
		$active_connection = get_option( 'tcm_email_service', true );
		$items             = [];

		foreach ( $email_services as $key => $instance ) {
			$img_src = get_site_url() . '/wp-content/plugins/thrive-comments/thrive-dashboard/inc/auto-responder/views/images/' . $key . '_small.jpg';
			$title   = $instance->get_title();
			$key     = $instance->get_key();

			$item = array(
				'key'    => $key,
				'title'  => $title,
				'image'  => $img_src,
				'active' => ( $key === $active_connection ) ? 1 : 0,
			);

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Get all email apis keys.
	 *
	 * @return array
	 */
	public function get_email_apis() {
		$email_services = Thrive_Dash_List_Manager::get_available_apis( false, [ 'include_types' => [ 'email' ] ] );
		$items          = [];

		foreach ( $email_services as $key => $instance ) {
			$key     = $instance->get_key();
			$items[] = $key;
		}

		return $items;

	}
}

/**
 *  Main instance of Thrive Comments Helpers
 *
 * @return Thrive_Admin_Helper
 */
function tcamh() {
	return Thrive_Moderation_Helper::instance();
}

tcamh();
