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
<div class="tvd-row tvd-collapse">
	<div class="tvd-col tvd-s2">
		<span class="thrive-ab-variation-name">
			<# if( test.get('type')=='monetary' ) { #>
				<?php echo __( 'Thank You Page', 'thrive-ab-page-testing' ) ?>
			<# } else { #>
				<?php echo __( 'Goal Page', 'thrive-ab-page-testing' ) ?>
			<# }  #>

		</span>
	</div>

	<div class="tvd-col tvd-s5 page-search"></div>

	<div class="tvd-col tvd-s3"	<#= test.get('type')=='monetary'? '':'style="display:none;"' #>>
		<label for="revenue-<#= item.cid #>" class="thrive-ab-value-label">
			<?php echo __( 'Value', 'thrive-ab-page-testing' ) ?>
		</label>
	</div>

	<div class="tvd-col tvd-s2" <#= test.get('type')=='monetary'? '':'style="display:none;"' #>>
		<div class="tvd-input-field">
			<input id="revenue-<#= item.cid #>" type="number" value="<#= item.get('revenue')? item.get('revenue'):0 #>" data-bind="revenue">
			<label><?php echo __( '$', 'thrive-ab-page-testing' ) ?></label>
		</div>
	</div>
</div>
