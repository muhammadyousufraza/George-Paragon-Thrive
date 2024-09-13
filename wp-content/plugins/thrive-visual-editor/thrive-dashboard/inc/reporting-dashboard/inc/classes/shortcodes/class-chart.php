<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Shortcodes;

use TVE\Reporting\Shortcode;

class Chart extends Shortcode {

	public static function get_tag() {
		return 'tve_reporting_chart';
	}

	public static function get_element_class() {
		return 'thrive-reporting-chart';
	}

	public static function get_allowed_attr() {
		return array_merge(
			parent::get_allowed_attr(),
			[
				'report-data-type' => 'chart',
				'chart-config'     => [
					'type'              => '',
					'on-click-url'      => '',
					'stacking'          => 0,
					'cumulative'        => 0,
					'cumulative-toggle' => 0,
					'has-legend'        => 0,
				],
			]
		);
	}

	/**
	 * Parse the chart config first because we want to correctly merge with default attributes
	 *
	 * @param $default
	 * @param $attr
	 * @param $only_default_keys
	 *
	 * @return array|mixed
	 */
	public static function recursive_merge_atts( $default, $attr, $only_default_keys = true ) {
		try {
			if ( isset( $attr['chart-config'] ) ) {
				$attr['chart-config'] = json_decode( str_replace( "'", '"', $attr['chart-config'] ), true );
			}
		} catch ( \Exception $e ) {
			$attr['chart-config'] = [];
		}

		return parent::recursive_merge_atts( $default, $attr, $only_default_keys );
	}
}
