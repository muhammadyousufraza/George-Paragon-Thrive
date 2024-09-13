<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Interface Thrive_Dash_List_Connection_FileUpload_Interface
 *
 * Holds definitions for all the necessary functionality for connecting a file upload service with a Form element
 */
interface Thrive_Dash_List_Connection_FileUpload_Interface {
	/**
	 * Upload a file to the storage
	 *
	 * @param string $file_contents contents of the uploaded file
	 * @param string $folder_id     folder identification
	 * @param array  $metadata      file metadata props, such as name
	 *
	 * @return string|WP_Error stored file id or WP_Error if any exceptions occured
	 */
	public function upload( $file_contents, $folder_id, $metadata );

	/**
	 * Retrieve data about a stored file
	 *
	 * @param string $file_id
	 *
	 * @return array containing URL and original name
	 */
	public function get_file_data( $file_id );

	/**
	 * Deletes an uploaded file
	 *
	 * @param string $file_id
	 *
	 * @return true|WP_Error
	 */
	public function delete( $file_id );

	/**
	 * Rename an uploaded file by applying a callback function on its name
	 * The callback function should return the new filename
	 *
	 * @param string   $file_id  file ID from google
	 * @param callable $callback function to apply to get the new filename
	 *
	 * @return array information about the renamed file
	 */
	public function rename_file( $file_id, $callback );
}
