<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/14/2017
 * Time: 12:56 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="thrive-ab-test-items"></div>
<div class="thrive-ab-test-footer">
	<p><span></span><?php echo __( 'Changes occurred while a test is running can sometimes invalidate the test results.', 'thrive-ab-page-testing' ); ?></p>
	<span id="thrive-ab-conversion-goals" class="click" data-fn="open_conversion_goal_modal"></span>
	<span id="thrive-ab-auto-win-text"></span>
	<button class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-blue tvd-waves-effect tvd-blue-text click"
	        data-fn="change_automatic_winner_settings"><?php echo __( 'Change', 'thrive-ab-page-testing' ) ?></button>
</div>
<div class="thrive-ab-test-stopped-items"></div>
