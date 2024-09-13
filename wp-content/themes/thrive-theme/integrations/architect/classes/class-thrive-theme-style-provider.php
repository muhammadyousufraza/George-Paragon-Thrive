<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Theme_Style_Provider extends TCB_Style_Provider {

	/**
	 * @inheritDoc
	 *
	 * @param array $styles
	 */
	public function save_styles( $styles ) {
		thrive_typography()->set_style( $styles );
	}

	/**
	 * @inheritDoc
	 *
	 * @return array
	 */
	protected function read_styles() {
		return (array) thrive_typography()->get_style();
	}

	/**
	 * @inheritDoc
	 *
	 * @return array
	 */
	protected function defaults() {
		$defaults = parent::defaults();

		$link_prefixes = [
			'p',
			'li',
			'blockquote',
			'pre',
		];

		$defaults['link']['selector'] .= ', .tcb-post-content ' . implode( ' a, .tcb-post-content ', $link_prefixes ) . ' a';

		$plain_text_selector = is_admin() && ! Thrive_Utils::is_theme_typography() ? '.tcb-plain-text' : 'body, .tcb-plain-text';

		/**
		 * Allow changing default plaintext selector while the typography is generated
		 *
		 * @param $selector
		 */
		$defaults['plaintext']['selector'] = apply_filters( 'thrive_theme_typography_plain_selector', $plain_text_selector );

		return $defaults;
	}
}
