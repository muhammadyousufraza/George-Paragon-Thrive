<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Skin_Taxonomy
 */
class Thrive_Skin_Taxonomy {

	/**
	 * @var array|false|WP_Term
	 */
	public $terms;

	/**
	 * Register taxonomy for skins
	 */
	public static function register_thrive_templates_tax() {

		register_taxonomy( SKIN_TAXONOMY, [ THRIVE_TEMPLATE ], [
			'hierarchical'      => false,
			'labels'            => static::get_taxonomy_labels(),
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => THRIVE_TEMPLATE ],
			'show_in_rest'      => true,
			'public'            => false,
		] );

		register_taxonomy_for_object_type( SKIN_TAXONOMY, THRIVE_TEMPLATE );
		register_taxonomy_for_object_type( SKIN_TAXONOMY, THRIVE_SECTION );
		register_taxonomy_for_object_type( SKIN_TAXONOMY, THRIVE_LAYOUT );
		register_taxonomy_for_object_type( SKIN_TAXONOMY, THRIVE_TYPOGRAPHY );
	}

	/**
	 * Get the labels for the taxonomy
	 *
	 * @return array
	 */
	public static function get_taxonomy_labels() {
		$default_labels = [
			'name'              => __( 'Skins', 'thrive-theme' ),
			'singular_name'     => __( 'Skin', 'thrive-theme' ),
			'search_items'      => __( 'Search Skins', 'thrive-theme' ),
			'all_items'         => __( 'All Skins', 'thrive-theme' ),
			'parent_item'       => __( 'Parent Skin', 'thrive-theme' ),
			'parent_item_colon' => __( 'Parent Skin', 'thrive-theme' ),
			'edit_item'         => __( 'Edit Skin', 'thrive-theme' ),
			'update_item'       => __( 'Update Skin', 'thrive-theme' ),
			'add_new_item'      => __( 'Add New Skin', 'thrive-theme' ),
			'new_item_name'     => __( 'New Skin Name', 'thrive-theme' ),
			'menu_name'         => __( 'Skins', 'thrive-theme' ),
		];

		return apply_filters( 'thrive_templates_taxonomy_labels', $default_labels );
	}

	/**
	 * Make skin active
	 *
	 * @param int $term_id
	 */
	public static function set_skin_active( $term_id ) {
		$new_skin     = new Thrive_Skin( $term_id );
		$current_skin = new Thrive_Skin( 0 );

		/* If we do not have an ID on the new skin we no not set a skin active */
		/* We change the active skin only if it's different than the new one */
		if ( ! empty( $new_skin->ID ) && $new_skin->ID !== $current_skin->ID ) {
			//set the new skin as active
			$new_skin->set_meta( Thrive_Skin::SKIN_META_ACTIVE, 1 );

			//make the current skin inactive
			$current_skin->set_meta( Thrive_Skin::SKIN_META_ACTIVE, 0 );

			$new_skin->update_colors();
		}
	}

	/**
	 * Return all terms from the taxonomy
	 *
	 * @param string  $output
	 * @param boolean $exclude_default_skin
	 *
	 * @return [WP_Term]|int|WP_Error
	 */
	public static function get_all( $output = 'object', $exclude_default_skin = true ) {
		$terms = get_terms( [
			'taxonomy'   => SKIN_TAXONOMY,
			'hide_empty' => 0,
			'meta_query' => [
				[
					'key'     => 'thrive_scope',
					'compare' => 'NOT EXISTS',
					'value'   => 'null',
				],
			],
		] );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			$terms = [];
		} else {

			if ( $exclude_default_skin ) {
				$terms = array_filter( $terms, static function ( $term ) {
					return $term->name !== Thrive_Skin::DEFAULT_SKIN;
				} );
			}

			$terms = array_map( static function ( $term ) use ( $output ) {
				if ( $output === 'ids' ) {
					$term = $term->term_id;
				} elseif ( $output === 'ids=>name' ) {
					/**
					 * Get term mapping
					 */
					$term = [
						'id'   => $term->term_id,
						'name' => $term->name,
					];
				} else {
					$skin = new Thrive_Skin( $term->term_id );

					$term->preview_url = $skin->get_preview_url();
					foreach ( Thrive_Skin::meta_fields() as $field => $default_value ) {
						$term->$field = $skin->get_meta( $field );
					}
				}

				return $term;
			}, $terms );
		}

		return array_values( $terms );
	}

	/**
	 * Returns a skin with the specified id
	 *
	 * @param int $skin_id
	 *
	 * @return array|false|WP_Term
	 */
	public static function get_skin_by_id( $skin_id ) {
		return get_term_by( 'term_id', $skin_id, SKIN_TAXONOMY );
	}

	/**
	 * Returns the Thrive Theme active skin tag
	 *
	 * Needed for visual builders when ThriveTheme is active to make a call to cloud templates with active skin tag
	 *
	 * @return string
	 */
	public static function get_active_skin_tag() {
		$active_skin = get_terms( [
			'taxonomy'   => SKIN_TAXONOMY,
			'meta_query' => [
				[
					'key'     => Thrive_Skin::SKIN_META_ACTIVE,
					'value'   => 1,
					'compare' => '=',
				],
			],
			'hide_empty' => 0,
		] );

		if ( ! is_wp_error( $active_skin ) && ! empty( $active_skin ) ) {
			return get_term_meta( $active_skin[0]->term_id, Thrive_Skin::TAG, true );
		}

		return '';
	}

	/**
	 * Get a list of skins from the cloud
	 *
	 * @param string $scope controls the scope of the getter. 'ttb' is the default scope for TTB
	 *
	 * @return array
	 */
	public static function get_cloud_skins( $scope = 'ttb' ) {

		try {
			$skins = Thrive_Theme_Cloud_Api_Factory::build( 'skins' )->get_items( [ 'filters' => compact( 'scope' ) ] );
		} catch ( Exception $e ) {
			$skins = $e->getMessage();
		}

		return $skins;
	}
}
