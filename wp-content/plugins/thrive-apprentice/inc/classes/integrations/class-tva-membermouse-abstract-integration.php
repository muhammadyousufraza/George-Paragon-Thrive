<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 16-Apr-19
 * Time: 10:59 AM
 */

/**
 * Class TVA_Membermouse_Abstract_Integration
 * - contains similar functions for both TVA_Membermouse_Bundle_Integration and TVA_Membermouse_Integration
 *
 * @see TVA_Membermouse_Bundle_Integration
 * @see TVA_Membermouse_Integration
 */
abstract class TVA_Membermouse_Abstract_Integration extends TVA_Integration {

	/**
	 * Save all items ids to membermouse table
	 *
	 * @param int   $course_id
	 * @param array $rule
	 *
	 * @inheritDoc
	 */
	public function before_saving_rule( $course_id, $rule ) {

		$course_id = (int) $course_id;
		if ( ! $course_id || false === is_array( $rule ) ) {
			return;
		}

		$posts_ids = TVA_Manager::get_course_item_ids( $course_id );

		if ( empty( $rule['items'] ) ) {
			$this->_delete_protected_posts( $posts_ids );
		} else {
			$excluded_ids = TVA_Manager::get_excluded_course_ids( $course_id );
			$this->_delete_protected_posts( $excluded_ids );
			$posts_ids = array_diff( $posts_ids, $excluded_ids );
			foreach ( $rule['items'] as $membership ) {
				$this->_assure_dripping_content( $posts_ids, $membership['id'] );
			}
		}
	}

	/**
	 * Saves into MM table the posts for membership
	 *
	 * @param array $post_ids
	 * @param int   $membership_id
	 *
	 * @return bool
	 */
	protected function _assure_dripping_content( $post_ids, $membership_id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$membership_id = (int) $membership_id;
		if ( false === is_array( $post_ids ) || empty( $post_ids ) ) {
			return false;
		}

		$ids = implode( ',', $post_ids );

		$saved_sql = 'SELECT post_id FROM mm_posts_access WHERE access_type = "' . $this->get_access_type() . '" AND access_id =' . $membership_id . ' AND post_id IN (' . $ids . ')';
		$saved_ids = $wpdb->get_col( $saved_sql );

		$to_save_ids = array_diff( $post_ids, $saved_ids );
		$inserted    = 0;

		if ( ! empty( $to_save_ids ) ) {

			//insert all the posts at once
			$insert_sql = 'INSERT INTO mm_posts_access (post_id, access_type, access_id, days, is_smart_content) VALUES ' . "\n";
			foreach ( $to_save_ids as $id ) {
				$insert_sql .= "({$id}, '" . $this->get_access_type() . "', {$membership_id}, 0, 0),\n";
			}

			$insert_sql = trim( $insert_sql, "\n," );
			$inserted   = $wpdb->query( $insert_sql );
		}

		return $inserted === count( $to_save_ids );
	}

	/**
	 * Removes from MM table the posts
	 *
	 * @param array $posts_ids
	 * @param int   $access_id
	 *
	 * @return int
	 */
	protected function _delete_protected_posts( $posts_ids, $access_id = 0 ) {

		if ( empty( $posts_ids ) || false === is_array( $posts_ids ) ) {
			return 0;
		}

		$access_id = (int) $access_id;

		/** @var wpdb $wpdb */
		global $wpdb;

		$ids = implode( ',', $posts_ids );

		$delete_sql = 'DELETE FROM mm_posts_access WHERE access_type = "' . $this->get_access_type() . '" AND post_id IN (' . $ids . ')  ';

		if ( $access_id ) {
			$delete_sql .= ' AND access_id =' . $access_id;
		}

		$deleted = $wpdb->query( $delete_sql );

		return (int) $deleted;
	}

	/**
	 * @inheritDoc
	 */
	public function trigger_no_access() {

		$url = MM_CorePageEngine::getUrl( MM_CorePageType::$ERROR, MM_Error::$ACCESS_DENIED );

		if ( ! empty( $url ) ) {
			wp_redirect( $url );
		}
	}

	/**
	 * Returns a value which is saved in `access_type` column of mm_posts_access table based on the instance's _slug
	 *
	 * @return string
	 * @see TVA_Integration::$_slug
	 */
	public function get_access_type() {

		return $this->_slug === 'membermouse' ? 'member_type' : 'access_tag';
	}
}
