<?php
/**
 * Thrive Themes  https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/** @var $this TD_DB_Migration $questions */

$this->create_table( 'results_links', "
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`quiz_id` BIGINT(20) UNSIGNED NOT NULL,
	`page_id` BIGINT(20) UNSIGNED NOT NULL,
	`status` ENUM('valid', 'invalid') NOT NULL DEFAULT 'valid',
	`lower_bound` INT(11) NOT NULL DEFAULT '0',
	`upper_bound` INT(11) NOT NULL DEFAULT '0',
	`type` ENUM('local', 'external') NOT NULL DEFAULT 'local',
	`post_id` BIGINT(20) UNSIGNED DEFAULT NULL,
	`link` VARCHAR(255) DEFAULT NULL,
	`result_id` INT(10) UNSIGNED NULL COMMENT 'quiz category id',
	`date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`date_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	INDEX IDX_quiz_id (quiz_id),
	INDEX IDX_page_id (page_id),
	INDEX IDX_post_id (post_id),
	INDEX IDX_result_id (result_id)
	" );
