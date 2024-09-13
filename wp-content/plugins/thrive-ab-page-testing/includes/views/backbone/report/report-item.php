<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/27/2017
 * Time: 3:09 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<td>
	<#= model.get('post_title') #>
	<a href="<#= model.get('preview_link') #>" target="_blank"><?php echo tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-dashboard-icons' ); ?></a>
</td>
<td>
	<#= model.get('traffic') #>
</td>
<td>
	<#= model.get('visitors') #>
</td>
<td>SALES!? WTF IS SALES?</td>
<td>
	<#= model.get('revenue') #>
</td>
<td>
	<#= model.get('revenue_visitor') #>
</td>
<td>COnversion RATE</td>
<# if( model.get('is_control') ) { #>
<td colspan="2" class="thrv-ab-variation-control">[ <?php echo __( 'This is the control', 'thrive-ab-page-testing' ); ?> ]</td>
<# }else{ #>
<td>
	<#= model.get('improvement') #>
</td>
<td>
	<#= model.get('chance_to_beat_orig') #>

	<a class="tvd-tooltipped tvd-right click" href="javascript:void(0);" data-fn="stop_variation" data-tooltip="<?php echo __( 'Stop this variation', 'thrive-ab-page-testing' ); ?>" data-position="top">
		<i class="tvd-icon-stop tvd-text-red" style="font-size: 16px;"></i>
	</a>
</td>
<# } #>
