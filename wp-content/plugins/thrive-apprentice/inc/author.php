<div class="tva-author-container">
	<?php
	if ( ! empty( $instance['title'] ) ) {
		echo do_shortcode( '[tva_author title="' . $instance['title'] . '"]' );
	} else {
		echo do_shortcode( '[tva_author]' );
	}
	?>
</div>
