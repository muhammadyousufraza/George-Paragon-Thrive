<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-login_form-component" class="tve-component hide-tablet hide-mobile" data-view="LoginForm">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="RememberMe"></div>
		<div class="tve-control" data-view="AddRemoveLabels"></div>
		<hr>
		<div class="tve-control" data-key="FieldsControl" data-initializer="getFieldsControl"></div>
	</div>
</div>
