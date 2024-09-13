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
		<span class="tab-truncate">
			<a class="thrive-ab-editor-link"><#= item.get('title') #></a>
			<# if( table_model.get('status') === 'completed' && parseInt(item.get('is_winner')) === 1 ) { #>
			&nbsp;<span class="tab-winner-label">(<?php echo __( 'winner', 'thrive-ab-page-testing' ); ?>)</span>
			<# } #>
		</span>
		<span class="tab-edit-controls">
			<a href="<#= item.get('preview_link') #>" target="_blank">
				<?php echo tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-dashboard-icons' ); ?>
			</a>
			<# if ( table_model.get('status') !== 'completed' && item.get('editor_link') ) { #>
			<a href="<#= item.get('editor_link') #>" class="top-edit-icon tvd-tooltipped" data-tooltip="<?php echo __( 'Edit variation with Architect', 'thrive-ab-page-testing' ); ?>" data-position="top"></a>
			<# } #>
		</span>
	</div>
	<div class="tvd-col tvd-s2">
		<# if( table_model.get('status') === 'completed') { #>
		<#= parseInt(item.get('traffic')) #>%
		<# }else{ #>
		<div class="thrive-ab-test-item-traffic tvd-row">
			<div class="tvd-col tvd-s8 thrive-ab-variation-traffic-slider">
				<input type="range" class="input change" min="0" max="100" data-fn="on_change" data-fn-input="on_input" value="<#= parseInt(item.get('traffic')) #>">
			</div>
			<div class="tvd-col tvd-s4 thrive-ab-variation-traffic-input">
				<input class="thrive-ab-card-traffic-input input change" data-fn="on_change" data-fn-input="on_input" type="number" min="0" max="100"
					   value="<#= parseInt(item.get('traffic')) #>">
			</div>
		</div>
		<# } #>
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
		<#= item.get('conversion_rate') #>%
	</div>
	<div class="tvd-col tvd-s2 tab-control-indicator">
		<?php echo __( '[This is the Control]', 'thrive-ab-page-testing' ) ?>
	</div>
</div>
