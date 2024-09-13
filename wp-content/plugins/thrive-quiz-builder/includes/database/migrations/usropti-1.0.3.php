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

$this->add_or_modify_column( 'users', 'quiz_id', 'BIGINT NOT NULL;' );
$this->create_index( 'IDX_users_quiz_id', 'users', 'quiz_id' );
$this->create_index( 'IDX_users_completed_quiz', 'users', 'completed_quiz' );
