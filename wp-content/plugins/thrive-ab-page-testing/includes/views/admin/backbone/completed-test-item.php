<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 5:18 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<td><#= model.get('title') #></td>
<td><#= model.get('date_completed_pretty') #></td>
<td><#= model.get('page_title') #></td>
<td><#= model.get('goal') #></td>
<td>
	<a href="<#= model.get('test_link') #>"><?php echo __( 'View Test Details', 'thrive-ab-page-testing' ); ?></a>&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(0);" class="tab-delete-test"><?php echo __( 'Delete Test', 'thrive-ab-page-testing' ); ?></a>
</td>
