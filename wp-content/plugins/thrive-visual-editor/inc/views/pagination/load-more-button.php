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

<div class="<?php echo esc_attr( TCB_Pagination_Load_More::IDENTIFIER . ' ' . THRIVE_WRAPPER_CLASS ); ?> tve_no_icons">
	<a href="javascript:void(0)" class="tcb-button-link tcb-pagination-load-more-link">
	<span class="tcb-button-texts">
		<span class="tcb-button-text thrv-inline-text">
			<?php echo esc_html__( 'Load More', 'thrive-cb' ); ?>
		</span>
	</span>
	</a>
</div>
