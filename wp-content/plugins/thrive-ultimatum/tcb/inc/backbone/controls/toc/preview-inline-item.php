<#if(typeof no_item !== 'undefined' && no_item){#>
<div class="tve-toc-no-headings">
	<div class="info-text orange tcb-text-center pt-5 pb-5 m-0">
		<p class="mb-0 mt-5">
			<strong>
				<?php echo esc_html__( 'No items found', 'thrive-cb' ); ?>
			</strong>
		</p></div>
</div>
<#} else {#>
<div data-level="<#=item.get('level')#>" class="preview-list-item preview-list-item-inline tcb-relative <#= item.get('visible') ? 'tve-item-visible' :''#>" style="margin-left:<#=(15*item.get('level'))#>px"
	 data-fn="item_click" data-index="<#=item.index#>" data-id="<#=item.get('id')#>">
	<div class="preview-list-sort-handle col-sep"><?php tcb_icon( 'dots2' ); ?></div>
	<input data-index="<#=item.index#>" class="item-label tcb-truncate preview-list-input ml-5 mr-10 keyup" value="<#=item.get('label')#>" readonly data-fn="keyupInline">
	<#if(item.get('editable')){#>
	<a href="javascript:void(0)" data-side="top" data-tooltip="<?php esc_attr_e( 'Edit', 'thrive-cb' ); ?>" class="action-edit click mr-5" data-fn-click="editInline"
	   data-index="<#=item.index#>" data-label="<#=item.get('label')#>" data-child-index="<#= item.child_index #>">
		<?php tcb_icon( 'pen-light' ); ?>
	</a>
	<a href="javascript:void(0)" data-side="top" data-tooltip="<?php esc_attr_e( 'Save', 'thrive-cb' ); ?>" class="action-save click tcb-hidden mr-5" data-fn-click="saveInline"
	   data-index="<#=item.index#>" data-label="<#=item.get('label')#>" data-child-index="<#= item.child_index #>">
		<?php tcb_icon( 'check' ); ?>
	</a>
	<#}#>
	<#if(item.get('canToggleVisible')){#>
	<a href="javascript:void(0)" data-side="right" data-tooltip="<?php esc_attr_e( 'Toggle Visibility', 'thrive-cb' ); ?>" class="action-remove mr-5 click <#= item.get('visible') ? 'tve-item-visible' :''#> <#= item.get('hiddenByParent') ? 'grey' :''#>" data-fn="toggleVisibility"
	   data-index="<#=item.index#>" data-child-index="<#= item.child_index #>">
		<#= TVE.icon( item.get('visible') ? 'eye-light' : 'eye-light-slash' ) #>
	</a>
	<#}#>
</div>
<#}#>
