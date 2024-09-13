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

use TCB\ConditionalDisplay\Field;
use TVA\Access\Expiry\Base;
use TVA\Product;


class Course_Access extends Field {

	public static function get_entity() {
		return 'course_data';
	}

	/**
	 * @param \TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_value( $course ) {
		$value = 'not_expired';

		if ( is_user_logged_in() && $course instanceof \TVA_Course_V2 ) {
			$product = $course->get_product();

			if ( $product instanceof Product ) {
				$access_expired = Base::access_has_expired( get_current_user_id(), $product );

				if ( $access_expired ) {
					$value = 'expired';
				}
			}
		}

		return $value;
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		return [
			[
				'value' => 'expired',
				'label' => __( 'Access is expired', 'thrive-apprentice' ),
			],
			[
				'value' => 'not_expired',
				'label' => __( 'Access is not expired', 'thrive-apprentice' ),
			],
		];
	}

	public static function get_key() {
		return 'course_access';
	}

	public static function get_label() {
		return __( 'Course access status', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}
}
