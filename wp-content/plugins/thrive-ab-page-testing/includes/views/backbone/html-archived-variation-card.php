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
			<div class="tvd-col tvd-s10">
				<div class="thrive-ab-title-content">
					<span class="thrive-ab-card-title">
						<#= item.get('post_title') #>
					</span>
				</div>
			</div>
			<div class="thrive-ab-card-options tvd-col tvd-s2 thrive-ab-card-actions">
				<a href="<#= item.get('preview_link') #>" target="_blank" class="tvd-tooltipped" data-tooltip="<?php echo __( 'Preview', 'thrive-ab-page-testing' ); ?>" data-position="top">
					<?php echo tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-dashboard-icons' ); ?>
				</a>
				<a class="tvd-icon-trash-o click tvd-tooltipped" data-tooltip="<?php echo __( 'Permanently delete variation', 'thrive-ab-page-testing' ); ?>" data-position="top" data-fn="delete"></a>
			</div>
		</div>
		<div class="thrive-ab-card-thumb" style="background-image: url('<#= item.get('thumb_link') #>')"></div>
		<div class="thrive-ab-card-footer tvd-row">
			<a class="click" data-fn="restore"><?php echo __( 'Restore Variation', 'thrive-ab-page-testing' ) ?></a>
		</div>
	</div>
</div>
