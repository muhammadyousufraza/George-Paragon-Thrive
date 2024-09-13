<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

class Store {

	use \TD_Singleton;

	/**
	 * @var array
	 */
	protected $registered_events = [];

	/**
	 * @var array
	 */
	protected $registered_report_apps = [];

	/**
	 * @param Event|String $event_class
	 *
	 * @return void
	 */
	public function register_event( $event_class ) {
		$this->registered_events[ $event_class::key() ] = $event_class;

		$event_class::after_register();
	}

	public function get_registered_events( $event_key = null ) {
		return $event_key === null ? $this->registered_events : $this->registered_events[ $event_key ] ?? null;
	}

	/**
	 * @param array $event_data
	 *
	 * @return Event|null
	 */
	public function event_factory( array $event_data ) {
		$event_instance = null;

		if ( ! empty( $event_data['event_type'] ) ) {
			$event_class = $this->get_registered_events( $event_data['event_type'] );

			if ( $event_class ) {
				$event_instance = new $event_class( $event_data );
			}
		}

		return $event_instance;
	}

	public function register_report_app( $app_class ) {
		$this->registered_report_apps[ $app_class::key() ] = $app_class;

		$app_class::after_register();
	}

	/**
	 * @param $app_key
	 *
	 * @return Report_App[]|Report_App
	 */
	public function get_registered_report_apps( $app_key = null ) {
		return $app_key === null ? $this->registered_report_apps : $this->registered_report_apps[ $app_key ] ?? null;
	}

	public function has_registered_report_app( $app_key ): bool {
		return isset( $this->registered_report_apps[ $app_key ] );
	}

	/**
	 * @param $app_key
	 * @param $type_key
	 *
	 * @return Report_Type|null
	 */
	public function get_report_type( $app_key, $type_key ) {
		$report_type      = null;
		$report_app_class = $this->get_registered_report_apps( $app_key );

		if ( $report_app_class ) {
			$report_types = $report_app_class::get_report_types();

			foreach ( $report_types as $report_type_class ) {
				if ( $report_type_class::key() === $type_key ) {
					$report_type = $report_type_class;
					break;
				}
			}
		}

		return $report_type;
	}
}
