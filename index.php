<?php

/*
 * Plugin Name: KGR Login with Google
 * Plugin URI: https://github.com/constracti/kgr-login-with-google
 * Description: Login or register to WP usign Sign In with Google.
 * Version: 1.2
 * Requires at least: 3.1.0
 * Requires PHP: 7.0
 * Author: constracti
 * Author URI: https://github.com/constracti
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: kgr-login-with-google
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) )
	exit;

// define plugin constants
define( 'KGR_LOGIN_WITH_GOOGLE_DIR', plugin_dir_path( __FILE__ ) );

// require php files
$files = glob( KGR_LOGIN_WITH_GOOGLE_DIR . '*.php' );
foreach ( $files as $file ) {
        if ( $file !== __FILE__ )
                require_once( $file );
}

// load plugin translations
add_action( 'init', function(): void {
        load_plugin_textdomain( 'kgr-login-with-google', FALSE, basename( __DIR__ ) . '/languages' );
} );

// add settings link
add_filter( 'plugin_action_links', function( array $actions, string $plugin_file ): array {
	if ( $plugin_file !== basename( __DIR__ ) . '/' . basename( __FILE__ ) )
		return $actions;
	$actions['settings'] = sprintf( '<a href="%s">%s</a>',
		menu_page_url( 'kgr-login-with-google', FALSE ),
		esc_html__( 'Settings', 'kgr-login-with-google' )
	);
	return $actions;
}, 10, 2 );
