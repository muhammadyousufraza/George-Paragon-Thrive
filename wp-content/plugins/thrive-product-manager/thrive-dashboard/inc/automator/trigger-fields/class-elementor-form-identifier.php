<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TVE\Dashboard\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Elementor_Form_Identifier extends \Thrive\Automator\Items\Trigger_Field {

	public static function get_name() {
		return __( 'Select specific form identifier', 'thrive-dash' );
	}

	public static function get_description() {
		return 'Target a specific Elementor form';
	}

	public static function get_placeholder() {
		return __( 'All forms (custom fields will not be available)', 'thrive-dash' );
	}

	public static function get_id() {
		return 'elementor_form_identifier';
	}

	public static function get_type() {
		return 'select';
	}

	public static function populate_post_forms( $elements, &$forms ) {
		foreach ( $elements as $element ) {
			if ( ! empty( $element['settings']['form_name'] ) ) {
				$additional               = empty( $element['settings']['form_id'] ) ? '' : ' (' . $element['settings']['form_id'] . ')';
				$forms [ $element['id'] ] = [
					'label' => $element['settings']['form_name'] . $additional,
					'id'    => $element['id'],
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				static::populate_post_forms( $element['elements'], $forms );
			}
		}

		return false;
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $trigger_id, $trigger_data ) {
		$posts = Elementor::get_elementor_posts();
		$forms = [];
		foreach ( $posts as $post ) {
			$meta = json_decode( get_post_meta( $post->ID, '_elementor_data', true ), true );
			foreach ( $meta as $section ) {
				static::populate_post_forms( $section['elements'], $forms );
			}

		}

		return $forms;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_dummy_value() {
		return 'test-form-23131231';
	}
}
