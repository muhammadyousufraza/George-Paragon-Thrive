<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-smash-balloon-options-component" class="tve-component" data-view="SmashBalloonOptions">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="tve-control tve-smash-type" data-view="SmashType"></div>
			<div class="tve-control tve-smash-feed" data-view="SmashFeed"></div>
			<div class="control-grid">
				<button class="tve-manage-feed tve-button lightgreen ghost control-grid click" data-fn="clickManageFeed">
					<span class="mr-5">
						<?php tcb_icon( 'settings' ); ?>
					</span>
					<span>
						<?php echo __( 'Manage Feed', 'thrive-cb' ) ?>
					</span>
				</button>
			</div>
		</div>
	</div>
</div>
