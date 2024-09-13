<?php
$this->add_or_modify_column( 'order_items', 'status', 'INT NOT NULL DEFAULT 1 AFTER order_id' );
