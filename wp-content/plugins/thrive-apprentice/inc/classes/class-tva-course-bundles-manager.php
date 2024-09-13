<?php

class TVA_Course_Bundles_Manager {

	/**
	 * @param false $count_customers
	 *
	 * @return TVA_Bundle[]
	 */
	public static function get_bundles( $count_customers = false ) {

		$bundles = TVA_Bundle::get_list();

		if ( $count_customers ) {
			foreach ( $bundles as $bundle ) {
				$bundle->count_customers();
			}
		}

		return $bundles;
	}

	/**
	 * Saves new course bundle in DB
	 *
	 * @param array $data
	 *
	 * @return TVA_Bundle|WP_Error
	 */
	public static function create_bundle( $data ) {

		if ( ! isset( $data['products'] ) || ! is_array( $data['products'] ) ) {
			$data['products'] = array();
		}

		$bundle = new TVA_Bundle(
			array(
				'name'     => $data['name'],
				'products' => $data['products'],
			)
		);

		$saved = $bundle->save();

		if ( is_wp_error( $saved ) ) {
			/** @var $saved WP_Error */
			return $saved;
		}

		return $bundle;
	}

	/**
	 * Updates a bundle with provided data props based on ID provided in data
	 *
	 * @param array $data
	 *
	 * @return TVA_Bundle|WP_Error
	 */
	public static function update_bundle( $data ) {

		$error = new WP_Error( 'invalid_data', esc_html__( 'Invalid data to update the course bundle', 'thrive-apprentice' ) );

		if ( ! isset( $data['products'] ) || ! is_array( $data['products'] ) || empty( $data['id'] ) || empty( $data['name'] ) ) {
			return $error;
		}

		$bundle = new TVA_Bundle( (int) $data['id'] );

		$bundle->name     = sanitize_text_field( $data['name'] );
		$bundle->products = $data['products'];
		$bundle->id       = (int) $data['id'];

		$saved = $bundle->save();

		if ( is_wp_error( $saved ) ) {
			/** @var $saved WP_Error */
			return $saved;
		}

		return $bundle;
	}

	/**
	 * Gets a list of bundle numbers(not IDs) and returns it
	 *
	 * @return array with all bundle
	 */
	public static function get_all_bundle_numbers() {

		$numbers = array();

		foreach ( static::get_bundles() as $bundle ) {
			$numbers[] = $bundle->number;
		}

		return $numbers;
	}

	/**
	 * Checks all the bundles for a product id and
	 * removes it from products list
	 *
	 * @param string|int $id
	 */
	public static function remove_product_from_bundles( $id ) {

		/** @var TVA_Bundle $bundle */
		foreach ( static::get_bundles() as $bundle ) {
			if ( true === $bundle->contains_product( $id ) ) {
				$bundle->remove_product( $id );
			}
		}
	}

	/**
	 * Updates all the products content sets from the bundles
	 *
	 * @return void
	 */
	public static function update_bundle_products() {
		$bundles = static::get_bundles();
		foreach ( $bundles as $bundle ) {
			foreach ( $bundle->products as $product_id ) {
				$product = new TVA\Product($product_id);
				$product->update_sets();
			}
		}
	}
}
