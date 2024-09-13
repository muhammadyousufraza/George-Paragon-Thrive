<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Notifications;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Content_Wrapper
 *
 * @package TCB\Notifications
 */
class Notifications_Content_Wrapper extends \TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Notification', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.notifications-content-wrapper';
	}

	public function own_components() {
		$components                                = parent::own_components();
		$components['animation']                   = [ 'hidden' => true ];
		$components['typography']                  = [ 'hidden' => true ];
		$components['layout']['disabled_controls'] = [ 'Alignment', 'Display', '.tve-advanced-controls' ];
		/* only apply the styles to the currently visible notification state */
		$components['layout']['config']     = [ 'to' => '.notifications-content:visible' ];
		$components['background']['config'] = [ 'to' => '.notifications-content:visible' ];
		$components['borders']['config']    = [ 'to' => '.notifications-content:visible' ];
		$components['shadow']['config']     = [ 'to' => '.notifications-content:visible' ];
		$components['notification']         = [
			'config' => [
				'DisplayPosition'    => [
					'config'  => [
						'name'          => __( 'Display position', 'thrive-cb' ),
						'large_buttons' => true,
						'buttons'       => [
							[
								'text'  => '',
								'value' => 'top-left',
							],
							[
								'text'  => '',
								'value' => 'top-center',
							],
							[
								'text'  => '',
								'value' => 'top-right',
							],
							[
								'text'  => '',
								'value' => 'middle-left',
							],
							[
								'text'  => '',
								'value' => 'middle-center',
							],
							[
								'text'  => '',
								'value' => 'middle-right',
							],
							[
								'text'  => '',
								'value' => 'bottom-left',
							],
							[
								'text'  => '',
								'value' => 'bottom-center',
							],
							[
								'text'  => '',
								'value' => 'bottom-right',
							],

						],
					],
					'extends' => 'ButtonGroup',
				],
				'VerticalSpacing'    => [
					'config'  => [
						'name'    => __( 'Top spacing', 'thrive-cb' ),
						'min'     => '0',
						'default' => '50',
						'um'      => [ 'px' ],
					],
					'extends' => 'Input',
				],
				'HorizontalSpacing'  => [
					'config'  => [
						'name'    => __( 'Side spacing', 'thrive-cb' ),
						'min'     => '0',
						'default' => '50',
						'um'      => [ 'px' ],
					],
					'extends' => 'Input',
				],
				'AnimationDirection' => [
					'config'  => [
						'name'    => __( 'Animation direction', 'thrive-cb' ),
						'default' => 'down',
						'options' => [
							[
								'value' => 'none',
								'name'  => __( 'No animation', 'thrive-cb' ),
							],
							[
								'value' => 'down',
								'name'  => __( 'Down', 'thrive-cb' ),
							],
							[
								'value' => 'up',
								'name'  => __( 'Up', 'thrive-cb' ),
							],
							[
								'value' => 'right',
								'name'  => __( 'Right', 'thrive-cb' ),
							],
							[
								'value' => 'left',
								'name'  => __( 'Left', 'thrive-cb' ),
							],
						],
					],
					'extends' => 'Select',
				],
				'AnimationTime'      => [
					'config'  => [
						'min'     => '0',
						'max'     => '10',
						'default' => '3',
						'step'    => '0.1',
						'label'   => __( 'Show for (s)', 'thrive-cb' ),
						'um'      => [ 's' ],
					],
					'extends' => 'Slider',
				],
				'MaximumWidth'       => [
					'config'  => [
						'min'     => '100',
						'max'     => '2000',
						'default' => '200',
						'label'   => __( 'Maximum Width', 'thrive-cb' ),
						'um'      => [ 'px' ],
					],
					'extends' => 'Slider',
				],
				'MinimumHeight'      => [
					'config'  => [
						'min'     => '1',
						'max'     => '1000',
						'default' => '200',
						'label'   => __( 'Minimum Height', 'thrive-cb' ),
						'um'      => [ 'px' ],
					],
					'extends' => 'Slider',
				],
				'VerticalPosition'   => [
					'config'  => [
						'name'    => __( 'Content Align', 'thrive-cb' ),
						'buttons' => [
							[
								'icon'  => 'top',
								'value' => 'flex-start',
							],
							[
								'icon'  => 'vertical',
								'value' => 'center',
							],
							[
								'icon'  => 'bot',
								'value' => 'flex-end',
							],
						],
					],
					'extends' => 'ButtonGroup',
				],
			],
		];

		return $components;
	}

	public function hide() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}
}

return new Notifications_Content_Wrapper( 'notification' );
