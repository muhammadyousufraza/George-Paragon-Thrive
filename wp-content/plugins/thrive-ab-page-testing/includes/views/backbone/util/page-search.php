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
<div class="page-search-input">
	<input type="text" id="page-search-<#= item.cid #>" class="page-search" data-bind="post_title" value="<#= item.get('post_title') #>"/>
	<label for="page-search-<#= item.cid #>">
		<# if( item.get('type')=='monetary' ) { #>
			<?php echo __( 'Search Thank You Page ', 'thrive-ab-page-testing' ) ?>
		<# } else { #>
			<?php echo __( 'Search Goal Page', 'thrive-ab-page-testing' ) ?>
		<# }  #>
	</label>
</div>
<div class="page-search-options">
	<a href="javascript:void(0)" class="thrive-ab-edit-page tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-primary" target="_blank" <#= item.get('post_title')? '': 'style="display:none"' #>>
		<?php echo __( 'Edit Page', 'thrive-ab-page-testing' ) ?>
	</a>
	<a href="javascript:void(0)" class="thrive-ab-preview-page tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-primary" target="_blank" <#= item.get('post_title')? '': 'style="display:none"' #>>
		<?php echo __( 'Preview', 'thrive-ab-page-testing' ) ?>
	</a>
	<a href="javascript:void(0)" class="thrive-ab-remove-page tvd-right tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark">
		<?php echo __( 'Remove', 'thrive-ab-page-testing' ) ?>
	</a>
</div>
