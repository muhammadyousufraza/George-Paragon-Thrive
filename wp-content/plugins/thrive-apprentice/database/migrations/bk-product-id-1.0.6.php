<?php
/**
 * @var $this TD_DB_Migration
 */
$this->add_or_modify_column( 'order_items', 'bk_product_id', 'VARCHAR(255) NULL DEFAULT NULL AFTER product_id' );
$this->add_query( "UPDATE {$this->get_table_name('order_items')} SET bk_product_id = product_id;" );
