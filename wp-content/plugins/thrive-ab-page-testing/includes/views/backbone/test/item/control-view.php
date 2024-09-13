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
		<span><#= ThriveAB.t.control #></span>
		<a href="<#= item.get('preview_link') #>" target="_blank">
			<?php echo tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-dashboard-icons' ); ?>
		</a>
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
	<div class="tvd-col tvd-s1">
		<?php echo __( '[This is the Control]', 'thrive-ab-page-testing' ) ?>
	</div>
</div>
