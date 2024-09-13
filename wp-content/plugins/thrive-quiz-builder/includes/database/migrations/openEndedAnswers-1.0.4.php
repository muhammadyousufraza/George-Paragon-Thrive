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

$this->add_or_modify_column( 'user_answers', 'answer_text', 'TEXT NULL DEFAULT NULL AFTER `quiz_id`' );
