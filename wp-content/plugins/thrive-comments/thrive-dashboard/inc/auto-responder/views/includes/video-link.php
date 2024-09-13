<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$api_slug = strtolower( str_replace( array( ' ', '-' ), '', $this->get_key() ) ); ?>
<?php $video_urls = $this->get_api_video_urls(); ?>
<?php if ( array_key_exists( $api_slug, $video_urls ) && ! empty( $video_urls[ $api_slug ] ) ) : ?>
	<div class="ttw-video-urls-container tvd-m6 tvd-no-padding tvd-left ttw-<?php echo esc_attr( $api_slug ); ?>-video">
		<p class="ttw-video-urls-wrapper">
			<a class="ttw-video-urls tvd-open-video" data-source="<?php echo esc_attr( $video_urls[ $api_slug ] ) ?>"><?php echo esc_html__( 'I need help with this', 'thrive-dash' ); ?></a>
		</p>
	</div>
<?php endif ?>
