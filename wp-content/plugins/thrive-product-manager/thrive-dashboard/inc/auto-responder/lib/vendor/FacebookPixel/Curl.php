<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Curl extends AbstractCurl {

	/**
	 * @throws RuntimeException
	 */
	public function __construct() {
		parent::__construct();
		if ( version_compare( PHP_VERSION, '5.5.0' ) >= 0 ) {
			throw new RuntimeException( "Unsupported Curl version" );
		}
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function escape( $string ) {
		return rawurlencode( $string );
	}

	/**
	 * @param int $bitmask
	 *
	 * @return int
	 */
	public function pause( $bitmask ) {
		return 0;
	}

	/**
	 * FIXME should introduce v2.10 breaking change:
	 * implement abstract support for FileParameter in AdapterInterface
	 *
	 * @param string|FileParameter $filepath
	 *
	 * @return string
	 */
	public function preparePostFileField( $filepath ) {
		$mime_type = $name = '';
		if ( $filepath instanceof FileParameter ) {
			$mime_type = $filepath->getMimeType() !== null
				? sprintf( ';type=%s', $filepath->getMimeType() )
				: '';
			$name      = $filepath->getName() !== null
				? sprintf( ';filename=%s', $filepath->getName() )
				: '';
			$filepath  = $filepath->getPath();
		}

		return sprintf( '@%s%s%s', $filepath, $mime_type, $name );
	}

	/**
	 * @return void
	 */
	public function reset() {
		$this->handle && curl_close( $this->handle );
		$this->handle = curl_init();
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
