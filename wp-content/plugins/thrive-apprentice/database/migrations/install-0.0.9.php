<?php
/** @var $this TD_DB_Migration */
$this->create_table( 'ipn_log', '
	`ID` BIGINT NOT NULL AUTO_INCREMENT,
	`order_id` BIGINT,
	`gateway_order_id` BIGINT ,
	`gateway` varchar(80),
	`status` TINYINT NOT NULL,
	`payment_status` varchar(80),
	`transaction_id` TEXT,
	`ipn_content` TEXT,
	`created_at` DATETIME DEFAULT "0000-00-00 00:00:00" NOT NULL,
	PRIMARY KEY (`ID`)
', true );

$this->create_table( 'order_items', '
	`ID` BIGINT NOT NULL AUTO_INCREMENT,
	`order_id` BIGINT,
	`gateway_order_id` BIGINT,
	`gateway_order_item_id` BIGINT ,
	`product_id` BIGINT,
	`product_type` VARCHAR(64),
	`product_name` TEXT NOT NULL,
	`product_price` VARCHAR(20) NOT NULL,
	`currency` CHAR(3),
	`quantity` SMALLINT DEFAULT 1,
	`unit_price` VARCHAR(20) DEFAULT 0,
	`total_price` VARCHAR(20) DEFAULT 0,
	`valid_until` datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
	`created_at` datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
	PRIMARY KEY (`ID`)
', true );

$this->create_table( 'orders', '
	`ID` BIGINT NOT NULL AUTO_INCREMENT,
	`user_id` BIGINT(9) NOT NULL,
	`status` TINYINT NOT NULL,
	`payment_id` VARCHAR(30),
	`gateway_order_id` BIGINT,
	`gateway` VARCHAR(80),
	`payment_method` varchar(80),
	`gateway_fee` VARCHAR(20) DEFAULT 0,
	`buyer_email` TEXT,
	`buyer_name` TEXT,
	`buyer_address1` TEXT,
	`buyer_address2` TEXT,
	`buyer_city` TEXT,
	`buyer_region` TEXT,
	`buyer_postcode` TEXT,
	`buyer_country` CHAR(2),
	`billing_address1` TEXT,
	`billing_address2` TEXT,
	`billing_city` TEXT,
	`billing_region` TEXT,
	`billing_postcode` TEXT,
	`billing_country` CHAR(2),
	`shipping_address1` TEXT,
	`shipping_address2` TEXT,
	`shipping_city` TEXT,
	`shipping_region` TEXT,
	`shipping_postcode` TEXT,
	`shipping_country` CHAR(2),
	`buyer_ip_address` VARCHAR(64),
	`is_gift` TINYINT DEFAULT 0,
	`price` VARCHAR(20) DEFAULT 0,
	`price_gross` VARCHAR(20) DEFAULT 0,
	`currency` CHAR(3),
	`created_at` datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
	`updated_at` datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
	PRIMARY KEY (`ID`)
', true );

$this->create_table( 'transactions', '
	`ID` BIGINT NOT NULL AUTO_INCREMENT,
	`order_id` BIGINT,
	`transaction_id` TEXT,
	`currency` CHAR(3),
	`price` VARCHAR(20) DEFAULT 0,
	`price_gross` VARCHAR(20) DEFAULT 0,
	`gateway_fee` VARCHAR(20) DEFAULT 0,
	`transaction_type` TINYINT NOT NULL,
	`gateway` varchar(80),
	`card_last_4_digits` CHAR(4),
	`card_expires_at` DATE DEFAULT "0000-00-00" NOT NULL,
	`created_at` datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
	PRIMARY KEY (`ID`)
', true );
