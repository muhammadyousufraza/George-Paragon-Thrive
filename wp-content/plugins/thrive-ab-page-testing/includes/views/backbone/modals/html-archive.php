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
	<p>
		<#= this.data.description #>
	</p>
</div>
<div class="tvd-modal-footer">
	<div class="tvd-row">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a href="javascript:void(0)" class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tvd-modal-close tvd-waves-effect">
				<#= this.data.btn_no_txt #>
			</a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a href="javascript:void(0)" class="tvd-waves-effect tvd-waves-light tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-light tvd-right tvd-modal-submit">
				<#= this.data.btn_yes_txt #>
			</a>
		</div>
	</div>
</div>
