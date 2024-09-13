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

final class HttpMethod extends AbstractEnum {
  const POST = 'POST';
  const PUT = 'PUT';
  const GET = 'GET';
  const DELETE = 'DELETE';
}
