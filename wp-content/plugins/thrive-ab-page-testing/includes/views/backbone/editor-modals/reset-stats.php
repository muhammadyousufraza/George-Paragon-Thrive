<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>


<h2 class="tcb-modal-title"><?php echo __( 'Apply changes: reset test data?' ) ?></h2>

<div class="row">
	<div class="col col-xs-12">
		<p><?php echo __( "You are about to save changes to a page in a running test. Note that major design changes will invalidate the data gathered in this test so far. For this reason, we recommend that you reset the test data to zero (the test will continue running).", 'thrive-ab-page-testing' ) ?></p>
	</div>
</div>
<div class="tcb-gray">
	<div class="row">
		<div class="col-xs-12">
			<label class="tcb-checkbox padding-bottom-10">
				<input type="checkbox" id="thrive-ab-reset-stats" checked="checked">
				<span><?php echo __( "Reset current test stats", 'thrive-cb' ) ?></span>
			</label>
		</div>
	</div>
</div>

<div class="tcb-modal-footer padding-top-20 control-grid">
	<button type="button" class="tcb-left tve-button text-only tcb-modal-cancel">
		<?php echo __( 'Cancel', 'thrive-cb' ) ?>
	</button>
	<button type="button" class="tcb-right tve-button medium tcb-modal-save">
		<?php echo __( 'Save variation and update test', 'thrive-cb' ) ?>
	</button>
</div>
