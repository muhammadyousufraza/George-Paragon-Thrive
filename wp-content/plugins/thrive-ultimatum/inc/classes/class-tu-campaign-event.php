<?php

/**
 * Created by PhpStorm.
 * User: Pop Aurelian
 * Date: 10-Mar-16
 * Time: 12:32 PM
 */
class TU_Campaign_Event {


	const TRIGGER_SPECIFIC   = 'specific';
	const TRIGGER_CONVERSION = 'conversion';
	const TYPE_END           = 'end';
	const TYPE_MOVE          = 'move';


	/**
	 * TU_Campaign_Event constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get triggers
	 *
	 * @return array
	 */
	public static function get_triggers() {

		if ( function_exists( 'tve_leads_get_groups' ) ) {
			$items[ self::TRIGGER_CONVERSION ] = array(
				'title' => __( 'User subscription', 'thrive-ult' ),
			);
		}
		$items[ self::TRIGGER_SPECIFIC ] = array(
			'title' => __( 'Visit to conversion page', 'thrive-ult' ),
		);


		return $items;
	}

	/**
	 * Get triggers
	 *
	 * @return array
	 */
	public static function get_types() {

		$items = array(
			self::TYPE_END  => array(
				'title' => __( 'End Campaign', 'thrive-ult' ),
			),
			self::TYPE_MOVE => array(
				'title' => __( 'Move to another Campaign', 'thrive-ult' ),
			),
		);


		return $items;
	}
}
