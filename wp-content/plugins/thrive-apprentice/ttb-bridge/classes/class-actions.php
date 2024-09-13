<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Actions
 */
class Actions {

	public static function init() {

		if ( Main::uses_builder_templates() ) {

			/* remove old template functionality */
			remove_action( 'template_include', 'tva_template', 99 );

			/* don't include TA styles when the builder templates are enabled */
			remove_action( 'wp_head', 'tva_add_head_styles' );
		}

		add_action( 'wp', [ __CLASS__, 'wp' ], 0 );

		add_action( 'tcb_before_get_content_template', [ Apprentice_Wizard::class, 'replace_data_in_preview_content' ] );

		/* typography CSS modification - include a `reset` CSS node - make sure this is outputted early on */
		add_action( 'wp_head', [ static::class, 'output_typography_reset_css' ], 1 );

		/*Dynamic CSS needed for Visual Builder*/
		add_action( 'tcb_get_extra_global_variables', [ static::class, 'output_extra_global_variables' ] );

		/**
		 * This callback is responsible for removing stuff from TTB - TAR on certain cases
		 *
		 * The prio here is 8 because of the  [ \Thrive_Architect::class, 'editor_enqueue_scripts' ] callback that has prio 9
		 */
		add_action( 'wp_enqueue_scripts', [ static::class, 'wp_enqueue_scripts' ], 8 );

		if ( ! Main::is_thrive_theme_active() ) {
			add_action( 'thrive_dashboard_loaded', [ Main::class, 'require_theme_product' ] );
			add_action( 'thrive_dashboard_loaded', [ Filters::class, 'on_dashboard_loaded' ] );
		}
	}

	/**
	 * Called on the WP hook - used to add some conditional actions after the query (request) data is available
	 */
	public static function wp_init() {

	}

	/**
	 * Set current active skin and template
	 */
	public static function wp() {
		$is_apprentice_context = tva_is_apprentice() || tva_general_post_is_apprentice();
		if ( $is_apprentice_context && Main::uses_builder_templates() ) {
			Main::requested_skin();
			thrive_apprentice_template( Main::get_active_template() );
		}

		/* if TTB is not active, and the current request is not something related to TA, do not enqueue anything from TTB */
		if ( ! $is_apprentice_context && ! \Thrive_Theme::is_active() && ! \Thrive_Utils::is_theme_template() ) {
			remove_action( 'tcb_lightspeed_load_unoptimized_styles', [ \Thrive_Theme_Lightspeed::class, 'tcb_lightspeed_load_unoptimized_styles' ], 10 );
		}
	}

	/**
	 * Enqueue visual-editing assets if required, and make sure to remove the ones that are not needed from TTB
	 */
	public static function wp_enqueue_scripts() {
		$thrive_theme_active = \Thrive_Theme::is_active();
		$is_apprentice_page  = ( tva_is_apprentice() && ! Check::course_certificate() ) || tva_general_post_is_apprentice();

		/* if TTB is not active, and the current request is not something related to TA, do not enqueue TTB scripts & styles */
		if ( ( ! $thrive_theme_active && ! \Thrive_Utils::is_theme_template() && ! $is_apprentice_page ) || ( ! $thrive_theme_active && $is_apprentice_page && ! Main::uses_builder_templates() ) ) {
			remove_action( 'wp_enqueue_scripts', [ thrive_theme(), 'enqueue_scripts' ], 11 );
			remove_action( 'wp_enqueue_scripts', [ \Thrive_Architect::class, 'editor_enqueue_scripts' ], 9 );
		}

		if ( ! $thrive_theme_active && $is_apprentice_page && Main::uses_builder_templates() ) {
			/**
			 * To avoid CSS conflicts from the theme such as margins applied on header or defined css on ::before or
			 * modified typography for apprentice pages we will need to remove theme CSS in case the Thrive Theme is not active
			 */
			add_action( 'wp_print_styles', 'tve_remove_theme_css', PHP_INT_MAX );
		}
	}

	/**
	 * If settings require it, output a typography reset CSS style node.
	 * (If the setting for inheriting typography from the theme is turned OFF)
	 */
	public static function output_typography_reset_css() {

		/*
		 * Output reset CSS
		 *  if the current page is apprentice typography edit or preview
		 *      OR
		 *  if we are on an apprentice-related page, we are using ttb templates and the active skin does not inherit typography
		 */
		if ( Check::needs_typography_reset() ) {
			$css = trim( str_replace( '/*# sourceMappingURL=typography-reset.css.map*/', '', tva_get_file_contents( 'css/typography-reset.css' ) ) );
			echo '<style type="text/css" id="tva-typography-reset">' . $css . '</style>';
		}
	}

	/**
	 * Outputs extra global variables for visual editor
	 */
	public static function output_extra_global_variables() {

		if ( tva_is_apprentice() ) {
			$active_course_id = \TVA_Course_V2::get_active_course_id();

			if ( ! empty( $active_course_id ) ) {
				$course      = new \TVA_Course_V2( $active_course_id );
				$topic_color = $course->get_topic()->color;
				$label_color = $course->get_label_data()['color'];

				echo TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . 'topic-color:' . $topic_color . ';';
				$hsl_data = tve_rgb2hsl( $topic_color );
				echo tve_print_color_hsl( TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . 'topic-color', $hsl_data );

				echo TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . 'label-color:' . $label_color . ';';
				$hsl_data = tve_rgb2hsl( $label_color );
				echo tve_print_color_hsl( TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . 'label-color', $hsl_data );

				echo TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'visual-edit-course-image:url("' . tcb_tva_visual_builder()->get_cover_image() . '");';
				echo TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'visual-edit-course-author:url("' . tcb_tva_visual_builder()->get_author_image() . '");';
			}
		}
	}

}
