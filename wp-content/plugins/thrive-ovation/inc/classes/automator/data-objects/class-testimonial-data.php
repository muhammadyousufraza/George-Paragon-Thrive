<?php

namespace TVO\Automator;

use Exception;
use Thrive\Automator\Items\Data_Object;
use function tvo_get_testimonial_details;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Testimonial_Data
 */
class Testimonial_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'testimonial_data';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'testimonial_id',
			'testimonial_title',
			'testimonial_author',
			'testimonial_author_email',
			'testimonial_submission_url',
			'testimonial_author_role',
			'testimonial_author_website',
			'testimonial_author_image',
			'testimonial_content',
		);
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new Exception( 'No parameter provided for Testimonial_Data object' );
		}

		$testimonial = null;
		$post_id     = empty( $_POST['post_id'] ) ? '' : $_POST['post_id'];

		if ( is_a( $param, 'WP_Post' ) || is_numeric( $param ) ) {
			$testimonial = tvo_get_testimonial_details( $param, $post_id );
		} elseif ( is_array( $param ) && isset( $param['testimonial_id'] ) ) {
			$testimonial = tvo_get_testimonial_details( $param['testimonial_id'], $post_id );
		}

		return $testimonial;
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'testimonial_author_email' );
	}
}
