<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TPM_Request {

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

	public function __construct( $route, $params ) {

		$this->route  = $route;
		$this->params = $params;

		$this->url = Thrive_Product_Manager::get_ttw_url();
	}

	public function execute() {

		$this->args['body'] = $this->get_body();

		return wp_remote_post( $this->get_url(), $this->args );
	}

	public function get_body() {

		return json_encode( $this->params );
	}

	public function get_params() {

		return $this->params;
	}

	public function set_header( $name, $value ) {

		$this->args['headers'][ $name ] = $value;
	}

	public function get_headers() {

		return $this->args['headers'];
	}

	public function get_url() {

		$url = trim( $this->url, '/' ) . '/' . trim( $this->route, '/' );

		return $url;
	}
}
