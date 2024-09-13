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
<div class="tcb-modal-tabs">
	<span class="tcb-new-tab click" data-fn="openIntercomArticle">
		<?php tcb_icon( 'external-link-regular' ); ?>
		<?php echo esc_html__( 'Open in new tab', 'thrive-cb' ) ?>
	</span>
</div>
<div class="tve-modal-content">
	<div class="tcb-video-instructions">
		<iframe></iframe>
	</div>
	<div class="tcb-article-instructions">
		<h1 class="tcb-article-title mt-0"></h1>
		<div class="tcb-article-content"></div>
	</div>
</div>
