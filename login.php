<?php

if ( !defined( 'ABSPATH' ) )
	exit;

function kgr_login_with_google_link( string $text = '' ): void {
	// https://developers.google.com/identity/protocols/oauth2/web-server#creatingclient
	$redirect_url = add_query_arg( 'action', 'kgr_login_with_google_redirect', admin_url( 'admin-ajax.php' ) );
	$url = add_query_arg( [
		'client_id' => get_option( 'kgr-login-with-google-client-id' ),
		'redirect_uri' => urlencode( $redirect_url ),
		'response_type' => 'code',
		'scope' => urlencode( 'https://www.googleapis.com/auth/userinfo.email' ),
		'prompt' => 'select_account',
		'state' => wp_create_nonce( 'kgr_login_with_google_redirect' ),
	], 'https://accounts.google.com/o/oauth2/v2/auth' );
	if ( $text === '' )
		$text = __( 'Login with Google', 'kgr-login-with-google' );
	echo sprintf( '<a href="%s">%s</a>', esc_url_raw( $url ), esc_html( $text ) ) . "\n";
}

add_action( 'login_form', function(): void {
	if ( get_option( 'kgr-login-with-google-client-id' ) === '' )
		return;
	if ( get_option( 'kgr-login-with-google-client-secret' ) === '' )
		return;
	echo '<div style="margin: 0 6px 16px 0;">' . "\n";
	kgr_login_with_google_link();
	echo '</div>' . "\n";
} );

add_action( 'register_form', function(): void {
	if ( get_option( 'kgr-login-with-google-client-id' ) === '' )
		return;
	if ( get_option( 'kgr-login-with-google-client-secret' ) === '' )
		return;
	echo '<div style="margin: 0 6px 16px 0;">' . "\n";
	kgr_login_with_google_link( __( 'Register with Google', 'kgr-login-with-google' ) );
	echo '</div>' . "\n";
} );

add_action( 'wp_meta', function(): void {
	if ( get_option( 'kgr-login-with-google-client-id' ) === '' )
		return;
	if ( get_option( 'kgr-login-with-google-client-secret' ) === '' )
		return;
	if ( is_user_logged_in() )
		return;
	echo '<li>' . "\n";
	echo kgr_login_with_google_link();
	echo '</li>' . "\n";
} );

add_action( 'wp_ajax_nopriv_kgr_login_with_google_redirect', function(): void {
	// https://developers.google.com/identity/protocols/oauth2/web-server#handlingresponse
	if ( array_key_exists( 'error', $_GET ) )
		wp_die( 'authorization_code: error ' . esc_html( $_GET['error'] ) );
	if ( !array_key_exists( 'code', $_GET ) || !is_string( $_GET['code'] ) )
		wp_die( 'authorization_code: code' );
	if ( !array_key_exists( 'state', $_GET ) || !is_string( $_GET['state'] ) )
		wp_die( 'authorization_code: state' );
	if ( wp_verify_nonce( $_GET['state'], 'kgr_login_with_google_redirect' ) === FALSE )
		wp_die( 'authorization_code: nonce' );
	// https://developers.google.com/identity/protocols/oauth2/web-server#exchange-authorization-code
	$authorization_code = $_GET['code'];
	$redirect_url = add_query_arg( 'action', 'kgr_login_with_google_redirect', admin_url( 'admin-ajax.php' ) );
	$response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
		'body' => [
			'client_id' => get_option( 'kgr-login-with-google-client-id' ),
			'client_secret' => get_option( 'kgr-login-with-google-client-secret' ),
			'code' => $authorization_code,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $redirect_url,
		],
	] );
	if ( is_wp_error( $response ) )
		wp_die( 'access_token: error ' . esc_html( $response->get_error_message() ) );
	$json = wp_remote_retrieve_body( $response );
	// https://developers.google.com/identity/protocols/oauth2/openid-connect
	$json = json_decode( $json, TRUE );
	if ( is_null( $json ) )
		wp_die( 'access_token: json_decode' );
	if ( array_key_exists( 'error', $json ) && is_string( $json['error'] ) )
		wp_die( 'access_token: error ' . esc_html( $json['error'] ) );
	if ( !array_key_exists( 'id_token', $json ) || !is_string( $json['id_token'] ) )
		wp_die( 'access_token: id_token' );
	// https://darutk.medium.com/understanding-id-token-5f83f50fa02e
	$id_token = explode( '.', $json['id_token'] );
	if ( count( $id_token ) !== 3 )
		wp_die( 'id_token: explode' );
	$header = base64_decode( $id_token[0], TRUE );
	if ( $header == FALSE )
		wp_die( 'header: base64_decode' );
	$header = json_decode( $header, TRUE );
	if ( is_null( $header ) )
		wp_die( 'header: json_decode' );
	$payload = base64_decode( $id_token[1], TRUE );
	if ( $payload === FALSE )
		wp_die( 'payload: base64_decode' );
	$payload = json_decode( $payload, TRUE );
	if ( is_null( $payload ) )
		wp_die( 'payload: json_decode' );
	if ( !array_key_exists( 'email', $payload ) || !is_string( $payload['email'] ) )
		wp_die( 'payload: email' );
	$email = $payload['email'];
	$email = filter_var( $email, FILTER_VALIDATE_EMAIL );
	if ( $email === FALSE )
		wp_die( 'payload: email' );
	$user = get_user_by( 'email', $email );
	if ( $user !== FALSE ) {
		$user_id = $user->ID;
	} elseif ( !boolval( get_option( 'users_can_register' ) ) ) {
		wp_redirect( add_query_arg( 'registration', 'disabled', site_url( 'wp-login.php' ) ) );
		exit;
	} else {
		$pref = substr( $email, 0, strpos( $email, '@' ) );
		$login = $pref;
		$cnt = 0;
		while ( username_exists( $login ) ) {
			$cnt++;
			$login = $pref . '-' . $cnt;
		}
		$user_id = register_new_user( $login, $email );
		if ( is_wp_error( $user_id ) )
			wp_die( esc_html( $user_id->get_error_message() ) );
	}
	$remember = get_option( 'kgr-login-with-google-remember' );
	wp_set_auth_cookie( $user_id, $remember );
	wp_redirect( admin_url() ); // TODO redirect to custom location
	exit;
} );
