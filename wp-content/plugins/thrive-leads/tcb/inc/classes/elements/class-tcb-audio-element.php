<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Audio_Element
 */
class TCB_Audio_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Audio', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'audio';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'audio';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_audio,.tve_audio_container';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'audio'      => [
				'config' => [
					'ExternalFields' => [
						'config'  => [
							'key'               => 'audio',
							'shortcode_element' => 'audio.tcb-audio',
						],
						'extends' => 'CustomFields',
					],
				],
			],
			'typography' => [ 'hidden' => true ],
			'background' => [ 'hidden' => true ],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'inner', 'text' ],
				],
			],
			'animation'  => [ 'hidden' => true ],
			'layout'     => [
				'config'            => [
					'Width'  => [
						'important' => true,
					],
					'Height' => [
						'css_suffix' => [ ' iframe', ' > :first-child' ],
					],
				],
				'disabled_controls' => [ 'Overflow', 'ScrollStyle' ],
			],
		];
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'audio_element',
				'link' => 'https://help.thrivethemes.com/en/articles/4425842-how-to-use-the-audio-element',
			],
		];
	}
}
