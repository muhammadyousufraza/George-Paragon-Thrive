<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Traits\Automation_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Trigger_Field
 *
 * for the time being trigger field is the same as action field
 * we might change this in the future
 */
abstract class Trigger_Field extends Action_Field {
	use Automation_Item;

	/**
	 *
	 * @deprecated since version 1.3.0
	 *
	 * @see        Trigger_Field::get_field_values - which support the same functionality but with additional filters
	 */
	public static function get_options_callback( $trigger_id, $trigger_data ) {
		return [
			[
				'id'    => 1,
				'label' => 'Label 1',
			],
		];
	}


	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 * FIELD_TYPE_SELECT, FIELD_TYPE_AUTOCOMPLETE, FIELD_TYPE_CHECKBOX should have their values fetched
	 * Data format should be like array{ array{id: String|int, label: String} , ...}
	 *
	 * @param $filters       array of filters to apply to the options
	 *                       e.g:
	 *                       - 'trigger_id' => string - current trigger id
	 *                       - 'trigger_data' => array - current trigger data
	 *                       - 'search' => string - search term to filter the options by
	 *                       - 'limit'  => int - limit the number of options to return (-1 could be used to return all)
	 *                       - 'page'   => int - page number to return (0 based)
	 */
	public static function get_field_values( $filters = [] ) {
		$trigger_id   = $filters['trigger_id'] ?? '';
		$trigger_data = $filters['trigger_data'] ?? [];

		return static::get_options_callback( $trigger_id, $trigger_data );
	}
}
