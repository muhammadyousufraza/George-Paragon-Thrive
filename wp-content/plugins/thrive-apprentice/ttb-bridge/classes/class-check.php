<?php

namespace TVA\TTB;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Offers some useful static methods for checking various conditions during a request
 *
 * Class Checks
 */
class Check {
	/**
	 * store a cache of object_id => skin_id mappings. calls to wp_get_object_terms are not cached by wordpress
	 *
	 * @var array
	 */
	private static $object_skin_cache = [];

	/**
	 * Check if an object or an ID of the object has a SKIN_TAXONOMY associated that belongs to Apprentice.
	 * If it has an apprentice skin, it returns its id. Otherwise, false is returned
	 *
	 * @param int|\WP_Post|\Thrive_Template|\Thrive_Typography $thing
	 *
	 * @return false|int skin instance if a skin has been identified
	 */
	public static function apprentice_skin( $thing ) {
		if ( is_numeric( $thing ) ) {
			$thing = get_post( $thing );
		}
		if ( ! is_object( $thing ) || ! $thing->ID ) {
			return false;
		}

		$thing_id = $thing->ID;

		if ( ! isset( self::$object_skin_cache[ $thing_id ] ) ) {
			if ( method_exists( $thing, 'get_skin_id' ) ) {
				$skin_id = $thing->get_skin_id();
			} else {
				$terms = wp_get_object_terms( $thing->ID, SKIN_TAXONOMY );
				if ( ! empty( $terms ) ) {
					$skin_id = $terms[0]->term_id;
				}
			}
			self::$object_skin_cache[ $thing_id ] = ! empty( $skin_id ) && Skin::has_tva_scope( $skin_id ) ? $skin_id : false;
		}


		return self::$object_skin_cache[ $thing_id ];
	}

	/**
	 * Check whether the current page is a Apprentice typography edit / preview page
	 *
	 * @return boolean
	 */
	public static function typography_page() {
		if ( ! \Thrive_Utils::is_theme_typography() ) {
			return false;
		}

		return (bool) static::apprentice_skin( thrive_typography() );
	}

	/**
	 * Whether or not the current page needs to have a typography reset style node in the <head> section
	 * Will return true for:
	 * 1) editing/previewing apprentice typography
	 * 2) on apprentice-related pages, while using ttb templates and the active skin does not inherit typography
	 *
	 * @return boolean
	 */
	public static function needs_typography_reset() {
		return static::typography_page() || ( tva_is_apprentice() && Main::uses_builder_templates() && ! Main::requested_skin()->inherit_typography );
	}

	/**
	 * Check if the current request is apprentice related and visual editing templates are used.
	 *
	 * @return bool
	 */
	public static function apprentice_visual() {
		return Main::uses_builder_templates() && ( tva_is_apprentice() || tva_general_post_is_apprentice() );
	}

	/**
	 * Check if a content is a module or a lesson
	 *
	 * @param null|int|string|\WP_Post|\TVA_Post $thing
	 *
	 * @return true
	 */
	public static function course_item( $thing = null ) {
		if ( ! $thing ) {
			$thing = get_the_ID();
		}
		if ( $thing instanceof \WP_Post ) {
			$thing = $thing->ID;
		} elseif ( $thing instanceof \TVA_Post ) {
			$thing = $thing->get_the_post()->ID;
		}

		$post_type = get_post_type( $thing );

		return in_array(
			$post_type,
			[
				\TVA_Const::MODULE_POST_TYPE,
				\TVA_Const::ASSESSMENT_POST_TYPE,
				\TVA_Const::LESSON_POST_TYPE,
				\TVA_Course_Certificate::POST_TYPE,
				\TVA_Course_Completed::POST_TYPE,
			]
		);
	}

	/**
	 * Check if the current request is a course overview, or a TAr editor page for the course overview post
	 *
	 * @return boolean
	 */
	public static function course_overview() {
		return ( get_post_type() === \TVA_Course_Overview_Post::POST_TYPE && is_editor_page_raw( true ) ) || is_tax( \TVA_Const::COURSE_TAXONOMY );
	}

	/**
	 * Check if the current request is a course certificate, or a TAr editor page for the course certificate post
	 *
	 * @return boolean
	 */
	public static function course_certificate() {
		return get_post_type() === \TVA_Course_Certificate::POST_TYPE && is_editor_page_raw( true );
	}

	/**
	 * Checks if this site instance is end user or is the builder website
	 *
	 * Makes magic happen in relation to Apprentice Builder Website
	 *
	 * @return bool
	 */
	public static function is_end_user_site() {
		return apply_filters( 'tva_is_end_user_site', true );
	}

	/**
	 * Checks if Thrive Automator plugin is installed and enabled
	 *
	 * @return bool
	 */
	public static function automator() {
		return defined( 'TAP_PLUGIN_URL' );
	}
}
