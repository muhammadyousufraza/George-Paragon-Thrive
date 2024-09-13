<?php

defined( 'TVE_LEADS_DB_UPGRADE' ) or exit();

/* find every csv and xls file from wp-content/uploads/thrive-contacts and remove it */
$upload_dir = wp_upload_dir();

if ( empty( $upload_dir['error'] ) ) {
	$save_path = $upload_dir['basedir'] . '/thrive-contacts';

	if ( is_dir( $save_path ) ) {
		$files = glob( $save_path . '/contacts-export-20*.{csv,xls}', GLOB_BRACE );
		foreach ( $files as $file ) {
			@unlink( $file );
		}
	} else {
		$save_path = $upload_dir['basedir'];
		/* search in uploads folder */
		$files = glob( $save_path . '/contacts-export-20*.{csv,xls}', GLOB_BRACE );
		//date( 'Y-m-d_H-i-s' );
		foreach ($files as $file) {
			if (preg_match('#/contacts-export-(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})-(\d{2})\.(csv|xls)#', $file)) {
				@unlink($file);
			}
		}
	}
}
