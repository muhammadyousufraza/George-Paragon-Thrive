<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Shortcodes;

use TVE\Reporting\Shortcode;

class Table extends Shortcode {

	public static function get_tag() {
		return 'tve_reporting_table';
	}

	public static function get_element_class() {
		return 'thrive-reporting-table';
	}

	public static function get_allowed_attr() {
		return array_merge(
			parent::get_allowed_attr(),
			[
				'report-data-type'          => 'table',
				'report-items-per-page'     => 10,
				'report-table-columns'      => '',
				'report-order-by'           => '',
				'report-order-by-direction' => '',
				'report-restrict-order-by'  => '',
				'has-pagination'            => 0,
				'export-title'              => '',
			]
		);
	}
}
