<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TAP_DB
 */
class TAP_DB {
	/**
	 * Get webhook post meta
	 *
	 * @param $webhook_id
	 *
	 * @return array|mixed|object|null
	 */
	public static function get_automator_webhook_fields_data( $webhook_id ) {

		global $wpdb;
		$result  = [];
		$query   = $wpdb->prepare( "SELECT meta_key AS 'key', meta_value AS 'value' FROM $wpdb->postmeta WHERE meta_key = %s", 'tap-webhook-' . $webhook_id );
		$results = $wpdb->get_results( $query, ARRAY_A );
		if ( ! empty( $results[0] ) ) {
			$result = maybe_unserialize( $results[0]['value'] );
		}

		return $result;
	}

	/**
	 * Get automation post meta data
	 *
	 * @param $post_id
	 *
	 * @return array|mixed|object|null
	 */
	public static function get_automator_post_meta( $post_id, $specific_meta = null ) {
		global $wpdb;
		if ( ! empty( $specific_meta ) ) {
			if ( strpos( $specific_meta, 'tap-' ) === false ) {
				$specific_meta = 'tap-' . $specific_meta;
			}
			$query = $wpdb->prepare( "SELECT meta_key AS 'key', meta_value AS 'value' FROM $wpdb->postmeta WHERE post_id = %s AND meta_key = %s", [
				$post_id,
				$specific_meta
			] );
		} else {
			$query = $wpdb->prepare( "SELECT meta_key AS 'key', meta_value AS 'value' FROM $wpdb->postmeta WHERE post_id = %s AND meta_key LIKE '%tap-%'", $post_id );
		}

		$results = $wpdb->get_results( $query, ARRAY_A );
		if ( ! empty( $results ) ) {
			$results = array_reduce( $results, 'Thrive\Automator\Utils::flat_key_value_pairs', [] );
			foreach ( $results as $key => $value ) {
				$new_key             = str_replace( 'tap-', '', $key );
				$value               = maybe_unserialize( $value );
				$results[ $new_key ] = $value;
				unset( $results[ $key ] );
			}
		}

		return $results;
	}
}