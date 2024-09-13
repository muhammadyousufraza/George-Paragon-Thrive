<?php
define( 'WP_CACHE', true );


























/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'redcbltt_wp687' );

/** Database username */
define( 'DB_USER', 'redcbltt_wp687' );

/** Database password */
define( 'DB_PASSWORD', '7!w)LS2.p!0b)0eO' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'xss14qvtlek5yuuyey7lk2mddczbomowk9kbunbhbkbifnvxveypj2hkujxqgvsi' );
define( 'SECURE_AUTH_KEY',  'chbazbazpmidtjaothk2kw3dkmv1peqa0uucgwuz41zqmjpa2ubxnfzzhzzhmh0g' );
define( 'LOGGED_IN_KEY',    '6xwijqaah4zdytcpcannqasjvhscp9myruivj8cjhc33ty21thzkwcrrnpqoxdce' );
define( 'NONCE_KEY',        'db7wu03afnlc3wvh7yerdtjfuylvcxeuuufz9rcel4mjtndjqpa86y71qralfbil' );
define( 'AUTH_SALT',        'menq415b3djovp4opcsk2hirx4da33en3zuv94suehrecmrwlctgzeftzl33elzn' );
define( 'SECURE_AUTH_SALT', 'i3phn3m0al8scjs0naruv4osdx3ombva6cyddp8mrdgf6ibdgtrt4qwwigxtzp6s' );
define( 'LOGGED_IN_SALT',   'xkkvnmoutm5veqopj78b609sszzc6osvb48fpflxqfqz1ve4igycl6bmuqgoxo5z' );
define( 'NONCE_SALT',       'b8cpmickxjlgtf5cpllkip57whzxeoxpuaeheprsqnhqwzzgsit8kztxsluht51v' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpqb_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
