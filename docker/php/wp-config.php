<?php
/**
 * WP config bootstrap for containers.
 *
 * @package CETECH
 */

declare(strict_types=1);

/**
 * Retrieve an environment variable or throw when required.
 *
 * @param string      $name     Environment variable name.
 * @param string|null $fallback Optional fallback value.
 * @return string
 * @throws RuntimeException When the variable is missing and no fallback provided.
 */
function cetech_env( string $name, ?string $fallback = null ): string {
	$value = getenv( $name );

	if ( false === $value || '' === $value ) {
		if ( null !== $fallback ) {
			return $fallback;
		}

		throw new RuntimeException( 'Required environment variable is missing.' );
	}

	return $value;
}

/**
 * Read a boolean environment value.
 *
 * @param string $name     Environment variable name.
 * @param bool   $fallback Default fallback value.
 * @return bool
 */
function cetech_env_bool( string $name, bool $fallback = false ): bool {
	$value = getenv( $name );

	if ( false === $value || '' === $value ) {
		return $fallback;
	}

	return filter_var( $value, FILTER_VALIDATE_BOOL );
}

define( 'DB_NAME', cetech_env( 'WORDPRESS_DB_NAME' ) );
define( 'DB_USER', cetech_env( 'WORDPRESS_DB_USER' ) );
define( 'DB_PASSWORD', cetech_env( 'WORDPRESS_DB_PASSWORD' ) );
define( 'DB_HOST', cetech_env( 'WORDPRESS_DB_HOST', 'mariadb:3306' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

${'table_prefix'} = cetech_env( 'WORDPRESS_TABLE_PREFIX', 'wp_' );

define( 'AUTH_KEY', cetech_env( 'WORDPRESS_AUTH_KEY' ) );
define( 'SECURE_AUTH_KEY', cetech_env( 'WORDPRESS_SECURE_AUTH_KEY' ) );
define( 'LOGGED_IN_KEY', cetech_env( 'WORDPRESS_LOGGED_IN_KEY' ) );
define( 'NONCE_KEY', cetech_env( 'WORDPRESS_NONCE_KEY' ) );
define( 'AUTH_SALT', cetech_env( 'WORDPRESS_AUTH_SALT' ) );
define( 'SECURE_AUTH_SALT', cetech_env( 'WORDPRESS_SECURE_AUTH_SALT' ) );
define( 'LOGGED_IN_SALT', cetech_env( 'WORDPRESS_LOGGED_IN_SALT' ) );
define( 'NONCE_SALT', cetech_env( 'WORDPRESS_NONCE_SALT' ) );

define( 'WP_HOME', cetech_env( 'WORDPRESS_HOME' ) );
define( 'WP_SITEURL', cetech_env( 'WORDPRESS_SITEURL' ) );

define( 'COOKIEPATH', cetech_env( 'WORDPRESS_COOKIE_PATH', '/' ) );
define( 'SITECOOKIEPATH', cetech_env( 'WORDPRESS_SITE_COOKIE_PATH', '/' ) );
define( 'COOKIE_DOMAIN', false );

$environment_type = cetech_env(
	'WORDPRESS_ENVIRONMENT_TYPE',
	'production'
);

define( 'WP_ENVIRONMENT_TYPE', $environment_type );

define( 'WP_DEBUG', cetech_env_bool( 'WORDPRESS_DEBUG', false ) );
define(
	'WP_DEBUG_DISPLAY',
	cetech_env_bool( 'WORDPRESS_DEBUG_DISPLAY', false )
);
define( 'WP_DEBUG_LOG', cetech_env_bool( 'WORDPRESS_DEBUG_LOG', true ) );
define(
	'SCRIPT_DEBUG',
	cetech_env_bool( 'WORDPRESS_SCRIPT_DEBUG', false )
);

define( 'DISABLE_WP_CRON', true );
define( 'WP_CRON_LOCK_TIMEOUT', 60 );
define( 'DISALLOW_FILE_EDIT', true );

$managed_environment = in_array(
	$environment_type,
	array( 'staging', 'production' ),
	true
);

define(
	'DISALLOW_FILE_MODS',
	cetech_env_bool(
		'WORDPRESS_DISALLOW_FILE_MODS',
		$managed_environment
	)
);

define(
	'AUTOMATIC_UPDATER_DISABLED',
	cetech_env_bool(
		'WORDPRESS_DISABLE_AUTO_UPDATES',
		$managed_environment
	)
);

define( 'WP_MEMORY_LIMIT', cetech_env( 'WORDPRESS_MEMORY_LIMIT', '256M' ) );
define( 'WP_MAX_MEMORY_LIMIT', cetech_env( 'WORDPRESS_MAX_MEMORY_LIMIT', '512M' ) );

define(
	'WP_AUTO_UPDATE_CORE',
	cetech_env_bool( 'WORDPRESS_AUTO_UPDATE_CORE', false )
);

define(
	'CORE_UPGRADE_SKIP_NEW_BUNDLED',
	true
);

define(
	'IMAGE_EDIT_OVERWRITE',
	false
);

define(
	'MEDIA_TRASH',
	true
);

define(
	'WP_HTTP_BLOCK_EXTERNAL',
	cetech_env_bool( 'WORDPRESS_BLOCK_EXTERNAL_HTTP', false )
);

define( 'FS_METHOD', cetech_env( 'WORDPRESS_FS_METHOD', 'direct' ) );
define( 'FORCE_SSL_ADMIN', true );

define( 'WP_POST_REVISIONS', 20 );
define( 'AUTOSAVE_INTERVAL', 120 );
define( 'EMPTY_TRASH_DAYS', 14 );

define( 'WP_CACHE', true );
define( 'WP_REDIS_CLIENT', 'phpredis' );
define( 'WP_REDIS_HOST', cetech_env( 'WORDPRESS_VALKEY_HOST' ) );
define(
	'WP_REDIS_PORT',
	(int) cetech_env( 'WORDPRESS_VALKEY_PORT', '6379' )
);

$valkey_username = cetech_env( 'WORDPRESS_VALKEY_USERNAME', '' );
$valkey_password = cetech_env( 'WORDPRESS_VALKEY_PASSWORD' );

if ( '' !== $valkey_username ) {
	define(
		'WP_REDIS_PASSWORD',
		array(
			$valkey_username,
			$valkey_password,
		)
	);
} else {
	define( 'WP_REDIS_PASSWORD', $valkey_password );
}

define( 'WP_REDIS_SCHEME', cetech_env( 'WORDPRESS_VALKEY_SCHEME', 'tcp' ) );
define( 'WP_REDIS_DATABASE', (int) cetech_env( 'WORDPRESS_VALKEY_DATABASE', '0' ) );
define( 'WP_REDIS_DISABLE_DROPIN_AUTOUPDATE', true );
define( 'WP_REDIS_DISABLE_BANNERS', true );
define( 'WP_REDIS_DISABLE_COMMENT', true );
define(
	'WP_REDIS_PREFIX',
	cetech_env( 'WORDPRESS_VALKEY_PREFIX' )
);
define(
	'WP_CACHE_KEY_SALT',
	cetech_env( 'WORDPRESS_CACHE_KEY_SALT' )
);
define( 'WP_REDIS_TIMEOUT', 1.0 );
define( 'WP_REDIS_READ_TIMEOUT', 1.0 );
define( 'WP_REDIS_RETRY_INTERVAL', 100 );
define( 'WP_REDIS_MAXTTL', 86400 * 7 );
define( 'WP_REDIS_DISABLED', false );

if ( cetech_env_bool( 'WORDPRESS_BLOCK_EXTERNAL_HTTP', false ) ) {
	define(
		'WP_ACCESSIBLE_HOSTS',
		cetech_env(
			'WORDPRESS_ACCESSIBLE_HOSTS',
			'api.wordpress.org,downloads.wordpress.org'
		)
	);
}

define( 'WP_HTTP_TIMEOUT', 15 );

/* Handle common forwarded headers safely. Use filter_input() to read raw values. */
$proto = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_PROTO', FILTER_UNSAFE_RAW );
if ( null !== $proto ) {
	if ( function_exists( 'wp_unslash' ) ) {
		$proto = wp_unslash( $proto );
	} else {
		$proto = stripslashes( (string) $proto );
	}

	if ( str_contains( strtolower( (string) $proto ), 'https' ) ) {
		$_SERVER['HTTPS']       = 'on';
		$_SERVER['SERVER_PORT'] = '443';
	}
}

$forwarded_hosts = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_HOST', FILTER_UNSAFE_RAW );
if ( null !== $forwarded_hosts ) {
	if ( function_exists( 'wp_unslash' ) ) {
		$forwarded_hosts = wp_unslash( $forwarded_hosts );
	} else {
		$forwarded_hosts = stripslashes( (string) $forwarded_hosts );
	}

	$forwarded_hosts = explode( ',', (string) $forwarded_hosts );

	$_SERVER['HTTP_HOST'] = trim( $forwarded_hosts[0] );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
