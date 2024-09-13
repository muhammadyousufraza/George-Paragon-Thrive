<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Emoji {

	const  DISABLE_EMOJI = '_tve_disable_emoji';

	/**
	 * Checks if emoji scripts are disabled on a certain page
	 *
	 * @return bool
	 */
	public static function is_emoji_disabled() {
		return ! empty( get_option( static::DISABLE_EMOJI ) );
	}


}