<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Global_Conditional_Set {
	const NAME = 'tve_global_cond_set';

	/** @var \WP_Post */
	private $post;

	public static function title() {
		return __( 'Global condition set', 'thrive-cb' );
	}

	public static function register() {
		register_post_type( static::NAME, [
			'public'              => isset( $_GET[ TVE_EDITOR_FLAG ] ),
			'publicly_queryable'  => is_user_logged_in(),
			'query_var'           => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'_edit_link'          => 'post.php?post=%d',
			'map_meta_cap'        => true,
			'label'               => static::title(),
			'capabilities'        => [
				'edit_others_posts'    => 'tve-edit-cpt',
				'edit_published_posts' => 'tve-edit-cpt',
			],
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'has_archive'         => false,
		] );
	}

	/**
	 * @param $post_id
	 *
	 * @return Global_Conditional_Set|null
	 */
	public static function get_instance( $post_id = null ) {
		if ( ! empty( $post_id ) ) {
			$post = get_post( $post_id );
		}

		if ( empty( $post ) ) {
			$post = null;
		}

		return new self( $post );
	}

	private function __construct( $post = null ) {
		$this->post = $post;
	}

	public function get_post() {
		return $this->post;
	}

	public function create( $rules, $label ) {
		$post_id = wp_insert_post( [
				'post_title'  => static::title(),
				'post_type'   => static::NAME,
				'post_status' => 'publish',
				'meta_input'  => [
					'rules' => $rules,
					'label' => $label,
				],
			]
		);

		$this->post = get_post( $post_id );

		return $post_id;
	}

	public function update( $rules, $label ) {
		update_post_meta( $this->post->ID, 'rules', $rules );
		update_post_meta( $this->post->ID, 'label', $label );
	}

	public function remove() {
		wp_delete_post( $this->post->ID, true );
	}

	public function get_rules() {
		return get_post_meta( $this->post->ID, 'rules', true );
	}

	public function get_label() {
		return empty( $this->post ) ? '' : get_post_meta( $this->post->ID, 'label', true );
	}

	/**
	 * @param string $searched_keyword
	 * @param bool   $strict
	 *
	 * @return array[]
	 */
	public static function get_sets_by_name( $searched_keyword, $strict = false ) {
		$posts = get_posts( [
			'post_type'      => static::NAME,
			'posts_per_page' => 20,
			'meta_query'     => [
				[
					'key'     => 'label',
					'value'   => $searched_keyword,
					'compare' => $strict ? '=' : 'LIKE',
				],
			],
		] );

		return array_map( function ( $item ) {
			return [
				'value' => $item->ID,
				'label' => get_post_meta( $item->ID, 'label' ),
			];
		}, $posts );
	}
}
