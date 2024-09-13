<?php

namespace TCB\Integrations\Automator;

use TCB\inc\helpers\FormSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Consent_Field
 */
class Form_Identifier_Data_Field extends \Thrive\Automator\Items\Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Specific forms';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Select forms that contain the identifier';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Filter by form name identifier';
	}

	public static function get_id() {
		return 'form_identifier';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$lg_ids = new \WP_Query( [
			'post_type'      => FormSettings::POST_TYPE,
			'fields'         => 'id=>parent',
			'posts_per_page' => '-1',
			'post_status'    => 'draft',
		] );
		$lgs    = [];

		foreach ( $lg_ids->posts as $lg ) {
			$lg_post = FormSettings::get_one( $lg->ID );

			if ( ! empty( $lg_post ) ) {
				$post = get_post( $lg->post_parent );
				if ( ! empty( $post ) && $post->post_status !== 'trash' ) {
					$saved_identifier = $lg_post->form_identifier;
					if ( empty( $saved_identifier ) && ! empty( $lg->post_parent ) ) {

						$form_identifier           = ( empty( $post->post_name ) ? '' : $post->post_name ) . '-form-' . substr( uniqid( '', true ), - 6, 6 );
						$config                    = $lg_post->get_config( false );
						$config['form_identifier'] = $form_identifier;
						$post_title                = 'Form settings' . ( $lg->post_parent ? ' for content ' . $lg->post_parent : '' );
						$lg_post->set_config( $config )
						        ->save( $post_title, [ 'post_parent' => $lg->post_parent ] );
					}

					$form_id         = $lg_post->form_identifier;
					$lgs[ $form_id ] = [
						'label' => $form_id,
						'id'    => $form_id,
					];
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
