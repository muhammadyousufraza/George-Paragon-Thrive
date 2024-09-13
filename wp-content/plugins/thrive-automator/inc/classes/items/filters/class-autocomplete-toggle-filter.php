<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Field values should be like
 * [
 *      item1 => [ 'label' => '' , 'items' => [ item11 => [label => '' value => ''], item12 => [label => '' value => '']]
 * ]
 * Those go as deep as a field doesnt have a value set and it has items
 */
class Autocomplete_Toggle extends Autocomplete {
	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'autocomplete_toggle';
	}

	public static function get_name() {
		return __( 'Autocomplete toggle', 'thrive-automator' );
	}
}
