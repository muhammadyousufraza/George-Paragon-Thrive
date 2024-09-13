<?php

namespace TVE\Reporting\Automator;

use Thrive\Automator\Items\Action_Field;

class Event_Key extends Action_Field {

	public static function get_id() {
		return 'reporting-event-key';
	}

	public static function get_type() {
		return 'input';
	}

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Event key';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'The key of the event to register';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'event-key';
	}

	/**
	 * @return string
	 */
	public static function get_preview_template() {
		return 'Event key: $$value';
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
