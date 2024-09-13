<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div class="error-container"></div>
<div class="tve-modal-content">
	<div id="cb-cloud-menu" class="modal-sidebar">
		<div class="fixed top">
			<div class="lp-search">
				<?php tcb_icon( 'search-regular' ); ?>
				<input type="text" data-source="search" class="keydown" data-fn="filter" placeholder="<?php echo esc_html__( 'Search', 'thrive-theme' ); ?>"/>
				<?php tcb_icon( 'close2', false, 'sidebar', 'click', [ 'data-fn' => 'domClearSearch' ] ); ?>
			</div>
		</div>
		<div class="lp-menu-wrapper fixed">
			<div id="lp-blk-pack-categories"></div>
			<div class="lp-label-wrapper mt-30">
				<span><?php echo __( 'SOURCE', 'thrive-theme' ); ?></span>
				<span class="separator"></span>
			</div>
			<div id="lp-groups-wrapper"></div>
		</div>
	</div>
	<div id="cb-cloud-templates" class="tcb-modal-content">
		<div id="lp-blk-pack-title" class="mb-5"></div>
		<div id="lp-blk-pack-description"></div>
		<div id="cb-pack-content"></div>
	</div>
</div>
