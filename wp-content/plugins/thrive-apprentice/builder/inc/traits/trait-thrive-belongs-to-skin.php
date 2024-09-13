<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package theme-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Offers functionality for binding a piece of content (wp post) to a skin (term)
 *
 * Trait Thrive_Belongs_To_Skin
 */
trait Thrive_Belongs_To_Skin {
	/**
	 * Store the cached version of the skin id. `wp_get_object_terms` is expensive and not cached internally in WordPress
	 *
	 * @var int|null
	 */
	protected $cached_skin_id = null;

	/**
	 * Assign current object to a skin
	 *
	 * @param $skin_id
	 */
	public function assign_to_skin( $skin_id = null ) {
		$skin_id = empty( $skin_id ) ? thrive_skin()->ID : $skin_id;
		wp_set_object_terms( $this->ID, $skin_id, SKIN_TAXONOMY );
		$this->cached_skin_id = $skin_id;
	}

	/**
	 * Get the associated skin (term) ID
	 *
	 * @return int the skin ID
	 */
	public function get_skin_id() {
		if ( ! isset( $this->cached_skin_id ) ) {
			$terms = wp_get_object_terms( $this->ID, SKIN_TAXONOMY );

			$term                 = reset( $terms );
			$this->cached_skin_id = $term ? (int) $term->term_id : 0;
		}

		return $this->cached_skin_id;
	}

	/**
	 * @param int $skin_id
	 */
	public function set_cached_skin_id( $skin_id ) {
		$this->cached_skin_id = (int) $skin_id;
	}
}
