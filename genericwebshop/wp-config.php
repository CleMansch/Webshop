<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'genericwebshop' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'o[;2O^M RNi^W:QUYn7g;WO|%6Pl)F)Us$H68UX%0H^VOq!5ferFB,g,H+$j~xw1' );
define( 'SECURE_AUTH_KEY',  '!uu/~9n3%|e1I*+) Gjj6:@}e 4DGz8<w8 k`3D,`l]ZHI>RyNHwR>xcC@z|9Z/8' );
define( 'LOGGED_IN_KEY',    'lzrKgWuG@>whXr uzz~@9XFdmGJr tX9k -.I]:UOFKF(n`iz, >j:bf=ttnfD_-' );
define( 'NONCE_KEY',        '/4U1xAphvGeKR6xn;.m$0~iU#*@BOyz4*^}$>$tTwK?~WOWc(]5iE~nC9,Y6,$=_' );
define( 'AUTH_SALT',        'E/yJYm<ci4Pt~Fi#8&i>pb(?,(NY;59~>*R[i5}g8]+ogo(RrYtQ28_mQ[{,^FMX' );
define( 'SECURE_AUTH_SALT', 'Nn;m6pIb4n/RC1ZM/BaNs`x7lXSbDQI`-,r&H^M*S0D4a8aF!vRelAi|%{+cp~7j' );
define( 'LOGGED_IN_SALT',   ']8[eO(IBY+,T,O4Sqw8HN2)R<`=I%=[r6{h^b8wi3Ve6XZrH%LL/=Yu_.glZfRVk' );
define( 'NONCE_SALT',       'J_typm/XD1?U&q[IGyt73}B%urG}.Qat,Vl]m s:F(wz%5&sJ68yP8`V}i[h^!l%' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
