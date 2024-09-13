<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TD_TTW_Request {

	protected $url;

	protected $args
		= array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'timeout' => 20,
		);

	protected $params = array();

	protected $route;

	/**
	 * TD_TTW_Request constructor.
	 *
	 * @param string $route
	 * @param array  $params
	 */
	public function __construct( $route, $params ) {

		$this->route  = $route;
		$this->params = $params;

		$this->url = TD_TTW_Connection::get_ttw_url();
	}

	/**
	 * Execute the request
	 *
	 * @return array|WP_Error
	 */
	public function execute() {

		$this->args['body'] = $this->get_body();

		return wp_remote_post( $this->get_url(), $this->args );
	}

	/**
	 * @return false|string
	 */
	public function get_body() {

		return json_encode( $this->params );
	}

	/**
	 * @return array
	 */
	public function get_params() {

		return $this->params;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function set_header( $name, $value ) {

		$this->args['headers'][ $name ] = $value;
	}

	/**
	 * @return mixed
	 */
	public function get_headers() {

		return $this->args['headers'];
	}

	/**
	 * @return string
	 */
	public function get_url() {

		return trim( $this->url, '/' ) . '/' . trim( $this->route, '/' );
	}
}
