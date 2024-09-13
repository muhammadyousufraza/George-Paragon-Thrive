<?php
/**
 * notice to be displayed if license is not validated / active
 * going to load the styles inline because there are so few lines and not worth an extra server hit.
 */
?>
<div class="tva-notice-modal-outer tve-ult-notice-overlay">
	<div id="tve_ult_license_notice" class="tva-notice-modal-inner">
		<img src="<?php echo TVA_Const::plugin_url( 'admin/img/ta-icon.png' ); ?>">

		<p>
			<?php echo __( "It looks like you have an old version of WordPress installed, but it's not compatible with this version of Thrive Apprentice. Thrive Apprentice uses WordPress functionality which is not available in versions earlier than 4.6. To be able to use this feature, please make sure you have the at least version 4.6 installed:", 'thrive-apprentice' ); ?>
		</p>

		<a class="tva-modal-btn tva-modal-btn-green tve-license-link"
		   href="<?php echo admin_url( 'update-core.php' ); ?>"><?php echo __( 'Update WordPress', 'thrive-apprentice' ); ?></a>
	</div>
</div>
