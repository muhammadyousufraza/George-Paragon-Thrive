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

<div class="video_overlay video_overlay_image" style="background-image: url( <?php echo $attr['thumbnail-url']; ?> ); background-repeat: no-repeat; background-size: cover;background-position: center center;">
	<?php if ( ! empty( $attr['thumbnail-play-icon'] ) ) :
		echo $attr['thumbnail-play-icon'];
		?>
	<?php else : ?>
		<span class="overlay_play_button">
		<svg class="tcb-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
			 viewBox="0 0 20 20"><title>play</title><path fill="#fff" d="M18.659 4.98c-0.889-1.519-2.12-2.75-3.593-3.614l-0.047-0.025q-2.298-1.341-5.020-1.341t-5.019 1.341c-1.52 0.889-2.751 2.12-3.614 3.593l-0.025 0.047q-1.341 2.298-1.341 5.020t1.341 5.020c0.889 1.519 2.12 2.75 3.593 3.614l0.047 0.025q2.298 1.341 5.020 1.341t5.020-1.341c1.519-0.889 2.75-2.12 3.614-3.593l0.025-0.047q1.341-2.298 1.341-5.020t-1.341-5.020zM15 10.716l-7.083 4.167c-0.118 0.074-0.262 0.117-0.416 0.117-0 0-0 0-0.001 0h0c-0.153-0.002-0.296-0.040-0.422-0.107l0.005 0.002q-0.417-0.247-0.417-0.729v-8.333q0-0.482 0.417-0.729 0.43-0.234 0.833 0.013l7.084 4.167q0.416 0.234 0.416 0.716t-0.416 0.716z"></path>
		</svg>
	</span>
	<?php endif; ?>
</div>
