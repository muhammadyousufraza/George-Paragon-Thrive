<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

use Thrive\Automator\Items\App;
use Thrive\Automator\Items\Automation;
use Thrive\Automator\Items\Data_Field;
use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Filter;
use Thrive\Automator\Suite\TTW;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Tracking {

	const THRIVE_KEY = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

	const TRACKING_URL = 'https://service-api.thrivethemes.com/plugin-tracking';

	const TRACKING_CRON_HOOK = 'tap_tracking_cron';

	const TRACKING_OPTION = 'tve-tracking-allowed';

	const TRACKING_NOTICE_ID = 'tap-tracking-notice';

	const TRACKING_FREQUENCY = 30; // 30 days

	public static function init() {
		if ( ! wp_next_scheduled( static::TRACKING_CRON_HOOK ) ) {
			static::schedule_cron();
		}
		add_action( static::TRACKING_CRON_HOOK, [ __CLASS__, 'send_tracking_data' ] );
		add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ], 1 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		add_action( 'tve_tracking_consent_changed', [ __CLASS__, 'on_dash_consent_change' ] );
	}

	/**
	 * Handle consent from Dashboard
	 *
	 * @param $consent
	 *
	 * @return void
	 */
	public static function on_dash_consent_change( $consent ) {
		if ( $consent ) {
			static::send_tracking_data();
		} else {
			static::unschedule_cron();
		}
	}

	/**
	 * Whether tracking is allowed or not
	 *
	 * @return bool
	 */
	public static function is_tracking_allowed(): bool {
		return (bool) get_option( static::TRACKING_OPTION, false );
	}

	/**
	 * Save user tracking preference
	 *
	 * @param $value
	 *
	 * @return void
	 */
	public static function set_tracking_allowed( $value ) {
		update_option( static::TRACKING_OPTION, $value, 'no' );
		if ( $value ) {
			static::send_tracking_data();
			static::schedule_cron();
		} else {
			static::unschedule_cron();
		}
	}


	/**
	 * Set up the tracking cron job
	 *
	 * @return void
	 */
	public static function schedule_cron() {
		static::unschedule_cron();
		if ( static::is_tracking_allowed() ) {
			wp_schedule_single_event( time() + static::TRACKING_FREQUENCY * DAY_IN_SECONDS, static::TRACKING_CRON_HOOK );
		}
	}

	/**
	 * Delete the cron event
	 *
	 * @return void
	 */
	public static function unschedule_cron() {
		if ( wp_next_scheduled( static::TRACKING_CRON_HOOK ) ) {
			wp_clear_scheduled_hook( static::TRACKING_CRON_HOOK );
		}
	}

	/**
	 * Get all the data that needs to be tracked
	 *
	 * @return array
	 */
	public static function get_tracking_data(): array {
		$active_automations   = static::get_automations();
		$inactive_automations = static::get_automations( [ 'post_status' => 'draft' ] );
		$all_automations      = array_merge( $active_automations, $inactive_automations );

		$tracking_data = [
			'plugin'                   => TAP_SLUG,
			'site_id'                  => Utils::hash_256( get_site_url() ),
			'timestamp'                => time(),
			'php_version'              => PHP_VERSION,
			'wp_version'               => get_bloginfo( 'version' ),
			'plugin_version'           => TAP_VERSION,
			'apps_installed'           => static::get_installed_apps(),
			'no_of_active_automations' => count( $active_automations ),
			'no_of_total_automations'  => count( $all_automations ),
		];

		$tracking_data = array_merge( $tracking_data, static::get_automations_data( $all_automations ) );

		/**
		 * Filter the tracking data
		 */
		return apply_filters( 'tap_tracking_data', $tracking_data );
	}

	/**
	 * Send tracking data to the tracking server
	 *
	 * @return void
	 */
	public static function send_tracking_data() {
		if ( static::is_tracking_allowed() ) {
			$body = static::get_tracking_data();

			$url = add_query_arg( [
				'p' => static::calc_hash( $body ),
			], static::TRACKING_URL );

			wp_remote_post( $url, [
				'body'      => json_encode( $body ),
				'headers'   => [
					'Content-Type' => 'application/json',
				],
				'sslverify' => false,
				'timeout'   => 30,
			] );
		}
	}

	/**
	 * Calc the hash that should be sent on APIs requests
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private static function calc_hash( $data ): string {
		return md5( static::THRIVE_KEY . serialize( $data ) . static::THRIVE_KEY );
	}

	/**
	 * Get installed apps
	 *
	 * @return array
	 */
	public static function get_installed_apps(): array {
		$apps           = App::get();
		$installed_apps = [];
		foreach ( $apps as $app ) {
			$installed_apps[] = [
				'id'   => $app::get_id(),
				'name' => $app::get_name(),
			];
		}

		return $installed_apps;
	}

	/**
	 * Get automations from db
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public static function get_automations( array $filters = [] ): array {
		$defaults = [
			'post_type'      => Automation::POST_TYPE,
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		];
		$filters  = array_merge( $defaults, $filters );

		return \get_posts( $filters );
	}

	/**
	 * Get automations data
	 *
	 * @param $automations
	 *
	 * @return array
	 */
	public static function get_automations_data( $automations ): array {
		$data = [];

		foreach ( $automations as $automation_id ) {
			static::process_automation( $automation_id, $data );
		}

		return $data;
	}

	/**
	 * Get the data for a single automation
	 *
	 * @param int   $automation_id
	 * @param array $data
	 *
	 * @return void
	 */
	public static function process_automation( int $automation_id, array &$data ) {
		$automation       = get_post( $automation_id );
		$automation_steps = Utils::safe_unserialize( $automation->post_content );

		$automation_identifier = Utils::get_automation_uniq_id( $automation_id );
		$run_times             = Utils::get_post_meta( $automation_id, 'run_times' );
		$aut_data              = [
			'run_times'       => $run_times ?: 0,
			'run_times_error' => static::get_automations_errors( $automation_id ),
			'structure'       => [],
		];

		foreach ( $automation_steps as $step ) {
			$step_data = [];

			switch ( $step['type'] ) {
				case 'triggers':
				case 'actions':
					foreach ( $step['data'] as $step_data_item ) {
						$item_runs    = (int) Utils::get_post_meta( $automation_id, substr( $step['type'], 0, - 1 ) . '_run_' . $step_data_item['key'] );
						$setup_values = static::get_step_additional_data( $step_data_item, $step['type'] );

						$step_data[] = [
							'name'         => $step_data_item['name'],
							'item_id'      => $step_data_item['key'],
							'conditions'   => static::process_filters( $step_data_item['conditions'] ?? [], $automation_id, $automation_identifier ),
							'aut_id'       => $automation_identifier,
							'setup_values' => $setup_values,
							'runs'         => $item_runs ?: 0,
						];
					}
					break;
				case 'filters':
					$step_data = array_merge( $step_data, static::process_filters( $step['data'], $automation_id, $automation_identifier ) );
					break;
				default:
					break;
			}
			$aut_data['structure'][] = [
				'type' => $step['type'],
				'data' => array_filter( array_map( 'array_filter', $step_data ) ),
			];
		}


		$data['automations'][] = $aut_data;
	}

	/**
	 * Get automation potential errors
	 *
	 * @param int $automation_id
	 *
	 * @return array
	 */
	public static function get_automations_errors( int $automation_id ): int {
		$error_count = 0;
		$logs        = Error_Log_Handler::get_automation_logs( $automation_id );
		foreach ( $logs as $log ) {
			$aut_errors = json_decode( $log['error'], true );
			foreach ( $aut_errors as $aut_error ) {
				foreach ( $aut_error as $error ) {
					if ( isset( $error['is_success'] ) && ! $error['is_success'] ) {
						$error_count ++;
					}
				}
			}
		}

		return $error_count;
	}

	/**
	 * Get essential data for filters
	 *
	 * @param array  $filters
	 * @param int    $aut_id
	 * @param string $aut_identifier
	 *
	 * @return array
	 */
	public static function process_filters( array $filters, int $aut_id = 0, string $aut_identifier = '' ): array {
		$filters_data = [];
		foreach ( $filters as $filter ) {
			if ( ! empty( $filter ) ) {
				$item_runs  = (int) Utils::get_post_meta( $aut_id, 'filter_run_' . $filter['field'] );
				$field      = Data_Field::get_by_id( $filter['field'] );
				$field_name = $field ? $field::get_name() : $filter['field'];

				$data_object = Data_Object::get_by_id( $filter['data_object'] );
				$object_name = $data_object ? $data_object::get_nice_name() : $filter['data_object'];

				$filter_data = Filter::get_by_id( $filter['filter'] );
				$filter_name = $filter_data ? $filter_data::get_name() : $filter['filter'];

				$filter_data = [
					'filter'         => $filter_name,
					'filter_id'      => $filter['filter'],
					'data_object'    => $object_name,
					'data_object_id' => $filter['data_object'],
					'field'          => $field_name,
					'field_id'       => $filter['field'],
					'runs'           => $item_runs ?: 0,
				];
				if ( ! empty( $aut_identifier ) ) {
					$filter_data['aut_id'] = $aut_identifier;
				}

				$filters_data[] = $filter_data;
			}
		}

		return $filters_data;
	}

	/**
	 * Get more data about a step
	 *
	 * @param array  $step_data
	 * @param string $step_type
	 *
	 * @return array
	 */
	public static function get_step_additional_data( array $step_data, string $step_type ): array {
		$data = [];

		if ( $step_type === 'actions' ) {
			static::get_fields_value( $step_data['extra_data'], $data );
		}

		return $data;
	}

	/**
	 * Get fields value
	 *
	 * @param $setup
	 * @param $collector
	 *
	 * @return void
	 */
	public static function get_fields_value( $setup, &$collector ) {
		$allowed_fields = static::get_fields_key();
		foreach ( $setup as $key => $data ) {
			if ( ! empty( $data['value'] ) && ! is_array( $data['value'] ) && in_array( $key, $allowed_fields, true ) ) {
				$collector[] = [
					'name'  => $key,
					'value' => $data['value'],
				];
			}
			if ( ! empty( $data['subfield'] ) ) {
				static::get_fields_value( $data['subfield'], $collector );
			}
		}
	}

	/**
	 * get fields key which are allowed to be logged
	 *
	 * @return mixed|null
	 */
	public static function get_fields_key() {
		$allowed_fields = [
			'autoresponder',
			'url_webhook',
		];

		/**
		 * Filter allowed fields to be logged
		 */
		return apply_filters( 'tap_tracking_data_allowed_fields', $allowed_fields );
	}

	public static function should_display_ribbon() {
		$should_enqueue = get_option( static::TRACKING_OPTION, '' ) === ''
		                  && ! Utils::has_suite_access()
		                  && ! ( TTW::has_tpm() && TTW::connection()->is_connected() )
		                  && Utils::get_user_meta( 0, 'notice-dismissed-' . TTW::RIBBON );

		/**
		 * Filter if the ribbon should be displayed
		 * generic filter used in our products for metrics
		 */
		return apply_filters( 'tve_dash_metrics_should_enqueue', $should_enqueue );
	}

	public static function admin_notices() {
		if ( static::should_display_ribbon() ) {
			Utils::tap_template( 'tracking-ribbon' );
		}
	}

	public static function enqueue_scripts() {
		if ( static::should_display_ribbon() ) {
			Utils::enqueue_assets( 'tracking', Hooks::get_localize_data() );
		}
	}
}
