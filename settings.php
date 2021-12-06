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
		echo '<p class="description">' . "\n";
		echo sprintf( '<span>%s</span>', esc_html__( 'Increases the lifetime of the login cookie.', 'kgr-login-with-google' ) ) . "\n";
		echo sprintf( '<a href="%s" target="_blank">%s</a>',
			'https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/',
			esc_html__( 'More information.', 'kgr-login-with-google' )
		) . "\n";
		echo '</p>' . "\n";
	}, $page, $section, [
		'name' => $id,
		'label_for' => $id,
	] );
	// SECTION
	$section = 'kgr-login-with-google-credentials';
	$title = esc_html__( 'Credentials', 'kgr-login-with-google' );
	add_settings_section( $section, $title, function(): void {
		echo sprintf( '<p>%s</p>', esc_html__( 'In order to obtain your Credentials, follow the steps below:', 'kgr-login-with-google' ) ) . "\n";
		echo '<ol>' . "\n";
		echo sprintf( '<li>%s</li>', sprintf( esc_html_x( 'Create a new project or select an existing one in %s.', 'Create a new project or select an existing one in *Google Cloud Platform*.', 'kgr-login-with-google' ), '<a href="https://console.cloud.google.com/" target="_blank">Google Cloud Platform</a>' ) ) . "\n";
		echo sprintf( '<li>%s</li>', sprintf( esc_html_x( 'Click on %s.', 'Click on *Create Credentials*.', 'kgr-login-with-google' ), '<strong>Create Credentials</strong>' ) ) . "\n";
		echo sprintf( '<li>%s</li>', sprintf( esc_html_x( 'Choose %s.', 'Choose *OAuth client ID*.', 'kgr-login-with-google' ), '<strong>OAuth client ID</strong>' ) ) . "\n";
		echo sprintf( '<li>%s</li>', sprintf( esc_html_x( 'Select %s as an %s.', 'Select *Web application* as an *Application type*.' , 'kgr-login-with-google' ), '<strong>Web application</strong>', '<strong>Application type</strong>' ) ) . "\n";
		echo '<li>' . "\n";
		echo sprintf( '<span>%s</span>', sprintf( esc_html_x( 'Under %s add the URI:', 'Under *Authorized redirect URIs* add the URI:', 'kgr-login-with-google' ), '<strong>Authorized redirect URIs</strong>' ) ) . "\n";
		echo '<br />' . "\n";
		echo sprintf( '<code>%s</code>', esc_url( add_query_arg( 'action', 'kgr_login_with_google_redirect', admin_url( 'admin-ajax.php' ) ) ) );
		echo '<br />' . "\n";
		echo sprintf( '<span>%s</span>', esc_html__( 'Remove any whitespaces from the URI.', 'kgr-login-with-google' ) ) . "\n";
		echo '</li>' . "\n";
		echo sprintf( '<li>%s</li>', sprintf( esc_html_x( 'Click on the %s button.', 'Click on the *Create* button.', 'kgr-login-with-google' ), '<strong>Create</strong>' ) ) . "\n";
		echo '</ol>' . "\n";
	}, $page );
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
