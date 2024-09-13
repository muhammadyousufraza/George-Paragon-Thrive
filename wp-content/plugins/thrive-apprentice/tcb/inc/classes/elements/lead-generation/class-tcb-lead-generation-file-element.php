<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

use TCB\inc\helpers\FileUploadConfig;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_ContentBox_Element
 */
class TCB_Lead_Generation_File_Element extends TCB_ContentBox_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'File Upload', 'thrive-cb' );
	}

	public function hide() {
		return true;
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-content-box.tve_lg_file';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$parent = parent::own_components();
		unset(
			$parent['shared-styles'],
			$parent['animation'],
			$parent['responsive'],
			$parent['contentbox'],
			$parent['borders']['config']['Borders']['to'],
			$parent['borders']['config']['Corners']['to'],
			$parent['background']['config']['to'],
			$parent['shadow']['config']['to']
		);
		$parent['borders']['config']['css_suffix']    = ' > .tve-content-box-background';
		$parent['background']['config']['css_suffix'] = ' > .tve-content-box-background';
		$parent['shadow']['config']['css_suffix']     = ' > .tve-content-box-background';

		return $parent +
		       array(
			       'lead_generation_file' => array(
				       'order'   => 0,
				       'options' => [
					       'default_config' => FileUploadConfig::$defaults,
				       ],
				       'config'  => array(
					       'required'   => array(
						       'config'  => array(
							       'default' => false,
							       'label'   => __( 'Required field', 'thrive-cb' ),
						       ),
						       'extends' => 'Switch',
					       ),
					       'ShowLabel'  => array(
						       'config'  => array(
							       'name'    => '',
							       'label'   => __( 'Show label', 'thrive-cb' ),
							       'default' => false,
						       ),
						       'extends' => 'Switch',
					       ),
					       'file_types' => array(
						       'groups'              => FileUploadConfig::get_allowed_file_groups(),
						       'extension_blacklist' => FileUploadConfig::get_extensions_blacklist(),
					       ),
					       'maxFiles'   => array(
						       'config' => array(
							       'name' => __( 'Max files', 'thrive-cb' ),
							       'min'  => 1,
							       'max'  => 5,
							       'size' => 'medium',
						       ),
					       ),
					       'maxSize'    => array(
						       'config' => array(
							       'name'      => __( 'Max file size', 'thrive-cb' ),
							       'maxlength' => 3,
							       'um'        => [ 'MB' ],
							       'min'       => 0,
							       'max'       => wp_max_upload_size() / 1024 / 1024,
							       'size'      => 'medium',
						       ),
					       ),
				       ),
			       ),
		       );
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return false;
	}
}
