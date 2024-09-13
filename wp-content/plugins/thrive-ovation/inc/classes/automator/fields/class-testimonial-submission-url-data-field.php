<?php

namespace TVO\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Testimonial_Submission_Url_Data_Field
 */
class Testimonial_Submission_Url_Data_Field extends \Thrive\Automator\Items\Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Testimonial submission URL';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter testimonials by the URL of the page where the testimonial was submitted';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'testimonial_submission_url';
	}

	public static function get_options_callback() {
		$posts = get_posts( [
			'numberposts' => - 1,
			'post_status' => 'publish',
			'post_type'   => array( 'post', 'page' ),
		] );
		$links = [];

		foreach ( $posts as $post ) {
			$link           = get_permalink( $post );
			$links[ $link ] = [
				'id'    => $link,
				'label' => empty( $post->post_title ) ? $post->post_name : $post->post_title,
			];
		}

		return $links;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_supported_filters() {
		return [ 'string_eca' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
