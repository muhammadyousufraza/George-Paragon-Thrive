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

class Course extends \TCB\ConditionalDisplay\Entity {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'course_data';
	}

	public static function get_label() {
		return __( 'Apprentice Course', 'thrive-apprentice' );
	}

	/**
	 * @param $extra_data
	 *
	 * @return \TVA_Course_V2
	 */
	public function create_object( $extra_data ) {
		return \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_active_course();
	}
}
