<?php

/**
 * Class TPM_Cron
 * WP Cron Event for refreshing TPM Token
 * - schedules a cron event to be executed on custom interval at a date defined by TTW
 * - when a new token is recived and connection saved a new event will be set
 *   and the current one unscheduled
 */
final class TPM_Cron {

	/**
	 * @var TPM_Cron
	 */
	private static $instance;

	/**
	 * Cron hook name
	 */
	const CRON_HOOK_NAME = 'tpm_cron_hook';

	/**
	 * Name of the Custom Cron Interval
	 */
	const CRON_INTERVAL_NAME = 'tpm_interval';

	/**
	 * TPM_Cron private constructor to ensure the singleton
	 */
	private function __construct() {

		$this->_init_hooks();
	}

	/**
	 * Defines hooks to be loaded once
	 */
	private function _init_hooks() {

		/**
		 * Cron Hook Definition
		 */
		add_action( self::CRON_HOOK_NAME, array( $this, 'execute' ) );

		/**
		 * define custom interval for wp cron
		 */
		add_filter( 'cron_schedules', array( $this, 'cron_interval' ) );

		/**
		 * unschedule cron from DB when TPM is deactivated
		 */
		add_action(
			'admin_init',
			function () {
				register_deactivation_hook(
					WP_PLUGIN_DIR . '/thrive-product-manager/thrive-product-manager.php',
					array(
						$this,
						'unschedule',
					)
				);
			}
		);
	}

	/**
	 * Pushes a custom interval
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function cron_interval( $schedules ) {

		$schedules[ self::CRON_INTERVAL_NAME ] = array(
			'interval' => DAY_IN_SECONDS,
			'display'  => esc_html__( 'Once Daily' ),
		);

		return $schedules;
	}

	/**
	 * Execution of Cron Event
	 * - fetches a new token from TTW
	 */
	public function execute() {

		tpm_cron()->log( 'execute()' );

		TPM_Connection::get_instance()->refresh_token();
	}

	/**
	 * Schedule a new Cron Event on a specific date
	 * - usually specified by TTW
	 * - unschedules current event before
	 *
	 * @param string $date
	 *
	 * @return bool
	 */
	public function schedule( $date ) {

		tpm_cron()->log( 'schedule()' );

		/**
		 * when this cron will be executed 1st time
		 */
		$at  = strtotime( $date );
		$set = false;

		if ( $at < time() ) {
			return false;
		}

		if ( ! $this->unschedule() ) {
			return add_filter( 'tpm_messages', array( $this, 'push_message_event_unscheduled' ) );
		}

		if ( ! wp_next_scheduled( self::CRON_HOOK_NAME ) ) {
			$set = wp_schedule_event( $at, self::CRON_INTERVAL_NAME, self::CRON_HOOK_NAME );
		}

		tpm_cron()->log( "set was " . var_export( $set, true ) . " to execute first time at: " . date( 'Y-m-d H:i:s', $at ) . "\n===========================" );

		return $set;
	}

	/**
	 * Unschedule current event
	 * - if it doesn't exist true is returned
	 *
	 * @return bool
	 */
	public function unschedule() {

		$timestamp = wp_next_scheduled( self::CRON_HOOK_NAME );
		$unset     = true;

		if ( false !== $timestamp ) {
			$unset = wp_unschedule_event( $timestamp, self::CRON_HOOK_NAME );
			tpm_cron()->log( 'unschedule event with: ' . var_export( $unset, true ) . ' scheduled at: ' . date( 'Y-m-d H:i:s', $timestamp ) );
		}

		return $unset;
	}

	/**
	 * Push a tpm message to be displayed by JS
	 * when the cron event cannot be unscheduled
	 *
	 * @param $messages
	 *
	 * @return array
	 */
	public function push_message_event_unscheduled( $messages ) {

		$messages[] = array(
			'status'  => 'warning',
			'message' => 'A cron event could not be unschedule. Please contact Thrive Themes Support !',
		);

		return $messages;
	}

	/**
	 * Singleton
	 *
	 * @return TPM_Cron
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Log log messages only if the debug mode is ON
	 *
	 * @param string $message
	 */
	public function log( $message ) {

		if ( false === Thrive_Product_Manager::is_debug_mode() ) {
			return;
		}

		$filename = WP_CONTENT_DIR . '/cron.log';
		file_put_contents( $filename, date( 'Y-m-d H:i:s', time() ) . " => " . $message . "\n", FILE_APPEND );
	}
}

/**
 * Helper for TPM_Cron Singleton
 *
 * @return TPM_Cron
 */
function tpm_cron() {
	return TPM_Cron::get_instance();
}

tpm_cron();
