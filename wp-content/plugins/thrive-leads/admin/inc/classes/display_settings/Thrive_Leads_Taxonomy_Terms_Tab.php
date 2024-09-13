<?php

/**
 * Class TaxonomyTermsTab
 */
class Thrive_Leads_Taxonomy_Terms_Tab extends Thrive_Leads_Tab implements Thrive_Leads_Tab_Interface {
	/**
	 * Stores a cache of post terms
	 *
	 * @var array
	 */
	public static $post_terms_cache = [];

	public function __construct() {

	}

	protected function matchItems() {
		if ( ! $this->getItems() ) {
			return array();
		}

		$optionArr = $this->getSavedOptions()->getTabSavedOptions( 1, $this->hanger );

		foreach ( $this->getItems() as $key => $term ) {
			$option = new Thrive_Leads_Option();
			$option->setLabel( $term->name );
			$option->setId( $term->term_id );
			$option->setType( $term->taxonomy );
			$option->setIsChecked( in_array( $term->term_id, $optionArr ) );
			$this->options[] = $option;
		}
	}

	protected function getSavedOption( $item ) {
		return $this->getSavedOptionForTab( 1, $item->term_id );
	}

	/**
	 * @return $this
	 */
	protected function initItems() {
		$taxonomies = get_taxonomies( array( 'public' => true ) );

		/* exclude taxonomies that can be "too many" - a user once had 10.000 tags => breaks the ajax */
		$exclude = array( 'post_tag' );

		// Filter for excluding categories [used on TTW]
		$exclude = apply_filters( 'tl_filter_exclude_categories_data', $exclude );

		$tag_found = false;
		foreach ( $taxonomies as $i => $t ) {
			if ( in_array( $t, $exclude ) !== false ) {
				if ( empty( $tag_found ) ) {
					$tag_found = $t;
				}
				unset( $taxonomies[ $i ] );
			}
		}
		$this->setItems( get_terms( $taxonomies ) );
		/**
		 * include the post_tag taxonomy
		 */
		$options = $this->getSavedOptions()->getTabSavedOptions( 1, $this->hanger );
		if ( isset( $tag_found ) && ! empty( $options ) ) {
			$this->items = array_merge( $this->items, get_terms( $tag_found, array(
				'include' => $options,
			) ) );
		}

		return $this;
	}

	/**
	 * For this case the filters are the taxonomies
	 *
	 * @return array of Filter elements
	 */
	public function getFilters() {
		if ( ! empty( $this->filters ) ) {
			return $this->filters;
		}

		$filters       = array();
		$excluded_tabs = array();

		// Apply filter for excluding categories tabs [used on TTW]
		$excluded_tabs = apply_filters( 'tl_filter_exclude_categories_tab', $excluded_tabs );

		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $taxonomy ) {

			if ( in_array( $taxonomy->name, $excluded_tabs, true ) ) {
				continue;
			}

			$filters[] = new Thrive_Leads_Filter( 'taxonomyFilter', $taxonomy->name, $taxonomy->label );
		}

		return $filters;
	}

	/**
	 * @param $taxonomyName
	 *
	 * @return bool|object
	 */
	public function getTaxonomy( $taxonomyName ) {
		return get_taxonomy( $taxonomyName );
	}

	/**
	 * @param $taxonomy
	 *
	 * @return bool
	 */
	public function displayWidget( $taxonomy = null ) {
		if ( ! $taxonomy ) {
			return false;
		}

		$this->hanger = 'show_group_options';
		$showOption   = $this->getSavedOption( $taxonomy );
		$display      = $showOption->isChecked;

		if ( $display === true ) {
			$this->hanger = 'hide_group_options';
			$display      = ! $this->getSavedOption( $taxonomy )->isChecked;
		}

		return $display;

	}

	public function isTaxonomyAllowed( $taxonomy = null ) {
		$this->hanger = 'show_group_options';

		return $this->getSavedOption( $taxonomy )->isChecked;
	}

	public function isTaxonomyDenied( $taxonomy = null ) {
		$this->hanger = 'hide_group_options';

		return $this->getSavedOption( $taxonomy )->isChecked;
	}

	public function isPostAllowed( $post ) {
		//get all taxonomy terms for all taxonomies the $post has
		$post_terms = static::get_post_terms( $post );

		//check if any of the posts taxonomy terms is checked
		$this->hanger = 'show_group_options';
		foreach ( $post_terms as $post_term ) {
			if ( $this->getSavedOption( $post_term )->isChecked ) {
				return true;
			}
		}

		return false;
	}

	public function isPostDenied( $post ) {
		$post_terms = static::get_post_terms( $post );

		//check if any of the posts taxonomy terms is checked
		$this->hanger = 'hide_group_options';
		foreach ( $post_terms as $post_term ) {
			if ( $this->getSavedOption( $post_term )->isChecked ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * get all taxonomy terms for all taxonomies the $post has
	 *
	 * @param $post
	 *
	 * @return WP_Term[]
	 */
	public static function get_post_terms( $post ) {
		if ( ! array_key_exists( $post->ID, static::$post_terms_cache ) ) {
			$taxonomies                            = get_taxonomies( array( 'public' => true ) );
			static::$post_terms_cache[ $post->ID ] = wp_get_post_terms( $post->ID, $taxonomies );
		}

		return static::$post_terms_cache[ $post->ID ];
	}

}
