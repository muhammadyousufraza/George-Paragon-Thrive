<?php
/** @var $this TD_DB_Migration */
$this->create_index( 'IDX_orders_status', 'orders', 'status' );
$this->create_index( 'IDX_orders_user_id', 'orders', 'user_id' );
$this->create_index( 'IDX_order_items_order_id', 'order_items', 'order_id' );
