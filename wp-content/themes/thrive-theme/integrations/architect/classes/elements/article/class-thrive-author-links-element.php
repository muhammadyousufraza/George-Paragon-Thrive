<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Author_Links_Element
 */
class Thrive_Author_Links_Element extends Thrive_Author_Follow_Element {
	/**
	 * Element name
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Business Social Links', 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'business-social-links';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive_author_links';
	}

	public function hide() {
		return true;
	}

	/**
	 * @return string
	 */
	public function category() {
		return Thrive_Defaults::theme_group_label();
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['thrive_author_links'] = $components['thrive_author_follow'];

		$styles = [];

		foreach ( range( 1, 8 ) as $i ) {
			$styles[ 'tve_links_style_' . $i ] = 'Style ' . $i;
		}

		$components['thrive_author_links']['config']['stylePicker'] = [
			'config' => [
				'label' => __( 'Change style', 'thrive-theme' ),
				'match' => 'tve_links_style_',
				'items' => $styles,
			],
		];

		unset( $components['thrive_author_follow'] );

		return $components;
	}
}

return new Thrive_Author_Links_Element( 'thrive_author_links' );
