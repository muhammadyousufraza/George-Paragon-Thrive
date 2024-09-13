<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Module_Id extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'module_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'module_id';
	}

	public static function get_label() {
		return __( 'Module title', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	/**
	 * @param \TVA_Module $module
	 *
	 * @return string
	 */
	public function get_value( $module ) {
		return empty( $module ) ? '' : $module->ID;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$modules = [];

		$query = [
			'posts_per_page' => empty( $selected_values ) ? min( 100, max( 20, strlen( $searched_keyword ) * 3 ) ) : - 1,
			'post_type'      => [ \TVA_Const::MODULE_POST_TYPE ],
			'post_status'    => \TVA_Post::$accepted_statuses,
			'meta_key'       => 'tva_module_order',
			'post_parent'    => 0,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => 'tva_is_demo', /* don't display dummy content */
					'compare' => 'NOT EXISTS',
				],
			],
		];

		if ( ! empty( $selected_values ) ) {
			$query['include'] = $selected_values;
		}
		if ( ! empty( $searched_keyword ) ) {
			$query['s'] = $searched_keyword;
		}

		foreach ( get_posts( $query ) as $module ) {
			if ( static::filter_options( $module->ID, $module->post_title, $selected_values, $searched_keyword ) ) {
				$modules[] = [
					'value' => (string) $module->ID,
					'label' => $module->post_title,
				];
			}
		}

		return $modules;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search modules', 'thrive-apprentice' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}
}
