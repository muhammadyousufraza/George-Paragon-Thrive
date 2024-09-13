<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Unlocked_Content_Data
 */
class Unlocked_Content_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'unlocked_content_data';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'content_type', 'lesson_title', 'module_title' ];
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Product_Data object' );
		}

		$content_type = null;

		if ( is_a( $param, '\WP_Post' ) ) {
			$content_type = $param;
		} elseif ( is_numeric( $param ) ) {
			$content_type = new WP_Post( (int) $param );
		} elseif ( is_array( $param ) ) {
			$content_type = new WP_Post( (int) $param[0] );
		}
		$content = [
			'content_type' => $content_type->post_type,
			'module_title' => '',
			'lesson_title' => '',
		];

		$content[ str_replace( 'tva_', '', $content_type->post_type ) . '_title' ] = $content_type->post_title;

		return $content;
	}
}
