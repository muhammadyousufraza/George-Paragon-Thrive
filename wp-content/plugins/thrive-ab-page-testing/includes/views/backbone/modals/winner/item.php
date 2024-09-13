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
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('impressions') #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('unique_impressions') #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('conversions') #>
	</div>
	<div class="tvd-col tvd-s2">
		<#= item.get('conversion_rate') #>%
	</div>
	<div class="tvd-col tvd-s2 <#= item.get('is_control')? '': ( item.get('improvement') >= 0 ? 'thrive-ab-positive' : 'thrive-ab-negative' ) #>">
		<#= item.get('is_control')? 'N/A' : item.get('improvement')+'%' #>
	</div>
	<div class="tvd-col tvd-s2 <#= item.get('is_control')? '': ( item.get('chance_to_beat_orig') >= 0 ? 'thrive-ab-positive' : 'thrive-ab-negative' ) #>">
		<#= item.get('is_control')? 'N/A' : item.get('chance_to_beat_orig')+'%' #>
	</div>
	<div class="tvd-col tvd-s1">
		<a href="javascript:void(0)" class="click" data-fn-click="set_as_winner">
			<?php echo __( 'Set as winner', 'thrive-ab-page-testing' ) ?>
		</a>
	</div>
</div>
