<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Entities;

use TCB\ConditionalDisplay\Entity;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Assessment extends Entity {
	public static function get_key() {
		return 'assessment_data';
	}

	public static function get_label() {
		return __( 'Apprentice Assessment', 'thrive-apprentice' );
	}

	public function create_object( $param ) {
		$object = tcb_tva_visual_builder()->get_active_object();

		return ! empty( $object ) && $object instanceof \TVA_Assessment ? $object : null;
	}
}
