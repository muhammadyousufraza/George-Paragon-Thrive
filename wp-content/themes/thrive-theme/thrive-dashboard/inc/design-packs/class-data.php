<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Design_Packs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Handle the available data for the export
 * Prepare queries for data edited with TAr to be used in the export
 */
class Data {

	/**
	 * Get the default data for the items query
	 *
	 * @param array $extra_args
	 *
	 * @return array
	 */
	public static function default_query_args( array $extra_args = [] ): array {
		$pagination = $extra_args['pagination'] ?? 0;
		$limit      = $extra_args['limit'] ?? Main::PER_PAGE_LIMIT;

		return [
			's'                      => $extra_args['search'],
			'posts_per_page'         => $limit,
			'post_status'            => [ 'draft', 'publish' ],
			'offset'                 => $limit * $pagination,
			'fields'                 => 'ids',
			'post__not_in'           => [ get_option( 'page_for_posts' ) ],
			'update_post_meta_cache' => false,
		];

	}

	/**
	 * Get the title of the post
	 *
	 * @param array $posts
	 *
	 * @return array
	 */
	public static function prepare_posts( array $posts ): array {
		$data = [];

		foreach ( $posts as $post_id ) {
			$data[] = [
				'id'   => $post_id,
				'name' => htmlspecialchars_decode( get_the_title( $post_id ) ),
			];
		}

		return $data;
	}

	/**
	 * Get the title of the landing page
	 *
	 * @param array $extra_args
	 *
	 * @return array
	 */
	public static function get_landing_pages( array $extra_args = [] ): array {
		$posts  = get_posts(
			array_merge(
				static::default_query_args( $extra_args ),
				[
					'post_type'  => 'any',
					'meta_query' => [
						[
							'key'     => 'tve_landing_page',
							'compare' => '!=',
							'value'   => '',
						],
					],
				] )
		);
		$posts  = static::prepare_posts( $posts );
		$upload = tve_filter_landing_page_preview_location( wp_upload_dir() );

		foreach ( $posts as &$post ) {
			$lp_name = '/lp-' . $post['id'] . '.png';
			if ( file_exists( $upload['path'] . $lp_name ) ) {
				$src   = $upload['url'] . $lp_name;
				$sizes = getimagesize( $src );
				if ( ! empty( $sizes ) ) {
					$post['width']  = $sizes[0];
					$post['height'] = $sizes[1];
				}
			} else {
				$src            = TVE_DASH_URL . '/inc/design-packs/assets/img/lp-placeholder.png';
				$post['width']  = 602;
				$post['height'] = 1004;
			}

			$post['thumbnail'] = $src;

		}

		return $posts;
	}

	/**
	 * Get posts with the given post type that are edited with TAr
	 *
	 * @param string $post_type
	 * @param array  $extra_args
	 *
	 * @return array
	 */
	public static function get_tar_posts( string $post_type = 'post', array $extra_args = [] ): array {
		$posts = get_posts(
			array_merge(
				static::default_query_args( $extra_args ),
				[
					'post_type'  => $post_type,
					'category'   => $extra_args['categories'] ?? [],
					'tag__in'    => $extra_args['tags'] ?? [],
					'meta_query' => [
						[
							'key'     => 'tcb_editor_enabled',
							'compare' => 'EXISTS',
						],
						[
							'key'     => 'tve_landing_page',
							'compare' => 'NOT EXISTS',
						],
					],
				] )
		);

		return static::prepare_posts( $posts );
	}
}
