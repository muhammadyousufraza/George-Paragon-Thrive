<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/18/2017
 * Time: 11:57 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-html-component" class="tve-component" data-view="Html">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="control-grid pb-5">
			<button class="blue fill tve-button click" data-fn="edit_html_content">
				<?php echo esc_html__( 'Edit HTML content', 'thrive-cb' ); ?>
			</button>
		</div>
	</div>
</div>
