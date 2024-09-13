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
 * Class TCB_Tweet_Element
 */
class TCB_Tweet_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post to X (Twitter)', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'social';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'twitter-x';
	}

	/**
	 * Tweet element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_tw_qs';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'click-tweet' => array(
				'config' => array(
					'LabelText'        => array(
						'config'  => array(
							'label' => __( 'Label Text', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'TweetText'        => array(
						'config'  => array(
							'label' => __( 'Post Text', 'thrive-cb' ),
						),
						'extends' => 'Textarea',
					),
					'ShareUrlCheckbox' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Custom Share URL', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'ShareUrlInput'    => [
						'config'  => [
							'label'       => '',
							'placeholder' => 'http://',
						],
						'extends' => 'LabelInput',
					],
					'ViaUsername'      => array(
						'config'  => array(
							'label' => __( 'Via', 'thrive-cb' ) . '<span class="extra-input-prefix">@</span>',
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'typography'  => [
				'config' => [
					'FontColor' => [
						'important' => true,
					],
				],
			],
			'borders'     => [
				'disabled_controls' => [ 'Corners', 'hr' ],
				'config'            => [],
			],
			'background'  => [
				'config' => [
					'css_suffix' => ' .thrv_tw_qs_container',
				],
			],
			'shadow'      => [
				'config' => [
					'to' => '.thrv_tw_qs_container',
				],
			],
			'animation'   => [ 'hidden' => true ],
			'layout'      => [
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
				],
			],
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
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
				'url'  => 'click_to_tweet',
				'link' => 'https://help.thrivethemes.com/en/articles/4425790-how-to-use-the-click-to-tweet-element',
			],
		];
	}
}
