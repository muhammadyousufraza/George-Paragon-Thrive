<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/18/2018
 * Time: 5:26 PM
 */

tve_enqueue_style( 'tve_architect_edit_links', tve_editor_css() . '/thrive-architect-edit-links.css' );

global $post;
$view_test_link = '';
try {
	$page    = new Thrive_AB_Page( (int) $post->ID );
	$test_id = $page->get_meta()->get( 'running_test_id' );

	$start_test_link = $page->get_start_test_url();
	$view_test_link  = $start_test_link . '&test-id=' . $test_id . '#test';
} catch ( Exception $e ) {
}

?>

<!--<a class="thrive-architect-edit-link tcb-enable-editor thrive-architect-edit-disabled">-->
<!--	<div class="thrive-architect-admin-icon-holder">-->
<!--		<div class="thrive-architect-admin-icon"></div>-->
<!--	</div>-->
<!--	<div class="thrive-architect-admin-text">-->
<!--		--><?php //echo __( 'Edit with Thrive Architect', 'thrive-ab-page-testing' ) ?>
<!--	</div>-->
<!--</a>-->
<!-- TOP-143 <p style="display: inline;">--><?php //echo sprintf( __( 'Page editing is disabled while the A/B test is running. Click <a href="%s">here</a> to manage/end the test and enable editing again.', 'thrive-ab-page-testing' ), $view_test_link ); ?><!--</p>-->
