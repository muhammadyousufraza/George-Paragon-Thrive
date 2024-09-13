<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * @return TVA_Term_Query
 */
function tva_term_query() {
	return new TVA_Term_Query();
}


global $tva_course;

/**
 * Depending on the current request
 * - tries to instantiate a course only once
 *
 * @return TVA_Course_V2
 */
function tva_course() {

	global $tva_course;

	/**
	 * if we have a course then return it
	 */
	if ( $tva_course instanceof TVA_Course_V2 && $tva_course->get_id() ) {
		return $tva_course;
	}

	/**
	 * instantiate it empty to be able to use ti
	 */
	$tva_course = new TVA_Course_V2( array() );

	$init_course = static function () {
		global $tva_course;

		$queried_object = get_queried_object();
		if ( ! $queried_object && ! empty( $_REQUEST['post_id'] ) && wp_doing_ajax() && is_editor_page_raw( true ) ) {
			/* this makes sure that the queried_object is correctly read also in ajax requests. e.g. when rendering a cloud template via ajax for an author box */
			$queried_object = get_post( $_REQUEST['post_id'] );
		}

		if ( $queried_object instanceof WP_Term ) {
			//this should enter only once
			if ( $queried_object->taxonomy === TVA_Const::COURSE_TAXONOMY ) {
				$tva_course = new TVA_Course_V2( $queried_object );
			}
		} else if ( $queried_object instanceof WP_Post ) {
			$terms = get_the_terms( $queried_object, TVA_Const::COURSE_TAXONOMY );

			if ( ! empty( $terms ) ) {
				$tva_course = new TVA_Course_V2( $terms[0] );
			}
		}
	};

	/**
	 * After WP is ready
	 * - try getting the course term
	 */
	add_action( 'wp', $init_course, 0 );

	/**
	 * in REST requests, setup the course if needed
	 */
	add_action( 'thrive_theme_after_query_vars', $init_course );

	/**
	 * In editor ajax requests, set the course from the `post_id` request field if present
	 */
	add_action( 'tcb_ajax_before', $init_course );

	return $tva_course;
}

tva_course();

/**
 * Global instance to be used allover
 */
global $tva_db;

/**
 * Set the db object
 */
$tva_db = new TVA_Db();

/**
 * Returns the TVA_Settings_Manager instance
 *
 * @param string $settings_key optional. If not null, it will get the settings instance instead of the settings manager instance
 *
 * @return TVA_Settings_Manager|TVA_Setting
 */
function tva_get_settings_manager( $settings_key = null ) {
	$instance = TVA_Settings_Manager::get_instance();

	return $settings_key && is_string( $settings_key ) ? $instance->factory( $settings_key ) : $instance;
}

tva_get_settings_manager();

/**
 * Helper function to retrieve the value of a setting by its key
 *
 * @param string $settings_key Settings key
 *
 * @return string|array|int|bool the setting value
 */
function tva_get_setting( $settings_key ) {
	return tva_get_settings_manager( $settings_key )->get_value();
}

new TVA_Privacy();

TVA_Cookie_Manager::instance();

new TVA_Sendowl_Manager();

/**
 * Returns an instance of TVA_Customer of the logged in user
 *
 * @return TVA_Customer
 */
function tva_customer() {
	global $tva_customer;

	/**
	 * if we have a customer then return it
	 */
	if ( $tva_customer instanceof TVA_Customer ) {
		return $tva_customer;
	}

	/**
	 * After WP is fully loaded
	 */
	add_action(
		'wp_loaded',
		function () {
			global $tva_customer;

			$tva_customer = new TVA_Customer( get_current_user_id() );
		}
	);

	return $tva_customer;
}

tva_customer();


global $tva_integrations;

/**
 * Global Accessor
 *
 * @return TVA_Integrations_Manager
 */
function tva_integration_manager() {

	global $tva_integrations;

	if ( empty( $tva_integrations ) ) {
		$tva_integrations = new TVA_Integrations_Manager();
	}

	return $tva_integrations;
}

tva_integration_manager();


global $tva_access_manager;

/**
 * Global Accessor for TVA_Access_Manager
 *
 * @return TVA_Access_Manager
 */
function tva_access_manager() {

	global $tva_access_manager;

	if ( false === $tva_access_manager instanceof TVA_Access_Manager ) {
		try {
			$tva_access_manager = new TVA_Access_Manager( tva_integration_manager() );
		} catch ( Exception $e ) {
		}
	}

	return $tva_access_manager;
}

tva_access_manager();

add_action( 'init', array( 'TVA_Database_Manager', 'push_db_manager' ) );


global $tva_email_templates;

/**
 * Method wrapper for singleton
 *
 * @return TVA_Email_Templates
 */
function tva_email_templates() {

	global $tva_email_templates;

	$tva_email_templates = TVA_Email_Templates::get_instance();

	return $tva_email_templates;
}

tva_email_templates();

function tva_assessment_settings() {

	return \TVA\Assessments\TVA_Assessment_Settings::get_instance();
}

tva_assessment_settings();


global $tva_nav_menu;

/**
 * Method wrapper for singleton
 *
 * @return TVA_Nav_Menu
 */
function tva_nav_menu() {

	global $tva_nav_menu;

	$tva_nav_menu = TVA_Nav_Menu::get_instance();

	return $tva_nav_menu;
}

add_action( 'after_setup_theme', 'tva_nav_menu' );

/**
 * Get the global access restriction instance, or an instance specific to a course
 *
 * @param int|string|TVA_Course|WP_Term $course_or_term
 *
 * @return TVA_Access_Restriction
 */
function tva_access_restriction_settings( $course_or_term = null ) {
	if ( ! empty( $course_or_term ) && ! is_numeric( $course_or_term ) ) {
		$course_or_term = $course_or_term->term_id;
	}

	return TVA_Access_Restriction::instance_factory( $course_or_term );
}

global $tva_course_overview_post;

/**
 * @return TVA_Course_Overview_Post
 */
function tva_course_overview() {

	global $tva_course_overview_post;

	$tva_course_overview_post = TVA_Course_Overview_Post::instance();

	return $tva_course_overview_post;
}

tva_course_overview();

global $tva_course_certificate;

/**
 * @return TVA_Course_Certificate
 */
function tva_course_certificate() {

	global $tva_course_certificate;

	$tva_course_certificate = TVA_Course_Certificate::instance();

	return $tva_course_certificate;
}

tva_course_certificate();


global $tva_course_completed;

/**
 * @return TVA_Course_Completed
 */
function tva_course_completed() {

	global $tva_course_completed;

	$tva_course_completed = TVA_Course_Completed::instance();

	return $tva_course_completed;
}

tva_course_completed();

global $tva_course_index_page;

function tva_course_index_page() {
	global $tva_course_index_page;

	if ( ! $tva_course_index_page ) {
		$tva_course_index_page = new TVA_Course_Index_Page();
	}
}

tva_course_index_page();

add_action( 'thrive_automator_init', array( 'TVA\Automator\Main', 'init' ) );

/**
 * Enables the Apprentice - Quiz Builder integration
 */
add_action( 'init', array( \TVA\TQB\Main::class, 'get_instance' ) );
