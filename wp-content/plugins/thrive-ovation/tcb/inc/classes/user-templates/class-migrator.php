<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\UserTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Migrator {
	private $option_key;
	private $backup_option_key;
	private $finished_option_key;
	private $migrate_callback;

	/**
	 * @param $options
	 */
	public function __construct( $options ) {
		$this->option_key          = $options['option_key'];
		$this->backup_option_key   = $options['backup_option_key'];
		$this->finished_option_key = $options['finished_option_key'];
		$this->migrate_callback    = $options['migrate_callback'];
	}

	/**
	 * @param $amount
	 */
	public function migrate_x_items( $amount ) {
		$items = get_option( $this->option_key, [] );

		/* make sure we have a backup */
		if ( empty( get_option( $this->backup_option_key ) ) ) {
			update_option( $this->backup_option_key, $items, 'no' );
		}

		if ( ! empty( get_option( $this->backup_option_key ) ) ) {
			for ( $i = 0; $i < $amount; $i ++ ) {
				$items = call_user_func( $this->migrate_callback, $items );
			}
		}
	}

	/**
	 * @return bool
	 */
	public function is_finished() {
		return ! empty( get_option( $this->finished_option_key, 0 ) );
	}

	public function finish() {
		update_option( $this->finished_option_key, 1, 'no' );
	}
}
