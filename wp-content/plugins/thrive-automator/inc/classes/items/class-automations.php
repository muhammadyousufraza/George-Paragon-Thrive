<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Automation
 */
class Automations {

	/**
	 * Get automations collection
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public static function localize( array $filters = [] ): array {
		$defaults = array(
			'posts_per_page' => - 1,
			'post_status'    => 'any',
			'post_type'      => Automation::POST_TYPE,
		);

		$post_filters = array_merge( $defaults, $filters );

		$automations_raw = get_posts( $post_filters );

		$automations = [];
		foreach ( $automations_raw as $automation ) {
			$class         = new Automation( $automation );
			$automations[] = ! empty( $filters['raw_data'] ) ? $class : $class->localize_data();
		}

		return $automations;
	}

	/**
	 * Get automation posts
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public static function get_raw_data( array $filters = [] ): array {
		$defaults = array(
			'posts_per_page' => - 1,
			'post_status'    => 'any',
			'post_type'      => Automation::POST_TYPE,
		);

		$post_filters = array_merge( $defaults, $filters );

		$automations_raw = get_posts( $post_filters );

		$automations = [];
		foreach ( $automations_raw as $automation ) {
			$content       = Utils::safe_unserialize( $automation->post_content );
			$automations[] = [
				'id'       => $automation->ID,
				'title'    => $automation->post_title,
				'content'  => $content,
				'status'   => $automation->post_status,
				'is_valid' => Automation::validate( $content, true ),
			];
		}

		return $automations;
	}


	/**
	 * Delete multiple automations
	 *
	 * @param array $automations
	 */
	public static function delete( array $automations ) {
		foreach ( $automations as $automation ) {
			Automation::delete( $automation );
		}
	}

	/**
	 * Setup listeners(triggers) for all automations
	 */
	public static function start() {

		$post_filters = [
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'post_type'      => Automation::POST_TYPE,
		];

		$automations = get_posts( $post_filters );

		foreach ( $automations as $automation ) {
			if ( Automation::validate( Utils::safe_unserialize( $automation->post_content ), true ) ) {
				$instance = new Automation( $automation );
				$instance->start();
			}
		}
	}

}
