<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/28/2017
 * Time: 4:34 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-gmap-component" class="tve-component" data-view="Gmap">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="tve-control custom-fields-state" data-view="address" data-state="static"></div>
		<div class="tve-control" data-view="zoom"></div>
		<div class="tve-control" data-view="fullWidth"></div>
		<div class="tve-control" data-view="width"></div>
		<div class="tve-control" data-view="height"></div>
	</div>
</div>
