<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Scripts
 * @package Thrive\Theme\AMP
 */
class Scripts {
	const VIDEO_SCRIPTS = [
		'youtube' => '<script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>',
		//todo uncomment when the cases are covered
		//'vimeo'   => '<script async custom-element="amp-vimeo" src="https://cdn.ampproject.org/v0/amp-vimeo-0.1.js"></script>',
		//'wistia'  => '<script async custom-element="amp-wistia-player" src="https://cdn.ampproject.org/v0/amp-wistia-player-0.1.js"></script>',
	];

	const MENU_SCRIPT = '<script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>';

	const ANALYTICS_SCRIPT = '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';

	/**
	 * Decide which scripts make sense to be loaded on this AMP page
	 *
	 * @param $post
	 *
	 * @return array|mixed
	 */
	public static function get_scripts( $post ) {
		$scripts = [];

		/* we almost always have menus in the header/footer, so always add this for now - todo: improvement - check if menus exist */
		$scripts[] = static::MENU_SCRIPT;

		$content = Main::$content;

		/* check the existing AMP video tags in order to decide if we want to add specific video scripts */
		foreach ( static::VIDEO_SCRIPTS as $type => $script_html ) {
			if ( strpos( $content, 'amp-' . $type ) !== false ) {
				$scripts[] = $script_html;
			}
		}

		if ( ! empty( Settings::get_analytics() ) ) {
			$scripts[] = static::ANALYTICS_SCRIPT;
		}

		$scripts = array_unique( $scripts );

		return implode( '', $scripts );
	}
}
