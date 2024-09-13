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

class Main {
	public static function init() {
		static::includes();
		Hooks::init();
	}

	public static function includes() {
		require_once __DIR__ . '/class-hooks.php';
		require_once __DIR__ . '/class-rest-api.php';
		require_once __DIR__ . '/class-filters.php';
		require_once __DIR__ . '/class-set.php';
	}

	/**
	 * @param $selected_values
	 * @param $extra_args
	 *
	 * @return array
	 */
	public static function get_testimonials( $selected_values = [], $extra_args = [] ) {
		$testimonial_data = [];

		$args = array_merge( \TVO\DisplayTestimonials\Main::DEFAULT_ARGS, [ 'posts_per_page' => - 1 ] );

		if ( empty( $selected_values ) ) {
			if ( ! empty( $extra_args['tags'] ) ) {
				$args['tax_query'] = static::get_tax_query( explode( ',', $extra_args['tags'] ) );
			}
		} else {
			$args['post__in'] = $selected_values;
			$args['orderby']  = 'post__in';
		}

		foreach ( get_posts( $args ) as $testimonial ) {
			$id = $testimonial->ID;

			$testimonial_meta = get_post_meta( $testimonial->ID, TVO_POST_META_KEY, true );
			$tags             = [];
			foreach ( wp_get_post_terms( $id, TVO_TESTIMONIAL_TAG_TAXONOMY ) as $tag ) {
				$tags[] = $tag->name;
			}

			$testimonial_data[] = [
				'ID'        => $id,
				'title'     => $testimonial->post_title,
				'author'    => $testimonial_meta['name'],
				'image'     => empty( $testimonial_meta['picture_url'] ) ? tvo_get_default_image_placeholder() : $testimonial_meta['picture_url'],
				'post_date' => date_i18n( 'jS F Y', strtotime( $testimonial->post_date ) ),
				'tags'      => $tags,
				'content'   => Utils::clean_testimonial_content( $testimonial->post_content ),
			];
		}

		return $testimonial_data;
	}

	/**
	 * This function maps the number of testimonials for each tag.
	 * We iterate once through the testimonials, then increment the map for each tag found inside a testimonial.
	 *
	 * If we have this testimonial-tag setup: T1[tag1], T2[tag1,tag2] T3[tag1,tag3], the map would look like this:
	 * tag1:3
	 * tag2:1
	 * tag3:1
	 *
	 * @param $tags
	 *
	 * @return array
	 */
	public static function get_tag_testimonial_count_map( $tags ) {
		$tag_testimonial_count_map = [];

		$tag_ids = array_map( static function ( $term ) {
			return $term->term_id;
		}, $tags );

		/* initialize each tag with a count of 0 */
		foreach ( $tag_ids as $tag_id ) {
			$tag_testimonial_count_map[ $tag_id ] = 0;
		}

		/* for each testimonial, increment the corresponding tags */
		foreach ( static::get_testimonials_for_tags( $tag_ids ) as $testimonial_id ) {
			$testimonial_tag_ids = wp_get_post_terms( $testimonial_id, TVO_TESTIMONIAL_TAG_TAXONOMY, [ 'fields' => 'ids' ] );

			foreach ( $testimonial_tag_ids as $tag_id ) {
				$tag_testimonial_count_map[ $tag_id ] ++;
			}
		}

		return $tag_testimonial_count_map;
	}

	/**
	 * Get the testimonial IDs for the given tags
	 *
	 * @param array $tag_ids
	 *
	 * @return int[]|\WP_Post[]
	 */
	public static function get_testimonials_for_tags( $tag_ids ) {
		return get_posts( array_merge( \TVO\DisplayTestimonials\Main::DEFAULT_ARGS, [
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'tax_query'      => static::get_tax_query( $tag_ids ),
		] ) );
	}

	/**
	 * @param array $tag_ids
	 *
	 * @return array[]
	 */
	public static function get_tax_query( $tag_ids ) {
		/* the double '[]' is intended */
		return [
			[
				'taxonomy' => TVO_TESTIMONIAL_TAG_TAXONOMY,
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			],
		];
	}
}
