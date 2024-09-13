<div class="tvd-am-item-wrapper tvd-am-functionality-wrapper tvd-am-<#= model.getTag() #>">
	<div class="tvd-am-item-icon tvd-am-functionality-icon">
		<?php dashboard_icon( '<#= model.getIcon() #>' ); ?>
	</div>
	<div class="tvd-am-item-title tvd-am-functionality-title">
		<#= model.getName()#>
	</div>
	<div>
		<select class="tvd-am-functionality-select" data-functionality="<#= model.getTag() #>">
			<# optionsTags.forEach((option, index) => {#>
			<option value="<#= option #>"
			<#= option === selectedValue ? 'selected=selected' : ''#>><#=  optionsNames[index] #></option>
			<#})#>
		</select>
	</div>
</div>
