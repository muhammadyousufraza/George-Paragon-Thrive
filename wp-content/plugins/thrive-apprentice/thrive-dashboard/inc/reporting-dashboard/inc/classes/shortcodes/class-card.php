<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Shortcodes;

use TVE\Reporting\Shortcode;

class Card extends Chart {

	public static function get_tag() {
		return 'tve_reporting_card';
	}

	public static function get_element_class() {
		return 'thrive-reporting-card';
	}

	public static function get_allowed_attr() {
		return array_merge(
			Shortcode::get_allowed_attr(),
			[
				'has-chart'           => 0,
				'has-date-comparison' => 0,
				'report-data-type'    => 'card',
				'chart-config'        => [
					'type'         => 'line',
					'on-click-url' => '',
					'cumulative'   => 1,
				],
			]
		);
	}
}
