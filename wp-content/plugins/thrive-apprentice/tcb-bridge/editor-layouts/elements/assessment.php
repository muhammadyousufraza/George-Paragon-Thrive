<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="thrv_wrapper <?php echo str_replace( '.', '', \TVA\Architect\Assessment\Main::IDENTIFIER ); ?> tcb-elem-placeholder">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'assessment-submit', false, 'editor' ) ?>
		<?php echo __( 'Select an assessment', 'thrive-apprentice' ); ?>
	</span>
</div>

