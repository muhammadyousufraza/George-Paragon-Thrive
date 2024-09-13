<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 8/30/2016
 * Time: 4:09 PM
 *
 * @package Thrive Quiz Builder
 */

/**
 * Gets the javascript variables.
 *
 * @return array
 */
function tqb_get_localization() {
	return array(
		't'                    => include tqb()->plugin_path( 'includes/admin/i18n.php' ),
		'dash_url'             => admin_url( 'admin.php?page=tve_dash_section' ),
		'quiz_templates'       => tqb()->get_quiz_templates(),
		'quiz_types'           => tqb()->get_quiz_types(),
		'quiz_styles'          => tqb()->get_quiz_styles(),
		'shortcode_name'       => Thrive_Quiz_Builder::SHORTCODE_NAME,
		'chart_colors'         => tqb()->chart_colors(),
		'badge_templates'      => tie()->template_manager()->get_templates(),
		'shown_quizzes'        => tqb()->get_shown_quizzes(),
		'qna_quiz_styles'      => TQB_QNA_Editor::get_editable_styles(),
		'quizzes_details'      => array(
			'quizzes_number'    => TQB_Quiz_Manager::get_quizzes_number(),
			'max_quiz_number'   => Thrive_Quiz_Builder::TQB_DASH_MAX_QUIZZES_IDENTIFIER,
			'quizzes_displayed' => Thrive_Quiz_Builder::TQB_DASH_MAX_QUIZZES_IDENTIFIER,
		),
		'data'                 => array(
			'settings'                  => Thrive_Quiz_Builder::get_settings(),
			//'quizzes'                   => TQB_Quiz_Manager::get_quizzes(),
			'quiz_types'                => array(
				'number'      => Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER,
				'percentage'  => Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE,
				'personality' => Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY,
				'right_wrong' => Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG,
				'survey'      => Thrive_Quiz_Builder::QUIZ_TYPE_SURVEY,
			),
			'colors'                    => array(
				'red'   => Thrive_Quiz_Builder::CHART_RED,
				'green' => Thrive_Quiz_Builder::CHART_GREEN,
				'grey'  => Thrive_Quiz_Builder::CHART_GREY,
			),
			'quiz_structure_item_types' => array(
				'splash'  => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'mandatory' => false,
					'type'      => 'splash',
				),
				'qna'     => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA ),
					'mandatory' => true,
					'type'      => 'qna',
				),
				'optin'   => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'mandatory' => false,
					'type'      => 'optin',
				),
				'results' => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'mandatory' => true,
					'type'      => 'results',
				),
			),
			'variation_status'          => array(
				'publish' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
				'archive' => Thrive_Quiz_Builder::VARIATION_STATUS_ARCHIVE,
			),
			'max_interval_number'       => Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS,
		),
		'event_types'          => array(
			'impression' => Thrive_Quiz_Builder::TQB_IMPRESSION,
			'conversion' => Thrive_Quiz_Builder::TQB_CONVERSION,
			'skip_optin' => Thrive_Quiz_Builder::TQB_SKIP_OPTIN,
		),
		'date_intervals'       => array(
			'days7'      => Thrive_Quiz_Builder::TQB_LAST_7_DAYS,
			'days30'     => Thrive_Quiz_Builder::TQB_LAST_30_DAYS,
			'month_this' => Thrive_Quiz_Builder::TQB_THIS_MONTH,
			'month_last' => Thrive_Quiz_Builder::TQB_LAST_MONTH,
			'year_this'  => Thrive_Quiz_Builder::TQB_THIS_YEAR,
			'year_last'  => Thrive_Quiz_Builder::TQB_LAST_YEAR,
			'months12'   => Thrive_Quiz_Builder::TQB_LAST_12_MONTHS,
			'custom'     => Thrive_Quiz_Builder::TQB_CUSTOM_DATE_RANGE,
		),
		'admin_nonce'          => wp_create_nonce( Thrive_Quiz_Builder_Admin::NONCE_KEY_AJAX ),
		'admin_csv_nonce'      => wp_create_nonce( 'tqb_question_cvs' ),
		'admin_answ_csv_nonce' => wp_create_nonce( 'tqb_answers_csv' ),
		'ajax_actions'         => array(
			'admin_controller' => 'tqb_admin_ajax_controller',
		),
		'results_page_types'   => TQB_Results_Page::localize_types(),
		'license'              => [
			'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tqb' ),
			'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tqb' ),
			'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'tqb' ),
			'link'          => tvd_get_individual_plugin_license_link( 'tqb' ),
			'grace_time'    => TD_TTW_User_Licenses::get_instance()->get_grace_period_left( 'tqb' ),
		],
	);
}

/**
 * Searches in DB for a media post which contains $filename string in guid column
 *
 * @param string $filename
 *
 * @return WP_Post|null
 */
function tqb_get_attachment_by_filename( $filename ) {

	global $wpdb;

	$query = "SELECT * FROM {$wpdb->posts} WHERE guid LIKE '%s'";

	return $wpdb->get_row( $wpdb->prepare( $query, '%' . $filename . '%' ) );
}

/**
 * Add a file which exists physically on HDD as
 * media post in media library
 *
 * @param string $source path
 *
 * @return array|void Array of attachment details.
 */
function tqb_import_file( $source ) {

	$filename = basename( $source );

	$media_post = tqb_get_attachment_by_filename( $filename );

	if ( $media_post ) {
		return wp_prepare_attachment_for_js( $media_post->ID );
	}

	$upload_file = wp_upload_bits( $filename, null, file_get_contents( $source ) );

	if ( ! $upload_file['error'] ) {

		$wp_filetype = wp_check_filetype( $filename, null );

		$media_post = array(
			'guid'           => $upload_file['url'],
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $media_post, $upload_file['file'] );

		if ( ! is_wp_error( $attachment_id ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
		}

		$media_post = wp_prepare_attachment_for_js( $attachment_id );
	}

	return $media_post;
}

/**
 * Check whether or not a file should be imported based on its source
 *
 * @param string $source
 * @param array  $args
 *
 * @return bool
 */
function tqb_skip_file_import( $source, $args = array() ) {

	if ( empty( $source ) ) {
		return false;
	}

	$old_quiz_id = ! empty( $args['old_quiz_id'] ) ? (int) $args['old_quiz_id'] : 0;

	$is_badge            = strpos( $source, 'thrive-quiz-builder/' . $old_quiz_id . '.png' );
	$is_tqb_asset        = strpos( $source, 'thrive-quiz-builder/assets/images' );
	$is_tcb_bridge_asset = strpos( $source, 'thrive-quiz-builder/tcb-bridge/' );

	return $is_badge || $is_tqb_asset || $is_tcb_bridge_asset;
}
