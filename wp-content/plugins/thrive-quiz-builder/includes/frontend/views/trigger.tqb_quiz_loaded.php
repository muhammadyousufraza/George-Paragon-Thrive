<?php
/*
 * Triggers a jQuery event once this script is loaded in DOM
 * - usually used on ajax requests (TL Lazy Load)
 */
/**
 * @var int $quiz_id
 */
?>
<script type="text/javascript">
	( function ( $ ) {
		$( document ).ready( function () {
			setTimeout( function () {
				ThriveGlobal.$j( document ).trigger( 'tqb_quiz_loaded', {
					quiz_id: '<?php echo esc_js( $quiz_id ); ?>'
				} )
			}, 2000 )
		} );
	} )( jQuery );
</script>
