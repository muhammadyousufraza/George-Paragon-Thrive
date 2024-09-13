<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div class="tcb-elem-placeholder thrv_audio thrv_wrapper" data-type="custom">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'audio-player', false, 'editor' ); ?>
		<?php echo esc_html__( 'Insert Audio', 'thrive-cb' ); ?>
	</span>

	<div class="tve_audio_container" style="display: none;">
		<div class="audio_overlay"></div>
	</div>
</div>
