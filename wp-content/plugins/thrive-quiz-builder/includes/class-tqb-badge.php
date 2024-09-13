<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TQB_Badge
 *
 * Helper for Thrive Images
 *
 */
class TQB_Badge {

	const TYPE = 'png';

	/**
	 * @var mixed
	 */
	private $result;

	/**
	 * @var int
	 */
	private $quiz_id;

	/**
	 * @var array
	 * @see wp_upload_dir()
	 */
	private $upload_dir;

	public function __construct( $result, $quiz_id ) {
		$this->result  = $result;
		$this->quiz_id = intval( $quiz_id );
	}

	private function _filename() {

		$prefix = str_replace( '%', '', $this->result );
		$prefix = str_replace( '/', '-', $prefix );

		$filename = $prefix . '-' . $this->quiz_id . '.' . self::TYPE;
		$filename = sanitize_file_name( $filename );

		return $filename;
	}

	private function _upload_dir() {

		if ( empty( $this->upload_dir ) ) {
			$this->upload_dir = wp_upload_dir();
		}

		return $this->upload_dir;
	}

	private function _subdir() {

		return '/thrive-quiz-builder/user_badges';
	}

	/**
	 * @param $uploaded_file
	 *
	 * @return null|string url
	 */
	public function save( &$uploaded_file ) {

		add_filter( 'upload_dir', array( $this, 'filter_upload_location' ) );

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$upload_overrides = array(
			'action'                   => 'tqb_frontend_ajax_controller',
			'unique_filename_callback' => array( $this, 'filter_filename' ),
		);

		$moved_file = wp_handle_upload( $uploaded_file, $upload_overrides );

		remove_filter( 'upload_dir', array( $this, 'filter_upload_location' ) );

		return isset( $moved_file['error'] ) ? null : $moved_file['url'];
	}

	public function filter_filename() {

		return $this->_filename();
	}

	public function filter_upload_location( $upload ) {

		$subdir = '/thrive-quiz-builder/user_badges';

		$upload['path']   = $upload['basedir'] . $subdir;
		$upload['url']    = $upload['baseurl'] . $subdir;
		$upload['subdir'] = $subdir;

		return $upload;
	}

	public function get_url() {

		$url        = null;
		$upload_dir = $this->_upload_dir();

		if ( is_file( $this->get_path() ) ) {
			$url = $upload_dir['baseurl'] . $this->_subdir() . '/' . $this->_filename();
		}

		return $url;
	}

	public function get_path() {

		$upload_dir = $this->_upload_dir();
		$path       = $upload_dir['basedir'] . $this->_subdir() . '/' . $this->_filename();

		return $path;
	}

}
