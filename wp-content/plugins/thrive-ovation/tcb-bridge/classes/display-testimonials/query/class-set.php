<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials\Query;

use TCB\Traits\Is_Singleton;
use TVO\DisplayTestimonials\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Set {
	use Is_Singleton;

	const OPTION_KEY = 'thrive_ovation_display_testimonial_sets';

	/**
	 * @var int
	 */
	private $ID;

	/**
	 * @param $id
	 */
	public function __construct( $id = null ) {
		$this->ID = (int) $id;
	}

	public function get() {
		$sets      = static::get_all();
		$set_index = static::get_set_index( $this->ID, $sets );

		return ( $set_index === false ) ? [] : $sets[ $set_index ];
	}

	public static function add( $set_data ) {
		$sets = static::get_all();

		$maximum_existing_id = empty( $sets ) ? 0 : max( array_column( $sets, 'id' ) );

		$set_data['id'] = ++ $maximum_existing_id;

		$sets[] = $set_data;

		static::save( $sets );

		return $set_data;
	}

	public function update( $set_data ) {
		$sets = static::get_all();

		$set_index = static::get_set_index( $this->ID, $sets );

		$sets[ $set_index ] = array_merge( $sets[ $set_index ], $set_data );

		static::save( $sets );
	}

	public function rename( $name ) {
		$data = [
			'name' => $name,
		];

		$this->update( $data );
	}

	public function delete() {
		$sets      = static::get_all();
		$set_index = static::get_set_index( $this->ID, $sets );

		unset( $sets[ $set_index ] );

		/* re-index the array */
		$sets = array_values( $sets );

		static::save( $sets );
	}

	public static function get_all() {
		return get_option( static::OPTION_KEY, [] );
	}

	/**
	 * @return int
	 */
	public static function get_testimonial_count( $set ) {
		$items = [];

		if ( isset( $set['testimonials'] ) ) {
			/* we have to re-query them because some might have been deleted or status-changed in the meantime */
			$items = array_filter( $set['testimonials'], static function ( $id ) {
				return ! empty( get_post( $id ) ) && get_post_meta( $id, TVO_STATUS_META_KEY, true ) === TVO_STATUS_READY_FOR_DISPLAY;
			} );
		} else if ( isset( $set['tags'] ) ) {
			$items = Main::get_testimonials_for_tags( $set['tags'] );
		}

		return count( $items );
	}

	public static function localize_all() {
		return array_map( static function ( $set ) {
			$set['type']  = isset( $set['testimonials'] ) ? 'static' : 'dynamic';
			$set['count'] = static::get_testimonial_count( $set );

			return $set;
		}, static::get_all() );
	}

	public static function save( $sets ) {
		update_option( static::OPTION_KEY, $sets, 'no' );
	}

	public static function get_set_index( $id, $sets ) {
		return array_search( $id, array_column( $sets, 'id' ), true );
	}

	/**
	 * Parse the set data into a format accepted by wp_query
	 *
	 * @param $set_data
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function parse_set_data( $set_data, $args, $has_pagination = false ) {
		$args = array_merge( $args, [
			'order'   => $set_data['ordering']['direction'],
			'orderby' => $set_data['ordering']['type'] === 'manual' ? 'post__in' : $set_data['ordering']['type'],
			'offset'  => $set_data['ordering']['offset'],
		] );

		if ( static::is_cloud_set_request() ) {
			if ( isset( $_REQUEST['query']['posts_per_page'] ) ) {
				/* when the cloud template is changed, the posts_per_page (if it exists) is sent inside 'query' */
				$posts_per_page = $_REQUEST['query']['posts_per_page'];
			}
		} else if ( isset( $args['posts_per_page'] ) ) {
			/* if we're in a normal rendering scenario (page load/editor), use the 'posts_per_page' that is set on the element attributes */
			$posts_per_page = $args['posts_per_page'];
		}

		/* in certain cases (detailed above), use the 'posts_per_page' saved inside the set */
		$can_overwrite_posts_per_page =
			/* if we receive a 'posts_per_page' argument here, we don't need to use the one from the set */
			! isset( $posts_per_page ) &&
			/* make sure we have something to set on the 'posts_per_page' ( either 'number_of_items' or a count of testimonials ) */
			( ! empty( $set_data['ordering']['number_of_items'] ) || $set_data['ordering']['type'] === 'manual' );

		if ( isset( $set_data['testimonials'] ) ) {
			$args['post__in'] = $set_data['testimonials'];

			if ( $can_overwrite_posts_per_page && ! $has_pagination ) {
				if ( $set_data['ordering']['type'] === 'manual' ) {
					/* when the ordering is set to manual, it's enough to count the items */
					$args['posts_per_page'] = count( $set_data['testimonials'] );
				} else {
					/* for dynamic ordering, the number of items is always specified from the input */
					$args['posts_per_page'] = $set_data['ordering']['number_of_items'];
				}
			}
		} else if ( isset( $set_data['tags'] ) ) {
			$args['tax_query'] = Main::get_tax_query( $set_data['tags'] );

			if ( $can_overwrite_posts_per_page ) {
				/* the number of items is specified from the input */
				$args['posts_per_page'] = $set_data['ordering']['number_of_items'];
			}
		}

		return $args;
	}

	/**
	 * A very specific check that we use to see if this is a 'cloud template' request - either added the first time, or when it was changed
	 *
	 * @return bool
	 */
	public static function is_cloud_set_request() {
		return ( \TCB_Utils::is_rest() || wp_doing_ajax() ) && /* make sure we're inside a request */
		       (
			       /* first time when the element is added, after going through the modal, this request is sent */
			       ! empty( $_REQUEST['template'] ) ||
			       /* when changing the cloud template, this ajax request is sent */
			       ( ! empty( $_REQUEST['custom'] ) && $_REQUEST['custom'] === 'cloud_content_template_download' )
		       );

	}
}
