<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/12/2017
 * Time: 2:14 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Reveal_Element
 */
class TCB_Reveal_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Content Reveal', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'container,box';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'reveal';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_content_reveal'; //For backwards compatibility
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'reveal'    => array(
				'config' => array(
					'RedirectURL' => array(
						'config'  => array(
							'label' => __( 'Redirect to URL', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'Time'        => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '0',
							'max'     => '21600',
							'label'   => __( 'Reveal Content After', 'thrive-cb' ),
							'css'     => 'width',
						),
						'extends' => 'TimeSlider',
					),
					'AutoScroll'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Autoscroll to content when revealed', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'animation' => [ 'hidden' => true ],
		);
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
				'url'  => 'content_reveal',
				'link' => 'https://help.thrivethemes.com/en/articles/4425778-how-to-use-the-content-reveal-element',
			],
		];
	}
}
