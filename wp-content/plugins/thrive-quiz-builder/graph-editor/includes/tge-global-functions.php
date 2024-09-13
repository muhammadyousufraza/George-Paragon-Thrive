<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-graph-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}


/**
 * Appends the WordPress tables prefix and the default tqb_ prefix to the table name
 *
 * @param string $table name of the table.
 *
 * @return string the modified table name
 */
function tge_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . Thrive_Graph_Editor::DB_PREFIX . $table;
}

/**
 * Load progress bar templates in frontend
 */
add_filter( 'tqb_backbone_frontend_templates', 'tge_backbone_frontend_templates' );

function tge_backbone_frontend_templates( $templates ) {

	$tpls = tve_dash_get_backbone_templates( tge()->path( 'includes/templates/backbone/quiz/progress-bar/skin' ), 'quiz' );

	return array_merge( $templates, $tpls );
}
