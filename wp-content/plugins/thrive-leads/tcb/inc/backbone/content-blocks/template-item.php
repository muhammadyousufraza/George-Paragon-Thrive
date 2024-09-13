<div class="cb-template-item modal-item click<#=selected ? ' active' : ''#><#=item.fav ? ' cb-fav' : ''#>" data-id="<#= item.id #>" data-fn="dom_select">
	<div class="cb-template-wrapper">
		<div class="cb-template-thumbnail lazy-loading">
			<a href="javascript:void(0);" class="click cb-thumb-overlay" data-id="<#= item.id #>" data-fn="dom_insert_into_content"></a>
			<img class="tve-lazy-img" src='<?php echo esc_url( tve_editor_css( 'images/loading-spinner.gif' ) ); ?>' data-ratio="<#= item.thumb_sizes ? parseFloat(parseInt(item.thumb_sizes.h) / parseInt(item.thumb_sizes.w)).toFixed(3) : ''#>" data-src="<#= item.thumb #>"/>
		</div>
		<div class="cb-actions">
			<a href="javascript:void(0);" class="click" data-id="<#= item.id #>" data-fn="dom_preview"><span id="cb-preview-light"><?php tcb_icon( 'eye-light' ); ?></span><span><?php echo esc_html__( 'Preview Block', 'thrive-cb' ) ?></span></a>
			<div>
				<div class="cb-separator"></div>
				<a href="javascript:void(0);" class="click cb-favorite" data-id="<#= item.id #>" data-fn="dom_fav">
					<span data-tooltip="<#= favorite_data[item.fav].tooltip #>"><#= TVE.icon(favorite_data[item.fav].icon) #></span>
				</a>
			</div>
		</div>
		<div class="selected"></div>
	</div>
</div>
