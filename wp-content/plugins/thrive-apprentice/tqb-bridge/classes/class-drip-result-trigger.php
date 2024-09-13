<?php

namespace TVA\TQB\Drip\Trigger;

use TVA\Drip\Trigger\Base;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


/**
 * Class Result
 *
 * @package TVA\TQB\Drip\Trigger
 * @project : thrive-apprentice
 */
class Result extends Base {
	/**
	 * Trigger Name
	 */
	const NAME = 'tqb_result';

	/**
	 * @var int ID of the quiz from the trigger
	 */
	protected $quiz_id = 0;

	/**
	 * @var null|string
	 */
	protected $when = null;

	/**
	 * @var null|string
	 */
	protected $when_cond = null;

	/**
	 * @param $product_id
	 * @param $post_id
	 *
	 * @return bool|mixed|void
	 */
	public function is_valid( $product_id, $post_id ) {
		/**
		 * Calls extra functionality implemented in Thrive Quiz Builder to check if the customer is able to view the drip content
		 *
		 * @param boolean $return - the default return value is "true" This also is returned when ThriveQuizBuilder is not active and the setting exists in the system
		 * @param array   $config - trigger data
		 */
		return apply_filters( 'tva_tqb_drip_valid_result_trigger', true, array(
			'quiz_id' => $this->quiz_id,
			'when'    => $this->when,
			'cond'    => $this->when_cond,
			'user_id' => get_current_user_id(), // This ID can be modified by reporting functionality. Therefore we send the ID to check if the trigger is valid for the user ID sent as param
		) );
	}
}
