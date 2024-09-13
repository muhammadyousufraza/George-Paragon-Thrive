<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div class="tvd-modal-content">
	<div class="tvd-green">
		<span class="tvd-icon-check"></span>
	</div>
	<p><?php echo __( 'Test completed!' ) ?></p>
	<div class="tab-relative">
		<?php echo tcb_icon( 'winner', true, 'sidebar' ) ?>
		<span><#= this.model.get('improvement') #>%</span>
		<p>
			<#= this.model.get('label') #>
		</p>
	</div>
</div>
<div class="tvd-modal-footer">
	<div class="tvd-row">
		<div class="tvd-col tvd-s12 tvd-center">
			<a href="javascript:void(0)"
			   class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-modal-submit">
				<?php echo __( 'Go to test report', 'thrive-ab-page-testing' ) ?>
			</a>
		</div>
	</div>
</div>
