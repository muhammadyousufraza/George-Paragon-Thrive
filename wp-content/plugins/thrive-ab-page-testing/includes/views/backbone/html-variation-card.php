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

<div class="tvd-card tvd-white">
	<div class="tvd-card-content">
		<div class="thrive-ab-card-head tvd-row">
			<div class="tvd-col tvd-s9">
				<div class="thrive-ab-title-content">
					<span class="thrive-ab-card-title">
						<#= item.get('post_title') #>
					</span>
					<span id="thrive-ab-control"><#= item.get('is_control') ? '[ Control ]' : '' #></span>
					<a href="javascript:void(0)" class="<#= item.get('is_control') ? '' : 'tvd-icon-pencil' #> click tvd-tooltipped" data-tooltip="<?php echo __( 'Edit variation name', 'thrive-ab-page-testing' ); ?>" data-position="top" data-fn="edit_title"></a>
				</div>
				<div class="thrive-ab-title-editor"></div>
			</div>
			<div class="thrive-ab-card-options tvd-col tvd-s3 thrive-ab-card-actions">
				<a class="tvd-icon-copy click tvd-tooltipped" data-tooltip="<?php echo __( 'Duplicate this variation', 'thrive-ab-page-testing' ); ?>" data-position="top" data-fn="duplicate"></a>
				<a class="tvd-icon-trash-o tab-archive click tvd-tooltipped" data-tooltip="<?php echo __( 'Archive variation', 'thrive-ab-page-testing' ); ?>" data-position="top" data-fn="archive" style="<#= item.get('is_control') ? 'display:none;' : '' #>"></a>
				<a class="tvd-icon-trash-o click tvd-tooltipped" data-tooltip="<?php echo __( 'Permanently delete variation', 'thrive-ab-page-testing' ); ?>" data-position="top" data-fn="delete" style="<#= item.get('is_control') ? 'display:none;' : '' #>"></a>
			</div>
		</div>
		<div class="thrive-ab-card-thumb" style="background-image: url('<#= item.get('thumb_link') #>')">
			<div>
				<a href="<#= item.get('edit_link') #>" class=""><?php echo __( 'Edit Variation', 'thrive-ab-page-testing' ) ?></a>
			</div>
		</div>
		<div class="thrive-ab-card-footer tvd-row">
			<div class="tvd-col tvd-s2">
				<label for="thrive-ab-card-traffic-input"><?php echo __( 'Traffic', 'thrive-ab-page-testing' ) ?></label>
			</div>
			<div class="tvd-col tvd-s8 thrive-ab-variation-traffic-slider">
				<input type="range" class="input change" min="0" max="100" data-fn="on_change" data-fn-input="on_input" value="<#= parseInt(item.get('traffic')) #>">
			</div>
			<div class="tvd-col tvd-s2 thrive-ab-variation-traffic-input">
				<input class="thrive-ab-card-traffic-input input change" data-fn="on_change" data-fn-input="on_input" type="number" min="0" max="100"
					   value="<#= parseInt(item.get('traffic')) #>">
			</div>
		</div>
	</div>
</div>
