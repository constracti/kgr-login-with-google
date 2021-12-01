<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_action( 'admin_menu', function(): void {
	$page_title = esc_html__( 'KGR Login with Google', 'kgr-login-with-google' );
	$menu_title = esc_html__( 'KGR Login with Google', 'kgr-login-with-google' );
	$capability = 'manage_options';
	$menu_slug = 'kgr-login-with-google';
	add_options_page( $page_title, $menu_title, $capability, $menu_slug, function(): void {
		if ( !current_user_can( 'manage_options' ) )
			return;
		echo '<div class="wrap">' . "\n";
		echo sprintf( '<h1>%s</h1>', esc_html__( 'KGR Login with Google', 'kgr-login-with-google' ) ) . "\n";
		echo '<form method="post" action="options.php">' . "\n";
		$page = 'kgr-login-with-google';
		settings_fields( $page );
		do_settings_sections( $page );
		submit_button( __( 'Save', 'kgr-login-with-google' ) );
		echo '</form>' . "\n";
		echo sprintf( '<p><a href="%s" class="button">%s</a></p>',
			add_query_arg( [
				'action' => 'kgr-login-with-google-clear',
				'nonce' => wp_create_nonce( 'kgr-login-with-google-clear' ),
			], admin_url( 'admin.php' ) ),
			esc_html__( 'Clear', 'kgr-login-with-google' )
		) . "\n";
		echo '</div>' . "\n";
	} );
} );

add_action( 'admin_init', function(): void {
	if ( !current_user_can( 'manage_options' ) )
		return;
	$page = 'kgr-login-with-google';
	// SECTION
	$section = 'kgr-login-with-google-remember';
	$title = '';
	add_settings_section( $section, $title, '__return_null', $page );
	// remember
	$id = 'kgr-login-with-google-remember';
	register_setting( $page, $id, [
		'type' => 'boolean',
		'sanitize_callback' => 'boolval',
		'default' => FALSE,
	] );
	$title = esc_html__( 'Remember', 'kgr-login-with-google' );
	add_settings_field( $id, $title, function( array $args ): void {
		echo sprintf( '<input type="checkbox" id="%s" name="%s" value="1"%s />',
			esc_attr( $args['name'] ),
			esc_attr( $args['label_for'] ),
			checked( get_option( $args['name'] ), TRUE, FALSE )
		) . "\n";
	}, $page, $section, [
		'name' => $id,
		'label_for' => $id,
	] );
	// SECTION
	$section = 'kgr-login-with-google-credentials';
	$title = esc_html__( 'Credentials', 'kgr-login-with-google' );
	add_settings_section( $section, $title, '__return_null', $page );
	$callback = function( array $args ): void {
		echo sprintf( '<input type="text" class="regular-text" id="%s" name="%s" value="%s" />',
			esc_attr( $args['name'] ),
			esc_attr( $args['label_for'] ),
			esc_attr( get_option( $args['name'] ) )
		) . "\n";
	};
	// client_id
	$id = 'kgr-login-with-google-client-id';
	register_setting( $page, $id, [
		'type' => 'string',
		'sanitize_callback' => 'trim',
		'default' => '',
	] );
	$title = esc_html__( 'Client ID', 'kgr-login-with-google' );
	add_settings_field( $id, $title, $callback, $page, $section, [
		'name' => $id,
		'label_for' => $id,
	] );
	// client_secret
	$id = 'kgr-login-with-google-client-secret';
	register_setting( $page, $id, [
		'type' => 'string',
		'sanitize_callback' => 'trim',
		'default' => '',
	] );
	$title = esc_html__( 'Client Secret', 'kgr-login-with-google' );
	add_settings_field( $id, $title, $callback, $page, $section, [
		'name' => $id,
		'label_for' => $id,
	] );
} );

add_action( 'admin_action_kgr-login-with-google-clear', function(): void {
	if ( !array_key_exists( 'nonce', $_GET ) || !is_string( $_GET['nonce'] ) )
		wp_die( 'nonce' );
	if ( wp_verify_nonce( $_GET['nonce'], 'kgr-login-with-google-clear' ) === FALSE )
		wp_die( 'nonce' );
	delete_option( 'kgr-login-with-google-remember' );
	delete_option( 'kgr-login-with-google-client-id' );
	delete_option( 'kgr-login-with-google-client-secret' );
	wp_redirect( menu_page_url( 'kgr-login-with-google', FALSE ) );
	exit;
} );
