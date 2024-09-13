<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div class="tve-modal-content">
	<div id="cb-cloud-menu" class="modal-sidebar">
		<div class="fixed top">
			<div class="icons-input">
				<?php tcb_icon( 'search-regular' ); ?>
				<input type="text" class="tve-search-icon keyup click" data-fn="searchIcon" data-fn-click="focusSearch" data-source="search" placeholder="<?php echo esc_html__( 'Search', 'thrive-cb' ); ?>"/>
				<?php tcb_icon( 'close2', false, 'sidebar', 'click tcb-hidden', [ 'data-fn' => 'domClearSearch' ] ); ?>
			</div>
		</div>
		<div class="icons-menu-wrapper">
			<div class="icons-label-wrapper p-10 pl-20">
				<span class="icons-label"><?php echo esc_html__( 'Icon style', 'thrive-cb' ); ?></span>
				<span class="separator"></span>
			</div>
			<div class="tve-icon-styles tve-icon-filters">
				<div class="tve-icon-pack click mt-5 tve-selected tve-icon-default-style" data-fn="filterByStyle"><span><?php echo esc_html__( 'All ', 'thrive-cb' ) ?></span></div>
			</div>
			<div class="icons-label-wrapper p-10 pl-20">
				<span class="icons-label"><?php echo esc_html__( 'Icon packs', 'thrive-cb' ); ?></span>
				<span class="separator"></span>
			</div>
			<div class="tve-icon-packs tve-icon-filters">

			</div>
		</div>
		<div class="fixed bottom mt-10 mb-10">
			<div class="tve-icon-settings click pr-10 mb-10 mt-10 pl-20" data-fn="showSettings">
				<hr>
				<div class="pt-10 pb-10 icons-wrapper">
					<?php tcb_icon( 'cog-light' ); ?>
					<span class="icons-label"><?php echo esc_html__( 'Manage icon packs', 'thrive-cb' ) ?></span>
					<?php tcb_icon( 'exclamation-circle-solid', false, 'sidebar', 'kit-warning', [ "data-tooltip" => "Something is wrong with your Font Awesome setup, click here to resolve", "data-side" => "top", "data-width" => "15%" ] ); ?>
				</div>
				<hr>
			</div>
		</div>
	</div>
	<div id="tve-icon-content" class="modal-content">
		<span class="tcb-modal-title"><?php echo esc_html__( 'Icon library', 'thrive-cb' ); ?></span>
		<div id="icon-pack-content">
			<div class="choose-icon">
				<span><?php echo esc_html__( 'Choose an icon', 'thrive-cb' ); ?></span>
				<span class="tve-icons-number"></span>
			</div>
			<div class="tve-icons-wrapper pt-5">
				<div class="tve-icons-before" style="height: 4000px;"></div>
				<div class="tve-icons-rendered"></div>
				<div class="tve-icons-after"></div>
			</div>
		</div>
		<div class="tcb-modal-footer flex-end pr-40">
			<button type="button" class="tve-button tcb-modal-save">
				<?php echo esc_html__( 'Select', 'thrive-cb' ); ?>
			</button>
		</div>
	</div>
	<div id="tve-icon-settings" class="tcb-hidden modal-content">
		<div class="tve-fa-pro-settings">
			<span class="tcb-modal-title"><?php echo esc_html__( 'Font Awesome Pack', 'thrive-cb' ); ?></span>
			<div class="tcb-modal-content ml-50">
				<span>
					<?php echo esc_html__( 'To enable Font Awesome Pro icons, paste your kit ID or script below. Once your kit has been accepted, your new icons will be available under the matching filters.', 'thrive-cb' ); ?>
				</span>
				<br>
				<a href="https://thrivethemes.com/tkb_item/how-to-add-font-awesome-pro-icons-in-thrive-architect/" target="_blank" class="mt-15 mb-35"><?php echo esc_html__( 'Learn how to use Font Awesome Pro here', 'thrive-cb' ); ?></a>
				<div class="icons-input white tve-fa-input pr-0">
					<input type="text" class="change input tve-fa-kit" data-fn="toggleProSettings" placeholder="<?php echo esc_html__( 'Add your Font Awesome Pro Kit', 'thrive-cb' ); ?>">
					<?php tcb_icon( 'check-regular', false, 'sidebar', 'click tcb-hidden kit-action', [ 'data-fn' => 'handlePro' ] ); ?>
					<?php tcb_icon( 'sync-regular', false, 'sidebar', 'click tcb-hidden kit-action', [ 'data-fn' => 'handlePro' ] ); ?>
					<?php tcb_icon( 'trash-alt-light', false, 'sidebar', 'click tcb-hidden kit-action', [ 'data-fn' => 'toggleDeletePro' ] ); ?>
				</div>
				<div class="icons-input tve-fa-warning tcb-hidden pr-0">
					<span><?php echo esc_html__( 'Are you sure you want to delete your kit?', 'thrive-cb' ); ?></span>
					<span class="mr-10 click kit-action" data-fn="clearFAPro"><?php echo esc_html__( 'YES', 'thrive-cb' ); ?></span>
					<span class="click kit-action" data-fn="toggleProInput"><?php echo esc_html__( 'CANCEL', 'thrive-cb' ); ?></span>
				</div>
				<div class="kit-warning kit-warning-message">
					<div class="warning-title">
						<?php tcb_icon( 'exclamation-circle-solid' ) ?>
						<?php echo esc_html__( 'There was an issue accessing your Font Awesome Pro Kit', 'thrive-cb' ); ?>
					</div>
					<div class="warning-description">
						<?php echo esc_html__( 'This usually occurs when your kit was deleted or you do not have an active subscription. Please check your Font Awesome account and update the above kit details as required.', 'thrive-cb' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
