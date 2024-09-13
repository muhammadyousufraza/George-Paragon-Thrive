<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\SavedLandingPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Migrator {
	private $option_meta_key;
	private $backup_option_meta_key;
	private $option_content_key;
	private $backup_option_content_key;
	private $finished_option_key;
	private $migrate_callback;

	public function __construct( $options ) {
		$this->option_meta_key           = $options['option_meta_key'];
		$this->backup_option_meta_key    = $options['backup_option_meta_key'];
		$this->option_content_key        = $options['option_content_key'];
		$this->backup_option_content_key = $options['backup_option_content_key'];
		$this->finished_option_key       = $options['finished_option_key'];
		$this->migrate_callback          = $options['migrate_callback'];
	}

	/**
	 * @param $amount
	 */
	public function migrate_x_items( $amount ) {
		$saved_lp_metas    = get_option( $this->option_meta_key, [] );
		$saved_lp_contents = get_option( $this->option_content_key, [] );

		$items = [
			'meta'    => $saved_lp_metas,
			'content' => $saved_lp_contents,
		];

		if ( is_array( $saved_lp_metas ) && is_array( $saved_lp_contents ) && ( count( $saved_lp_metas ) === count( $saved_lp_contents ) ) ) {
			/* make sure we have a backup */
			if ( empty( get_option( $this->backup_option_meta_key ) ) ) {
				update_option( $this->backup_option_meta_key, $saved_lp_metas, 'no' );
			}
			if ( empty( get_option( $this->backup_option_content_key ) ) ) {
				update_option( $this->backup_option_content_key, $saved_lp_contents, 'no' );
			}

			if ( ! empty( get_option( $this->backup_option_meta_key ) ) && ! empty( get_option( $this->backup_option_content_key ) ) ) {
				for ( $i = 0; $i < $amount; $i ++ ) {
					$items = call_user_func( $this->migrate_callback, $items );
				}
			}
		}
	}

	public function is_finished() {
		return ! empty( get_option( $this->finished_option_key, 0 ) );
	}

	public function finish() {
		update_option( $this->finished_option_key, 1, 'no' );
	}
}
