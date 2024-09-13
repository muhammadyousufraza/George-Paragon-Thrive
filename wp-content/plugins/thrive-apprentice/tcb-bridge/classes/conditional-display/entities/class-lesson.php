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

class Lesson extends \TCB\ConditionalDisplay\Entity {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'lesson_data';
	}

	public static function get_label() {
		return __( 'Apprentice Lesson', 'thrive-apprentice' );
	}

	/**
	 * @param $extra_data
	 *
	 * @return \TVA_Lesson|null
	 */
	public function create_object( $extra_data ) {
		$object = \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_active_object();

		return ! empty( $object ) && $object instanceof \TVA_Lesson ? $object : null;
	}
}
