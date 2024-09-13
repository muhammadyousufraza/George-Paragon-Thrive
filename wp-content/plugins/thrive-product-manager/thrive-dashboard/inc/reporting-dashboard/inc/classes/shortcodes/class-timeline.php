<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Shortcodes;

use TVE\Reporting\Event;
use TVE\Reporting\Shortcode;
use TVE\Reporting\Store;

class Timeline extends Shortcode {
	public static function get_tag() {
		return 'tve_reporting_timeline';
	}

	public static function get_element_class() {
		return 'thrive-reporting-timeline';
	}

	public static function get_allowed_attr() {
		return array_merge(
			parent::get_allowed_attr(),
			[
				'report-items-per-page' => 4,
				'report-data-type'      => '',
				'has-pagination'        => 0,
				'report-size'           => 'sm',
				'user-url'              => '',
			]
		);
	}

	/**
	 * More or less, all fields can filter user activity
	 *
	 * @param $app
	 * @param $type
	 *
	 * @return array|int[]|string[]
	 */
	public static function get_filter_fields( $app, $type ) {
		$fields = [];

		/** @var $all_events Event[] */
		$all_events = Store::get_instance()->get_registered_events();

		foreach ( $all_events as $event ) {
			foreach ( $event::get_registered_fields() as $field ) {
				$field_key = $field::key();

				if ( ! in_array( $field_key, $fields ) ) {
					$fields[] = $field_key;
				}
			}
		}

		return $fields;
	}
}
