<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials\Query;

use TVO\DisplayTestimonials\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Filters {
	/**
	 * @param $filter_args
	 *
	 * @return array
	 */
	public static function get_filtered_testimonials( $filter_args ) {
		$filters = shortcode_atts( static::DEFAULT_ARGS, $filter_args );

		$testimonials = array_filter( Main::get_testimonials( [], $filters ), static function ( $testimonial ) use ( $filters ) {
			return
				( empty( $filters['search'] ) || static::filter_by_keyword( $testimonial, $filters['search'] ) ) &&
				( ! isset( $filters['title'] ) || static::filter_by_meta( $testimonial, $filters, 'title' ) ) &&
				( ! isset( $filters['image'] ) || static::filter_by_image( $testimonial, $filters ) ) &&
				( empty( $filters['word_count']['enabled'] ) || static::filter_by_word_count( $testimonial, $filters['word_count'] ) );
		} );

		return array_values( $testimonials );
	}

	/**
	 * @param $testimonial
	 * @param $searched_keyword
	 *
	 * @return bool
	 */
	public static function filter_by_keyword( $testimonial, $searched_keyword ) {
		/* search inside the title, content and author name */
		$haystack = $testimonial['title'] . $testimonial['content'] . $testimonial['author'];

		return stripos( $haystack, $searched_keyword ) !== false;
	}

	/**
	 * @param $testimonial
	 * @param $filters
	 *
	 * @return bool
	 */
	public static function filter_by_image( $testimonial, $filters ) {
		$testimonial['image'] = Utils::is_placeholder_image( $testimonial['image'] ) ? '' : $testimonial['image'];

		return static::filter_by_meta( $testimonial, $filters, 'image' );
	}

	/**
	 * @param $testimonial
	 * @param $filters
	 * @param $filter_key
	 *
	 * @return bool
	 */
	public static function filter_by_meta( $testimonial, $filters, $filter_key ) {

		return $filters[ $filter_key ] === '' ||
		       ( (int) $filters[ $filter_key ] === 1 && ! empty( $testimonial[ $filter_key ] ) ) ||
		       ( (int) $filters[ $filter_key ] === 0 && empty( $testimonial[ $filter_key ] ) );
	}

	/**
	 * @param $testimonial
	 * @param $word_count_config
	 *
	 * @return bool
	 */
	public static function filter_by_word_count( $testimonial, $word_count_config ) {
		$word_count = count( preg_split( '/\n|\s/', $testimonial['content'], - 1, PREG_SPLIT_NO_EMPTY ) );

		return (int) $word_count_config['enabled'] === 1 &&
		       ( empty( $word_count_config['from'] ) || (int) $word_count_config['from'] <= $word_count ) &&
		       ( empty( $word_count_config['to'] ) || (int) $word_count_config['to'] >= $word_count );
	}

	const DEFAULT_ARGS = [
		'search'     => '',
		'image'      => '',
		'title'      => '',
		'word_count' => [
			'enabled' => 0,
			'from'    => 0,
			'to'      => PHP_INT_MAX,
		],
		'tags'       => [],
	];
}
