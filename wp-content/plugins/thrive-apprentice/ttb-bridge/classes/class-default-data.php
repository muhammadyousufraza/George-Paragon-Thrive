<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Default_Data extends \Thrive_Theme_Default_Data {
	/**
	 * Default templates that should be created when a new TA skin is created
	 *
	 * @return array
	 */
	public static function templates_meta() {
		return [
			[
				'meta_input' => [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_SINGULAR_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::LESSON_POST_TYPE,
					'default'                 => 1,
				],
				'post_title' => __( 'Standard lesson', THEME_DOMAIN ),
				'format'     => THRIVE_STANDARD_POST_FORMAT,
			],
			[
				'meta_input' => [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_SINGULAR_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::LESSON_POST_TYPE,
					'default'                 => 1,
					'format'                  => 'audio',
				],
				'post_title' => __( 'Audio lesson', THEME_DOMAIN ),
			],
			[
				'meta_input' => [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_SINGULAR_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::LESSON_POST_TYPE,
					'default'                 => 1,
					'format'                  => 'video',
				],
				'post_title' => __( 'Video lesson', THEME_DOMAIN ),
			],
			[
				'meta_input' => [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_SINGULAR_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::MODULE_POST_TYPE,
					'default'                 => 1,
				],
				'post_title' => __( 'Module', THEME_DOMAIN ),
			],
			[
				'meta_input' => [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_ARCHIVE_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::COURSE_TAXONOMY,
					'default'                 => 1,
				],
				'post_title' => __( 'Course Overview', THEME_DOMAIN ),
			],
			[
				'meta_input' => [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_HOMEPAGE_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::COURSE_POST_TYPE,
					'default'                 => 1,
				],
				'post_title' => __( 'School homepage', THEME_DOMAIN ),
			],
		];
	}

	/**
	 * Creates a new skin. If all parameters are default, it will create the skin and set it as default
	 *
	 * @param string|null $skin_name      optional, allows creating a skin with a specific name
	 * @param bool        $set_as_default optional whether or not to set the new skin as default
	 * @param bool        $create_default_data whether or not to generate default data for the skin
	 *
	 * @return integer ID of the newly created skin
	 */
	public static function create_skin( $skin_name = null, $set_as_default = true, $create_default_data = true ) {
		$skin_id = parent::create_skin( $skin_name, $set_as_default, $create_default_data );

		Main::skin( $skin_id )->ensure_scope();

		return $skin_id;
	}

	/**
	 * Get a default TA logo. If an attachment is not found, attach a default image
	 *
	 * @return array with the following keys:
	 *               - src
	 *               - width
	 *               - height
	 *               - attachment_id (if available)
	 */
	public static function get_default_logo() {
		$logo_attachment_id = get_option( 'tva_default_logo', 0 );
		if ( ! empty( $logo_attachment_id ) ) {
			$image = wp_get_attachment_image_src( $logo_attachment_id, 'full' );
		}

		if ( empty( $image ) ) {
			/**
			 * overwrite WP's copy() and rename() operations with just copy() in order not to lose the original file
			 *
			 * @see _wp_handle_upload()
			 */
			add_filter( 'pre_move_uploaded_file', static function ( $value, $file, $new_file ) {
				// this is taken directly from wp-includes/file.php
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				@copy( $file['tmp_name'], $new_file );

				// always return true, so that the original file is not removed.
				return true;
			}, 10, 3 );

			/* insert new attachment */
			$inserted_id = media_handle_sideload( [
				'tmp_name' => \TVA_Const::plugin_path( 'admin/img/dashboard-thrive-apprentice-horizontal.png' ),
				'name'     => 'apprentice-default-logo.png',
				'error'    => 0,
			] );

			if ( ! is_wp_error( $inserted_id ) ) {
				$logo_attachment_id = $inserted_id;
				update_option( 'tva_default_logo', $logo_attachment_id, 'no' );
				$image = wp_get_attachment_image_src( $logo_attachment_id, 'full' );
			}
		}

		if ( ! empty( $image ) ) {
			$attachment = [
				'src'    => $image[0],
				'width'  => $image[1],
				'height' => $image[2],
			];
		} else {
			/* nothing worked. return a default array ... */
			$attachment = [
				'src'    => \TVA_Const::plugin_url( 'admin/img/dashboard-thrive-apprentice-horizontal.png' ),
				'width'  => 214,
				'height' => 42,
			];
		}

		$attachment['attachment_id'] = (int) $logo_attachment_id;

		return $attachment;
	}
}
