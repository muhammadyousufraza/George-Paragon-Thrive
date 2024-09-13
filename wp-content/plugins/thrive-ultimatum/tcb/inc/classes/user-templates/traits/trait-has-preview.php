<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\UserTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Has_Preview {
	/**
	 * @param array $image_file_data
	 * @param array $template
	 *
	 * @return array
	 */
	public static function upload_preview_image( $image_file_data, $template ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		add_filter( 'upload_dir', 'tve_filter_upload_user_template_location' );

		$preview_data = wp_handle_upload(
			$image_file_data,
			[
				'action'                   => 'tcb_editor_ajax',
				'unique_filename_callback' => sanitize_file_name( $template['name'] . '.png' ),
			]
		);

		remove_filter( 'upload_dir', 'tve_filter_upload_user_template_location' );

		return $preview_data;
	}

	/**
	 * @param string $image_file
	 */
	public static function resize_preview_image( $image_file ) {
		$preview = wp_get_image_editor( $image_file );

		if ( ! is_wp_error( $preview ) ) {
			$preview->resize( static::get_resize_width(), null );
			$preview->save( $image_file );
		}
	}

	/**
	 * @param $name
	 */
	public static function delete_preview_image( $name ) {
		/* black magic in case the name contains spaces */
		$name = implode( '-', explode( ' ', $name ) );

		$upload_dir = wp_upload_dir();

		$file_name = $upload_dir['basedir'] . '/' . static::get_upload_dir_path() . '/' . $name . '.jpg';

		@unlink( $file_name );
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_url() {
		return tve_editor_url( 'admin/assets/images/no-template-preview.jpg' );
	}

	/**
	 * @return array
	 */
	public static function get_placeholder_data() {
		return [
			'url' => static::get_placeholder_url(),
			/* hardcoded sizes taken from 'no-template-preview.jpg' */
			'h'   => '120',
			'w'   => '250',
		];
	}

	public static function get_resize_width() {
		return 330;
	}

	public static function get_upload_dir_path() {
		return 'thrive-visual-editor/user_templates';
	}
}
