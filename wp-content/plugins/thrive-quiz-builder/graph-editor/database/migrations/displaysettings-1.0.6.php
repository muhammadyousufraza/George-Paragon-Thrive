<?php
/**
 * Thrive Themes  https://thrivethemes.com
 *
 * @package thrive-graph-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/** @var $this TD_DB_Migration $questions */

$questions = tge_table_name( 'questions' );
$this->add_or_modify_column( $questions, 'display_settings', 'MEDIUMTEXT NULL DEFAULT NULL AFTER `settings`' );
