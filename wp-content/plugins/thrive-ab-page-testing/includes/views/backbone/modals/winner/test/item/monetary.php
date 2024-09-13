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
<div class="tvd-row">
	<div class="tvd-col tvd-s2">
		<span><#= item.get('title') #></span>
		<span class="tvd-gray-accent"><#= item.get('is_control')? '(control)' : '' #></span>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('impressions') == 0 ? 'N/A' : item.get('impressions') #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('unique_impressions') == 0 ? 'N/A' : item.get('unique_impressions') #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('revenue') == 0 ? 'N/A' : parseFloat(item.get('revenue')).toFixed(2) #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('revenue_visitor') == 0 ? 'N/A' : parseFloat(item.get('revenue_visitor')).toFixed(2) #>
	</div>
	<div class="tvd-col tvd-s2 <#= item.get('is_control') || !item.get('improvement') ? '' : ( item.get('improvement') > 0 ? 'thrive-ab-positive' : 'thrive-ab-negative' ) #>">
		<#= item.get('is_control') || !item.get('improvement') ? 'N/A' : parseFloat(item.get('improvement')).toFixed(2)+'%' #>
	</div>
	<div class="tvd-col tvd-s2 <#= item.get('is_control') || !item.get('chance_to_beat_orig') ? '' : ( item.get('chance_to_beat_orig') > 0 ? 'thrive-ab-positive' : 'thrive-ab-negative' ) #>">
		<#= item.get('is_control') || !item.get('chance_to_beat_orig') ? 'N/A' : parseFloat(item.get('chance_to_beat_orig')).toFixed(2) + '%' #>
	</div>
	<div class="tvd-col tvd-s2 tvd-right-align">
		<a href="javascript:void(0)" class="click" data-fn-click="set_as_winner">
			<?php echo __( 'Set as winner', 'thrive-ab-page-testing' ) ?>
		</a>
	</div>
</div>
