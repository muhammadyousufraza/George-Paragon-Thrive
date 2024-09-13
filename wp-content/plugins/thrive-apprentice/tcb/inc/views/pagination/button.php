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

<a href="javascript:void(0)" class="tcb-button-link tcb-pagination-button-link">
	<?php echo $data['icon']; ?>
	<span class="tcb-button-texts">
		<span class="tcb-button-text thrv-inline-text">
			<?php echo esc_html__( $data['name'], 'thrive-cb' ); ?>
		</span>
	</span>
</a>
