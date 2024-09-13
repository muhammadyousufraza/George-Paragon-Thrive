<div class="tva-container-notice tva-container-notice-orange tva-stripe-v1-notice mt-10 mb-10">
	<?php tva_get_svg_icon( 'exclamation-triangle' ); ?>
	<div class="tva-stripe-notice-text">
		<h4 class="m-0 mt-10"><?php echo __( 'Please reconnect Apprentice to your Stripe Account', 'thrive-apprentice' ); ?></h4>
		<p class="m-0 mt-10"><?php echo __( 'We have upgraded our connections with Stripe to provide you a more streamlined connection and more data to process. In order to make this happen we would like to request you need to reconnect your stripe account. ', 'thrive-apprentice' ) ?> </p>
		<p class="m-0 mt-10"><?php echo __( 'Don’t worry, your stripe payment processes are still working but for advanced features we’d recommend you reconnect your account as soon as possible.', 'thrive-apprentice' ) ?> <a href="" target="_blank"><?php echo __( 'Learn More', 'thrive-apprentice' ); ?></a></p>
		<button class="tva-btn tva-btn-blue click mt-20" id="tva-stripe-reconnect" data-fn="createAccount"><?php echo __( 'Reconnect Stripe', 'thrive-apprentice' ); ?></button>
	</div>
</div>
