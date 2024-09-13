<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Module extends \TCB\ConditionalDisplay\Entity {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'module_data';
	}

	public static function get_label() {
		return __( 'Apprentice Module', 'thrive-apprentice' );
	}

	/**
	 * @param $extra_data
	 *
	 * @return \TVA_Module|null
	 */
	public function create_object( $extra_data ) {
		$object = \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_active_object();

		return ! empty( $object ) && $object instanceof \TVA_Module ? $object : null;
	}
}
