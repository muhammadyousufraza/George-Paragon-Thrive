<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( class_exists( 'Tve_Dash_Font_Import_Manager_View' ) ) {
	return;
}

class Tve_Dash_Font_Import_Manager_View {
	protected $path;

	protected $data = array();

	public function __construct( $path ) {
		$path       = rtrim( $path, "/" );
		$this->path = $path;
	}

	public function render( $file, $data = array() ) {
		if ( strpos( $file, '.php' ) === false ) {
			$file .= '.php';
		}

		if ( ! is_file( $this->path . '/' . $file ) ) {
			echo sprintf( esc_html__( "No template found for %s", 'thrive-dash' ), esc_html( $file ) );

			return;
		}

		if ( ! is_array( $data ) ) {
			$data = array( 'data' => $data );
		}

		if ( ! empty( $data ) ) {
			$this->data = array_merge_recursive( $this->data, $data );
		}

		include $this->path . "/" . $file;
	}

	public function __get( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}

	public function __set( $key, $value ) {
		$this->data[ $key ] = $value;
	}
}

if ( ! class_exists( 'Thrive_Font_Import_Manager_View' ) ) {
	class Thrive_Font_Import_Manager_View extends Tve_Dash_Font_Import_Manager_View {

	}
}
