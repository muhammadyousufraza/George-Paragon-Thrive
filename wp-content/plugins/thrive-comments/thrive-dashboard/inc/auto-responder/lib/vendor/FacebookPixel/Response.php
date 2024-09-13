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

if ( ! class_exists( 'ResponseInterface', false ) ) {
	require_once __DIR__ . '/ResponseInterface.php';
}

class Response implements ResponseInterface {

	/**
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * @var int
	 */
	protected $statusCode;

	/**
	 * @var Headers
	 */
	protected $headers;

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var mixed
	 */
	protected $content;

	/**
	 * @return RequestInterface
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param RequestInterface $request
	 */
	public function setRequest( RequestInterface $request ) {
		$this->request = $request;
	}

	/**
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * @param int $status_code
	 */
	public function setStatusCode( $status_code ) {
		$this->statusCode = $status_code;
	}

	/**
	 * @return Headers
	 */
	public function getHeaders() {
		if ( $this->headers === null ) {
			$this->headers = new Headers();
		}

		return $this->headers;
	}

	/**
	 * @param Headers $headers
	 */
	public function setHeaders( Headers $headers ) {
		$this->headers = $headers;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param string $body
	 */
	public function setBody( $body ) {
		$this->body    = $body;
		$this->content = null;
	}

	/**
	 * @return array|null
	 */
	public function getContent() {
		if ( $this->content === null ) {
			$this->content = json_decode( $this->getBody(), true );
		}

		return $this->content;
	}
}
