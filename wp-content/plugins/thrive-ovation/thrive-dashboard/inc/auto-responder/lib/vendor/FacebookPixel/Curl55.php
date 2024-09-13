<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

use CURLFile;
use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Curl55 extends AbstractCurl {

	/**
	 * @throws RuntimeException
	 */
	public function __construct() {
		parent::__construct();
		if ( version_compare( PHP_VERSION, '5.5.0' ) < 0 ) {
			throw new RuntimeException( "Unsupported Curl version" );
		}
	}

	/**
	 * @param string $string
	 *
	 * @return bool|string
	 */
	public function escape( $string ) {
		return curl_escape( $this->handle, $string );
	}

	/**
	 * @param int $bitmask
	 *
	 * @return int
	 */
	public function pause( $bitmask ) {
		return curl_pause( $this->handle, $bitmask );
	}

	/**
	 * FIXME should introduce v2.10 breaking change:
	 * implement abstract support for FileParameter in AdapterInterface
	 *
	 * @param string|FileParameter $filepath
	 *
	 * @return CURLFile
	 */
	public function preparePostFileField( $filepath ) {
		$mime_type = $name = ''; // can't be null in HHVM
		if ( $filepath instanceof FileParameter ) {
			$mime_type = $filepath->getMimeType() ?: '';
			$name      = $filepath->getName() ?: '';
			$filepath  = $filepath->getPath();
		}

		return new CURLFile( $filepath, $mime_type, $name );
	}

	/**
	 * @return void
	 */
	public function reset() {
		$this->handle && curl_reset( $this->handle );
	}

	/**
	 * @param int $errornum
	 *
	 * @return NULL|string
	 */
	public static function strerror( $errornum ) {
		return curl_strerror( $errornum );
	}

	/**
	 * @param string $string
	 *
	 * @return bool|string
	 */
	public function unescape( $string ) {
		return curl_unescape( $this->handle, $string );
	}
}
