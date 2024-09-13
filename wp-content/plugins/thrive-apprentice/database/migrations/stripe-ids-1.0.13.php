<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use Stripe\Exception\ApiErrorException;
use TVA\Stripe\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

try {
	Hooks::v2_update();
} catch ( ApiErrorException $e ) {
}
