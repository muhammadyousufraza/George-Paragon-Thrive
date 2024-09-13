<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\UserTemplates;

use TCB\Traits\Is_Singleton;
use TCB_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Template {
	use Is_Singleton;
	use Has_Preview;
	use Has_Post_Type;
	use Has_Taxonomy;

	const OPTION_KEY           = 'tve_user_templates';
	const BACKUP_OPTION_KEY    = 'tve_user_templates_backup';
	const OLD_ID_PREFIX        = 'o_';
	const MIGRATED_ITEM_AMOUNT = 2;

	private static $migrator_instance;

	/**
	 * @var int
	 */
	private $ID;

	/**
	 * @param $id
	 */
	public function __construct( $id = null ) {
		$this->ID = $id;
	}

	/**
	 * @param bool $can_migrate - we migrate only when localizing in the editor, in order to avoid collisions
	 *
	 * @return array|mixed|\WP_Post|null
	 */
	public static function get_all( $can_migrate = false ) {
		$migrator_instance = static::get_migrator_instance();

		if ( $migrator_instance->is_finished() ) {
			$templates = static::get_new_templates();
		} else {
			if ( $can_migrate ) {
				$migrator_instance->migrate_x_items( static::MIGRATED_ITEM_AMOUNT );
			}

			$old_templates = static::get_old_templates();

			if ( empty( $old_templates ) ) {
				$migrator_instance->finish();
			}

			$templates = array_merge( static::get_new_templates(), $old_templates );
		}

		if ( empty( $templates ) || ! is_array( $templates ) ) {
			$templates = [];
		}

		return static::order_templates_by_migration_status( $templates );
	}

	/**
	 * @return array
	 */
	public function get() {
		if ( $this->is_new_template() ) {
			$template = $this->get_post_data();
		} else {
			$templates      = array_values( static::get_old_templates() );
			$template_index = array_search( $this->ID, array_column( $templates, 'id' ), true );
			$template       = ( $template_index === false ) ? [] : $templates[ $template_index ];
		}

		return $template;
	}

	/**
	 * @return bool|mixed|void
	 */
	public static function get_old_templates() {
		$templates = get_option( static::OPTION_KEY, [] );

		/* add the ID as a field along with a prefix so we can identify it */
		foreach ( $templates as $index => $template ) {
			$templates[ $index ]['id'] = static::OLD_ID_PREFIX . $index;
		}

		return $templates;
	}

	/**
	 * Retrieve and map the template data
	 *
	 * @return array
	 */
	public static function get_new_templates() {
		$normalized_templates = [];

		foreach ( static::get_posts() as $template ) {
			/* @var Template $template_instance */
			$template_instance = static::get_instance_with_id( $template->ID );

			$normalized_templates[] = $template_instance->get_post_data();
		}

		return $normalized_templates;
	}

	/**
	 * Reorder the migrated templates so that they show before the non-migrated ones.
	 * Newly inserted templates should also show at the end of everything that is being migrated.
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public static function order_templates_by_migration_status( $templates ) {
		$migrated_or_old_templates  = [];
		$non_migrated_new_templates = [];

		foreach ( $templates as $template ) {
			/* @var Template $template_instance */
			$template_instance = static::get_instance_with_id( $template['id'] );

			if ( empty( $template['is_migrated'] ) && $template_instance->is_new_template() ) {
				$non_migrated_new_templates[] = $template;
			} else {
				$migrated_or_old_templates[] = $template;
			}
		}

		return array_merge( $migrated_or_old_templates, $non_migrated_new_templates );
	}

	/**
	 * @param array $templates
	 */
	public static function save_old_templates( $templates ) {
		update_option( static::OPTION_KEY, $templates, 'no' );
	}

	/**
	 * @param $template_data
	 *
	 * @return int|\WP_Error
	 */
	public static function insert( $template_data ) {
		return static::insert_post( $template_data );
	}

	/**
	 * @param $data
	 */
	public function update( $data ) {
		if ( $this->is_new_template() ) {
			$this->update_post( $data );
		} else {
			$templates = static::get_old_templates();
			$id        = static::normalize_old_id( $this->ID );

			if ( isset( $data['id_category'] ) ) {
				$category_id = Category::normalize_category_id( $data['id_category'] );

				$data['id_category'] = $category_id;
			}

			$templates[ $id ] = array_merge( $templates[ $id ], $data );

			static::save_old_templates( $templates );
		}
	}

	/**
	 * @param $name
	 */
	public function rename( $name ) {
		if ( $this->is_new_template() ) {
			$this->rename_post( $name );
		} else {
			$templates = static::get_old_templates();
			$id        = static::normalize_old_id( $this->ID );

			$templates[ $id ]['name'] = $name;

			static::save_old_templates( $templates );
		}
	}

	public function delete() {
		/* @var Template $template_instance */
		$template_instance = static::get_instance_with_id( $this->ID );

		$template = $template_instance->get();

		static::delete_preview_image( $template['name'] );

		if ( $this->is_new_template() ) {
			$this->delete_post();
		} else {
			$templates = static::get_old_templates();
			$id        = static::normalize_old_id( $this->ID );
			unset( $templates[ $id ] );

			static::save_old_templates( $templates );
		}
	}

	/**
	 * @param bool $can_migrate - we migrate only when localizing in the editor, in order to avoid collisions
	 *
	 * @return array
	 */
	public static function localize( $can_migrate = false ) {
		$templates      = static::get_all( $can_migrate );
		$localized_data = [];

		foreach ( $templates as $template ) {
			$template_data = [
				'id'          => $template['id'],
				'label'       => rawurldecode( $template['name'] ),
				'type'        => empty( $template['type'] ) ? '' : $template['type'],
				'thumb'       => isset( $template['thumb'] ) ? $template['thumb'] : TCB_Utils::get_placeholder_data(),
				'id_category' => isset( $template['id_category'] ) ? $template['id_category'] : null,
			];

			if ( ! empty( $template['thumb']['url'] ) ) {
				$template_data['thumb'] = $template['thumb'];
				//if the image sizes couldn't be retrieved before
				if ( empty( $template['thumb']['h'] ) && ! empty( $template['thumb']['url'] ) && ini_get('allow_url_fopen') ) {
					list( $width, $height ) = getimagesize( $template['thumb']['url'] );

					$template_data['thumb']['h'] = $height;
					$template_data['thumb']['w'] = $width;
				}
			} else {
				$template_data['thumb'] = TCB_Utils::get_placeholder_data();
			}

			if ( $template_data['type'] === 'button' ) {
				$template_data['media']   = $template['media_css'];
				$template_data['content'] = stripslashes( $template['content'] );
			}

			$localized_data[] = $template_data;
		}

		return $localized_data;
	}

	/**
	 * @return array
	 */
	public function load() {
		/* @var $template_instance Template $template_instance */
		$template_instance = Template::get_instance_with_id( $this->ID );

		$template  = $template_instance->get();
		$media_css = null;
		if ( isset( $template['media_css'] ) ) {
			$media_css = $template['media_css'];
		}
		if ( is_array( $media_css ) ) {
			$media_css = array_map( 'stripslashes', $template['media_css'] );
			if ( $media_css ) {
				/* make sure the server did not mess up the inline rules - e.g. instances where it replaces double quotes with single quotes */
				foreach ( $media_css as $k => $value ) {
					$media_css[ $k ] = preg_replace( "#data-css='(.+?)'#s", 'data-css="$1"', $value );
				}
			}
		}


		$template_data = [
			'html_code'   => stripslashes( $template['content'] ),
			'css_code'    => stripslashes( $template['css'] ),
			'is_imported' => $template['is_imported'] ?: 0,
			'media_css'   => $media_css,
		];

		if ( ob_get_contents() ) {
			ob_end_clean();
		}

		return $template_data;
	}

	/**
	 * @return bool
	 */
	public function is_new_template() {
		return is_numeric( $this->ID );
	}

	/**
	 * @return Migrator
	 */
	public static function get_migrator_instance() {
		if ( empty( static::$migrator_instance ) ) {
			static::$migrator_instance = new Migrator( [
				'option_key'          => static::OPTION_KEY,
				'backup_option_key'   => static::BACKUP_OPTION_KEY,
				'finished_option_key' => 'thrive_user_template_migration_finished',
				'migrate_callback'    => static function ( $templates ) {
					$template = array_shift( $templates );

					if ( ! empty( $template ) ) {
						static::insert_post( $template, true );
					}

					static::save_old_templates( $templates );

					return $templates;
				},
			] );
		}

		return static::$migrator_instance;
	}

	/**
	 * Transforms the format used to identify the old content templates into a numeric one.
	 * 'o_5' -> 5
	 *
	 * @param $old_id
	 *
	 * @return int
	 */
	public static function normalize_old_id( $old_id ) {
		return (int) str_replace( static::OLD_ID_PREFIX, '', $old_id );
	}
}
