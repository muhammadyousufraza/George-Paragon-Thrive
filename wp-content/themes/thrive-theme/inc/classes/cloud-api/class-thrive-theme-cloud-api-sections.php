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
 * Class Thrive_Theme_Cloud_Api_Sections
 */
class Thrive_Theme_Cloud_Api_Sections extends Thrive_Theme_Cloud_Api_Base {

	public $theme_element = 'sections';

	/**
	 * Filter sections by skin tag
	 *
	 * @param array $sections
	 *
	 * @return mixed|void
	 */
	protected function before_data_list( $sections ) {
		$active_skin_template_tag = thrive_skin()->get_tag();
		$template_type            = thrive_template()->is_singular() ? THRIVE_SINGULAR_TEMPLATE : 'list';

		/**
		 * Changes the template type used to filter the sections from the cloud
		 *
		 * @param string $template_type
		 */
		$template_type = apply_filters( 'thrive_theme_section_template_type', $template_type );

		/* Keep only the sections that are assigned to the current skin or the ones that are assigned to all skins and only for that specific template */
		$sections = array_filter( $sections, function ( $section ) use ( $active_skin_template_tag, $template_type ) {
			return ( ( empty( $section['skin_tag'] ) || $section['skin_tag'] === $active_skin_template_tag ) // if the section is assigned to all skins or to the current one
			         && ( empty( $section['template_type'] ) || $section['template_type'] === $template_type ) ); // if the sections is assigned to all the templates or the current one
		} );

		return apply_filters( 'thrive_theme_cloud_sections', $sections );
	}

	/**
	 * Download section from the cloud
	 *
	 * @param string       $id
	 * @param string       $version
	 * @param array|object $options Options to forward to the `import()` call
	 *
	 * @return array|Thrive_Section
	 * @throws Exception
	 */
	public function download_item( $id, $version = '', $options = [] ) {
		//TODO some response with error handling in js

		$options = wp_parse_args( $options, [ 'linked' => false ] );

		/**
		 * Fires when downloading a section template from the cloud
		 *
		 * @param string $id the template id
		 */
		do_action( 'thrive_section_template_download', $id );

		$response = [];
		$this->ensure_folders();

		$zip_path = $this->theme_folder_path . 'sections/' . $id . '-' . $version . '.zip';

		/* If the file with the version from cloud was previously downloaded, than we don't need to download it again */
		if ( Thrive_Utils::bypass_transient_cache() || ! file_exists( $zip_path ) ) {
			$zip_path = $this->get_zip( $id, $zip_path );
		}

		$import = new Thrive_Transfer_Import( $zip_path );
		/** @var Thrive_Section $section */
		$section = $import->import( 'section', $options );
		if ( ! empty( $section ) ) {
			$response = [
				'name'    => $section->name(),
				'type'    => $section->type(),
				'thumb'   => $section->thumbnail(),
				'style'   => $section->style( false, true ),
				'content' => $section->render(),
			];
		}

		/**
		 * One last change before returning the downloaded section
		 *
		 * @param array          $response
		 * @param Thrive_Section $section
		 */
		return apply_filters( 'thrive_theme_section_download', $response, $section );
	}
}
