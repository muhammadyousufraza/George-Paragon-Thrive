<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="tvd-modal-content">
	<h3 class="tvd-modal-title"><?php echo __( 'Edit member', 'thrive-apprentice' ) ?></h3>
	<?php include dirname( dirname( __FILE__ ) ) . '/utils/customers/service-cards.phtml'; ?>
	<div class="tva-flex tva-flex-row tva-flex-end mt-30">
		<button type="button" class="click tva-modal-btn tva-modal-btn-green" data-fn="save"><?php echo __( 'Save', 'thrive-apprentice' ) ?></button>
	</div>
</div>
