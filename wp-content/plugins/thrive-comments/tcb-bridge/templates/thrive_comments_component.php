<?php
/**
 * Created by PhpStorm.
 * User: istvan
 * Date: 2/23/2018
 * Time: 3:59 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-thrive_comments-component" class="tve-component" data-view="thrive_comments">
	<div class="text-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Comment Options', Thrive_Comments_Constants::T ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control" data-view="ColorPicker"></div>
		</div>
	</div>
</div>
