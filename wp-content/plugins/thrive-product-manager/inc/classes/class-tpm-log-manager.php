<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Log_Manager {

	const FILE_NAME = 'tpm.log';

	protected $_message;

	protected static $_instance;

	private function __construct() {
	}

	public static function get_instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function _is_valid() {

		return ! empty( $this->_message );
	}

	/**
	 * @param $message string|WP_Error
	 *
	 * @return $this
	 */
	public function set_message( $message ) {

		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		$this->_message = $message;

		return $this;
	}


	public function log() {

		if ( ! $this->_is_valid() ) {

			return false;
		}

		return $this->_write();
	}

	protected function _get_file() {

		return thrive_product_manager()->path( self::FILE_NAME );
	}

	protected function _write() {

		$bytes = 0;

		if ( Thrive_Product_Manager::is_debug_mode() && wp_is_writable( $this->_get_file() ) ) {

			$bytes = file_put_contents( $this->_get_file(), "\n" . "[" . date( 'Y-m-d h:i:s' ) . "] " . $this->_message, FILE_APPEND );
		}

		return $bytes !== false;
	}
}
