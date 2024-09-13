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

interface ResponseInterface {

	/**
	 * @return RequestInterface
	 */
	public function getRequest();

	/**
	 * @param RequestInterface $request
	 */
	public function setRequest( RequestInterface $request );

	/**
	 * @return int
	 */
	public function getStatusCode();

	/**
	 * @param int $status_code
	 */
	public function setStatusCode( $status_code );

	/**
	 * @return Headers
	 */
	public function getHeaders();

	/**
	 * @param Headers $headers
	 */
	public function setHeaders( Headers $headers );

	/**
	 * @return string
	 */
	public function getBody();

	/**
	 * @param string $body
	 */
	public function setBody( $body );

	/**
	 * @return array|null
	 */
	public function getContent();
}
