<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\Automator;

use TCB\inc\helpers\FormSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Form_Identifier extends \Thrive\Automator\Items\Trigger_Field {

	public static function get_name() {
		return 'Select specific form identifier';
	}

	public static function get_description() {
		return 'Target a specific form on your website. Form identifiers are added under advanced options when editing a form. If your form identifier is not listed, please re-save the page containing the form. Custom form fields are only available when a specific form is selected.';
	}

	public static function get_placeholder() {
		return 'All forms (custom fields will not be available)';
	}

	public static function get_id() {
		return 'form_identifier';
	}

	public static function get_type() {
		return 'select';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $trigger_id, $trigger_data ) {
		$lg_ids = new \WP_Query( [
			'post_type'      => FormSettings::POST_TYPE,
			'fields'         => 'id=>parent',
			'posts_per_page' => '-1',
			'post_status'    => 'draft',
		] );
		$lgs    = [
			'none' => [
				'label' => 'All forms (custom fields will not be available)',
				'id'    => 'none',
			],
		];

		foreach ( $lg_ids->posts as $lg ) {
			$lg_post = FormSettings::get_one( $lg->ID );

			if ( ! apply_filters( 'tve_automator_should_use_form', true, $lg_post, $trigger_id, $trigger_data ) ) {
				continue;
			}

			if ( $lg_post !== null ) {
				$post = get_post( $lg->post_parent );
				if ( ! empty( $post ) && $post->post_status !== 'trash' ) {
					$form_id = $lg_post->form_identifier;
					$parent  = $lg->post_parent;
					if ( empty( $form_id ) && ! empty( $parent ) ) {

						$form_identifier           = ( empty( $post->post_name ) ? '' : $post->post_name ) . '-form-' . substr( uniqid( '', true ), - 6, 6 );
						$config                    = $lg_post->get_config( false );
						$config['form_identifier'] = $form_identifier;
						$post_title                = 'Form settings' . ( $lg->post_parent ? ' for content ' . $lg->post_parent : '' );
						$lg_post->set_config( $config )
						        ->save( $post_title, [ 'post_parent' => $lg->post_parent ] );
					}
					$inputs     = $lg_post->inputs;
					if ( ! empty( $inputs ) ) {
						$lgs[ $lg->ID ] = [
							'label' => $form_id,
							'id'    => $lg->ID,
						];
					}

				}
			}
		}

		return $lgs;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_dummy_value() {
		return 'test-form-23131231';
	}
}
