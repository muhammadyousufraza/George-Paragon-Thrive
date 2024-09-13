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

class Gender extends AbstractEnum {

	const MALE   = 'm';
	const FEMALE = 'f';

	public function getFieldTypes() {
		return array(
			'm' => 'string',
			'f' => 'string',
		);
	}
}
