<?php
/**
 * notice to be displayed if license is not validated / active
 * going to load the styles inline because there are so few lines and not worth an extra server hit.
 */
?>
<div class="tva-notice-modal-outer tve-apprentice-notice-overlay">
	<div id="tve_apprentice_license_notice" class="tva-notice-modal-inner">
        <img src="<?php echo TVA_Const::plugin_url( 'admin/img/ta-icon.png' ); ?>">

		<p>
			<?php echo __( 'You need to activate your license before you can use Thrive Apprentice plugin!', 'thrive-apprentice' ); ?>
		</p>

		<a class="tva-modal-btn tva-modal-btn-green tve-license-link"
		   href="<?php echo admin_url( 'admin.php?page=tve_dash_license_manager_section' ); ?>"><?php echo __( 'Activate license', 'thrive-apprentice' ); ?></a>
	</div>
</div>
