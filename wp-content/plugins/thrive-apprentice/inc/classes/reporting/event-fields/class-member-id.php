<?php
/**
 * Thrive Apprentice - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVA\Access\History_Table;
use TVE\Reporting\EventFields\User_Id;

class Member_Id extends User_Id {
	public static function get_label( $singular = true ): string {
		return $singular ? __( 'Member', 'thrive-apprentice' ) : __( 'Members', 'thrive-apprentice' );
	}

	/**
	 * Use only enrolled students as filter options
	 *
	 * @return array
	 */
	public static function get_filter_options(): array {
		$members = History_Table::get_instance()->get_all_students();

		$members = array_map( static function ( $member ) {
			$user = get_user_by( 'ID', $member['ID'] );

			return [
				'id'    => (int) $member['ID'],
				'label' => $user instanceof \WP_User ? $user->display_name : "Member {$member['ID']}",
			];
		}, $members );

		usort( $members, static function ( $a, $b ) {
			return strcasecmp( $a['label'], $b['label'] );
		} );

		return $members;
	}
}
