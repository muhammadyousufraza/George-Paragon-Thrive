<?php

namespace TVA\Assessments\Grading;

use TVA_Post;
use WP_Post;

class Category extends Base {

	/**
	 * Grading category post type
	 */
	const POST_TYPE = 'tva_assessment_gr_c';

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * Meta value that is stored in post meta for pass gradings
	 */
	const PASS_META = 'pass';

	/**
	 * Meta value that is stored in post meta for fail gradings
	 */
	const FAIL_META = 'fail';

	/**
	 * @param array $data
	 */
	public function __construct( $data ) {

		$this->data = $data;

		parent::__construct( $data );
	}


	/**
	 * Checks if the $value is passing the grading
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function passed( $value ) {
		return is_numeric( $value ) && get_post_meta( (int) $value, 'tva_type', true ) === static::PASS_META;
	}

	/**
	 * Save grading
	 *
	 * @return bool
	 */
	public function save() {
		$was_saved = parent::save();

		if ( $was_saved && ! empty( $this->data['grading_method_data'] ) && isset( $this->data['grading_method_data'][ static::PASS_META ], $this->data['grading_method_data'][ static::FAIL_META ] ) ) {

			foreach ( $this->data['grading_method_data'] as $key => $grading_data ) {

				$existing_gradings = array_map( static function ( $post ) {
					return $post->ID;
				}, $this->get_grading_posts( $key ) );

				$removed_gradings = array_filter( array_diff( $existing_gradings, array_map( static function ( $tmp ) {
					return empty( $tmp['ID'] ) ? 0 : $tmp['ID'];
				}, $grading_data ) ) );

				foreach ( $removed_gradings as $removed_grading ) {
					wp_delete_post( $removed_grading, true );
				}

				foreach ( $grading_data as $data ) {
					$this->upsert_grading_post( $data, $key );
				}
			}
		}

		return $was_saved;
	}

	/**
	 * Returns the grading details
	 * Used in localization
	 *
	 * @return array
	 */
	public function get_grading_details() {
		return array_merge( parent::get_grading_details(), [
			'grading_method_data' => $this->get_method_data(),
		] );
	}

	/**
	 * Get grading posts
	 *
	 * @return array[]
	 */
	public function get_method_data() {
		return [
			static::PASS_META => array_map( [ $this, 'prepare' ], $this->get_grading_posts( static::PASS_META ) ),
			static::FAIL_META => array_map( [ $this, 'prepare' ], $this->get_grading_posts( static::FAIL_META ) ),
		];
	}

	/**
	 * Clone categories too
	 *
	 * @param $clone_instance
	 *
	 * @return void
	 */
	public function after_clone( $clone_instance ) {
		$data = $this->get_method_data();
		foreach ( $data as $type => $posts ) {
			foreach ( $posts as $post ) {
				unset( $post['ID'] );
				$clone_instance->upsert_grading_post( $post, $type );
			}
		}
	}

	/**
	 * Update / Insert grading post depending on the data sent
	 *
	 * @param array  $data
	 * @param string $type
	 *
	 * @return void
	 */
	public function upsert_grading_post( $data, $type ) {

		if ( empty( $data['ID'] ) ) {
			wp_insert_post( [
				'post_type'    => static::POST_TYPE,
				'post_title'   => $data['name'],
				'post_content' => $data['slug'],
				'post_status'  => 'draft',
				'post_parent'  => $this->assessment_id,
				'meta_input'   => [
					'tva_order' => $data['order'],
					'tva_type'  => $type,
				],
			] );

		} else {
			wp_update_post( [
				'ID'         => (int) $data['ID'],
				'post_title' => $data['name'],
			] );

			update_post_meta( (int) $data['ID'], 'tva_order', $data['order'] );
			update_post_meta( (int) $data['ID'], 'tva_type', $type );
		}
	}

	/**
	 * Returns a list of grading posts
	 *
	 * @param $type
	 *
	 * @return WP_Post[]
	 */
	public function get_grading_posts( $type ) {
		$args = [
			'posts_per_page' => - 1,
			'post_type'      => static::POST_TYPE,
			'post_status'    => TVA_Post::$accepted_statuses,
			'post_parent'    => $this->assessment_id,
			'meta_key'       => 'tva_order',
			'order_by'       => 'meta_value_num',
			'meta_query'     => [
				[
					'key'   => 'tva_type',
					'value' => $type,
				],
			],
		];

		return get_posts( $args );
	}

	/**
	 * Prepare the grading post for localization
	 *
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function prepare( $post ) {
		return [
			'ID'    => $post->ID,
			'slug'  => $post->post_content,
			'name'  => $post->post_title,
			'order' => (int) get_post_meta( $post->ID, 'tva_order', true ),
		];
	}

	/**
	 * Returns the grade value
	 *
	 * @param numeric $grade
	 *
	 * @return string
	 */
	public function get_value( $grade ) {
		return get_post( $grade )->post_title;
	}

	/**
	 * Registers the post type
	 *
	 * @return void
	 */
	public static function init_post_type() {
		register_post_type( static::POST_TYPE, [
			'labels'              => [
				'name'          => 'Assessment Grading Categories',
				'singular_name' => 'Assessment Grading Category',
			],
			'publicly_queryable'  => false,
			'public'              => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'map_meta_cap'        => true,
		] );
	}

	/**
	 * Returns the first passing grade in the array
	 *
	 * @return int|mixed|string
	 */
	public function get_passing_grade() {
		return $this->data['grading_method_data'][ static::PASS_META ][0]['ID'];
	}


	/**
	 * Default categories for assessments
	 *
	 * @return array[]
	 */
	public static function get_default_categories() {
		return [
			'simple_grades' => [
				'label' => __( 'Add simple grades (A to F)', 'thrive-apprentice' ),
				'pass'  => [
					'A',
					'B',
					'C',
					'D',
				],
				'fail'  => [
					'F',
				],
			],
			'grades'        => [
				'label' => __( 'Add grades (A+ to F)', 'thrive-apprentice' ),
				'pass'  => [
					'A+',
					'A',
					'A-',
					'B+',
					'B',
					'B-',
					'C+',
					'C',
					'C-',
					'D+',
					'D',
					'D-',
				],
				'fail'  => [
					'F',
				],
			],
			'academic'      => [
				'label' => __( 'Add grades (academic)', 'thrive-apprentice' ),
				'pass'  => [
					'High Distinction',
					'Distinction',
					'Credit',
					'Pass',
				],
				'fail'  => [
					'Fail',
					'Insufficient',
				],
			],
		];
	}
}
