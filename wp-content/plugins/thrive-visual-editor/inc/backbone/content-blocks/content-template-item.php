<div class="ct-item modal-item click" data-fn="selectTemplate" data-id="<#= item.id #>" data-name="<#= item.label #>" data-category="<#= item.id_category #>">
	<div class="modal-title-w-options">
		<div class="modal-item-name"><#= item.label #></div>
		<span><?php tcb_icon( 'check-regular' ); ?></span>
	</div>
	<div class="card">
		<div class="cb-template-thumbnail lazy-loading">
			<img class="tve-lazy-img" src="<?php echo esc_url( tve_editor_css( 'images/loading-spinner.gif' ) ); ?>" data-src="<#= item.thumb.url #>" data-ratio="<#= parseFloat(parseInt(item.thumb.h) / parseInt(item.thumb.w)).toFixed(3) #>"/>
		</div>
	</div>
</div>
