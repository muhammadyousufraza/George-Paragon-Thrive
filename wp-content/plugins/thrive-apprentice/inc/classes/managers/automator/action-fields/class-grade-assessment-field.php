<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;
use TVA_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Grade_Assessment_Field extends Action_Field {
	public static function get_id() {
		return 'assessment';
	}

	public static function get_type() {
		return 'select';
	}

	public static function get_description() {
		return 'Please select an assessment';
	}

	public static function get_name() {
		return '';
	}

	public static function get_placeholder() {
		return 'Choose an assessment to grade';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Choose assessment to grade : $$value';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		$values  = [];
		$filters = [
			'meta_query' => [
				[
					'key'     => 'tva_is_demo',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		foreach ( TVA_Manager::get_assessments( $filters ) as $assessment ) {
			$values[] = [
				'id'    => $assessment->ID,
				'label' => $assessment->post_title,
			];
		}

		return $values;
	}
	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}


	public static function allowed_data_set_values() {
		return [ Assessment_Data::get_id() ];
	}

}
