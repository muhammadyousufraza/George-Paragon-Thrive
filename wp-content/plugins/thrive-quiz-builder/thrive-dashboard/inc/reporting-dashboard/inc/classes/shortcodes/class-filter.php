<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Shortcodes;

use TVE\Reporting\Store;

class Filter {
	public static function add() {
		add_shortcode( 'tve_reporting_filter', [ static::class, 'render' ] );
	}

	public static function get_allowed_attr() {
		return [
			'report-app'          => '',
			'report-type'         => '',
			'options'             => '',
			'filter-label'        => '',
			'field-key'           => '',
			'is-global'           => 0,
			'is-multiple'         => 1,
			'placeholder'         => '',
			'default-value'       => '',
			'persist-value'       => 0,
			'retrieve-all-values' => 0,
			'no-options-text'     => 0,
		];
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render( $attr ) {
		$attr = shortcode_atts( static::get_allowed_attr(), $attr );

		$report_type_class = Store::get_instance()->get_report_type( $attr['report-app'], $attr['report-type'] );

		if ( empty( $attr['field-key'] ) || $report_type_class === null ) {
			$content = '';
		} else {
			$element_data = [];

			/* allowed attr from shortcode */
			foreach ( static::get_allowed_attr() as $key => $default_value ) {
				$element_data[ $key ] = sprintf( 'data-%s="%s"', $key, $attr[ $key ] );
			}

			/* attr from filter ( filter-label and filter-type ) */
			foreach ( $report_type_class::get_filters()[ $attr['field-key'] ] as $key => $value ) {
				if ( empty( $attr["filter-$key"] ) ) {
					$element_data["filter-$key"] = sprintf( 'data-filter-%s="%s"', $key, $value );
				}
			}

			$content = sprintf(
				'<div class="thrive-reporting-filter" %s></div>',
				implode( ' ', $element_data )
			);
		}

		return $content;
	}
}
