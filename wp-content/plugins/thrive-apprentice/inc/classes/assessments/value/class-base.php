<?php

namespace TVA\Assessments\Value;

use Exception;
use TVA\Architect\Assessment\Main;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Base class for User Assessment Value
 *
 * Needs to be extended by other child classes
 */
class Base {

	/**
	 * @var numeric
	 */
	protected $user_assessment_id;

	/**
	 * @param string $type
	 *
	 * @return Base|Link|Quiz|Upload|Youtube
	 */
	public static function factory( $type ) {

		$class_name = __NAMESPACE__ . '\Base';

		switch ( $type ) {
			case Main::TYPE_QUIZ:
				$class_name = __NAMESPACE__ . '\Quiz';
				break;
			case Main::TYPE_EXTERNAL_LINK:
				$class_name = __NAMESPACE__ . '\Link';
				break;
			case Main::TYPE_YOUTUBE_LINK:
				$class_name = __NAMESPACE__ . '\Youtube';
				break;
			case Main::TYPE_UPLOAD:
				$class_name = __NAMESPACE__ . '\Upload';
				break;
		}

		return new $class_name();
	}

	/**
	 * @param numeric $user_assessment_id
	 *
	 * @return $this
	 */
	public function set_user_assessment_id( $user_assessment_id ) {
		$this->user_assessment_id = $user_assessment_id;

		return $this;
	}

	/**
	 * Saves the value in the database
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws Exception
	 */
	public function save( $value ) {
		if ( empty( $this->user_assessment_id ) ) {
			throw new Exception( 'Invalid assessment_id', 404 );
		}

		update_post_meta( $this->user_assessment_id, 'tva_assessment_value', $this->prepare_value( $value ) );
	}

	/**
	 * Returns the assessment value that is stored in the database for a user
	 *
	 * @return mixed
	 */
	public function get() {
		return get_post_meta( $this->user_assessment_id, 'tva_assessment_value', true );
	}

	/**
	 * Returns a configuration array needed to compute the value
	 *
	 * @return array
	 */
	public function get_value_config() {
		return [];
	}

	/**
	 * Prepares the value for save
	 * Is used to be extended by other child classes
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function prepare_value( $value ) {
		return $value;
	}
}
