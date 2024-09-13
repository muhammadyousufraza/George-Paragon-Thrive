<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/25/2017
 * Time: 5:39 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<h2 class="tcb-modal-title"><?php echo __( 'Add / edit design state', 'thrive-ult'); ?></h2>
<div class="tve-templates-wrapper">
	<div class="tvd-input-field margin-bottom-5 margin-top-25">
		<input type="text" id="tve-ult-state-name" required>
		<label for="tve-ult-state-name"><?php echo __( 'State Name', 'thrive-ult'); ?></label>
	</div>
</div>
<div class="tcb-modal-footer">
	<button type="button" class="tcb-right tve-button medium green click white-text" data-fn="save">
		<?php echo __( 'Save', 'thrive-ult') ?>
	</button>
</div>
