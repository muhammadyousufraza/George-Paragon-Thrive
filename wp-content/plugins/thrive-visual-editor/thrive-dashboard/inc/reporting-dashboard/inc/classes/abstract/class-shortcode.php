<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\Shortcodes\Filter;

abstract class Shortcode {

	/**
	 * Tag used for the shortcode
	 *
	 * @return string
	 */
	public static function get_tag() {
		return '';
	}

	/**
	 * Extra class for container element
	 *
	 * @return string
	 */
	public static function get_element_class() {
		return '';
	}

	/**
	 * Allowed attributes for the shortcode
	 *
	 * @return array
	 */
	public static function get_allowed_attr() {
		return [
			'report-app'                  => '',
			'report-type'                 => '',
			'report-title'                => '',
			'report-group-by'             => '',
			'report-size'                 => 'default',
			'report-global-filter-fields' => '',
			'report-filter-fields'        => '',
			'report-expanded-view'        => '',
			'report-has-export'           => 0,
		];
	}

	public static function add() {
		add_shortcode( static::get_tag(), [ static::class, 'render' ] );
	}

	/**
	 * Display shortcode
	 *
	 * @param array  $attr
	 * @param string $icon
	 *
	 * @return string
	 */
	public static function render( $attr, $icon = '' ) {
		$attr = static::recursive_merge_atts( static::get_allowed_attr(), $attr );

		/* get all filters used by the report */
		$all_filter_fields = static::get_filter_fields( $attr['report-app'], $attr['report-type'] );

		/* render only the filters the shortcode wants */
		$filters = static::get_filters_html( $attr['report-app'], $attr['report-type'], $attr['report-filter-fields'], $all_filter_fields );

		/* save allowed filter fields, so we can trigger change only when those change */
		$attr['report-filter-fields'] = empty( $all_filter_fields ) ? '' : implode( ',', $all_filter_fields );

		if ( $attr['report-global-filter-fields'] === 'none' || empty( $all_filter_fields ) ) {
			/* when we don't listen to anything global */
			$attr['report-global-filter-fields'] = '';
		} elseif ( empty( $attr['report-global-filter-fields'] ) ) {
			/* in case nothing was added, we listen to all available filter fields */
			$attr['report-global-filter-fields'] = implode( ',', $all_filter_fields );
		}

		$content = sprintf( '<div class="thrive-reporting-shortcode %s" %s>%s</div>',
			static::get_element_class(),
			static::render_attr( $attr, 'data-' ),
			$icon
		);

		return $filters . $content;
	}

	/**
	 * Group attr for shortcode render
	 *
	 * @param $attr
	 * @param $prefix
	 *
	 * @return string
	 */
	public static function render_attr( $attr, $prefix = '' ) {
		$element_data = [];

		foreach ( $attr as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = str_replace( '"', "'", json_encode( $value ) );
			}

			$element_data[] = sprintf( '%s%s="%s"', $prefix, $key, $value );
		}

		return implode( ' ', $element_data );
	}

	/**
	 * Get shortcode filters to display
	 *
	 * @param string       $app
	 * @param string       $type
	 * @param array|string $used_filter_fields
	 * @param array        $all_filter_fields
	 *
	 * @return string
	 */
	public static function get_filters_html( $app, $type, $used_filter_fields = 'all', $all_filter_fields = [] ) {
		if ( $used_filter_fields !== 'all' ) {
			$used_filter_fields = empty( $used_filter_fields ) ? [] : explode( ',', $used_filter_fields );
		}

		$filters = '';

		if ( ! empty( $used_filter_fields ) ) {
			foreach ( $all_filter_fields as $field_key ) {
				if ( $used_filter_fields === 'all' || in_array( $field_key, $used_filter_fields, true ) ) {
					$filters .= Filter::render( [
						'report-app'  => $app,
						'report-type' => $type,
						'field-key'   => $field_key,
					] );
				}
			}

			if ( ! empty( $filters ) ) {
				$filters = sprintf( '<div class="thrive-reporting-filter-wrapper" >%s </div >', $filters );
			}
		}

		return $filters;
	}

	/**
	 * Fields used by the shortcode filters
	 *
	 * @param $app
	 * @param $type
	 *
	 * @return array|int[]|string[]
	 */
	public static function get_filter_fields( $app, $type ) {
		$report_type_class = Store::get_instance()->get_report_type( $app, $type );

		return $report_type_class === null ? [] : array_keys( $report_type_class::get_filters() );
	}

	/**
	 * Recursive merge for attributes used by shortcode
	 *
	 * @param $default
	 * @param $attr
	 * @param $only_default_keys
	 *
	 * @return array|mixed
	 */
	public static function recursive_merge_atts( $default, $attr, $only_default_keys = true ) {
		$out = $only_default_keys ? [] : $attr;

		foreach ( $default as $key => $value ) {
			if ( isset( $attr[ $key ] ) ) {
				if ( is_array( $value ) ) {
					$out[ $key ] = static::recursive_merge_atts( $default[ $key ], $attr[ $key ], $only_default_keys );
				} else {
					$out[ $key ] = $attr[ $key ];
				}
			} else {
				$out[ $key ] = $value;
			}
		}

		return $out;
	}

	/**
	 * Enqueue scripts used by shortcode
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		tve_dash_enqueue_vue();
		tve_dash_enqueue_script( 'td-highcharts', '//code.highcharts.com/highcharts.js' );
		tve_dash_enqueue_script( Main::SCRIPTS_HANDLE, TVE_DASH_URL . '/assets/dist/js/reporting-charts.js', [ 'tve-dash-main-vue', 'td-highcharts' ] );
		tve_dash_enqueue_style( Main::SCRIPTS_HANDLE, TVE_DASH_URL . '/assets/dist/css/reporting-charts.css' );

		wp_localize_script( Main::SCRIPTS_HANDLE, 'ThriveReporting', [
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'route' => get_rest_url( get_current_blog_id(), Main::REST_NAMESPACE ),
		] );
	}
}
