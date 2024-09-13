<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once 'class-tcb-text-element.php';

/**
 * Class TCB_Image_Caption_Element
 */
class TCB_Image_Caption_Element extends TCB_Text_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Image Caption', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.wp-caption-text.thrv-inline-text';
	}

	/**
	 * Hide Element From Sidebar Menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['text']['config']['ImageCaptionPosition'] = array(
			'config'  => array(
				'default' => 'none',
				'name'    => __( 'Position', 'thrive-cb' ),
				'options' => array(
					array(
						'name'  => __( 'Below image', 'thrive-cb' ),
						'value' => 'below',
					),
					array(
						'name'  => __( 'Above image', 'thrive-cb' ),
						'value' => 'above',
					),
					array(
						'name'  => __( 'Inside image', 'thrive-cb' ),
						'value' => 'inside',
					),
				),
			),
			'extends' => 'Select',
		);

		$components['text']['config']['CaptionVerticalPosition'] = array(
			'config'  => array(
				'name'    => __( 'Vertical position', 'thrive-cb' ),
				'buttons' => [
					[
						'icon'    => 'top',
						'default' => true,
						'value'   => 'top',
					],
					[
						'icon'  => 'vertical',
						'value' => 'center',
					],
					[
						'icon'  => 'bot',
						'value' => 'bottom',
					],
				],
			),
			'extends' => 'ButtonGroup',
		);


		$components['text']['config']['TextAlign'] = array(
			'config'  => array(
				'name'    => __( 'Alignment', 'thrive-cb' ),
				'buttons' => [
					[
						'icon'    => 'format-align-left',
						'text'    => '',
						'value'   => 'left',
						'default' => true,
					],
					[
						'icon'  => 'format-align-center',
						'text'  => '',
						'value' => 'center',
					],
					[
						'icon'  => 'format-align-right',
						'text'  => '',
						'value' => 'right',
					],
					[
						'icon'  => 'format-align-justify',
						'text'  => '',
						'value' => 'justify',
					],
				],
			),
			'extends' => 'ButtonGroup',
		);
		$components['image_caption']               = $components['text'];

		unset( $components['text'], $components['layout'], $components['borders'], $components['animation'], $components['background'], $components['responsive'], $components['styles-templates'] );

		$components['scroll'] = [ 'hidden' => true ];

		return $components;
	}
}
