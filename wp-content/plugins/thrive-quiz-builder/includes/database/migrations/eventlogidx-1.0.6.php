<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/27/2018
 * Time: 5:07 PM
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/** @var $this TD_DB_Migration $questions */

$this->create_index( 'IDX_event_log_page_id', 'event_log', 'page_id' );
