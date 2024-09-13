<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class JS_Module
 *
 * @package TCB\Lightspeed
 */
class JSModule {
	private $key;
	private $libraries;
	private $dependencies;

	private $loaded;

	private static $instances = [];

	public static function get_instance( $key = '', $libraries = [] ) {
		if ( empty( self::$instances[ $key ] ) ) {
			self::$instances[ $key ] = new self( $key, $libraries );
		}

		return self::$instances[ $key ];
	}

	public function __construct( $key = '', $libraries = [] ) {
		$this->key       = $key;
		$this->libraries = $libraries;
	}

	private function get_enqueue_key() {
		return 'tve_frontend_' . $this->key;
	}

	/**
	 * Module url
	 *
	 * @param boolean $include_version
	 *
	 * @return string
	 */
	public function get_url( $include_version = true ) {
		return tve_editor_js( '/modules/' . $this->key . \TCB_Utils::get_js_suffix() ) . ( $include_version ? '?v=' . TVE_VERSION : '' );
	}

	/**
	 * @param bool $print_inline
	 */
	public function load( $print_inline = false ) {
		/* allow multiple enqueue because it doesn't affect the code */
		if ( $this->loaded === null || ! $print_inline ) {
			$this->dependencies = [];

			$file_url    = $this->get_url();
			$enqueue_key = $this->get_enqueue_key();

			/* load any additional libraries that this module needs */
			$this->loaded = $this->load_libraries( $print_inline );

			if ( $print_inline ) {
				$this->loaded .= sprintf( '<script id="%s" type="text/javascript" src="%s" ></script>', $enqueue_key, $file_url );
			} else {
				/* all the modules have to depend on tve_frontend in order to makes sure that it's always loaded first */
				$this->dependencies[] = 'tve_frontend';

				tve_enqueue_script( $enqueue_key, $file_url, $this->dependencies, false, true );

				$this->loaded = '';
			}
		}

		return $this->loaded;
	}

	/**
	 * @param bool $print_inline
	 */
	public function load_libraries( $print_inline = false ) {
		$inline_libraries = '';

		foreach ( $this->libraries as $handler => $url ) {
			if ( $print_inline ) {
				$inline_libraries .= sprintf( '<script type="text/javascript" src="%s"></script>', $url );
			} elseif ( ! in_array( $handler, JS::LIBRARIES_ENQUEUED_AUTOMATICALLY, true ) ) {
				tve_enqueue_script( $handler, $url, [ 'jquery' ], false, true );

				$this->dependencies[] = $handler;
			}
		}

		return $inline_libraries;
	}
}
