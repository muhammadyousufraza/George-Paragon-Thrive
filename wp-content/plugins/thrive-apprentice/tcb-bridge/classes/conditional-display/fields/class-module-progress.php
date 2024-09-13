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

class Module_Progress extends \TCB\ConditionalDisplay\Field {
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
		return 'module_progress';
	}

	public static function get_label() {
		return __( 'Module progress', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'number_comparison' ];
	}

	/**
	 * @param \TVA_Module $module
	 *
	 * @return string
	 */
	public function get_value( $module ) {
		return empty( $module ) ? '' : \TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions()->get_progress_by_type( 'module' );
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return [
			'min' => 0,
			'max' => 100,
		];
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 5;
	}
}
