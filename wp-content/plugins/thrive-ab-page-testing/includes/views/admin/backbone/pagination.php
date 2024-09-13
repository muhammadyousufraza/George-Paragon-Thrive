<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2018
 * Time: 5:36 PM
 */
?>
<div class="tvd-right">
	<span class="tvd-inline-block"><strong><?php echo __('Rows per page:','thrive-ab-page-testing' ); ?></strong></span>
	<div class="tvd-input-field tvd-input-field-small tvd-inline-block tab-pagination-select">
		<select class="tab-items-per-page">
			<option value="10" <# if(itemsPerPage == 10) { #> selected="selected"  <# } #>>10</option>
			<option value="15" <# if(itemsPerPage == 15) { #> selected="selected"  <# } #>>15</option>
			<option value="30" <# if(itemsPerPage == 30) { #> selected="selected"  <# } #>>30</option>
			<option value="50" <# if(itemsPerPage == 50) { #> selected="selected"  <# } #>>50</option>
		</select>
	</div>

	<div class="tvd-inline-block ">
		<strong>
			<#= (currentPage-1)*itemsPerPage #> - <#= currentPage * itemsPerPage > total_items ? total_items : currentPage * itemsPerPage #>
			<?php echo __('of','thrive-ab-page-testing' ) ?>   <#= total_items #>
		</strong>
	</div>

	<div id="pages" class="tvd-inline-block tab-pagination-icons">
		<# if( pageCount > 1) { #>
		<a <# if(currentPage > 1) { #> class="page" value="<#= currentPage - 1 #>" <# } #>> <span class="tab-icon-pagination tvd-icon-chevron-left"></span></a>
		<a <# if(currentPage < pageCount) { #> class="page" value="<#= (currentPage + 1) #>" <# } #>> <span class="tab-icon-pagination tvd-icon-chevron-left tab-next-pagination"></span></a>
		<# } #>
	</div>
</div>
