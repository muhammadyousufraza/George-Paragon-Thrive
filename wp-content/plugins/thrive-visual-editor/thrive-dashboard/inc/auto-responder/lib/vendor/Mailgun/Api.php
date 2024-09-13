<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Mailgun_Api {
	const API_USER = "api";
	const SDK_VERSION = "1.7";
	const SDK_USER_AGENT = "mailgun-sdk-php";
	const RECIPIENT_COUNT_LIMIT = 1000;
	const CAMPAIGN_ID_LIMIT = 3;
	const TAG_LIMIT = 3;
	const DEFAULT_TIME_ZONE = "UTC";
}
 