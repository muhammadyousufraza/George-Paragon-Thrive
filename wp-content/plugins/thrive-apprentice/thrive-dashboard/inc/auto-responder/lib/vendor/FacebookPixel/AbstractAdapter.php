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

if ( ! class_exists( 'AdapterInterface', false ) ) {
	require_once __DIR__ . '/AdapterInterface.php';
}

abstract class AbstractAdapter implements AdapterInterface {

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @param Client $client
	 */
	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * @return Client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * @return string
	 */
	public function getCaBundlePath() {
		return $this->getClient()->getCaBundlePath();
	}
}
