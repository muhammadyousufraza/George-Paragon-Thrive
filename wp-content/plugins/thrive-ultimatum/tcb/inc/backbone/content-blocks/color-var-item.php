<div class="control-grid">
	<span class="label tcb-truncate"><#= model.get( 'local_variable_name' ) #></span>
	<div class="input">
		<span class="sp-transparent click" data-fn="edit_local_variable" data-var="<#= model.get( 'local_variable' ) #>" data-index="<#= index #>">
			<span class="tcb-icon-inline" style="background-size:auto;background-image:<#= bg #>"></span>
		</span>
		<input type="text" value="<#= model.get( 'local_variable_code' ) #>" readonly>
	</div>
</div>