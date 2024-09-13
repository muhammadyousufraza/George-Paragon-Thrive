<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 16-Apr-19
 * Time: 10:59 AM
 */

/**
 * Class TVA_Membermouse_Integration
 * - implements TVA_Integration methods
 */
class TVA_Membermouse_Integration extends TVA_Membermouse_Abstract_Integration {

	protected function init_items() {

		$items = array();

		if ( class_exists( 'MM_MembershipLevel' ) ) {

			$membership_levels = MM_MembershipLevel::getMembershipLevelsList();

			if ( ! empty( $membership_levels ) && is_array( $membership_levels ) ) {

				foreach ( $membership_levels as $id => $name ) {
					try {
						$items[] = new TVA_Integration_Item( $id, $name );
					} catch ( Exception $e ) {

					}
				}
			}
		}

		$this->set_items( $items );
	}

	protected function _get_item_from_membership( $key, $value ) {

		$membership = new MM_MembershipLevel( $value );

		return new TVA_Integration_Item( (int) $membership->getId(), $membership->getName() );
	}

	public function is_rule_applied( $rule ) {

		$applied = false;

		if ( function_exists( 'mm_access_decision' ) ) {

			foreach ( $rule['items'] as $item ) {

				$applied = mm_member_decision( array( 'membershipId' => $item['id'] ) );

				if ( $applied && $this->_post instanceof WP_Post ) {

					$date_available = mm_content_data( array(
						'id'   => $this->_post->ID,
						'name' => 'dateAvailable',
					) );

					//checks if current time is greater than the one from dripping
					$applied = time() >= strtotime( $date_available );
				}

				if ( $applied ) {
					break;
				}
			}
		}

		return $applied;
	}

	public function get_customer_access_items( $customer ) {
		return array();
	}

	/**
	 * Returns the SQL Parts from the MemberMouse integration needed to fetch all the users that have access to a product
	 * protected by the MemberMouse integration
	 *
	 * @param array $levels
	 *
	 * @return string
	 */
	public function get_users_with_level_query_part( $levels = array() ) {

		if ( defined( 'MM_TABLE_USER_DATA' ) ) {
			$params = array();
			foreach ( $levels as $id ) {
				$params[] = '%s';
			}

			global $wpdb;

			return $wpdb->prepare( "ID IN (SELECT wp_user_id FROM " . MM_TABLE_USER_DATA . " WHERE membership_level_id IN (" . implode( ',', $params ) . "))", $levels );
		}

		return '';
	}

}
