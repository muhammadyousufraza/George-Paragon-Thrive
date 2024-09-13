<?php

/**
 * Class TCB_Show_When_Menu_Option
 * - adds show when dropdown to each menu item in any menu
 */
class TCB_Show_When_Menu_Option {

	/**
	 * @var TCB_Show_When_Menu_Option
	 */
	protected static $instance;

	public $settings = [];

	private function __construct() {
		add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'add_show_when_dropdown_to_menu_item' ], 10, 4 );
		add_filter( 'wp_setup_nav_menu_item', [ $this, 'add_show_when_to_menu_item' ] );
		add_action( 'wp_update_nav_menu_item', [ $this, 'wp_update_nav_menu_item' ], 10, 2 );
		add_filter( 'wp_nav_menu_objects', [ $this, 'remove_specific_menu_items' ], 9 ); //before TA and TL
	}

	public function add_show_when_dropdown_to_menu_item( $item_id, $item ) {
		echo tcb_template( 'show-when-dropdown.phtml', [ 'item' => $item, 'item_id' => $item_id ], true ); //phpcs:ignore
	}

	/**
	 * Adds the show when option to post meta
	 *
	 * @param int $menu_id
	 * @param int $menu_item_id
	 */
	public function wp_update_nav_menu_item( $menu_id, $menu_item_id ) {

		$value = get_post_meta( $menu_item_id, '_menu_item_tcb_show_when', true );

		if ( ! empty( $_REQUEST['tcb_show_when'][ $menu_item_id ] ) ) {
			$value = sanitize_text_field( $_REQUEST['tcb_show_when'][ $menu_item_id ] );
		}

		if ( empty( $value ) ) {
			$value = 'always';
		}

		update_post_meta( $menu_item_id, '_menu_item_tcb_show_when', $value );
	}

	/**
	 * @return TCB_Show_When_Menu_Option
	 */
	public static function get_instance() {

		if ( ! static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Adds the show when option from post meta to the menu item
	 *
	 * @param WP_Post $menu_item
	 *
	 * @return WP_Post
	 */
	public function add_show_when_to_menu_item( $menu_item ) {

		$menu_item->tcb_show_when = get_post_meta( $menu_item->ID, '_menu_item_tcb_show_when', true );

		return $menu_item;
	}

	/**
	 * Cuts menu items that shouldn't appear in the menu based on the 'Show when' option value
	 *
	 * @param array $sorted_menu_items indexing starts from 1
	 *
	 * @return array of nav menu items
	 */
	public function remove_specific_menu_items( $sorted_menu_items ) {
		/**
		 * shift elements to left so that the elements start from 0 and not 1
		 */
		array_splice( $sorted_menu_items, 0, 0 );
		$length = count( $sorted_menu_items );
		$index  = 0;

		while ( $index < $length ) {
			$show_when   = $sorted_menu_items[ $index ]->tcb_show_when;
			$shouldUnset = false;

			switch ( $show_when ) {
				case 'loggedin':
					if ( ! is_user_logged_in() ) {
						$shouldUnset = true;
					}
					break;

				case 'loggedout':
					if ( is_user_logged_in() ) {
						$shouldUnset = true;
					}
					break;
			}

			if ( $shouldUnset ) {
				$currentID        = $sorted_menu_items[ $index ]->ID;
				$currentElParent  = $sorted_menu_items[ $index ]->menu_item_parent;
				$i                = $index + 1;
				$previousElID     = ! empty( $sorted_menu_items[ $index - 1 ] ) ? $sorted_menu_items[ $index - 1 ]->ID : null;
				$previousElParent = ! empty( $sorted_menu_items[ $index - 1 ] ) ? $sorted_menu_items[ $index - 1 ]->menu_item_parent : '0';

				/**
				 * iterate over every child menu item if it was a direct child then change child parent to current parent
				 */
				while ( $i < $length && $sorted_menu_items[ $i ]->menu_item_parent !== $previousElParent && $sorted_menu_items[ $i ]->menu_item_parent !== '0' ) {

					if ( $sorted_menu_items[ $i ]->menu_item_parent == $currentID ) {
						$sorted_menu_items[ $i ]->menu_item_parent = $currentElParent;
					}

					$i ++;
				}

				/**
				 * if it was the last child of an element then remove the has child class, so that the item won't indicate that it has children
				 */
				if ( $i == $index + 1 && $previousElID == $currentElParent ) {

					$key = array_search( 'menu-item-has-children', $sorted_menu_items[ $index - 1 ]->classes );

					if ( $key !== false ) {
						unset( $sorted_menu_items[ $index - 1 ]->classes[ $key ] );
					}
				}

				/**
				 * Remove the item from the array and thus the keys are reindexed
				 */
				array_splice( $sorted_menu_items, $index, 1 );
				$index  -= 1;
				$length -= 1;
			}
			$index += 1;
		}
		//reset top level count once the elements are hidden
		$GLOBALS['tcb_wp_menu']['top_level_count'] = count( array_filter( $sorted_menu_items, function ( $item ) {
			return empty( $item->menu_item_parent );
		} ) );

		return $sorted_menu_items;
	}
}

global $tcb_show_when;

/**
 * Method wrapper for singleton
 *
 * @return TCB_Show_When_Menu_Option
 */
function tcb_show_when_menu_option() {

	global $tcb_show_when;

	$tcb_show_when = TCB_Show_When_Menu_Option::get_instance();

	return $tcb_show_when;
}

add_action( 'after_setup_theme', 'tcb_show_when_menu_option' );
