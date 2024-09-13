<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

return array(
	'control'                          => __( 'Control', 'thrive-ab-page-testing' ),
	'variation_added'                  => __( 'Variation Added', 'thrive-ab-page-testing' ),
	'delete_title'                     => __( 'Yes, delete', 'thrive-ab-page-testing' ),
	'archive_title'                    => __( 'Yes, archive', 'thrive-ab-page-testing' ),
	'cancel'                           => __( 'Cancel', 'thrive-ab-page-testing' ),
	'stop'                             => __( 'Stop', 'thrive-ab-page-testing' ),
	'keep_it_running'                  => __( 'Keep It Running', 'thrive-ab-page-testing' ),
	'add_variation_error'              => __( 'Error adding new variation', 'thrive-ab-page-testing' ),
	'variation_no'                     => __( 'Variation %s', 'thrive-ab-page-testing' ),
	'about_to_delete'                  => __( 'You are about to delete %s. Do you want to continue?', 'thrive-ab-page-testing' ),
	'about_to_archive'                 => __( 'You are about to archive %s. Do you want to continue?', 'thrive-ab-page-testing' ),
	'about_to_stop_variation'          => __( 'Are you sure you want to stop showing the variation: "%s"', 'thrive-ab-page-testing' ),
	'invalid_test_title'               => __( 'Invalid test title', 'thrive-ab-page-testing' ),
	'invalid_min_win_conversions'      => __( 'Invalid minimum conversions', 'thrive-ab-page-testing' ),
	'not_number_min_win_conversions'   => __( 'Minimum conversions is not number', 'thrive-ab-page-testing' ),
	'greater_zero_min_win_conversions' => __( 'Minimum conversions must be greater than zero', 'thrive-ab-page-testing' ),
	'invalid_auto_win_min_duration'    => __( 'Minimum duration invalid', 'thrive-ab-page-testing' ),
	'invalid_auto_win_chance_original' => __( 'Chance to beat original invalid', 'thrive-ab-page-testing' ),
	'variation_status_not_changed'     => __( 'Variation status could not be changed', 'thrive-ab-page-testing' ),
	'variation_winner'                 => __( '<b>%s</b> was declared as being the winner for the current test.', 'thrive-ab-page-testing' ),
	'choose_winner'                    => __( 'Choose winner variation', 'thrive-ab-page-testing' ),
	'automatic_winner_settings'        => __( 'Automatic winner settings', 'thrive-ab-page-testing' ),
	'auto_win_enabled'                 => __( 'Automatic winner settings enabled.', 'thrive-ab-page-testing' ),
	'auto_win_disabled'                => __( 'Automatic winner settings disabled.', 'thrive-ab-page-testing' ),
	'delete_variation'                 => __( 'Delete variation', 'thrive-ab-page-testing' ),
	'archive_variation'                => __( 'Archive variation', 'thrive-ab-page-testing' ),
	'select_thank_you_page'            => __( 'Select a thank you page', 'thrive-ab-page-testing' ),
	'select_goal_page'                 => __( 'Select a goal page', 'thrive-ab-page-testing' ),
	'select_measurement_option'        => __( 'Select a measurement option', 'thrive-ab-page-testing' ),
);
