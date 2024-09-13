<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Symbols_Taxonomy {

	const SYMBOLS_TAXONOMY = 'tcb_symbols_tax';

	private $_default_terms;

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'init', [ $this, 'register_symbols_tax' ] );
		add_filter( 'tcb_main_frame_localize', [ $this, 'terms_localization' ] );
	}

	public function register_symbols_tax() {
		$tax_labels = $this->get_labels();

		register_taxonomy( self::SYMBOLS_TAXONOMY, [ TCB_Symbols_Post_Type::SYMBOL_POST_TYPE ], [
			'hierarchical'      => true,
			'labels'            => $tax_labels,
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'tcb_symbol' ],
			'show_in_rest'      => true,
		] );

		register_taxonomy_for_object_type( self::SYMBOLS_TAXONOMY, TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );
		$this->insert_default_terms();

	}

	public function insert_default_terms() {
		$terms = [ 'Headers', 'Footers', 'Gutenberg block' ];

		foreach ( $terms as $term ) {
			$exists = term_exists( $term, self::SYMBOLS_TAXONOMY );
			if ( $exists !== 0 && $exists !== null ) {
				$term_id = $exists['term_id'];
			} else {
				$term_insert = wp_insert_term( $term, TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
				if ( ! is_wp_error( $term_insert ) ) {
					$term_id = $term_insert['term_id'];
				}
			}

			if ( isset( $term_id ) ) {
				$this->add_default_term( $term_id );
			}
		}
	}

	public function get_default_terms() {
		//take only h&f because others can be deleted in time
		return array_splice( $this->_default_terms, 0, 2 );
	}

	public function add_default_term( $term_id ) {
		$this->_default_terms[] = $term_id;
	}

	public function get_labels() {

		$default_labels = array(
			'name'              => __( 'Symbols', 'thrive-cb' ),
			'singular_name'     => __( 'Symbol', 'thrive-cb' ),
			'search_items'      => __( 'Search Symbols', 'thrive-cb' ),
			'all_items'         => __( 'All Symbols', 'thrive-cb' ),
			'parent_item'       => __( 'Parent Symbol', 'thrive-cb' ),
			'parent_item_colon' => __( 'Parent Symbol', 'thrive-cb' ),
			'edit_item'         => __( 'Edit Symbol', 'thrive-cb' ),
			'update_item'       => __( 'Update Symbol', 'thrive-cb' ),
			'add_new_item'      => __( 'Add New Symbol', 'thrive-cb' ),
			'new_item_name'     => __( 'New Symbol Name', 'thrive-cb' ),
			'menu_name'         => __( 'Symbols', 'thrive-cb' ),
		);

		return apply_filters( 'tcb_symbols_tax_labels', $default_labels );
	}

	/**
	 * Get the symbols taxonomies split by the fact if they are tax for sections or for normal symbols
	 *
	 * @param bool $show_tax_terms
	 *
	 * @return array|int|WP_Error
	 */
	public function get_symbols_tax_terms( $show_tax_terms = false ) {
		$result        = [];
		$section_terms = [];

		$terms = get_terms( [
			'order'      => 'DESC',
			'orderby'    => 'term_id',
			'taxonomy'   => self::SYMBOLS_TAXONOMY,
			'hide_empty' => false,
		] );

		foreach ( $terms as $key => $term ) {
			if ( ! in_array( $term->name, [ 'Headers', 'Footers' ] ) ) {
				$result[] = $term;
			} else {
				$section_terms[] = $term;
			}
		}

		if ( $show_tax_terms ) {
			return $section_terms;
		}

		return $result;
	}

	/**
	 * Add categories to localization for tcb editor
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function terms_localization( $data ) {
		$data['symbols_tax_terms']  = $this->get_symbols_tax_terms();
		$data['sections_tax_terms'] = $this->get_symbols_tax_terms( true );
		$data['symbols_rest_terms'] = rest_url( sprintf( '%s/%s', 'wp/v2', self::SYMBOLS_TAXONOMY ) );
		$data['symbols_rest_nonce'] = TCB_Utils::create_nonce();
		$data['symbol_type']        = static::get_symbol_type( get_the_ID() );

		return $data;
	}

	/**
	 * Get symbol type based on the category
	 *
	 * @param $symbol_id
	 *
	 * @return bool
	 */
	public static function get_symbol_type( $symbol_id = 0 ) {

		$type = false;

		if ( ! empty( $symbol_id ) ) {
			$terms = get_the_terms( $symbol_id, self::SYMBOLS_TAXONOMY );

			if ( ! empty( $terms ) ) {
				$type = $terms[0]->slug;
			}
		}

		return $type;
	}

	/**
	 * Returns a term id by name from the symbols taxonomy
	 *
	 * @param string $slug
	 *
	 * @return int
	 */
	public static function get_term_id( $slug ) {
		$term = get_term_by( 'slug', $slug, static::SYMBOLS_TAXONOMY );

		return ( $term ) ? $term->term_id : 0;
	}

	/**
	 * Add symbol to taxonomy
	 *
	 * @param int    $id
	 * @param string $tax_slug
	 */
	public static function add_to_tax( $id, $tax_slug ) {
		$term_id = static::get_term_id( $tax_slug );
		wp_set_post_terms( $id, [ $term_id ], TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY );
	}
}

global $tcb_symbol_taxonomy;
/**
 * Main instance of TCB Symbols Dashboard
 *
 * @return TCB_Symbols_Taxonomy
 */
function tcb_symbol_taxonomy() {
	global $tcb_symbol_taxonomy;

	if ( ! $tcb_symbol_taxonomy ) {
		$tcb_symbol_taxonomy = new TCB_Symbols_Taxonomy();
	}

	return $tcb_symbol_taxonomy;
}

tcb_symbol_taxonomy();
