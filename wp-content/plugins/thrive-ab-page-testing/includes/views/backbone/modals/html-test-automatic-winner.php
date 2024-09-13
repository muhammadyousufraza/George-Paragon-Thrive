<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/14/2017
 * Time: 1:00 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="tvd-row tvd-collapse">
	<div class="tvd-col tvd-s12">
		<span class="tab-label"><?php echo __( 'Enable Automatic Winner Settings', 'thrive-ab-page-testing' ) ?></span>
		<div class="tvd-switch">
			<label class="tvd-active">
				<input type="checkbox" id="auto-win-enabled" data-bind="auto_win_enabled">
				<span class="tvd-lever"></span>
			</label>
		</div>
	</div>
</div>
<div id="auto-win-settings">
	<span><?php echo __( 'Here you can set the conditions by which a form will be considered the winner.', 'thrive-ab-page-testing' ) ?></span>
	<div class="tvd-row tvd-gray">
		<div class="tvd-col tvd-s4">
			<div class="tvd-input-field">
				<input type="text" maxlength="4" id="auto_win_min_conversions" data-bind="auto_win_min_conversions"
					   value="<#= this.model.get('auto_win_min_conversions') #>">
				<label for="auto_win_min_conversions"><?php echo __( 'Minimum conversions', 'thrive-ab-page-testing' ) ?></label>
			</div>
		</div>
		<div class="tvd-col tvd-s4">
			<div class="tvd-input-field">
				<input type="text" maxlength="2" id="auto_win_min_duration" data-bind="auto_win_min_duration"
					   value="<#= this.model.get('auto_win_min_duration') #>">
				<label for="auto_win_min_duration" class=""><?php echo __( 'Minimum duration (days)', 'thrive-ab-page-testing' ) ?></label>
			</div>
		</div>
		<div class="tvd-col tvd-s4">
			<div class="tvd-input-field">
				<input type="text" maxlength="3" id="auto_win_chance_original" data-bind="auto_win_chance_original"
					   value="<#= this.model.get('auto_win_chance_original') #>">
				<label for="auto_win_chance_original" class=""><?php echo __( 'Chance to beat original (%)', 'thrive-ab-page-testing' ) ?></label>
			</div>
		</div>
	</div>
</div>
