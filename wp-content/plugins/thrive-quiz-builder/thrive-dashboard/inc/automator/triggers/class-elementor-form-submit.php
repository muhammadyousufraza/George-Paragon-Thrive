<?php

namespace TVE\Dashboard\Automator;


use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Email_Data;
use Thrive\Automator\Items\Trigger;
use Thrive\Automator\Items\User_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Elementor_Form_Submit extends Trigger {

	public static function get_id() {
		return 'elementor/form-submit';
	}

	public static function get_wp_hook() {
		return Elementor::FORM_SUBMIT_HOOK;
	}

	public function get_automation_wp_hook() {
		return empty( $this->data['elementor_form_identifier']['value'] ) ? static::get_wp_hook() : Elementor::create_dynamic_trigger( static::get_wp_hook(), strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $this->data['elementor_form_identifier']['value'] ) ) ) );
	}

	public static function get_provided_data_objects() {
		return [ Elementor_Form_Data::get_id(), User_Data::get_id(), Email_Data::get_id() ];
	}

	public static function get_hook_params_number() {
		return 1;
	}

	public static function get_app_id() {
		return Elementor_App::get_id();
	}

	public static function get_name() {
		return __( 'Elementor form submit', 'thrive-dash' );
	}

	public static function get_description() {
		return __( 'Triggers when a visitor submits a form built with Elementor', 'thrive-dash' );
	}

	public static function get_image() {
		return 'tap-elementor-logo';
	}

	/**
	 * Override default method so we manually init user data if we can match the form's email with an existing user
	 *
	 * @param ElementorPro\Modules\Forms\Classes\Form_Record[] $params
	 *
	 * @return array
	 * @see Automation::start()
	 */
	public function process_params( $params = [] ) {

		$data_objects = array();
		$aut_id       = $this->get_automation_id();
		$form_record  = $params[0] ?? null;

		if ( $form_record ) {
			$form_data = $form_record->get( 'sent_data' );

			foreach ( $form_data as $key => $param ) {
				if ( is_array( $param ) ) {
					$form_data[ $key ] = implode( ',', $param );
				}
			}

			/* get all registered data objects and see which ones we use for this trigger */
			$data_object_classes = Data_Object::get();

			if ( empty( $data_object_classes[ Elementor_Form_Data::get_id() ] ) ) {
				/* if we don't have a class that parses the current param, we just leave the value as it is */
				$data_objects[ Elementor_Form_Data::get_id() ] = $form_data;
			} else {
				/* when a data object is available for the current parameter key, we create an instance that will handle the data */
				$data_objects[ Elementor_Form_Data::get_id() ] = new $data_object_classes[ Elementor_Form_Data::get_id() ]( $form_data, $aut_id );
			}

			$user_data = null;
			/**
			 * try to match email with existing user
			 */

			if ( ! empty( $form_data['email'] ) ) {
				$user_data = get_user_by( 'email', $form_data['email'] );

				if ( empty( $data_object_classes[ Email_Data::get_id() ] ) ) {
					$data_objects[ Email_Data::get_id() ] = [ 'email' => $form_data['email'] ];
				} else {
					$data_objects[ Email_Data::get_id() ] = new $data_object_classes[ Email_Data::get_id() ]( $form_data['email'], $aut_id );
				}

			}
			if ( ! empty( $user_data ) ) {
				if ( empty( $data_object_classes['user_data'] ) ) {
					$data_objects[ User_Data::get_id() ] = $user_data;
				} else {
					$data_objects[ User_Data::get_id() ] = new $data_object_classes[ User_Data::get_id() ]( $user_data, $aut_id );
				}
			}
		}

		return $data_objects;
	}

	public static function sync_trigger_data( $trigger_data ) {
		$posts         = Elementor::get_elementor_posts();
		$custom_fields = [];
		foreach ( $posts as $post ) {
			$meta = json_decode( get_post_meta( $post->ID, '_elementor_data', true ), true );
			foreach ( $meta as $section ) {
				if ( empty( $form ) ) {
					$form = static::find_form_settings( $section['elements'], $trigger_data['extra_data'][ Elementor_Form_Identifier::get_id() ]['value'] );
				}
			}
		}

		if ( ! empty( $form ) && ! empty( $form['settings']['form_fields'] ) ) {
			$data_fields                                                        = Data_Object::get_all_filterable_fields( [ Elementor_Form_Data::get_id() ] );
			$trigger_data['filterable_fields'][ Elementor_Form_Data::get_id() ] = $data_fields[ Elementor_Form_Data::get_id() ];
			$hide                                                               = [
				'email',
				'password',
				'recaptcha',
				'recaptcha_v3',
				'honeypot',
				'step',
				'upload',
				'html',
			];

			foreach ( $form['settings']['form_fields'] as $input ) {

				if ( ! empty( $input['custom_id'] ) && ( empty( $input['field_type'] ) || ( ! empty( $input['field_type'] ) && ! in_array( $input['field_type'], $hide, true ) ) ) ) {

					$custom_fields[ $input['custom_id'] ] = [
						'id'            => $input['custom_id'],
						'validators'    => [],
						'name'          => empty( $input['field_label'] ) ? 'No label set' : $input['field_label'],
						'description'   => __( 'Custom field description', 'thrive-dash' ),
						'tooltip'       => __( 'Custom field tooltip', 'thrive-dash' ),
						'placeholder'   => __( 'Custom field placeholder', 'thrive-dash' ),
						'is_ajax_field' => false,
						'value_type'    => 'string',
						'shortcode_tag' => '%' . $input['custom_id'] . '%',
						'dummy_value'   => $input['field_label'] . ' field value',
						'primary_key'   => false,
						'filters'       => [ 'string_ec' ],
					];
				}

			}

			unset( $trigger_data['filterable_fields'][ Elementor_Form_Data::get_id() ][ Elementor_Form_Identifier::get_id() ] );
			$trigger_data['filterable_fields'][ Elementor_Form_Data::get_id() ] = array_merge( $trigger_data['filterable_fields'][ Elementor_Form_Data::get_id() ], $custom_fields );
		}

		return $trigger_data;
	}

	public static function find_form_settings( $elements, $form_id ) {
		foreach ( $elements as $element ) {

			if ( $element['id'] == $form_id ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				return static::find_form_settings( $element['elements'], $form_id );
			}
		}

		return false;
	}

	public static function get_required_trigger_fields() {
		return [ Elementor_Form_Identifier::get_id() ];
	}
}

