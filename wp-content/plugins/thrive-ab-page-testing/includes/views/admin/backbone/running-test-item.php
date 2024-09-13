<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 4:35 PM
 */
?>
<td><#= model.get('title') #></td>
<td><#= model.get('date_started_pretty') #></td>
<td><#= model.get('page_title') #></td>
<td><#= model.get('goal') #></td>
<td><#= model.get('unique_impressions') #></td>
<td><#= model.get('conversions') #></td>
<td><a href="<#= model.get('test_link') #>"><?php echo __( 'View Test Details', 'thrive-ab-page-testing' ); ?></a></td>
