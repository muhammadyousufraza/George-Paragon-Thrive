<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="tvd-row">
	<div class="tvd-col tvd-s12">
		<h1>Switch the update channel</h1>
	</div>
</div>
<div class="tvd-row">
	<div class="tvd-col tvd-s12">
		<span>This will download on your site the beta version of Thrive Suite products</span>
	</div>
</div>
<div class="tvd-row">
	<div class="tvd-col tvd-s12">
		<div class="tvd-switch">
			<label>
				Beta
				<input type="checkbox" class="tvd-toggle-input" <?php checked( 'beta', tvd_get_update_channel() ); ?>>
				<span class="tvd-lever"></span>
			</label>
		</div>
	</div>
</div>
<script type="text/javascript">
	( function ( $ ) {
		$( '.tvd-toggle-input' ).on( 'change', function ( event ) {
			wp.ajax.post( {
				_wpnonce: TVE_Dash_Const.nonce,
				value: event.currentTarget.checked ? 'beta' : 'stable',
				action: 'tve_update_settings'
			} );
		} )
	} )( jQuery )
</script>
