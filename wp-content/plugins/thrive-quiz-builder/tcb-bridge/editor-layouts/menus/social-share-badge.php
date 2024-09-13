<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/30/2017
 * Time: 4:05 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-tqb_social_share_badge-component" class="tve-component" data-view="tqb_social_share_badge">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Social Share Badge Options', 'thrive-quiz-builder' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row padding-top-10 middle-xs">
			<div class="col-xs-12 tcb-text-center">
				<button class="blue tve-button click" data-fn="change_template">
					<?php echo esc_html__( 'Change Template', 'thrive-quiz-builder' ); ?>
				</button>
			</div>
		</div>
		<hr>
		<div class="tve-control" data-key="type" data-view="ButtonGroup"></div>
		<hr>
		<div class="tve-control" data-key="style" data-initializer="style_control"></div>
		<hr>
		<div class="tve-control" data-key="orientation" data-view="ButtonGroup"></div>
		<hr>
		<div class="tve-control" data-key="size" data-view="Slider"></div>
		<hr>
		<div class="row middle-xs between-xs">
			<div class="col-xs-8">
				<span class="input-label"><?php echo esc_html__( 'Social Networks', 'thrive-quiz-builder' ) ?></span>
			</div>
		</div>
		<div class="tve-control" data-key="selector" data-initializer="selector_control"></div>
		<div class="tve-control" data-key="preview" data-view="PreviewList"></div>
		<hr>
		<div class="tve-control" data-key="has_custom_url" data-view="Checkbox"></div>
		<div class="tve-control" data-key="custom_url" data-view="LabelInput"></div>
		<hr>
		<div class="row middle-xs between-xs">
			<div class="col-xs-9">
				<div class="tve-control" data-key="total_share" data-view="Checkbox"></div>
			</div>
			<div class="col-xs-3" style="flex-basis: 22%;max-width: 22%">
				<div class="tve-control" data-key="counts" data-view="Input"></div>
			</div>
		</div>
	</div>
</div>
