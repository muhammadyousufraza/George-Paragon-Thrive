<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/12/2017
 * Time: 4:46 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-reveal-component" class="tve-component" data-view="Reveal">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="Time"></div>
		<hr>
		<div class="tve-control" data-view="AutoScroll"></div>
		<hr>
		<div class="tve-control full-width" data-view="RedirectURL"></div>
	</div>
</div>
