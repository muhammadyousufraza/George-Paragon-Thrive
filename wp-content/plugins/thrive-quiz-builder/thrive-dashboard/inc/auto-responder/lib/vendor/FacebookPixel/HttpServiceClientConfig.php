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

if ( ! class_exists( 'Singleton', false ) ) {
	require_once __DIR__ . '/Singleton.php';
}

class HttpServiceClientConfig extends Singleton {
	protected $client       = null;
	protected $access_token = null;
	protected $appsecret    = null;

	public function __construct() {
	}

	public function getClient() {
		return $this->client;
	}

	public function getAccessToken() {
		return $this->access_token;
	}

	public function getAppsecret() {
		return $this->appsecret;
	}

	public function setClient( $client ) {
		$this->client = $client;
	}

	public function setAccessToken( $access_token ) {
		$this->access_token = $access_token;
	}

	public function setAppsecret( $appsecret ) {
		$this->appsecret = $appsecret;
	}
}
