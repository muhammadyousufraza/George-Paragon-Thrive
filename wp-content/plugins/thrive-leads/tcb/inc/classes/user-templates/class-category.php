<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\UserTemplates;

use TCB\Traits\Is_Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Category {
	use Is_Singleton;
	use Has_Taxonomy;

	const OPTION_KEY          = 'tve_user_templates_categories';
	const BACKUP_OPTION_KEY   = 'tve_user_templates_categories_backup';
	const FINISHED_OPTION_KEY = 'thrive_user_template_category_migration_finished';

	const PAGE_TEMPLATE_IDENTIFIER = '[#page#]';

	public static $migration_replacement_map = [];

	public static $default_categories = [
		'uncategorized'  => [
			'name'   => 'Uncategorized',
			'old_id' => '',
			'slug'   => 'uncategorized',
			'type'   => 'uncategorized',
		],
		'page-templates' => [
			'name'   => 'Page Templates',
			'old_id' => '[#page#]',
			'slug'   => 'page-templates',
			'type'   => 'page_template',
		],
	];

	/**
	 * @var Migrator
	 */
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
	 * @return array
	 */
	public static function get_all() {
		$migrator_instance = static::get_migrator_instance();

		if ( $migrator_instance->is_finished() ) {
			$categories = static::get_terms();
			$categories = static::normalize_categories( $categories );
		} else {
			$categories = static::get_old_categories();
		}

		return $categories;
	}

	/**
	 * @param $categories
	 *
	 * @return array
	 */
	public static function normalize_categories( $categories ) {
		$normalized_categories = [];

		foreach ( $categories as $category ) {
			$normalized_categories[] = [
				'id'   => $category->term_id,
				'name' => $category->name,
			];
		}

		return $normalized_categories;
	}

	/**
	 * Move the categories from the option field into the wp_terms table.
	 * After doing this, it updates the 'id_category' value of the templates ( which are still in the option field, because this always runs first )
	 */
	public static function maybe_migrate() {
		$migrator_instance = static::get_migrator_instance();

		if ( ! $migrator_instance->is_finished() ) {
			static::insert_default_categories();

			$old_categories = static::get_old_categories();
			$migrator_instance->migrate_x_items( count( $old_categories ) );
			$templates = Template::get_old_templates();

			/* update the id_category for the templates */
			foreach ( $templates as $index => $template ) {
				if ( ! empty( static::$migration_replacement_map[ $template['id_category'] ] ) ) {
					$templates[ $index ]['id_category'] = static::$migration_replacement_map[ $template['id_category'] ];
				}
			}

			Template::save_old_templates( $templates );
			static::save_old_categories( [] );

			$migrator_instance->finish();
		}
	}

	/**
	 * Adds 'uncategorized' and 'page templates' to the terms table.
	 */
	public static function insert_default_categories() {
		foreach ( static::$default_categories as $default_category ) {
			$new_category    = static::insert_term( $default_category['name'] );
			$new_category_id = is_wp_error( $new_category ) ? $new_category->get_error_data( 'term_exists' ) : $new_category['term_id'];

			/* @var Category $category_instance */
			$category_instance = static::get_instance_with_id( $new_category_id );
			$category_instance->update_meta( 'type', $default_category['type'] );

			static::add_to_migration_replacement_map( $default_category['old_id'], $new_category_id );
		}
	}

	/**
	 * @param $old_category_id
	 * @param $new_category_id
	 */
	public static function add_to_migration_replacement_map( $old_category_id, $new_category_id ) {
		if ( ! empty( $new_category_id ) ) {
			static::$migration_replacement_map[ $old_category_id ] = $new_category_id;
		}
	}

	/**
	 * @return array|bool|mixed|void
	 */
	public static function get_old_categories() {
		$categories = get_option( static::OPTION_KEY, [] );

		if ( empty( $categories ) || ! is_array( $categories ) ) {
			$categories = [];
		}

		return $categories;
	}

	/**
	 * @return mixed
	 */
	public static function get_migrator_instance() {
		if ( empty( static::$migrator_instance ) ) {
			static::$migrator_instance = new Migrator( [
				'option_key'          => static::OPTION_KEY,
				'backup_option_key'   => static::BACKUP_OPTION_KEY,
				'finished_option_key' => static::FINISHED_OPTION_KEY,
				'migrate_callback'    => static function ( $categories ) {
					$old_category = array_shift( $categories );

					if ( ! empty( $old_category ) ) {
						$new_category = static::insert_term( $old_category['name'] );

						$new_category_id = is_wp_error( $new_category ) ? $new_category->get_error_data( 'term_exists' ) : $new_category['term_id'];

						static::add_to_migration_replacement_map( $old_category['id'], $new_category_id );
					}

					return $categories;
				},
			] );
		}

		return static::$migrator_instance;
	}

	/**
	 * @param $categories
	 */
	public static function save_old_categories( $categories ) {
		update_option( static::OPTION_KEY, $categories, 'no' );
	}

	/**
	 * @param $category_name
	 *
	 * @return array
	 */
	public static function add( $category_name ) {
		$new_category = static::insert_term( $category_name );

		return [
			'id'   => $new_category['term_id'],
			'name' => $category_name,
		];
	}

	/**
	 * @param $name
	 */
	public function rename( $name ) {
		$this->rename_term( $name );
	}

	public function delete() {
		$this->delete_term();
	}

	/**
	 * '$category_id' can be empty, '[#page#], or a regular numeric ID
	 * empty -> get the ID for the 'uncategorized' term slug
	 * [#page#] -> get the ID for the 'page-templates' term slug
	 * numeric -> cast to int
	 *
	 * @param $category_id
	 *
	 * @return int
	 */
	public static function normalize_category_id( $category_id ) {
		if ( empty( $category_id ) ) {
			$category    = get_term_by( 'slug', static::$default_categories['uncategorized']['slug'], static::get_taxonomy_name() );
			$category_id = $category ? $category->term_id : 0;
		} else if ( is_numeric( $category_id ) ) {
			$category_id = (int) $category_id;
		} else if ( $category_id === static::PAGE_TEMPLATE_IDENTIFIER ) {
			$category    = get_term_by( 'slug', static::$default_categories['page-templates']['slug'], static::get_taxonomy_name() );
			$category_id = $category ? $category->term_id : 0;
		} else {
			$category_id = 0;
		}

		return $category_id;
	}
}
