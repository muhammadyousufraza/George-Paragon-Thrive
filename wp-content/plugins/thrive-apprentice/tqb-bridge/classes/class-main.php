<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TQB;

use TVA_Const;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main entry-point for TVA-TQB integration
 */
class Main {
	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * @var boolean
	 */
	private $is_tqb_active;

	/**
	 * Singleton implementation for Main
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Return true if Thrive Quiz Builder is active
	 *
	 * @return bool
	 */
	public function is_quiz_builder_active() {
		return $this->is_tqb_active;
	}

	/**
	 * Class constructor
	 */
	private function __construct() {
		$this->is_tqb_active = tve_dash_is_plugin_active( 'thrive-quiz-builder' );

		/**
		 * If the Quiz Builder plugin is active, include the dependencies
		 */
		if ( $this->is_tqb_active ) {
			new Hooks();
		}

		/**
		 * Add stuff that apply also when Thrive Quiz Builder plugin is not active
		 */
		$this->general_actions();
		$this->general_filters();
	}

	/**
	 * Actions that apply also when Thrive Quiz Builder plugin is not active
	 *
	 * @return void
	 */
	public function general_actions() {
		add_action( 'tva_admin_print_icons', [ $this, 'add_extra_icons' ] );
	}

	/**
	 * Filters that apply also when Thrive Quiz Builder plugin is not active
	 *
	 * @return void
	 */
	public function general_filters() {
		add_filter( 'tva_admin_localize', [ $this, 'admin_localize' ] );
		add_filter( 'tva_get_frontend_localization', [ $this, 'get_frontend_localization' ] );
	}


	/**
	 * Localize stuff for TQB integration
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_frontend_localization( $data ) {
		$post_type = get_post_type();
		if ( $post_type === TVA_Const::LESSON_POST_TYPE ) {
			$course_nav_labels = tcb_tva_dynamic_actions()->get_course_nav_labels();

			if ( tcb_tva_visual_builder()->get_active_object() ) {
				$data['allow_mark_complete'] = (int) tcb_tva_visual_builder()->get_active_object()->can_be_marked_as_completed();
			}
			$data['deny_mark_complete_toast'] = $course_nav_labels['mark_complete_requirements']['title'];
		}

		return $data;
	}

	/**
	 * Includes the Quiz Builder integration icons
	 *
	 * @return void
	 */
	public function add_extra_icons() {
		include TVA_Const::plugin_path( 'tqb-bridge/assets/svg/admin-icons.svg' );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function admin_localize( $data = [] ) {
		$data['tqb_active'] = (int) $this->is_quiz_builder_active();

		return $data;
	}
}

/**
 * @return Main
 */
function tva_tqb_integration() {
	return Main::get_instance();
}
