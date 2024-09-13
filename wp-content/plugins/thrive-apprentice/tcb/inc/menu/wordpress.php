<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/10/2017
 * Time: 1:09 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-wordpress-component" class="tve-component" data-view="Wordpress">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="control-grid">
			<button class="blue tve-button click fill" data-fn="edit_wordpress_content">
				<?php echo esc_html__( 'EDIT WORDPRESS CONTENT', 'thrive-cb' ); ?>
			</button>
		</div>
	</div>
</div>
