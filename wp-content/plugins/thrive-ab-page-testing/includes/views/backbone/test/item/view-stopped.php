<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/11/2017
 * Time: 9:25 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="tvd-row">
	<div class="tvd-col tvd-s2 tab-headline">
		<span class="tab-truncate"><#= item.get('title') #></span>
		<span class="tab-edit-controls">
			<a href="<#= item.get('preview_link') #>" target="_blank">
				<?php echo tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-dashboard-icons' ); ?>
			</a>
		</span>
	</div>
	<div class="tvd-col tvd-s2">
		<#= item.get('traffic') #>
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
		<#= item.get('improvement') #>
	</div>
	<div class="tvd-col tvd-s2">
		<#= item.get('chance_to_beat_orig') #>
	</div>
</div>
