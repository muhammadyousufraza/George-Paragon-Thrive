<?php

namespace TVA\Architect\Certificate;

use TVA\Architect\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Handles Certificate Verification Form Element and its logic
 */
class Main {
	/**
	 * Cache course on request
	 *
	 * @var array
	 */
	private static $course_cache = [];

	/**
	 * Initialize the hooks and adds shortcodes
	 */
	public static function init() {
		Hooks::init();
		Shortcodes::init();
		Compat::init();
	}

	/**
	 * Returns the course from a certificate ID
	 *
	 * @param int $certificate_id
	 *
	 * @return \TVA_Course_V2
	 */
	public static function get_certificate_course( $certificate_id ) {

		if ( empty( static::$course_cache[ $certificate_id ] ) ) {
			$terms = wp_get_object_terms( $certificate_id, \TVA_Const::COURSE_TAXONOMY );
			$id    = 0;

			if ( ! empty( $terms ) ) {
				$id = (int) $terms[0]->term_id;
			}

			static::$course_cache[ $certificate_id ] = new \TVA_Course_V2( $id );
		}

		return static::$course_cache[ $certificate_id ];
	}

	/**
	 * Remove generated certificates
	 *
	 * this action should happen
	 * - when the certificate post is updated -> so save request comes from tar to a certificate post
	 * - when the certificate post is deleted -> when the actual post is deleted or the course associated to the certificate
	 *
	 * @param int $certificate_id
	 *
	 * @return void
	 */
	public static function remove_generated_certificates( $certificate_id ) {
		\TVD_PDF_From_URL::delete_by_prefix( \TVA_Course_Certificate::FILE_NAME_PREFIX . '-' . $certificate_id );
	}

	public static function get_group_editing_options() {
		return array(
			'exit_label'    => __( 'Exit Group Styling', 'thrive-cb' ),
			'select_values' => array(
				array(
					'value'    => 'all_states',
					'selector' => '.tve-form-state',
					'name'     => __( 'Form States', 'thrive-cb' ),
					'singular' => __( '-- Form State %s', 'thrive-cb' ),
				),
			),
		);
	}

	/**
	 * To be implemented by third party in order to get access to more functionalities
	 * - able to remove the single certificate validation form in the content
	 * - load the default html structure of the element when added into content on LP-Build
	 *
	 * @return bool|false
	 */
	public static function is_lp_build() {
		return apply_filters( 'tva_certificate_on_lp_build', false );
	}
}
