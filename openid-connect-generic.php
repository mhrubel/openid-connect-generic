<?php
/*
Plugin Name: OpenID Connect - Generic Client
Plugin URI: https://github.com/daggerhart/openid-connect-generic
Description:  Connect to an OpenID Connect identity provider with Authorization Code Flow
Version: 3.2.0
Author: daggerhart
Author URI: http://www.daggerhart.com
License: GPLv2 Copyright (c) 2015 daggerhart
*/

/* 
Notes
  Spec Doc - http://openid.net/specs/openid-connect-basic-1_0-32.html

  Filters
  - openid-connect-generic-alter-request      - 3 args: request array, plugin settings, specific request op
  - openid-connect-generic-settings-fields    - modify the fields provided on the settings page
  - openid-connect-generic-login-button-text  - modify the login button text 
  - openid-connect-generic-user-login-test    - (bool) should the user be logged in based on their claim
  - openid-connect-generic-user-creation-test - (bool) should the user be created based on their claim

  Actions
  - openid-connect-generic-user-create - 2 args: fires when a new user is created by this plugin
  - openid-connect-generic-update-user-using-current-claim - 2 args: fires every time an existing user logs

  User Meta
  - openid-connect-generic-user                - (bool) if the user was created by this plugin
  - openid-connect-generic-subject-identity    - the identity of the user provided by the idp
  - openid-connect-generic-last-id-token-claim - the user's most recent id_token claim, decoded
  - openid-connect-generic-last-user-claim     - the user's most recent user_claim
  - openid-connect-generic-refresh-cookie-key  - encryption key used to secure refresh token info in cookie
  
  Options
  - openid_connect_generic_settings     - plugin settings
  - openid-connect-generic-valid-states - locally stored generated states
*/

use OpenIdConnectGeneric\Plugin;
use OpenIdConnectGeneric\Settings;
use OpenIdConnectGeneric\Logger;

// do it
openid_connect_generic_bootstrap();

/**
 * Instantiate the plugin and hook into WP
 */
function openid_connect_generic_bootstrap(){
	spl_autoload_register( 'openid_connect_generic_autoload' );

	$settings = new Settings(
		'openid_connect_generic_settings',
		// default settings values
		array(
			// oauth client settings
			'login_type'        => 'button',
			'client_id'         => '',
			'client_secret'     => '',
			'scope'             => '',
			'endpoint_login'    => '',
			'endpoint_userinfo' => '',
			'endpoint_token'    => '',
			'endpoint_end_session' => '',

			// non-standard settings
			'no_sslverify'    => 0,
			'http_request_timeout' => 5,
			'identity_key'    => 'preferred_username',
			'nickname_key'    => 'preferred_username',
			'email_format'       => '{email}',
			'displayname_format' => '',
			'identify_with_username' => false,

			// plugin settings
			'enforce_privacy' => 0,
			'alternate_redirect_uri' => 0,
			'link_existing_users' => 0,
			'redirect_user_back' => 0,
			'enable_logging'  => 0,
			'log_limit'       => 1000,
		)
	);

	$logger = new Logger( 'openid-connect-generic-logs', 'error', $settings->enable_logging, $settings->log_limit );

	$plugin = Plugin::register( $settings, $logger );
}

/**
 * Simple autoloader
 *
 * @param $class
 */
function openid_connect_generic_autoload( $class ) {
	// project-specific namespace prefix
	$prefix = 'OpenIdConnectGeneric\\';

	// relative class name is the class minus the defined prefix
	if ( stripos( $class, $prefix ) === 0 ) {
		$class = substr($class, strlen($prefix));
	}


	// base directory for the namespace prefix
	$base_dirs = array(
		__DIR__ . '/src/',
		__DIR__ . '/vendor/'
	);

	foreach( $base_dirs as $base_dir ){
		$file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

		// if the file exists, require it
		if (file_exists($file)) {
			require $file;
		}
	}
}
