<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/18/2017
 * Time: 7:31 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$singular        = isset( $singular ) ? $singular : null;
$start_test_link = $this->_page->get_start_test_url();
/** @var Thrive_AB_Meta_Box_Variations_Table $this */
?>
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tbody id="top-the-list"<?php echo $singular ? " data-wp-lists='list:$singular'" : ''; ?>>
	<?php $this->display_rows_or_placeholder(); ?>
	</tbody>
</table>
<?php if ( $this->_pagination_args['total_items'] > 0 ) : ?>
	<div class="thrv-ab-action-button-wrapper">
		<?php if ( count( $this->_page_tests ) > 0 ) : ?>
			<?php $view_test_link = $this->_page->get_test_link( $this->_page_tests[0]['id'] ) ?>
			<p><span></span><?php echo __( 'Changes occurred while a test is running can sometimes invalidate the test results.', 'thrive-ab-page-testing' ); ?></p>
			<a class="thrv-ab-action-button" href="<?php echo $view_test_link; ?>"><?php echo __( 'View Test Details', 'thrive-ab-page-testing' ); ?></a>
		<?php else : ?>
			<a class="thrv-ab-action-button" href="<?php echo $start_test_link . '#dashboard/start-test'; ?>"><?php echo __( 'Set Up & Start A/B Test', 'thrive-ab-page-testing' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>
