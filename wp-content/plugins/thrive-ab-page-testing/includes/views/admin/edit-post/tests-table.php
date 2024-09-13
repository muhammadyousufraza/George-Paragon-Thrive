<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/21/2017
 * Time: 9:03 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$singular = isset( $singular ) ? $singular : null;
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
