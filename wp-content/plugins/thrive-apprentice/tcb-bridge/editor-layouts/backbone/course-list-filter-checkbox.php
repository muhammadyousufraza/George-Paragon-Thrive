<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div data-source="<#= source #>" data-id="<#= data.ID #>">
	<input type="checkbox" class="click" data-fn="filterCheckboxClicked" data-source="<#= source #>" id="<#= source #>_<#= data.ID #>" value="<#= data.ID #>"/>
	<label for="<#= source #>_<#= data.ID #>"><#= data.label #></label>
</div>
