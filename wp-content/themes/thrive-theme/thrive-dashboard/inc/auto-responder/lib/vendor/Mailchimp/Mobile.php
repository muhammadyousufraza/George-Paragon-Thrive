<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Mailchimp_Mobile {
	public function __construct( Thrive_Dash_Api_Mailchimp $master ) {
		$this->master = $master;
	}

}


