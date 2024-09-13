<?php

/**
 * @var $this TD_DB_Migration
 */
$this->add_or_modify_column( 'orders', 'type', 'VARCHAR(255) NULL DEFAULT NULL AFTER gateway' );
