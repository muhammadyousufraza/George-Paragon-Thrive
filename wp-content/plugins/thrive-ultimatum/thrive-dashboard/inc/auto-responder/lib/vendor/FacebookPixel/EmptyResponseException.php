<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
if ( ! class_exists( 'RequestException', false ) ) {
	require_once __DIR__ . '/RequestException.php';
}
class EmptyResponseException extends RequestException {

	/**
	 * @param ResponseInterface $response
	 */
	public function __construct( ResponseInterface $response ) {
		$content = array(
			'error' => array(
				'message' => 'Empty Response',
			),
		);
		$response->setBody( json_encode( $content ) );
		parent::__construct( $response );
	}
}
