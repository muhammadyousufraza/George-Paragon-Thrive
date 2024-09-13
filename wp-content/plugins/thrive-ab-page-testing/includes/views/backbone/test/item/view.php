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
	<div class="tvd-col tvd-s2 tab-headline">
		<span>
			<a class="thrive-ab-editor-link" href="<#= item.get('editor_link') #>"><#= item.get('title') #></a>
		</span>
		<span class="tab-edit-controls">
			<a href="<#= item.get('preview_link') #>" target="_blank">
				<?php echo tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-dashboard-icons' ); ?>
			</a>
		</span>
	</div>
	<div class="tvd-col tvd-s2">
		<div class="thrive-ab-test-item-traffic tvd-row">
			<div class="tvd-col tvd-s8 thrive-ab-variation-traffic-slider">
				<input type="range" class="input change" min="0" max="100" data-fn="on_change" data-fn-input="on_input" value="<#= parseInt(item.get('traffic')) #>">
			</div>
			<div class="tvd-col tvd-s4 thrive-ab-variation-traffic-input">
				<input class="thrive-ab-card-traffic-input input change" data-fn="on_change" data-fn-input="on_input" type="number" min="0" max="100" value="<#= parseInt(item.get('traffic')) #>">
			</div>
		</div>
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
	<div class="tvd-col tvd-s1">
		<#= item.get('revenue') #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('revenue_visitor') #>
	</div>
	<div class="tvd-col tvd-s1">
		<#= item.get('conversion_rate') #>
	</div>
	<div class="tvd-col tvd-s1 thrive-ab-positive">
		<#= item.get('improvement') #>
	</div>
	<div class="tvd-col tvd-s1 thrive-ab-negative">
		<#= item.get('chance_to_beat_orig') #>
		<a class="tvd-tooltipped tvd-right tvd-line-height click" data-fn="stop_variation" href="javascript:void(0);" data-tooltip="<?php echo __( 'Stop this variation', 'thrive-ab-page-testing' ); ?>" data-position="top">
			<i class="tvd-icon-stop tvd-text-red" style="font-size: 16px;"></i>
		</a>
	</div>
</div>
