<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\SavedLandingPages;

use TCB\Traits\Is_Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Saved_Lp {
	use Is_Singleton;
	use Has_Post_Type;

	const OPTION_META_KEY           = 'tve_saved_landing_pages_meta';
	const OPTION_CONTENT_KEY        = 'tve_saved_landing_pages_content';
	const BACKUP_OPTION_META_KEY    = 'tve_saved_landing_pages_meta_backup';
	const BACKUP_OPTION_CONTENT_KEY = 'tve_saved_landing_pages_content_backup';
	const OLD_ID_PREFIX             = 'o_';
	const MIGRATED_ITEM_AMOUNT      = 1;

	private static $migrator_instance;
	/**
	 * @var int
	 */
	private $ID;

	public function __construct( $id = null ) {
		$this->ID = $id;
	}

	/**
	 * @param bool $can_migrate - we migrate only when localizing in the editor, in order to avoid collisions
	 *
	 * @return array|mixed|\WP_Post|null
	 */
	public static function get_all( $can_migrate = false ) {
		/* @var Migrator $migrator_instance */
		$migrator_instance = static::get_migrator_instance();

		if ( $migrator_instance->is_finished() ) {
			$saved_lps = static::get_new_saved_lps();
		} else {
			if ( $can_migrate ) {
				$migrator_instance->migrate_x_items( static::MIGRATED_ITEM_AMOUNT );
			}

			/* because it's used only for displaying in modal we can only get the metas without the content */
			$old_saved_lps_meta = static::get_old_saved_lps_meta();

			if ( empty( $old_saved_lps_meta ) ) {
				$migrator_instance->finish();
			}

			$new_saved_lps = static::get_new_saved_lps();

			$saved_lps = array_merge( $new_saved_lps, $old_saved_lps_meta );
		}

		if ( empty( $saved_lps ) || ! is_array( $saved_lps ) ) {
			$saved_lps = [];
		}

		return static::order_saved_lps_by_migration_status( $saved_lps );
	}

	/**
	 * @return array
	 */
	public function get() {
		if ( $this->is_new_saved_lp() ) {
			$saved_lp = $this->get_post_data();
		} else {
			$saved_lps      = array_values( static::get_old_saved_lps_meta() );
			$saved_lp_index = array_search( $this->ID, array_column( $saved_lps, 'id' ), true );
			$saved_lp       = ( $saved_lp_index === false ) ? [] : $saved_lps[ $saved_lp_index ];
		}

		return $saved_lp;
	}

	/**
	 * @return bool
	 */
	public function is_new_saved_lp() {
		return is_numeric( $this->ID );
	}

	/**
	 * @return bool|mixed|void
	 */
	public static function get_old_saved_lps_meta() {
		$lp_metas = get_option( static::OPTION_META_KEY, [] );

		/* add the ID as a field along with a prefix so we can identify it */
		foreach ( $lp_metas as $index => $lp ) {
			$lp_metas[ $index ]['id'] = static::OLD_ID_PREFIX . $index;
		}

		return $lp_metas;
	}

	/**
	 * @return array|false|mixed|void
	 */
	public static function get_old_saved_lps_content() {
		$lp_contents = get_option( static::OPTION_CONTENT_KEY, [] );

		/* add the ID as a field along with a prefix so we can identify it */
		foreach ( $lp_contents as $index => $lp ) {
			$lp_contents[ $index ]['id'] = static::OLD_ID_PREFIX . $index;
		}

		return $lp_contents;
	}

	public static function get_new_saved_lps() {
		$normalized_saved_lps = [];

		foreach ( static::get_posts() as $saved_lp ) {
			/* @var Saved_Lp $saved_lp_instance */
			$saved_lp_instance      = static::get_instance_with_id( $saved_lp->ID );
			$normalized_saved_lps[] = $saved_lp_instance->get_localized_post_data();
		}

		return $normalized_saved_lps;
	}

	/**
	 * @param array $saved_lps
	 */
	public static function save_old_saved_lps( $saved_lps_meta, $saved_lps_content ) {
		update_option( static::OPTION_META_KEY, $saved_lps_meta, 'no' );
		update_option( static::OPTION_CONTENT_KEY, $saved_lps_content, 'no' );
	}

	/**
	 * @param array $saved_lp_data
	 * @param bool  $is_migrated
	 *
	 * @return int|\WP_Error
	 */
	public static function insert( $saved_lp_data, $is_migrated = false ) {
		return static::insert_post( $saved_lp_data, $is_migrated );
	}

	/**
	 * Transforms the format used to identify the old content saved_lps into a numeric one.
	 * 'o_5' -> 5
	 *
	 * @param $old_id
	 *
	 * @return int
	 */
	public static function normalize_old_id( $old_id ) {
		return (int) str_replace( static::OLD_ID_PREFIX, '', $old_id );
	}

	/**
	 * @param $id
	 */
	public function delete() {
		if ( $this->is_new_saved_lp() ) {
			/* @var Saved_Lp $saved_lp_instance */
			$saved_lp_instance = static::get_instance_with_id( $this->ID );
			$saved_lp_instance->remove_post();
		} else {
			$saved_lps_meta    = static::get_old_saved_lps_meta();
			$saved_lps_content = static::get_old_saved_lps_content();
			$id                = (int) str_replace( static::OLD_ID_PREFIX, '', $this->ID );

			/**
			 * Delete also the generated preview image
			 */
			if ( ! empty( $saved_lps_meta[ $this->ID ] ) && ! empty( $saved_lps_meta[ $this->ID ]['preview_image'] ) ) {

				$upload_dir = tve_filter_upload_user_saved_lp_location( wp_upload_dir() );
				$base       = $upload_dir['basedir'] . $upload_dir['subdir'];
				$file_name  = $base . '/' . basename( $saved_lps_meta[ $id ]['preview_image']['url'] );
				@unlink( $file_name );
			}


			unset( $saved_lps_meta[ $id ], $saved_lps_content[ $id ] );

			static::save_old_saved_lps( $saved_lps_meta, $saved_lps_content );
		}
	}

	/**
	 * @param bool $can_migrate - we migrate only when localizing in the editor, in order to avoid collisions
	 *
	 * @return array
	 */
	public static function localize( $can_migrate = false ) {
		$saved_lps      = static::get_all( $can_migrate );
		$localized_data = [];

		foreach ( $saved_lps as $saved_lp ) {
			$saved_lp_data       = [];
			$saved_lp_data['id'] = $saved_lp['id'];

			foreach ( $saved_lp as $key => $value ) {
				$saved_lp_data[ $key ] = $value;
			}

			$localized_data[] = $saved_lp_data;
		}

		return $localized_data;
	}

	/**
	 * Reorder the migrated saved_lps so that they show before the non-migrated ones.
	 * Newly inserted saved_lps should also show at the end of everything that is being migrated.
	 *
	 * @param $saved_lps
	 *
	 * @return array
	 */
	public static function order_saved_lps_by_migration_status( $saved_lps ) {
		$migrated_or_old_saved_lps  = [];
		$non_migrated_new_saved_lps = [];

		foreach ( $saved_lps as $saved_lp ) {
			/* @var Saved_Lp $saved_lp_instance */
			$saved_lp_instance = static::get_instance_with_id( $saved_lp['id'] );

			if ( empty( $saved_lp['is_migrated'] ) && $saved_lp_instance->is_new_saved_lp() ) {
				$non_migrated_new_saved_lps[] = $saved_lp;
			} else {
				$migrated_or_old_saved_lps[] = $saved_lp;
			}
		}

		return array_merge( $migrated_or_old_saved_lps, $non_migrated_new_saved_lps );
	}

	public static function get_migrator_instance() {
		if ( empty( static::$migrator_instance ) ) {
			static::$migrator_instance = new Migrator( [
				'option_meta_key'           => static::OPTION_META_KEY,
				'backup_option_meta_key'    => static::BACKUP_OPTION_META_KEY,
				'option_content_key'        => static::OPTION_CONTENT_KEY,
				'backup_option_content_key' => static::BACKUP_OPTION_CONTENT_KEY,
				'finished_option_key'       => 'thrive_saved_lps_migration_finished',
				'migrate_callback'          => static function ( $meta_content ) {
					if (
						! empty( $meta_content['meta'] ) && is_array( $meta_content['meta'] ) &&
						! empty( $meta_content['content'] ) && is_array( $meta_content['content'] )
					) {
						$saved_lp_meta    = array_shift( $meta_content['meta'] );
						$saved_lp_content = array_shift( $meta_content['content'] );

						if ( ! empty( $saved_lp_meta ) && ! empty( $saved_lp_content ) ) {
							$data = array_merge( $saved_lp_meta, $saved_lp_content );

							static::insert( $data, true );
						}

						static::save_old_saved_lps( $meta_content['meta'], $meta_content['content'] );

						return [ $meta_content['meta'], $meta_content['content'] ];
					}
				},
			] );
		}

		return static::$migrator_instance;
	}
}
