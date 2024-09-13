<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * This is the color sizes, naming and
 * The settings variable should always be named $settings and the proprieties should be the same as in the default
 * template (Template 1)
 */
$settings = array(
	'template' => array(
		'ID'                  => 2,
		'name'                => 'Template 2',
		'font_family'         => 'Georgia, serif',
		'main_color'          => '#CC0000',
		'course_title'        => 15,
		'course_title_color'  => '#AA0000',
		'page_headline'       => 20,
		'page_headline_color' => '#DD0000',
		'lesson_headline'     => 15,
		'chapter_headline'    => 15,
		'module_headline'     => 15,
		'paragraph'           => 12,
		'paragraph_color'     => '#EE0000',
		'logo_url'            => TVA_Const::plugin_url() . '/img/university-logo.png',
	),
);
