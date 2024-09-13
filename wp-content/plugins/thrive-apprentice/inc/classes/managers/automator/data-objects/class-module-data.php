<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Module_Data
 */
class Module_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'module_data';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'module_id', 'module_title', 'course_title' ];
	}

	public static function get_nice_name() {
		return 'Apprentice module ID';
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Module_Data object' );
		}

		$module = null;
		if ( is_a( $param, 'TVA_Module' ) ) {
			$module = $param;
		} elseif ( is_numeric( $param ) ) {
			$module = new TVA_Module( $param );
		} elseif ( is_array( $param ) ) {
			$module = new TVA_Module( $param['module_id'] );
		}

		if ( $module ) {
			$post   = $module->get_the_post();
			$course = $module->get_course_v2();

			return [
				'module_id'    => $post->ID,
				'module_title' => $post->post_title,
				'course_id'    => $course->get_id(),
				'course_title' => $course->name,
			];
		}

		return $module;
	}

	public static function get_data_object_options() {
		return Main::get_modules();
	}
}
