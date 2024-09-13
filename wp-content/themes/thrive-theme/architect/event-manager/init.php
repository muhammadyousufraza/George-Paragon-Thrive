<?php
/** setup the event and event callbacks( = actions) manager */

require_once dirname( __FILE__ ) . '/classes/TCB_Event_Action_Abstract.php';
require_once dirname( __FILE__ ) . '/classes/TCB_Event_Trigger_Abstract.php';

/**
 * get all available Event triggers
 *
 * Each event trigger a a way for the user to interact with a DOM element on a page
 * each trigger must have a javascript name for the event, e.g. 'click', 'dblclick' as a key and an existing class as value
 *
 * each Event Trigger class must override TCB_Event_Trigger_Abstract
 *
 * @param $scope can be empty or 'page' for now
 *
 * @return TCB_Event_Trigger_Abstract[]
 *
 * @see TCB_Event_Trigger_Abstract
 */
function tve_get_event_triggers( $scope = '' ) {
	/* make sure these will not get overwritten */
	$tcb_triggers = [
		''     => [
			'click'        => 'TCB_Event_Trigger_Click',
			'mouseover'    => 'TCB_Event_Trigger_Mouseover',
			'mouseenter'   => 'TCB_Event_Trigger_Mouseover',
			'tve-viewport' => 'TCB_Event_Trigger_Viewport',
		],
		'page' => [
			'exit'  => 'TCB_Event_Trigger_Exit_Intent',
			'timer' => 'TCB_Event_Trigger_Timer',
		],
	];
	$tcb_triggers = $tcb_triggers[ $scope ];

	$api_triggers = apply_filters( 'tcb_event_triggers', [], [ 'scope' => $scope ] );

	if ( is_array( $api_triggers ) ) {
		foreach ( $api_triggers as $key => $class_name ) {
			$key = strtolower( $key );
			if ( isset( $tcb_triggers[ $key ] ) || ! is_string( $class_name ) || ! preg_match( '#^([a-z0-9-_]+)$#', $key ) || ! class_exists( $class_name ) ) {
				continue;
			}
			$tcb_triggers[ $key ] = $class_name;
		}
	}

	$triggers = [];
	foreach ( $tcb_triggers as $key => $class ) {
		$triggers[ $key ] = TCB_Event_Trigger_Abstract::triggerFactory( $class );
	}

	return $triggers;
}

/**
 * Get a list with all available actions as pairs key => action class
 *
 * @return array
 */
function tcb_get_all_action_classes() {
	$classes = [
		'thrive_lightbox'  => 'TCB_Thrive_Lightbox',
		'close_lightbox'   => 'TCB_Thrive_LightboxClose',
		'thrive_animation' => 'TCB_Thrive_CSS_Animation',
		'thrive_zoom'      => 'TCB_Thrive_Image_Zoom',
		'thrive_video'     => 'TCB_Thrive_Video_Popup',
		'thrive_tooltip'   => 'TCB_Thrive_Tooltip',
	];

	/**
	 * Allow adding php classes to the available Actions
	 */
	return apply_filters( 'tcb_event_action_classes', $classes );
}

/**
 * get all available event actions
 *
 * Event Actions are behaviours that happen after a user interaction via an event, such as 'click'
 *
 * each event action must extend the TCB_Event_Action_Abstract class and implement its required methods
 *
 * All the classes that are specified here MUST be previously loaded
 * Each array key has to be a lowercase, unique identifier for the Action
 *
 * @return TCB_Event_Action_Abstract[] with action_key => Action Name (Action name will be taken from the class representing the Action)
 */
function tve_get_event_actions( $scope = '' ) {
	$actions = tcb_get_all_action_classes();
	foreach ( $actions as $key => $class ) {
		$actions[ $key ] = TCB_Event_Action_Abstract::actionFactory( $class );
	}

	/* deprecated - Wistia popups - added here for backwards compatibility */
	$actions['thrive_wistia'] = TCB_Event_Action_Abstract::actionFactory( 'TCB_Thrive_Video_Popup' );

	return $actions;
}

/**
 * get all available event actions
 *
 * Event Actions are behaviours that happen after a user interaction via an event, such as 'click'
 *
 * each event action must extend the TCB_Event_Action_Abstract class and implement its required methods
 *
 * All the classes that are specified here MUST be previously loaded
 * Each array key has to be a lowercase, unique identifier for the Action
 *
 * @return TCB_Event_Action_Abstract[] with action_key => Action Name (Action name will be taken from the class representing the Action)
 */
function tve_get_event_actions_old( $scope = '' ) {
	$post_id           = empty( $_POST['post_id'] ) ? get_the_ID() : absint( $_POST['post_id'] );
	$tcb_event_actions = [
		''     => [
			'thrive_lightbox'  => [
				'class' => 'TCB_Thrive_Lightbox',
				'order' => 10,
			],
			'thrive_animation' => [
				'class' => 'TCB_Thrive_CSS_Animation',
				'order' => 30,
			],
			'thrive_zoom'      => [
				'class' => 'TCB_Thrive_Image_Zoom',
				'order' => 40,
			],
			'thrive_wistia'    => [
				'class' => 'TCB_Thrive_Wistia',
				'order' => 50,
			],
			'thrive_tooltip'   => [
				'class' => 'TCB_Thrive_Tooltip',
				'order' => 60,
			],
		],
		'page' => [
			'thrive_lightbox' => [
				'class' => 'TCB_Thrive_Lightbox',
				'order' => 10,
			],
		],
	];

	$tcb_event_actions = $tcb_event_actions[ $scope ];
	$tcb_event_actions = apply_filters( 'tcb_event_actions', $tcb_event_actions, $scope, $post_id );

	uasort( $tcb_event_actions, 'tcb_event_manager_sort_actions' );

	$actions = [];
	foreach ( $tcb_event_actions as $key => $data ) {
		$class           = $data['class'];
		$actions[ $key ] = TCB_Event_Action_Abstract::actionFactory( $class );
	}

	return $actions;
}

/**
 * @param $a
 * @param $b
 *
 * @return int
 */
function tcb_event_manager_sort_actions( $a, $b ) {
	return $a['order'] < $b['order'] ? - 1 : 1;
}

/**
 * Returns a list of TCB actions for the editor page - structured in tabs and sub-sections
 *
 * @return array
 */
function tcb_get_editor_actions() {
	$actions = tcb_get_all_action_classes();

	$action_tabs = array(
		'animation' => array(
			'title'     => __( 'CSS Animation', 'thrive-cb' ),
			'icon'      => 'animation2',
			'class'     => $actions['thrive_animation'],
			'on_states' => [ 'default', 'hover' ],
		),
		'popup'     => array(
			'title'     => __( 'Popups', 'thrive-cb' ),
			'trigger'   => 'click',
			'icon'      => 'open-lightbox2',
			'on_states' => [ 'default' ],
			'actions'   => [
				'thrive_lightbox' => [
					'class' => $actions['thrive_lightbox'],
					'order' => 10,
				],
				'thrive_zoom'     => [
					'class' => $actions['thrive_zoom'],
					'order' => 30,
				],
				'thrive_video'    => [
					'class' => $actions['thrive_video'],
					'order' => 40,
				],
				'close_lightbox'  => [
					'class' => $actions['close_lightbox'],
					'order' => 50,
				],
			],
		),
		'tooltip'   => array(
			'title'     => __( 'Display tooltip', 'thrive-cb' ),
			'trigger'   => 'mouseover',
			'icon'      => 'tooltip-text',
			'class'     => $actions['thrive_tooltip'],
			'on_states' => [ 'hover' ],
		),
		'link'      => array(
			'title'     => __( 'Create hyperlink', 'thrive-cb' ),
			'icon'      => 'link-variant2',
			'on_states' => [ 'default' ],
		),
		'custom'    => array(
			'title'     => __( 'Custom integrations', 'thrive-cb' ),
			'icon'      => 't-lightbox',
			'actions'   => [],
			'on_states' => [ 'default' ],
		),
	);
	$action_tabs = apply_filters( 'tcb_event_manager_action_tabs', $action_tabs );
	if ( get_post_type() !== 'tcb_lightbox' ) {
		unset( $action_tabs['popup']['actions']['close_lightbox'] );
	}

	foreach ( $action_tabs as $key => $data ) {
		if ( isset( $data['class'] ) ) {
			$instance = TCB_Event_Action_Abstract::actionFactory( $data['class'] );
			if ( isset( $data['available'] ) ) {
				$instance->set_is_available( $data['available'] );
			}
			$action_tabs[ $key ]['instance'] = $instance;
		} elseif ( isset( $data['actions'] ) ) {
			uasort( $data['actions'], 'tcb_event_manager_sort_actions' );
			$action_tabs[ $key ]['actions'] = $data['actions'];
			foreach ( $data['actions'] as $action_key => $action ) {
				$instance = TCB_Event_Action_Abstract::actionFactory( $action['class'] );
				if ( isset( $action['available'] ) ) {
					$instance->set_is_available( $action['available'] );
				}
				$action_tabs[ $key ]['actions'][ $action_key ]['instance'] = $instance;
			}
		}
		if ( $key !== 'link' && empty( $data['class'] ) && empty( $data['actions'] ) ) {
			unset( $action_tabs[ $key ] );
		}
	}

	return $action_tabs;
}

/**
 * Build the javascript config object for the animation and actions component
 *
 * @return array
 */
function tcb_event_manager_config() {
	$tabs = tcb_get_editor_actions();
	/** @var TCB_Event_Action_Abstract[] $actions */
	$config = $actions = [];
	foreach ( $tabs as $k => $tab ) {
		/** @var TCB_Event_Action_Abstract[] $tab */
		if ( isset( $tab['class'] ) ) {
			$actions[ $tab['instance']->get_key() ] = $tab['instance'];
		} elseif ( isset( $tab['actions'] ) ) {
			foreach ( $tab['actions'] as $action_key => $action ) {
				/** @var TCB_Event_Action_Abstract[] $action */
				$actions[ $action_key ] = $action['instance'];
			}
		}
		$config['tabs'][ $k ]['visible'] = isset( $tab['visible'] ) ? $tab['visible'] : true;
		//tabs will be toggled based on state
		$config['tabs'][ $k ]['on_states'] = isset( $tab['on_states'] ) ? $tab['on_states'] : [];
	}

	$triggers = tve_get_event_triggers();
	foreach ( $actions as $key => $action ) {
		$config['actions'][ $key ]            = $action->get_options();
		$config['actions'][ $key ]['visible'] = $action->is_available();
	}
	foreach ( $triggers as $key => $trigger ) {
		$config['triggers'][ $key ] = $trigger->get_options();
	}

	return $config;
}
