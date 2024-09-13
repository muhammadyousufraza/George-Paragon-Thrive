<div style="margin: 20px auto; width: 911px">
	<button id="transient_remove" class="button button-primary"><?php echo esc_html__( 'Remove all transients', 'thrive-dash' ); ?></button>

	<hr>

	<script type="text/javascript">
		( function ( $ ) {
			$( '#transient_remove' ).on( 'click', function () {
				$.ajax( {
					url: ajaxurl,
					type: 'post',
					data: {
						_wpnonce: TVE_Dash_Const.nonce,
						action: 'tve_debug_reset_transient'
					}
				} ).always( function ( response ) {
					alert( response );
				} )
			} )
		} )( jQuery )
	</script>

	<pre><?php print_r( tve_get_debug_data() ); ?></pre>

</div>
