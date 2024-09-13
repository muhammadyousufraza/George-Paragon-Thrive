<?php

use \TD\DB_Updater\Updater;

class Thrive_Leads_Data_Updater extends Updater {
	protected $product = 'Thrive Leads';

	public function output_welcome_message() {
		echo "<h4 class=''>We've just made a great deal of performance improvements to Thrive Leads. As a result, some of the database tables need updating. Click the button below to start the process. This may take a while, depending on the amount of impressions that your forms have gathered until now.</h4>";
	}

	public function get_dashboard_url() {
		return admin_url( 'admin.php?page=thrive_leads_dashboard' );
	}

	public function execute_step( $step ) {
		global $tvedb;

		$summary_table = tve_leads_table_name( 'form_summary' );

		$state = array();

		$response = array();
		switch ( $step ) {
			case 'init':
				// check if table exists
				$result = $this->ensure_summary_table();
				if ( $result !== true ) {
					$response['error'] = $result;
					break;
				}
				// table exists
				// count total entries, make sure table exists
				$response['log']       = array(
					'Checking summary table ... summary table exists.',
					'Deleting archived logs ...',
				);
				$response['next_step'] = 'delete_archived_logs';
				$response['progress']  = 1;
				break;
			case 'delete_archived_logs':
				$deleted_count = $tvedb->delete( tve_leads_table_name( 'event_log' ), array( 'archived' => 1 ) );
				if ( $deleted_count === false ) {
					$response['error'] = 'Something went wrong while deleting archived logs: ' . $tvedb->last_error();
					break;
				}
				$response['progress']   = 3;
				$response['log_append'] = 'deleted ' . number_format( $deleted_count ) . ' archived logs';
				$response['next_step']  = 'collect_count';
				$response['log']        = 'Gathering form impressions and conversions...';
				break;
			case 'collect_count':
				$prepared       = $tvedb->prepare( 'SELECT COUNT(*) FROM {event_log} WHERE event_type != %d', TVE_LEADS_IMPRESSION );
				$state['total'] = (int) $tvedb->get_var( $prepared );
				$state['limit'] = min( 30000, ceil( $state['total'] / 50 ) );
				if ( $state['limit'] < 30000 ) {
					$state['limit'] = 30000;
				}
				$state['progress_delta'] = 95 / ceil( $state['total'] / $state['limit'] );
				$state['iteration']      = 0;
				$state['last_id']        = 0;

				$response['progress']   = 5; // start from 5%
				$response['log_append'] = 'found ' . number_format( $state['total'] ) . ' items to migrate.';
				if ( $state['total'] === 0 ) {
					$this->finish();
					$response['finished'] = true;
				} else {
					update_option( 'tve_leads_perf_state', $state );
					$response['next_step'] = 'process';
					$response['log']       = 'Processing ' . number_format( min( $state['limit'], $state['total'] ) ) . ' items...';
				}
				break;
			case 'process':
				$start = microtime( true );
				$state = get_option( 'tve_leads_perf_state' );
				if ( empty( $state ) ) {
					$response['error'] = 'Something went wrong (could not find previous step).';
					break;
				}
				$prepared = $tvedb->prepare(
					'SELECT id, DATE( `date` ) AS `day`, event_type, main_group_id, form_type_id, variation_key, is_unique FROM {event_log} 
					WHERE
						id > %d AND  
						event_type != %d ORDER BY `id` ASC LIMIT %d',
					array(
						$state['last_id'],
						TVE_LEADS_IMPRESSION,
						$state['limit'],
					)
				);
				$counts   = array();
				$min_id   = false;
				foreach ( $tvedb->get_results( $prepared, ARRAY_A ) as $index => $item ) {
					if ( $index === 0 ) {
						$min_id = $item['id'];
					}
					if ( ! isset( $counts[ $item['day'] ] ) ) {
						$counts[ $item['day'] ] = array();
					}
					if ( ! isset( $counts[ $item['day'] ][ $item['variation_key'] ] ) ) {
						$counts[ $item['day'] ][ $item['variation_key'] ] = array(
							'date'                 => $item['day'],
							'main_group_id'        => $item['main_group_id'],
							'form_type_id'         => $item['form_type_id'],
							'variation_key'        => $item['variation_key'],
							'impression_count'     => 0,
							'unique_visitor_count' => 0,
							'conversion_count'     => 0,
						);
					}
					$key = (int) $item['event_type'] === TVE_LEADS_UNIQUE_IMPRESSION ? 'impression_count' : 'conversion_count';
					$counts[ $item['day'] ][ $item['variation_key'] ][ $key ] ++;
					if ( ! empty( $item['is_unique'] ) && (int) $item['event_type'] === TVE_LEADS_UNIQUE_IMPRESSION ) {
						$counts[ $item['day'] ][ $item['variation_key'] ]['unique_visitor_count'] ++;
					}
				}
				if ( empty( $item ) ) {
					$this->finish();
					/* nothing found => finished */
					$response['finished'] = true;
					break;
				}
				$max_id           = $item['id'];
				$state['last_id'] = $max_id;

				foreach ( $counts as $date => $items ) {
					foreach ( $items as $variation_key => $row ) {
						$summary = $tvedb->get_summary( $date, $variation_key );
						if ( empty( $summary ) ) {
							// summary does not exist for date / variation_key => insert
							$tvedb->insert( $summary_table, $row );
						} else {
							/* just update counts */
							$tvedb->update(
								$summary_table,
								array(
									'impression_count'     => $row['impression_count'] + $summary['impression_count'],
									'conversion_count'     => $row['conversion_count'] + $summary['conversion_count'],
									'unique_visitor_count' => $row['unique_visitor_count'] + $summary['unique_visitor_count'],
								),
								array(
									'id' => $summary['id'],
								)
							);
						}
					}
				}
				/* delete each impression row */
				$sql_delete    = $tvedb->prepare( 'DELETE FROM {event_log} WHERE event_type = %d AND id >= %d AND id <= %d', array(
					TVE_LEADS_UNIQUE_IMPRESSION,
					$min_id,
					$max_id,
				) );
				$deleted_count = $tvedb->query( $sql_delete );
				if ( $deleted_count === false ) {
					$response['error'] = 'Could not upgrade some of the entries. Error was: ' . $tvedb->last_error();
					break;
				}
				$state['total'] -= $state['limit'];
				if ( $state['total'] <= 0 ) {
					$state['total']       = 0;
					$response['finished'] = true;
					$this->finish();
				} else {
					$response['next_step'] = 'process';
					$response['log']       = 'Processing ' . number_format( min( $state['limit'], $state['total'] ), 0 ) . ' items...';
				}
				$response['log_append'] = 'done. Items left: ' . number_format( $state['total'], 0 );
				$state['iteration'] ++;
				$response['progress'] = 5 + $state['iteration'] * $state['progress_delta'];
				update_option( 'tve_leads_perf_state', $state );
				$end = microtime( true );

				$speed                     = $state['limit'] / ( $end - $start );
				$response['est_time_left'] = 'Current speed: ' . floor( $speed ) . ' items / sec.';
				break;
			default:
				$response['error'] = 'Invalid step';
				break;
		}

		return $response;
	}

	/**
	 * Mark the upgrade as finished
	 */
	public function finish() {
		update_option( 'tve_leads_impressions_migrate', 1 );
	}

	/**
	 * Ensure the main form summary table exists
	 *
	 * @return true|string true or error message in case of failure
	 */
	public function ensure_summary_table() {
		global $tvedb;
		$table_name = tve_leads_table_name( 'form_summary' );
		$prepared   = $tvedb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		$result     = $tvedb->get_row( $prepared, ARRAY_A );

		if ( empty( $result ) ) {
			/* try to create table */
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name}(
    `id` INT( 11 ) AUTO_INCREMENT,
    `date` VARCHAR(10) NULL DEFAULT NULL,
    `main_group_id` INT( 11 ) NULL DEFAULT NULL,
    `form_type_id` INT( 11 ) NULL,
    `variation_key` INT( 11 ) NULL,
    `impression_count` INT( 11 ) NULL DEFAULT 0,
    `unique_visitor_count` INT( 11 ) NULL DEFAULT 0,
    `conversion_count` INT( 11 ) NULL DEFAULT 0,
     PRIMARY KEY( `id` ),
     KEY `date` (`date`),
     KEY `variation_key` (`variation_key`)
 )";
			$tvedb->hide_errors();
			$result = $tvedb->query( $sql );

			if ( $result !== true ) {
				return 'Could not create a needed table (most likely this is an issue with database permissions, please contact your hosting provider). Error message: ' . $tvedb->last_error();
			}
		}

		return true;
	}
}

if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'tve_dash_db_updater' && wp_doing_ajax() ) {
	$instance = new Thrive_Leads_Data_Updater();
	add_filter( 'tve_dash_updater_instance_' . tve_dash_get_updater_key( $instance ), function () use ( $instance ) {
		return $instance;
	} );
}
