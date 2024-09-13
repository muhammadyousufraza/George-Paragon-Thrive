<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 12-Apr-19
 * Time: 04:50 PM
 */

/**
 * Class TVA_WL_Integration
 * - implements TVA_Integration methods
 */
class TVA_WL_Integration extends TVA_Integration {

	/**
	 * Wishlist instance class name
	 *
	 * @var $_wl_classname
	 */
	private $_wl_classname;

	/**
	 * Set Wishlist instance class name
	 *
	 * @param string $name
	 */
	public function set_wl_classname( $name ) {
		$this->_wl_classname = $name;
	}

	/**
	 * Init WL Instance Class
	 *
	 * @throws Exception
	 */
	public function before_init_items() {
		if ( class_exists( 'WishListMember_Level', false ) ) {
			$this->set_wl_classname( 'WishListMember_Level' );
		} elseif ( class_exists( '\WishListMember\Level', false ) ) { // Wishlist v 3+
			$this->set_wl_classname( '\WishListMember\Level' );
		} else {
			throw new Exception( 'Failed to init Wishlist main instance' );
		}
	}

	protected function init_items() {
		$instance = $this->_wl_classname;
		$items    = array();

		if ( method_exists( $instance, 'get_all_levels' ) ) {
			$levels = $instance::get_all_levels( true );
		} else {
			$levels = $instance::GetAllLevels( true );
		}

		foreach ( $levels as $level ) {

			try {

				if ( $level instanceof $instance ) {
					$items[] = new TVA_Integration_Item( $level->ID, $level->name );
				}
			} catch ( Exception $e ) {

			}
		}

		$this->set_items( $items );
	}

	protected function _get_item_from_membership( $key, $value ) {

		$level = new $this->_wl_classname( $value );

		return new TVA_Integration_Item( $level->ID, $level->name );
	}

	/**
	 * Gets user's WishList Levels and checks if one of them can be found in rule
	 *
	 * @param array $rule
	 *
	 * @return bool
	 */
	public function is_rule_applied( $rule ) {

		$user  = tva_access_manager()->get_logged_in_user();
		$allow = false;

		if ( false === $user instanceof WP_User || false === class_exists( 'WishListMember3', false ) ) {
			return false;
		}

		global $WishListMemberInstance;
		if ( method_exists( $WishListMemberInstance, 'get_member_active_levels' ) ) {
			$user_active_levels = $WishListMemberInstance->get_member_active_levels( $user->ID );
		} else {
			$user_active_levels = $WishListMemberInstance->GetMemberActiveLevels( $user->ID );
		}

		foreach ( $rule['items'] as $item ) {

			if ( in_array( $item['id'], $user_active_levels ) ) {
				$allow = true;
				break;
			}
		}

		return $allow;
	}

	public function trigger_no_access() {
		/* nothing needed here. just let the request flow the way it's been setup by the user (TA will handle redirection) */
	}

	public function get_customer_access_items( $customer ) {
		return array();
	}

	/**
	 * Returns the SQL Parts from the WishList integration needed to fetch all the users that have access to a product
	 * protected by the WishList integration
	 *
	 * @param array $levels
	 *
	 * @return string
	 */
	public function get_users_with_level_query_part( $levels = array() ) {

		global $WishListMemberInstance;

		if ( empty( $WishListMemberInstance ) || empty( $WishListMemberInstance->table_names ) || empty( $WishListMemberInstance->table_names->userlevels ) ) {
			return '';
		}

		$params = array();
		foreach ( $levels as $id ) {
			$params[] = '%s';
		}

		global $wpdb;

		return $wpdb->prepare( "ID IN (SELECT user_id FROM {$WishListMemberInstance->table_names->userlevels} WHERE level_id IN (" . implode( ',', $params ) . "))", $levels );
	}
}
