<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Utils
 *
 * @package  TVA\Architect\Course
 * @project  : thrive-apprentice
 */
class Utils {
	/**
	 * Check if the array contains at least one published object
	 *
	 * @param Object[] $children_array
	 *
	 * @return bool
	 */
	public static function has_published_children( $children_array ) {
		$has_published_children = false;

		if ( ! empty( $children_array ) ) {
			foreach ( $children_array as $child ) {
				if ( $child->post_status === 'publish' ) {
					$has_published_children = true;
					break;
				}
			}
		}

		return $has_published_children;
	}
}
