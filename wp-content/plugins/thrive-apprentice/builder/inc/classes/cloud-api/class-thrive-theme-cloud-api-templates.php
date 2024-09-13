<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Theme_Cloud_Api_Templates
 */
class Thrive_Theme_Cloud_Api_Templates extends Thrive_Theme_Cloud_Api_Base {

	public $theme_element = 'templates';

	/**
	 * Download template archive and update the exiting from one
	 *
	 * @param string $tag
	 * @param string $version
	 * @param array  $options
	 *
	 * @return array
	 * @throws Exception
	 */
	public function download_item( $tag, $version = '', $options = [] ) {
		$response = [];
		$this->ensure_folders();

		$zip_path = $this->theme_folder_path . 'templates/' . $tag . '-' . thrive_skin()->get_tag() . '.zip';

		$zip_path = $this->get_zip( $tag, $zip_path );

		if ( isset( $zip_path['success'] ) && $zip_path['success'] === false ) {
			throw new Exception( $zip_path['message'] );
		}

		$import   = new Thrive_Transfer_Import( $zip_path );
		$template = $import->import( 'template', $options );

		if ( ! empty( $template ) ) {
			/* When we are saving we are returning an array with all the inserted templates. In this case, just one */
			if ( is_array( $template ) ) {
				$template = new Thrive_Template( $template[0] );
			}
			$response = $template->ID;
		}

		return $response;

	}

	/**
	 * Filter cloud templates before return
	 *
	 * @param $items
	 *
	 * @return mixed|void
	 */
	public function before_data_list( $items ) {
		return apply_filters( 'thrive_theme_cloud_templates', $items );
	}
}
