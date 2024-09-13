<?php

namespace TVA\Automator;


use Thrive\Automator\Items\Action;
use function TVA\Drip\unlock_content_for_everyone;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Unlock_Content extends Action {

	protected $content_type;

	protected $content;

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/unlockcontent';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Unlock content for everyone';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return static::get_name();
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-unlock-content';
	}

	/**
	 * Get the name of app to which action belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	/**
	 * Array of action-field keys, required for the action to be setup
	 *
	 * @return array
	 */
	public static function get_required_action_fields() {
		return array( 'content_type' => array( 'content' ) );
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return [];
	}

	public function prepare_data( $data = array() ) {
		$content_type = $data['content_type'];
		/* it's ok to take the data like this because both fields are required */
		if ( ! empty( $content_type ) && isset( $content_type['subfield']['content']['value'] ) ) {
			$this->content_type = $content_type['value'];
			$this->content      = $content_type['subfield']['content']['value'];
		}
	}

	public function do_action( $data ) {
		foreach ( $this->content as $post_id ) {
			unlock_content_for_everyone( (int) $post_id );
		}
	}

	public static function hidden() {
		return true;
	}

}
