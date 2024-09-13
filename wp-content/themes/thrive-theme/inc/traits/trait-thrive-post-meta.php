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
 * Trait Thrive_Term_Meta
 *
 * Interacts with post meta
 *
 * @property int|string $ID assumes the instance always has an ID
 */
trait Thrive_Post_Meta {

	/**
	 * Array of meta attributes
	 * @var array
	 */
	protected $meta = [];

	/**
	 * Get the meta value for the given meta field.
	 *
	 * @param $meta_field
	 *
	 * @return mixed
	 */
	public function get_meta( $meta_field = '' ) {
		if ( ! isset( $this->meta[ $meta_field ] ) ) {
			$this->meta[ $meta_field ] = get_post_meta( $this->ID, $meta_field, true );
		}

		return $this->meta[ $meta_field ];
	}

	/**
	 * Set the meta value for the given meta field.
	 *
	 * @param $meta_field
	 * @param $meta_value
	 *
	 * @return $this fluent interface
	 */
	public function set_meta( $meta_field, $meta_value ) {
		update_post_meta( $this->ID, $meta_field, $meta_value );

		$this->meta[ $meta_field ] = $meta_value;

		return $this;
	}

	/**
	 * Deletes a meta field
	 *
	 * @param string $meta_field
	 *
	 * @return $this fluent interface
	 */
	public function delete_meta( $meta_field ) {
		delete_post_meta( $this->ID, $meta_field );

		return $this;
	}
}
