<?php

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/inc/data.php';
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/hooks.php';
require_once __DIR__ . '/inc/classes/class-tvo-privacy.php';
/**
 * REST Routes
 */
require_once __DIR__ . '/inc/classes/class-tvo-rest-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-settings-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-testimonials-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-tags-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-social-media-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-comments-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-shortcodes-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-post-meta-controller.php';
require_once __DIR__ . '/inc/classes/endpoints/class-tvo-rest-filters-controller.php';

/**
 * TCB Bridge
 */
require_once __DIR__ . '/tcb-bridge/hooks.php';
require_once __DIR__ . '/tcb-bridge/functions.php';

/**
 * at this point, we need to either hook into an existing Content Builder plugin, or use the copy we store in the tcb folder
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! file_exists( dirname( __DIR__ ) . '/thrive-visual-editor/thrive-visual-editor.php' ) || ! is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'tcb-bridge/init.php';
}

/**
 * Database
 */
require_once __DIR__ . '/inc/classes/class-tvo-db.php';

/**
 * Blocks
 */
require_once __DIR__ . '/blocks/tvo-block.php';

/**
 * Automator
 */
require_once __DIR__ . '/inc/classes/class-tvo-automator.php';
