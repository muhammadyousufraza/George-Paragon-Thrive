<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVA\Product;
use TVE\Reporting\EventFields\Event_Field;

class Product_Id extends Event_Field {

	public static function key(): string {
		return 'product_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? __( 'Product', 'thrive-apprentice' ) : __( 'Products', 'thrive-apprentice' );
	}

	public function get_title(): string {
		if ( ! empty( $this->value ) ) {
			$product = new Product( (int) $this->value );
		}

		return $product->name ?? __( 'Product', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}

	public static function get_filter_options(): array {
		return array_map( static function ( $product ) {
			return [
				'id'    => (string) $product->get_id(),
				'label' => $product->get_name(),
			];
		}, \TVA\Product::get_items() );
	}
}
