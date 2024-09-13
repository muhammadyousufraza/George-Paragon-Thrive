<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/21/2017
 * Time: 3:55 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h2 class="tcb-modal-title"><?php echo __( 'Save Page Template', 'thrive-ult'); ?></h2>
<div class="margin-top-20">
	<?php echo __( 'You can save the current page as a template for use on another post / page on your site.', 'thrive-ult') ?>
</div>
<div class="tve-templates-wrapper">
	<div class="tvd-input-field margin-bottom-5 margin-top-25">
		<input type="text" id="tve-template-name" required>
		<label for="tve-template-name"><?php echo __( 'Template Name', 'thrive-ult'); ?></label>
	</div>
</div>
<div class="tcb-modal-footer flex-end">
	<button type="button" class="tcb-right tve-button medium green click white-text" data-fn="save">
		<?php echo __( 'Save Template', 'thrive-ult') ?>
	</button>
</div>
