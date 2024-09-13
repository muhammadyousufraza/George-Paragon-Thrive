<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

use ArrayObject;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

interface AdapterInterface {

	/**
	 * @param Client $client
	 */
	public function __construct( Client $client );

	/**
	 * @return Client
	 */
	public function getClient();

	/**
	 * @return string
	 */
	public function getCaBundlePath();

	/**
	 * @return ArrayObject
	 */
	public function getOpts();

	/**
	 * @param ArrayObject $opts
	 *
	 * @return void
	 */
	public function setOpts( ArrayObject $opts );

	/**
	 * @param RequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function sendRequest( RequestInterface $request );
}
