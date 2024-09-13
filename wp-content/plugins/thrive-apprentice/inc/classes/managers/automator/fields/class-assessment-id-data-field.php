<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Assessment_ID_Data_Field
 */
class Assessment_ID_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Assessment Id';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by assessment id';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$assessments = [];
		$filters     = [
			'meta_query' => [
				[
					'key'     => 'tva_is_demo',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		foreach ( TVA_Manager::get_assessments( $filters ) as $assessment ) {
			$assessments[ $assessment->ID ] = [
				'label' => $assessment->post_title,
				'id'    => $assessment->ID,
			];
		}

		return $assessments;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_id() {
		return 'assessment_id';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return 9;
	}

	public static function primary_key() {
		return Assessment_Data::get_id();
	}
}
