<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="error-container"></div>
<div style="position: absolute;top: 0;" id="lp-lightbox-drop-panels"></div>
<div class="tcb-modal-step" data-step="1">
	<div class="lp-cloud-menu modal-sidebar">
		<div class="lp-search">
			<?php tcb_icon( 'search-regular' ); ?>
			<input type="text" data-source="search" class="keydown" data-fn="filter" placeholder="<?php echo esc_html__( 'Search', 'thrive-cb' ); ?>"/>
			<?php tcb_icon( 'close2', false, 'sidebar', 'click', [ 'data-fn' => 'domClearSearch' ] ); ?>
		</div>
		<div class="lp-menu-wrapper">
			<div class="mt-30">
				<div class="sidebar-title">
					<p><?php echo esc_html__( 'Default Templates', 'thrive-cb' ); ?></p>
					<span class="tcb-hl"></span>
				</div>
				<div id="lp-default-filters"></div>
			</div>
			<div class="mt-30">
				<div class="sidebar-title">
					<p><?php echo esc_html__( 'My Templates', 'thrive-cb' ); ?></p>
					<span class="tcb-hl"></span>
				</div>
				<div id="lp-saved-filters"></div>
			</div>
		</div>
	</div>
	<div class="lp-cloud-templates modal-content">
		<div class="lp-template-title-text ml-10 space-between">
			<span class="tcb-modal-title">
				<?php if ( defined( 'TVE_STAGING_TEMPLATES' ) && TVE_STAGING_TEMPLATES ) : ?>
					<span style="color: #810000"><?php echo esc_html__( 'Warning! The templates listed here are only used for testing purposes', 'thrive-cb' ); ?></span>
				<?php else : ?>
					<?php echo esc_html__( 'Landing Page Library', 'thrive-cb' ); ?>
				<?php endif; ?>
			</span>
				<span data-fn="clearCache" class="tcb-refresh mr-30 click flex-center">
					<span class="mr-10"><?php tcb_icon( 'sync-regular' ); ?></span>
					<span class="mr-10"><?php echo esc_html__( 'Refresh from cloud', 'thrive-cb' ); ?></span>
				</span>
		</div>
		<div class="lp-set-list-group mt-20 lp-smart">
			<p>
				<?php echo esc_html__( 'Theme Landing Page sets', 'thrive-cb' ); ?>
			</p>
			<span class="tve-separator"></span>
		</div>
		<div id="lp-ttb-set-list" class="pl-30 pt-10 lp-smart"></div>
		<div class="lp-set-list-group mt-20 lp-smart">
			<p>
				<?php echo esc_html__( 'Smart Landing page sets', 'thrive-cb' ); ?>
			</p>
			<span class="tve-separator"></span>
		</div>
		<div id="lp-set-list" class="pl-30 pt-10"></div>
	</div>
</div>
<div class="tcb-modal-step" data-step="2" style="display: none;">
	<div class="lp-cloud-menu modal-sidebar">
		<div class="lp-menu-wrapper">
			<div class="sidebar-title mt-10">
				<p class="set-name"></p>
				<span class="tcb-hl"></span>
			</div>
			<div class="set-filters"></div>
		</div>
	</div>
	<div class="lp-cloud-templates modal-content">
		<div class="lp-modal-back ml-40 click" data-fn="domGoToSetsView">
			<span>
				<?php tcb_icon( 'chevron-circle-left-light', false, 'sidebar' ); ?>
			</span>
			<span>
				<?php echo esc_html__( 'BACK to library', 'thrive-cb' ); ?>
			</span>
		</div>
		<div class="lp-template-title-text ml-10">
			<span class="set-name tcb-modal-title"></span>
			<i></i>
			<span class="set-full lp-locked"><?php echo esc_html__( 'Upgrade', 'thrive-cb' ); ?></span>
			<?php
			/**
			 * Allows other plugins / themes to insert icons here
			 */
			do_action( 'tcb_extra_landing_page_lightbox_icons' );
			?>
			<span><?php tcb_icon( 'cubes-light', false, 'sidebar', 'set-blocks' ); ?></span>
		</div>
		<div id="lp-set-tpl-list" class="pl-30 pt-10"></div>
	</div>
	<div class="lp-footer">
		<button class="tve-btn tve-button click green click" data-fn="save"><?php echo esc_html__( 'Apply Template', 'thrive-cb' ); ?></button>
	</div>
</div>
