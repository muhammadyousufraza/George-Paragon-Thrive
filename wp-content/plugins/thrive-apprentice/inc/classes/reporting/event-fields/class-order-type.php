<?php

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Order_Type extends Event_Field {

	public static function key(): string {
		return 'order_type';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return __( 'Order type', 'thrive-apprentice' );
	}

	public function get_title(): string {
		return __( 'Order type', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (string) $value;
	}
}