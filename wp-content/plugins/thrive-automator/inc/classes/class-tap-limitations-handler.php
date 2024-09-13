<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

use Thrive\Automator\Items\User_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Limitations_Handler
 *
 * @package Thrive\Automator
 */
class Limitations_Handler {

	/**
	 * Instance running with each running automation
	 */
	protected static $_instances = [];

	/**
	 * Automation identifier
	 */
	protected $automation_id;

	/**
	 * @var \wpdb
	 */
	protected $wpdb;


	/**
	 * Only one instance available at a time
	 *
	 * @return Limitations_Handler
	 */
	public static function instance( $aut_id ) {
		if ( empty( static::$_instances[ $aut_id ] ) ) {
			static::$_instances[ $aut_id ] = new self( $aut_id );
		}

		return static::$_instances[ $aut_id ];
	}

	public function __construct( $automation_id ) {
		global $wpdb;
		$this->automation_id = $automation_id;
		$this->wpdb          = $wpdb;
	}

	/**
	 * Check limitations and log individual user executions
	 *
	 * @return array
	 */
	public function get_parameters_for_execution_count( $settings, $automation_params = [] ) {
		$params = [];
		switch ( $settings['rule'] ) {
			case 'logged_out':
				$params['user_id'] = 'logged_out';
				break;
			case 'any_logged_in':
				$params['user_id'] = 'any_logged_in';
				break;
			case 'each_logged_in':
				if ( ! empty( $automation_params['user_id'] ) ) {
					$params['user_id'] = $automation_params['user_id'];
				}
				break;
			default:
				break;
		}

		if ( ! empty( $settings['subject'] ) && $settings['subject'] === 'each' ) {
			$params['trigger'] = $automation_params['trigger'];

		}
		if ( ! empty( $automation_params['additional'] ) && $settings['subject'] === 'each' ) {
			$params['additional'] = $automation_params['additional'];
		}

		return $params;
	}

	/**
	 * Check limitations and log individual user executions
	 *
	 * @return boolean
	 */
	public function handle_limitations( $automation_params ) {
		$valid = true;
		$meta  = TAP_DB::get_automator_post_meta( $this->automation_id );

		if ( ! empty( $meta['limitations'] ) ) {

			$settings     = $meta['limitations'];
			$query_params = [
				'trigger'    => $automation_params['trigger'],
				'additional' => [],
			];
			global $automation_data;
			$data_object = $automation_data->get( User_Data::get_id() );
			if ( ! empty( $data_object ) ) {
				$query_params['user_id'] = $data_object->get_value( 'user_id' );
			} elseif ( $settings['rule'] === 'each_logged_in' ) {
				tap_logger( $this->automation_id )->register( [
					'key'         => User_Data::get_id(),
					'id'          => 'no-param-on-data-create',
					'message'     => 'No user provided to match limitation settings',
					'class-label' => tap_logger( $this->automation_id )->get_nice_class_name( 'User_Data' ),
				] );
				tap_logger( $this->automation_id )->log();

				return false;
			}

			if ( is_array( $automation_params['raw_data'] ) && ! empty( $automation_params['raw_data']['webhook_id'] ) ) {
				$query_params['additional']['webhook_id'] = $automation_params['raw_data']['webhook_id'];
			}

			$is_user = ! empty( $query_params['user_id'] );
			switch ( $settings['rule'] ) {
				case 'logged_out':
					if ( $is_user ) {
						$valid = false;
					}
					break;
				case 'all':
					break;
				default:
					if ( ! $is_user ) {
						$valid = false;
					}
					break;
			}


			if ( $valid ) {
				$valid = $this->validate_limitation_settings( $settings, $query_params );
			}
			$query_params['additional']['executed'] = $valid;
			$this->log_execution(
				[
					'user_id'    => empty( $query_params['user_id'] ) ? null : $query_params['user_id'],
					'trigger'    => $automation_params['trigger'],
					'additional' => $query_params['additional'],
				]
			);
		}

		return $valid;
	}

	public function validate_limitation_settings( $settings, $query_params ): bool {
		$valid = true;
		$count = $this->get_entry_count( $this->get_parameters_for_execution_count( $settings, $query_params ) );
		switch ( $settings['execution_count'] ) {
			case 'once':
				if ( $count > 0 && $settings['moment'] !== 'after' ) {
					$valid = false;
				}
				break;
			case 'number':
				if ( $count >= $settings['execution_count_limit'] && $settings['moment'] !== 'after' ) {
					$valid = false;
				}
				break;
			default:
				break;
		}
		if ( $settings['moment'] === 'after' ) {
			if ( $count < $settings['execution_count_minimum'] ) {
				$valid = false;
			}

			if ( $settings['execution_count'] === 'number' && $count >= ( (int) $settings['execution_count_minimum'] + (int) $settings['execution_count_limit'] ) ) {
				$valid = false;
			}

			if ( $settings['execution_count'] === 'once' && $count >= ( (int) $settings['execution_count_minimum'] + 1 ) ) {
				$valid = false;
			}
		}

		return $valid;
	}

//	- limitation
//		- execution count
//          - once
//          - unlimited
//          - number
//              - execution_count_limit
//      - rule
//			- for all users and events  - all
//			- for each logged in user - any_logged_in
//			- for any logged in user - each_logged_in
//			- for any logged out user - logged_out
//      - moment
//			- when
//              - subject
//			        - any
//                  - each
//			- after
//				- execution_count_minimum
//

	/**
	 * @param $params
	 *
	 * @return void
	 */
	public function log_execution( $params ) {

		$log_data = array(
			'date_started'  => gmdate( 'Y-m-d H:i:s' ),
			'user_id'       => empty( $params['user_id'] ) ? null : $params['user_id'],
			'automation_id' => (int) $this->automation_id,
			'trigger_id'    => $params['trigger'],
			'additional'    => json_encode( $params['additional'] ),
		);

		$this->wpdb->insert( $this->get_table_name(), $log_data );
	}

	/**
	 * Get runs for each trigger from a specific automation
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_trigger_runs() {

		$query = $this->wpdb->prepare(
			"SELECT trigger_id, json_extract(additional, '$.webhook_id') as webhook_id, COUNT(trigger_id) as trigger_runs FROM " . $this->get_table_name() . " WHERE automation_id = %d AND json_extract(additional, '$.executed') = true GROUP BY trigger_id, json_extract(additional, '$.webhook_id')",
			$this->automation_id
		);

		return $this->wpdb->get_results( $query );
	}

	/**
	 * Get entries count
	 *
	 * @param array $params
	 *
	 * @return int
	 */
	public function get_entry_count( array $params = [] ): int {

		$sql        = 'SELECT COUNT(*) FROM ' . $this->get_table_name() . ' WHERE automation_id = %d ';
		$sql_params = [ $this->automation_id ];

		if ( isset( $params['user_id'] ) ) {
			switch ( $params['user_id'] ) {
				case 'logged_out':
					$sql .= ' AND user_id IS NULL';
					break;
				case 'any_logged_in':
					$sql .= ' AND user_id IS NOT NULL';
					break;
				default:
					$sql          .= ' AND user_id = %d';
					$sql_params[] = $params['user_id'];
			}
		}

		if ( ! empty( $params['trigger'] ) ) {
			$sql          .= ' AND trigger_id = %s ';
			$sql_params[] = $params['trigger'];
		}

		if ( ! empty( $params['additional'] ) ) {
			foreach ( $params['additional'] as $key => $filter ) {
				$sql          .= " AND json_extract(additional, '$." . $key . "') = %s ";
				$sql_params[] = $filter;
			}

		}

		$query = $this->wpdb->prepare( $sql, $sql_params );

		return (int) $this->wpdb->get_var( $query );
	}

	/**
	 * Delete specific log entry
	 *
	 * @param null  $trigger_id
	 * @param array $additional
	 *
	 * @return bool
	 */
	public function delete_automation_entries( $trigger_id = null, $additional = [] ): bool {

		$sql        = 'DELETE FROM ' . $this->get_table_name() . ' WHERE automation_id = %d ';
		$sql_params = [ $this->automation_id ];

		if ( ! empty( $trigger_id ) ) {
			$sql          .= ' AND trigger_id = %s ';
			$sql_params[] = $trigger_id;

		}

		if ( ! empty( $additional ) ) {
			foreach ( $additional as $key => $filter ) {
				$sql          .= " AND json_extract(additional, '$." . $key . "') = %s ";
				$sql_params[] = $filter;
			}
		}

		return $this->wpdb->query( $this->wpdb->prepare( $sql, $sql_params ) );
	}

	/**
	 * Get error log table name
	 *
	 * @return string
	 */
	public function get_table_name() {

		return $this->wpdb->prefix . TAP_DB_PREFIX . 'limitations';
	}

}

function tap_limitations( $automation_id = 0 ) {
	return Limitations_Handler::instance( $automation_id );
}
