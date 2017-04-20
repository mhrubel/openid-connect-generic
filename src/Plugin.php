<?php

namespace OpenIdConnectGeneric;

class Plugin {
	// plugin version
	const VERSION = '3.2.0';

	/**
	 * @var \OpenIdConnectGeneric\Settings
	 */
	private $settings;

	/**
	 * @var \OpenIdConnectGeneric\Logger
	 */
	private $logger;

	/**
	 * @var \OpenIdConnectGeneric\Client
	 */
	private $client;

	/**
	 * @var \OpenIdConnectGeneric\SettingsPage
	 */
	private $settings_page;

	/**
	 * @var \OpenIdConnectGeneric\LoginForm
	 */
	private $login_form;

	/**
	 * Setup the plugin
	 *
	 * @param Settings $settings
	 * @param Logger $logger
	 */
	function __construct( Settings $settings, Logger $logger ){
		$this->settings = $settings;
		$this->logger = $logger;
	}

	/**
	 * Hook into wp
	 *
	 * @param \OpenIdConnectGeneric\Settings $settings
	 * @param \OpenIdConnectGeneric\Logger $logger
	 *
	 * @return \OpenIdConnectGeneric\Plugin
	 */
	static public function register( Settings $settings, Logger $logger ){
		$plugin = new self( $settings, $logger );

		add_action( 'init', array( $plugin, 'init' ) );

		// privacy hooks
		add_action( 'template_redirect', array( $plugin, 'enforce_privacy_redirect' ), 0 );
		add_filter( 'the_content_feed', array( $plugin, 'enforce_privacy_feeds' ), 999 );
		add_filter( 'the_excerpt_rss',  array( $plugin, 'enforce_privacy_feeds' ), 999 );
		add_filter( 'comment_text_rss', array( $plugin, 'enforce_privacy_feeds' ), 999 );

		return $plugin;
	}

	/**
	 * WP Hook 'init'
	 */
	function init(){
		$redirect_uri = admin_url( 'admin-ajax.php?action=openid-connect-authorize' );

		if ( $this->settings->alternate_redirect_uri ){
			$redirect_uri = site_url( '/openid-connect-authorize' );
		}

		$this->client = new Client(
			$this->settings->client_id,
			$this->settings->client_secret,
			$this->settings->scope,
			$this->settings->endpoint_login,
			$this->settings->endpoint_userinfo,
			$this->settings->endpoint_token,
			$redirect_uri
		);

		$this->client_wrapper = ClientWrapper::register( $this->client, $this->settings, $this->logger );
		$this->login_form = LoginForm::register( $this->settings, $this->client_wrapper );

		$this->upgrade();

		if ( is_admin() ){
			$this->settings_page = SettingsPage::register( $this->settings, $this->logger );
		}
	}

	/**
	 * Check if privacy enforcement is enabled, and redirect users that aren't
	 * logged in.
	 */
	function enforce_privacy_redirect() {
		if ( $this->settings->enforce_privacy && ! is_user_logged_in() ) {
			// our client endpoint relies on the wp admind ajax endpoint
			if ( ! defined( 'DOING_AJAX') || ! DOING_AJAX || ! isset( $_GET['action'] ) || $_GET['action'] != 'openid-connect-authorize' ) {
				auth_redirect();
			}
		}
	}

	/**
	 * Enforce privacy settings for rss feeds
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	function enforce_privacy_feeds( $content ){
		if ( $this->settings->enforce_privacy && ! is_user_logged_in() ) {
			$content = 'Private site';
		}
		return $content;
	}

	/**
	 * Handle plugin upgrades
	 */
	function upgrade(){
		$last_version = get_option( 'openid-connect-generic-plugin-version', 0 );
		$settings = $this->settings;

		if ( version_compare( self::VERSION, $last_version, '>' ) ) {
			// upgrade required

			// @todo move this to another file for upgrade scripts
			if ( isset( $settings->ep_login ) ) {
				$settings->endpoint_login = $settings->ep_login;
				$settings->endpoint_token = $settings->ep_token;
				$settings->endpoint_userinfo = $settings->ep_userinfo;

				unset( $settings->ep_login, $settings->ep_token, $settings->ep_userinfo );
				$settings->save();
			}

			// update the stored version number
			update_option( 'openid-connect-generic-plugin-version', self::VERSION );
		}
	}
}
