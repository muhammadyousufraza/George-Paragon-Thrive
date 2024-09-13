<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Id_Field extends Trigger_Field {
	public static function get_name() {
		return __( 'Select specific post(s)', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'You must select at least one post', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Select at least one post', 'thrive-automator' );
	}

	public static function get_id() {
		return 'post_id_trigger_field';
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_AUTOCOMPLETE;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_values( $filters = [] ) {
		$trigger_data = $filters['trigger_data'];
		$limit        = $filters['limit'] ?? 25;
		$page         = $filters['page'] ?? 1;
		$args         = [
			'post_status'    => 'published',
			'posts_per_page' => $limit,
			'offset'         => $limit * $page,
			's'              => $filters['search'] ?? '',
		];
		if ( ! empty( $trigger_data->post_type_trigger_field ) ) {
			if ( ! empty( $trigger_data->post_type_trigger_field->subfield->{static::get_id()}->value ) ) {
				$current_values = $trigger_data->post_type_trigger_field->subfield->{static::get_id()}->value;
			}

			$args['post_type'] = $trigger_data->post_type_trigger_field->value;
		}
		$page_ids = new \WP_Query( $args );
		$pages    = [];

		foreach ( $page_ids->posts as $page ) {
			if ( ! empty( $page ) && $page->post_status !== 'trash' ) {
				$pages[ $page->ID ] = [
					'label' => $page->post_title,
					'id'    => $page->ID,
				];
			}
		}

		if ( ! empty( $current_values ) ) {
			foreach ( $current_values as $value ) {
				if ( empty( $pages[ $value ] ) ) {
					$post            = get_post( $value );
					$pages[ $value ] = [
						'label' => $post->post_title,
						'id'    => $value,
					];
				}
			}
		}

		return $pages;
	}

	public static function get_extra_options() {
		return [
			'limit' => 25,
		];
	}
}
