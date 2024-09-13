<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA_Bundle;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Bundle_Data
 */
class Bundle_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'bundle_data';
	}

	public static function get_nice_name() {
		return 'Apprentice bundle';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'bundle_id', 'bundle_title' ];
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for TVA_Bundle object' );
		}

		$bundle = null;
		if ( is_a( $param, 'TVA_Bundle' ) ) {
			$bundle = $param;
		} elseif ( is_numeric( $param ) ) {
			$bundle = new TVA_Bundle( $param );
		} elseif ( is_string( $param ) ) {
			$bundle = TVA_Bundle::init_by_number( $param );
		} elseif ( is_array( $param ) ) {
			$bundle = new TVA_Bundle( $param['bundle_id'] );
		}

		if ( $bundle ) {

			return [
				'bundle_title' => $bundle->name,
				'bundle_id'    => $bundle->id,
			];
		}

		return $bundle;
	}
}
