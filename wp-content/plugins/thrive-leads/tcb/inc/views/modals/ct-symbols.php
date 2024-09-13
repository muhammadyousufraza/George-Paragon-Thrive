<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div class="error-container"></div>
<div class="tve-modal-content">
	<div id="cb-cloud-menu" class="modal-sidebar">
		<div class="lp-search">
			<?php tcb_icon( 'search-regular' ); ?>
			<input type="text" data-source="search" class="keydown" data-fn="filter" placeholder="<?php echo esc_html__( 'Search', 'thrive-cb' ); ?>"/>
			<?php tcb_icon( 'close2', false, 'sidebar', 'click', [ 'data-fn' => 'domClearSearch' ] ); ?>
		</div>
		<div class="lp-menu-wrapper">
			<div class="sidebar-title mt-30">
				<p><?php echo esc_html__( 'Type', 'thrive-cb' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="lp-groups-wrapper"></div>
		</div>
		<div class="fixed bottom"></div>
	</div>

	<div id="cb-cloud-templates" class="modal-content">
		<span id="lp-blk-pack-title" class="tcb-modal-title"><?php echo __( 'Templates', 'thrive-cb' ); ?></span>
		<div id="cb-pack-content">
			<div class="tve-symbols-wrapper">
				<div class="text-no-symbols">
					<?php echo esc_html__( "Oups! We couldn't find anything called " ) ?><span class="search-word"></span><?php echo esc_html__( '. Maybe search for something else ?' ); ?>
				</div>
			</div>

			<div class="tve-content-templates-wrapper">
				<div class="text-no-templates" style="display: none;">
					<?php echo esc_html__( "Oups! We couldn't find anything called " ) ?><span class="search-word"></span><?php echo esc_html__( '. Maybe search for something else ?' ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
