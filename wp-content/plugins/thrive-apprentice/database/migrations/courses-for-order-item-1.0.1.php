<?php

/** @var $this TD_DB_Migration */

$this->add_or_modify_column( 'order_items', 'courses', 'VARCHAR(255) NULL DEFAULT NULL AFTER order_id' );
