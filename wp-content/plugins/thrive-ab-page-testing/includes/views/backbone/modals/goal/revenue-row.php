<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div class="tvd-row">
	<div class="tvd-col tvd-s6">
		<span> <#= model.get( 'post_title' ) #> </span>
	</div>
	<div class="tvd-col tvd-s1">
		<span>$&nbsp;<#= model.get( 'revenue' ) #></span>

	</div>
	<div class="tvd-col tvd-s5">
		<a href="<#= model.get( 'permalink' ) #>" target="_blank" class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green top-conv-page">
			<?php echo __( 'View Page', 'thrive-ab-page-testing' ); ?>
		</a>
	</div>
</div>
