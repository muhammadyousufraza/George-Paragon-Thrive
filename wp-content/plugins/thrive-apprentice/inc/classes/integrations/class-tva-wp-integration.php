<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 12-Apr-19
 * Time: 01:47 PM
 */

class TVA_WP_Integration extends TVA_Integration {

	private $_blacklist_roles
		= array(
			'administrator',
		);

	protected $_course_membership_meta_name = 'tva_roles';

	protected function init_items() {

		$roles = wp_roles()->roles;
		$items = array();

		foreach ( $roles as $slug => $role ) {
			try {
				if ( true === in_array( $slug, $this->_blacklist_roles ) ) {
					continue;
				}
				$items[] = new TVA_Integration_Item( $slug, translate_user_role( $role['name'] ) );
			} catch ( Exception $e ) {
			}
		}

		$this->set_items( $items );
	}

	/**
	 * Based on old roles saved into db
	 * build a new rule
	 *
	 * @param TVA_Course $course
	 *
	 * @return array
	 */
	public function get_old_rule( $course ) {

		$rule = array(
			'integration' => $this->get_slug(),
			'items'       => array(),
		);

		if ( false === $course instanceof TVA_Course ) {
			return $rule;
		}

		$db_roles = get_term_meta( $course->get_id(), $this->_course_membership_meta_name, true );

		if ( ! empty( $db_roles ) && is_array( $db_roles ) ) {

			foreach ( $db_roles as $role => $value ) {

				try {
					$rule['items'][] = $this->_get_item_from_membership( $role, $value );
				} catch ( Exception $e ) {

				}
			}
		}

		return $rule;
	}

	protected function _get_item_from_membership( $key, $value ) {

		return new TVA_Integration_Item( $key, ucfirst( $key ) );
	}

	public function remove_old_rule( $course_id ) {

		$deleted   = false;
		$course_id = (int) $course_id;

		if ( $course_id ) {
			$deleted = delete_term_meta( $course_id, $this->_course_membership_meta_name );
		}

		return $deleted;
	}

	/**
	 * Checks if the current user has a role from the rule
	 *
	 * @param array $rule
	 *
	 * @return bool
	 */
	public function is_rule_applied( $rule ) {

		$user_logged_in = is_user_logged_in();
		$allowed        = false;

		if ( $user_logged_in ) {

			$user = tva_access_manager()->get_logged_in_user();

			foreach ( $rule['items'] as $item ) {
				if ( $user instanceof WP_User && in_array( $item['id'], $user->roles ) ) {
					$allowed = true;
					break;
				}
			}
		}

		return $allowed;
	}

	public function get_customer_access_items( $customer ) {
		return array();
	}

	/**
	 * Returns the SQL Parts from the WordPress integration needed to fetch all the users that have access to a product
	 * protected by the WordPress integration
	 *
	 * @param array $wp_roles
	 *
	 * @return string
	 */
	public function get_users_with_level_query_part( $wp_roles = array() ) {
		if ( empty( $wp_roles ) ) {
			return '';
		}

		global $wpdb;
		$role_clause = array();
		foreach ( $wp_roles as $role ) {
			$role_clause[] = "m.meta_value like '%" . esc_sql( $role ) . "%'";
		}

		$role_clause = implode( ' OR ', $role_clause );

		if ( count( $wp_roles ) > 1 ) {
			$role_clause = '(' . $role_clause . ')';
		}

		return "ID IN (SELECT u.ID from $wpdb->users u INNER JOIN $wpdb->usermeta m ON u.ID = m.user_id WHERE m.meta_key LIKE '%_capabilities' AND  $role_clause)";
	}
}
