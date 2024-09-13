<div class="tva-lessons-container">

	<?php

	echo do_shortcode( sprintf( '[tva_lesson_list %s]',
		! empty( $instance['title'] ) ? 'title="' . $instance['title'] . '"' : ''
	) );
	$json = json_encode( ! empty( $instance['collapse'] ) ? $instance['collapse'] : array() );
	?>
</div>
<script type="text/javascript">

	/**
	 * Localize widget config
	 * @type {{}}
	 */
	var ThriveAppWidgets = window.ThriveAppWidgets || {};

	ThriveAppWidgets.config = ThriveAppWidgets.config || {};
	jQuery( document ).ready( function ( $ ) {
		ThriveAppWidgets.config = $.extend( ThriveAppWidgets.config, {
			"<?php echo $this->id; ?>": <?php echo $json; ?>
		} );
	} );

</script>
