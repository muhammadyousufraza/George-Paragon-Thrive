<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/20/2017
 * Time: 9:45 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-responsivevideo-component" class="tve-component" data-view="ResponsiveVideo">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="tve-control" data-key="responsive_video" data-initializer="responsive_video"></div>
		<div class="tve-control" data-key="style" data-initializer="responsive_video_style"></div>
		<div class="tve-control" data-key="resize" data-initializer="responsiveVideoResize"></div>
	</div>
</div>
