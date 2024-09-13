<?php

/** @var $this TD_DB_Migration */

$this->add_or_modify_column( 'orders', 'number', 'VARCHAR(30) NULL DEFAULT NULL AFTER ID' );
