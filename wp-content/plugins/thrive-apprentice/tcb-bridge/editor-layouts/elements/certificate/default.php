<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="thrv_wrapper tva-certificate-verification-element" data-message="No certificate found">
	<?php include __DIR__ . '/states/valid.php'; ?>
	<?php include __DIR__ . '/states/form.php'; ?>
</div>
