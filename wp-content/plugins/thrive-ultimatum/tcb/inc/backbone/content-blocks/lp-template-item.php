<div class="cb-template-item modal-item click" data-id="<#= item.id #>" data-fn="domDownloadTpl">
	<div class="cb-template-wrapper">
		<div class="cb-template-thumbnail lazy-loading">
			<img class="tve-lazy-img" src="<?php echo esc_url( tve_editor_css( 'images/loading-spinner.gif' ) ); ?>" data-src="<#= item.thumb #>" data-ratio="<#= parseFloat(parseInt(item.thumb_sizes.h) / parseInt(item.thumb_sizes.w)).toFixed(3) #>"/>
		</div>
	</div>
</div>
