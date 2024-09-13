<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/4/2017
 * Time: 12:21 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-commentsfacebook-component" class="tve-component" data-view="CommentsFacebook">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control fill" data-view="URL"></div>
		<div class="tve-control" data-view="color_scheme"></div>
		<div class="tve-control" data-view="order_by"></div>
		<div class="tve-control" data-view="nr_of_comments"></div>
		<div class="tve-advanced-controls">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo esc_html__( 'Comments Moderators', 'thrive-cb' ); ?>
				</span>
			</div>
			<div class="dropdown-content pt-0">
				<div class="tve-control" data-view="moderators"></div>
				<div class="info-text grey-text"><?php echo esc_html__( 'This only works if you are friends on Facebook with the moderator(s)', 'thrive-cb' ); ?></div>
			</div>
		</div>

	</div>
</div>
