<div class="tva-progress-container">
	<?php
	if ( ! empty( $instance['title'] ) ) {
		echo do_shortcode( '[tva_progress_bar title="' . $instance['title'] . '"]' );
	} else {
		echo do_shortcode( '[tva_progress_bar]' );
	}
	?>
</div>
