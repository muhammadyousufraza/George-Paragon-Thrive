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

if ( ! class_exists( 'AbstractEnum', false ) ) {
	require_once __DIR__ . '/AbstractEnum.php';
}

class ActionSource extends AbstractEnum {

	/**
	 * Conversion happened over email.
	 */
	const EMAIL = 'email';

	/**
	 * Conversion was made on your website.
	 */
	const WEBSITE = 'website';

	/**
	 * Conversion was made using your app.
	 */
	const APP = 'app';

	/**
	 * Conversion was made over the phone.
	 */
	const PHONE_CALL = 'phone_call';

	/**
	 * Conversion was made via a messaging app, SMS, or online messaging feature.
	 */
	const CHAT = 'chat';

	/**
	 * Conversion was made in person at your physical store.
	 */
	const PHYSICAL_STORE = 'physical_store';

	/**
	 * Conversion happened automatically, for example, a subscription renewal that’s set on auto-pay each month.
	 */
	const SYSTEM_GENERATED = 'system_generated';

	/**
	 * Conversion happened in a way that is not listed.
	 */
	const OTHER = 'other';
}
