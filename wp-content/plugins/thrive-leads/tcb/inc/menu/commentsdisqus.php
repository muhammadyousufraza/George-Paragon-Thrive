<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/3/2017
 * Time: 2:04 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-commentsdisqus-component" class="tve-component" data-view="CommentsDisqus">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control fill no-space" data-view="ForumName"></div>
		<div class="info-text grey-text"><?php echo esc_html__( 'Your forum name is part of the login address to Disqus. E.g. "http://healtylife.disqus.com", the forum name is "healtylife".', 'thrive-cb' ); ?></div>
		<div class="tve-control fill mt-10 no-space" data-view="URL"></div>
		<div class="info-text grey-text"><?php echo esc_html__( 'The URL of the current place of content will be used. You can specify a different URL to store comments against if you prefer.', 'thrive-cb' ); ?></div>
	</div>
</div>
