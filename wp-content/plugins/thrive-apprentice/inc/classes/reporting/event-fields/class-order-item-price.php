<?php

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Order_Item_Price extends Event_Field {

	public static function key(): string {
		return 'order_item_price';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return __( 'Order item price', 'thrive-apprentice' );
	}

	public function get_title(): string {
		return __( 'Order item price', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (float) $value;
	}
}
