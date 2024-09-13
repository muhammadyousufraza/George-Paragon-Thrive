<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Assessments\Types;

use TCB\inc\helpers\FileUploadConfig;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Upload extends Base {

	protected $upload_allowed_files;
	protected $upload_max_file_size;
	protected $upload_max_files;
	protected $upload_custom_extensions;

	protected static $meta_keys = [
		'upload_allowed_files',
		'upload_max_file_size',
		'upload_max_files',
		'upload_custom_extensions',
	];

	public function __construct( $data ) {
		parent::__construct( $data );

		foreach ( static::$meta_keys as $key ) {
			if ( ! empty( $data[ $key ] ) ) {
				$this->{$key} = $data[ $key ];
			}
		}
	}

	/**
	 * Get the allowed file types for this assessment
	 *
	 * @param $assessment
	 *
	 * @return array
	 */
	public static function get_extensions( $assessment ) {
		$file_types = (array) get_post_meta( $assessment->ID, 'tva_upload_allowed_files', true );
		$extensions = [];
		foreach ( FileUploadConfig::get_allowed_file_groups() as $key => $group ) {
			if ( ! empty( $group['extensions'] ) && in_array( $key, $file_types, true ) ) {
				$extensions = array_merge( $extensions, $group['extensions'] );
			}
		}
		$custom_extensions = get_post_meta( $assessment->ID, 'tva_upload_custom_extensions', true );
		if ( ! empty( $custom_extensions ) ) {
			$custom_extensions = explode( ',', $custom_extensions );
			$custom_extensions = array_map( 'trim', $custom_extensions );
			$extensions        = array_merge( $extensions, $custom_extensions );
		}

		$blacklist = FileUploadConfig::get_extensions_blacklist();

		/**
		 * Make sure that we don't have empty strings or banned types
		 */
		$extensions = array_filter( $extensions, static function ( $extension ) use ( $blacklist ) {
			return ! empty( $extension ) && ! in_array( $extension, $blacklist, true );
		} );

		return array_unique( array_values( $extensions ) );
	}
}
