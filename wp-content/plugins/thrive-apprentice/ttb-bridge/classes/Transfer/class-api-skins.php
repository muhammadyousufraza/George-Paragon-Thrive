<?php

namespace TVA\TTB\Transfer;
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Api_Skins extends \Thrive_Theme_Cloud_Api_Skins {

	/**
	 * Get the skin thumb name included in the response header
	 *
	 * @return string
	 */
	public function get_thumb_from_header() {
		return (string) wp_remote_retrieve_header( $this->response, 'X-Thrive-Item-Thumb' );
	}

	/**
	 * Wrapper over skin -> get_zip
	 * DELETES the zip tranzient.
	 * The cloud request needs to happen because we need to fetch information from headers
	 * -> in this case we need the thumb name from headers
	 *
	 * @param $id
	 * @param $new_zip_file
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_zip( $id, $new_zip_file ) {

		delete_transient( 'tcb_zip_tranzient_' . $new_zip_file );

		return parent::get_zip( $id, $new_zip_file );
	}

// uncomment the following lines if there's no need for TPM validation for TA designs.
//	/**
//	 * Override this in order to skip license check for TTB skins
//	 */
//	public function before_zip() {
//
//	}
//
//	/**
//	 * For now, no TPM connection is needed for downloading TA skins
//	 * TODO: see if this is needed.
//	 *
//	 * @return array
//	 */
//	protected function validate_tpm_connection() {
//		return [];
//	}


}
