<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 11.08.2014
 * Time: 16:03
 */
class TCB_Thrive_CSS_Animation extends TCB_Event_Action_Abstract {

	protected $key = 'thrive_animation';

	/**
	 * available CSS animations
	 *
	 * @var array
	 */
	protected $_animations
		= array(
			'slide_top'              => 'Top to bottom',
			'slide_bottom'           => 'Bottom to top',
			'slide_left'             => 'Left to right',
			'slide_right'            => 'Right to left',
			'appear'                 => 'Appear from Centre (Zoom In)',
			'zoom_out'               => 'Zoom Out',
			'fade'                   => 'Fade in',
			'rotate'                 => 'Rotational',
			'roll_in'                => 'Roll In',
			'roll_out'               => 'Roll Out',
			'grow'                   => 'Grow',
			'shrink'                 => 'Shrink',
			'pulse'                  => 'Pulse',
			'pulse_grow'             => 'Pulse Grow',
			'pulse_shrink'           => 'Pulse Shrink',
			'push'                   => 'Push',
			'pop'                    => 'Pop',
			'bounce_in'              => 'Bounce In',
			'bounce_out'             => 'Bounce Out',
			'bob'                    => 'Bob',
			'hang'                   => 'Hang',
			'wobble_horizontal'      => 'Wobble Horizontal',
			'wobble_vertical'        => 'Wobble Vertical',
			'buzz'                   => 'Buzz',
			'buzz_out'               => 'Buzz Out',
			'forward'                => 'Forward',
			'backward'               => 'Backward',
			'sweep_to_right'         => 'Sweep to right',
			'sweep_to_left'          => 'Sweep to left',
			'sweep_to_bottom'        => 'Sweep to bottom',
			'sweep_to_top'           => 'Sweep to top',
			'bounce_to_right'        => 'Bounce to right',
			'bounce_to_left'         => 'Bounce to left',
			'bounce_to_bottom'       => 'Bounce to bottom',
			'bounce_to_top'          => 'Bounce to top',
			'radial_out'             => 'Radial out',
			'radial_in'              => 'Radial in',
			'rectangle_in'           => 'Rectangle in',
			'rectangle_out'          => 'Rectangle out',
			'shutter_out_horizontal' => 'Shutter out horizontal',
			'shutter_out_vertical'   => 'Shutter out vertical',
		);

	/**
	 *
	 * @return array
	 */
	public static function get_config() {
		$config = array(
			'slide'      => array(
				'title' => __( 'Sliding', 'thrive-cb' ),
				'items' => array(
					'slide_top'    => array(
						'title'   => __( 'Slide, top', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
					'slide_bottom' => array(
						'title'   => __( 'Slide, bottom', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
					'slide_right'  => array(
						'title'   => __( 'Slide, right', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
					'slide_left'   => array(
						'title'   => __( 'Slide, left', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
				),
			),
			'zoom'       => array(
				'title' => __( 'Zoom (Appear)', 'thrive-cb' ),
				'items' => array(
					'appear'   => array(
						'title'   => __( 'Zoom in', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
					'zoom_out' => array(
						'title'   => __( 'Zoom out', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
				),
			),
			'modify'     => array(
				'title' => __( 'Modify', 'thrive-cb' ),
				'items' => array(
					'grow'              => array(
						'title'   => __( 'Grow', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport', 'mouseover' ],
					),
					'shrink'            => array(
						'title'   => __( 'Shrink', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport', 'mouseover' ],
					),
					'pulse'             => array(
						'title'   => __( 'Pulse', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'pulse_grow'        => array(
						'title'   => __( 'Pulse Grow', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'pulse_shrink'      => array(
						'title'   => __( 'Pulse Shrink', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'push'              => array(
						'title'   => __( 'Push', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'pop'               => array(
						'title'   => __( 'Pop', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bounce_in'         => array(
						'title'   => __( 'Bounce In', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bounce_out'        => array(
						'title'   => __( 'Bounce Out', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bob'               => array(
						'title'   => __( 'Bob', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'hang'              => array(
						'title'   => __( 'Hang', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'wobble_horizontal' => array(
						'title'   => __( 'Wobble Horizontal', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'wobble_vertical'   => array(
						'title'   => __( 'Wobble Vertical', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'buzz'              => array(
						'title'   => __( 'Buzz', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'buzz_out'          => array(
						'title'   => __( 'Buzz Out', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'forward'           => array(
						'title'   => __( 'Forward', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'backward'          => array(
						'title'   => __( 'Backward', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
				),
			),
			'background' => array(
				'title' => __( 'Background', 'thrive-cb' ),
				'items' => array(
					'sweep_to_right'         => array(
						'title'   => __( 'Sweep to right', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'sweep_to_left'          => array(
						'title'   => __( 'Sweep to left', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'sweep_to_bottom'        => array(
						'title'   => __( 'Sweep to bottom', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'sweep_to_top'           => array(
						'title'   => __( 'Sweep to top', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bounce_to_right'        => array(
						'title'   => __( 'Bounce to right', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bounce_to_left'         => array(
						'title'   => __( 'Bounce to left', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bounce_to_bottom'       => array(
						'title'   => __( 'Bounce to bottom', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'bounce_to_top'          => array(
						'title'   => __( 'Bounce to top', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'radial_out'             => array(
						'title'   => __( 'Radial out', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'radial_in'              => array(
						'title'   => __( 'Radial in', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'rectangle_in'           => array(
						'title'   => __( 'Rectangle in', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'rectangle_out'          => array(
						'title'   => __( 'Rectangle out', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'shutter_out_horizontal' => array(
						'title'   => __( 'Shutter out horizontal', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
					'shutter_out_vertical'   => array(
						'title'   => __( 'Shutter out vertical', 'thrive-cb' ),
						'trigger' => [ 'mouseover' ],
					),
				),
			),
			'other'      => array(
				'title' => __( 'Other (Appear)', 'thrive-cb' ),
				'items' => array(
					'fade_in' => array(
						'title'   => __( 'Fade in', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
					'rotate'  => array(
						'title'   => __( 'Rotate', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
					'roll_in' => array(
						'title'   => __( 'Roll in', 'thrive-cb' ),
						'trigger' => [ 'tve-viewport' ],
					),
				),
			),
		);

		return apply_filters( 'tcb_animations', $config );
	}

	/**
	 * Should return the user-friendly name for this Action
	 *
	 * @return string
	 */
	public function getName() {
		return 'Animation';
	}

	/**
	 * Should output the settings needed for this Action when a user selects it from the list
	 *
	 * @param mixed $data existing configuration data, etc
	 */
	public function renderSettings( $data ) {
		return $this->renderTCBSettings( 'animation', $data );
	}

	/**
	 * Should return an actual string containing the JS function that's handling this action.
	 * The function will be called with 3 parameters:
	 *      -> event_trigger (e.g. click, dblclick etc)
	 *      -> action_code (the action that's being executed)
	 *      -> config (specific configuration for each specific action - the same configuration that has been setup in the settings section)
	 *
	 * Example (php): return 'function (trigger, action, config) { console.log(trigger, action, config); }';
	 *
	 * The function will be called in the context of the element
	 *
	 * The output MUST be a valid JS function definition.
	 *
	 * @return string the JS function definition (declaration + body)
	 */
	public function getJsActionCallback() {
		return tcb_template( 'actions/animation.js', null, true );
	}

	public function getSummary() {
		if ( ! empty( $this->config ) ) {
			return ': ' . $this->_animations[ $this->config['anim'] ];
		}
	}

	public function get_editor_js_view() {
		return 'Animation';
	}

	public function render_editor_settings() {
		tcb_template( 'actions/animation', self::get_config() );
	}

	public function get_options() {
		$labels   = [
			'__config_key' => 'anim',
		];
		$triggers = [];
		foreach ( self::get_config() as $item ) {
			foreach ( $item['items'] as $key => $data ) {
				$labels[ $key ]   = $data['title'];
				$triggers[ $key ] = $data['trigger'];
			}
		}

		return [
			'labels'   => $labels,
			'triggers' => $triggers,
		];
	}
}
