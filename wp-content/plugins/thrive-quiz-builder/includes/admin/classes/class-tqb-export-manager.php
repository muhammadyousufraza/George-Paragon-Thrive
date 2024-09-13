<?php

require_once __DIR__ . '/export/interface-step.php';
require_once __DIR__ . '/export/class-tqb-export-step-abstract.php';
require_once __DIR__ . '/export/class-tqb-export-step-details.php';
require_once __DIR__ . '/export/class-tqb-export-step-questions.php';
require_once __DIR__ . '/export/class-tqb-export-structure-item.php';
require_once __DIR__ . '/export/class-tqb-export-step-structure.php';
require_once __DIR__ . '/export/class-tqb-export-step-badge.php';
require_once __DIR__ . '/export/class-tqb-export-step-resultlinks.php';
require_once __DIR__ . '/export/class-tqb-export-step-clearexport.php';

/**
 * Class TQB_Export_Manager
 * - factory export steps
 * - prepares the zip file to be downloaded
 */
class TQB_Export_Manager {

	/**
	 * Factory for a specific export step
	 * - quiz details
	 * - questions
	 * - etc
	 *
	 * @param string $step_name
	 *
	 * @return TQB_Export_Step_Abstract
	 * @throws Exception
	 */
	public static function make_step( $step_name ) {

		$class_name = 'TQB_Export_Step_' . ucfirst( $step_name );

		if ( class_exists( $class_name, false ) ) {
			return new $class_name();
		}

		throw new Exception( sprintf( __( 'Could not find a specific implementation for step: %s', 'thrive-quiz-builder' ), $step_name ) );
	}

	/**
	 * Prepares a zip file to be downloaded by the user
	 * - ZipArchive module is required to be enabled
	 * - loops through quiz folder for all files and add them into a zip file
	 *
	 * @param int $quiz_id
	 *
	 * @return array [path,url] for zip file
	 * @throws Exception
	 */
	public static function prepare_zip( $quiz_id ) {

		if ( ! class_exists( 'ZipArchive' ) ) {
			throw new Exception( __( 'The PHP ZipArchive extension must be enabled in order to use this functionality. Please contact your hosting provider.', 'thrive-cb' ) );
		}

		WP_Filesystem();

		/** @var WP_Filesystem_Direct $wp_filesystem */
		global $wp_filesystem;

		$old_umask    = umask( 0 );
		$zip          = new ZipArchive();
		$zip_filename = 'Quiz_' . $quiz_id . '.zip';
		$wp_upload    = wp_upload_dir();
		$zip_path     = trailingslashit( $wp_upload['basedir'] ) . 'thrive-quiz-builder/exports/';
		$zip_url      = trailingslashit( $wp_upload['baseurl'] ) . 'thrive-quiz-builder/exports/' . $zip_filename;
		$quiz_folder  = $zip_path . $quiz_id;

		/**
		 * Delete previously created zip file for this quiz
		 */
		$wp_filesystem->delete( $zip_path . $zip_filename );

		if ( false === $wp_filesystem->is_dir( $quiz_folder ) ) {
			throw new Exception( __( 'Quiz folder was not created', 'thrive-quiz-builder' ) );
		}

		if ( $zip->open( trailingslashit( $zip_path ) . $zip_filename, ZipArchive::CREATE ) !== true ) {
			throw new Exception( __( 'Could not create zip archive', 'thrive-quiz-builder' ) );
		}

		foreach ( $wp_filesystem->dirlist( $quiz_folder ) as $item ) {
			$zip->addFile( trailingslashit( $quiz_folder ) . $item['name'], $item['name'] );
		}

		if ( ! $zip->close() ) {
			throw new Exception( __( 'Could not write the zip file', 'thrive-quiz-builder' ) );
		}

		umask( $old_umask );

		return array(
			'path' => trailingslashit( $zip_path ) . $zip_filename,
			'url'  => $zip_url,
		);
	}
}
