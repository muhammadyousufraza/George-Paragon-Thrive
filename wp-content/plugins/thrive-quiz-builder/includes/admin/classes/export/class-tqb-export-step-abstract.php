<?php

/**
 * Class TQB_Export_Step_Abstract
 * - defines common methods for an export step
 */
abstract class TQB_Export_Step_Abstract implements TQB_Export_Step {

	/**
	 * @var string step name
	 */
	protected $_name = '';

	/**
	 * @var WP_Post
	 */
	protected $quiz;

	/**
	 * Quiz details which will be written into a file
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	private $wp_upload_dir;

	/**
	 * Sets quiz wp_post
	 *
	 * @param int $quiz
	 *
	 * @return bool
	 */
	public function set_quiz( $quiz ) {

		$quiz_id    = (int) $quiz;
		$this->quiz = get_post( $quiz_id );

		return null !== $this->quiz;
	}

	/**
	 * Method to be implemented by the inherited step classes
	 * - data to be written into a file
	 *
	 * @return true
	 */
	abstract protected function _prepare_data();

	/**
	 * Prepared data is put into a file
	 * - to be added into import zip
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function execute() {

		if ( empty( $this->quiz ) ) {
			throw new Exception( sprintf( __( 'No quiz has been provided to step %s be exported.', 'thrive-quiz-builder' ), $this->_name ) );
		}

		$this->_prepare_data();
		$saved = $this->write_data_to_file( $this->data, $this->_name . '.json' );

		if ( ! $saved ) {
			throw new Exception( sprintf( __( 'Step %s data could not be written to file.', 'thrive-quiz-builder' ), $this->_name ) );
		}

		return true;
	}

	/**
	 * Put data into a file
	 *
	 * @param array  $data
	 * @param string $filename only the file name, not the whole path
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function write_data_to_file( $data, $filename ) {

		WP_Filesystem();

		$old_umask = umask( 0 );

		$data_saved = false;

		/** @var $wp_filesystem WP_Filesystem_Direct */
		global $wp_filesystem;

		/**
		 * preparing the quiz folder
		 */
		if ( false === $wp_filesystem->is_dir( $this->get_path() ) ) {
			wp_mkdir_p( $this->get_path() );
		}

		/**
		 * saving file
		 */
		if ( $wp_filesystem->is_dir( $this->get_path() ) && $wp_filesystem->is_writable( $this->get_path() ) ) {
			$data_saved = $wp_filesystem->put_contents( $this->get_path() . '/' . $filename, json_encode( $data ) );
		}

		umask( $old_umask );

		return $data_saved;
	}

	/**
	 * Returns the paths to quiz folder
	 * - where all the files will be exported
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function get_path() {

		$this->get_upload_dir();

		return $this->wp_upload_dir['basedir'] . '/thrive-quiz-builder/exports/' . $this->quiz->ID;
	}

	/**
	 * Gets and returns the wp_upload_dir()
	 *
	 * @param string $type index from array [path,url,subdir,basedir,baseurl,error]
	 *
	 * @return array|string|WP_Error based on $type parameter
	 * @throws Exception
	 * @see wp_upload_dir()
	 */
	protected function get_upload_dir( $type = null ) {

		if ( empty( $this->wp_upload_dir ) ) {
			$this->wp_upload_dir = wp_upload_dir();
		}

		if ( ! empty( $this->wp_upload_dir['error'] ) ) {
			throw new Exception( sprintf( __( 'Could not determine uploads folder (%s)', 'thrive-cb' ), $this->wp_upload_dir['error'] ) );
		}

		if ( $type ) {
			return isset( $this->wp_upload_dir[ $type ] ) ? $this->wp_upload_dir[ $type ] : new WP_Error( 400, __( 'Undefined type for upload dir', 'thrive-quiz-builder' ) );
		}

		return $this->wp_upload_dir;
	}

	/**
	 * Copies the image from wp/uploads to export folder
	 *
	 * @param stdClass $attachment
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function _prepare_file( $attachment ) {

		if ( false === $attachment instanceof stdClass || empty( $attachment->id ) ) {
			return false;
		}

		WP_Filesystem();

		/** @var WP_Filesystem_Direct $wp_filesystem */
		global $wp_filesystem;

		$source      = get_attached_file( $attachment->id );
		$destination = trailingslashit( $this->get_path() ) . $attachment->filename;

		if ( $wp_filesystem->exists( $destination ) ) {
			return true;
		}

		if ( ! empty( $source ) ) {
			return $wp_filesystem->copy( $source, $destination );
		}
	}
}
