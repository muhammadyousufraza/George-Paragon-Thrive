<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\EventFields;

use TVE\Reporting\Event;
use TVE\Reporting\Store;

class Event_Type extends Event_Field {

	public static function key(): string {
		return 'event_type';
	}

	public static function can_filter_by(): bool {
		return false;
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return 'Event type';
	}

	public function get_title(): string {
		/** @var Event $event */
		$event = Store::get_instance()->get_registered_events( $this->value );

		return $event === null ? $this->value : $event::label();
	}

	public static function get_filter_options(): array {
		return array_map( static function ( $event ) {
			/** @var Event $event */
			return [
				'id'    => $event::key(),
				'label' => $event::label(),
			];
		}, Store::get_instance()->get_registered_events() );
	}
}
