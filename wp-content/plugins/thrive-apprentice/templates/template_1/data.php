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
 * This is the color sizes, naming and other good presets for the apprentice
 *
 * The settings variable should always be named $settings and the proprieties should be the same as in the default
 * template (Template 1)
 */
$settings = array(
	'template' => array(
		'ID'                  => 1,
		'name'                => 'Default',
		'font_family'         => 'Maven Pro, sans-serif',
		'main_color'          => '#5bc0eb',
		'course_title'        => 28,
		'course_title_color'  => '#333333',
		'page_headline'       => 24,
		'page_headline_color' => '#9f9f9f',
		'lesson_headline'     => 26,
		'chapter_headline'    => 28,
		'module_headline'     => 28,
		'paragraph'           => 16,
		'paragraph_color'     => '#333333',
		'logo_url'            => TVA_Const::plugin_url() . 'admin/img/dashboard-thrive-apprentice-horizontal.png',
	),
);

return $settings;
