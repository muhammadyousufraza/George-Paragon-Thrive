<div class="notice notice-warning">
	<h4 class="tva-stripe-notice-heading"><?php echo __( 'Please reconnect Apprentice to your Stripe Account', 'thrive-apprentice' ); ?></h4>
	<p class="tva-stripe-notice-p"><?php echo __( 'We have upgraded our connections with Stripe to provide you a more streamlined connection and more data to process. In order to make this happen we would like to request you need to reconnect your stripe account. ', 'thrive-apprentice' ) ?> </p>
	<p class="tva-stripe-notice-p"><?php echo __( 'Don’t worry, your stripe payment processes are still working but for advanced features we’d recommend you reconnect your account as soon as possible.', 'thrive-apprentice' ) ?> <a href="" target="_blank"><?php echo __( 'Learn More', 'thrive-apprentice' ); ?></a></p>
	<button id="tva-stripe-reconnect"><?php echo __( 'Reconnect Stripe', 'thrive-apprentice' ); ?></button>
</div>
<style>
    .tva-stripe-notice-heading {
        margin: 10px 0 5px !important;
    }

    .tva-stripe-notice-p {
        margin: 0 0 5px 0 !important;
    }

    #tva-stripe-reconnect {
        cursor: pointer;
        color: #fff !important;
        border-radius: 3px;
        background: #3858E9 !important;
        margin: 10px 0 !important;
        padding: 10px;
        outline: none !important;
        border: none !important;
    }

    #tva-stripe-reconnect:hover {
        opacity: 0.8 !important;
    }
</style>
<script>
	const button = document.getElementById( 'tva-stripe-reconnect' );

	button.addEventListener( 'click', function ( e ) {
		wp.apiRequest( {
			url: `<?php echo tva_get_route_url( 'stripe' );?>/connect_account`,
			method: 'POST'
		} ).then( response => {
			if ( response.success ) {
				window.location = response.url;
			}

		} )
	} );
</script>
