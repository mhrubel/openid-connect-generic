=== OpenID Connect Generic Client ===
Contributors: daggerhart
Donate link: http://www.daggerhart.com/
Tags: security, login, oauth2, openidconnect, apps, authentication, autologin, sso 
Requires at least: 4
Tested up to: 4.7.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple client that provides SSO or opt-in authentication against a generic OAuth2 Server implementation.

== Description ==

This plugin allows to authenticate users against OpenID Connect OAuth2 API with Authorization Code Flow.
Once installed, it can be configured to automatically authenticate users (SSO), or provide a "Login with OpenID Connect"
button on the login form. After consent has been obtained, an existing user is automatically logged into WordPress, while 
new users are created in WordPress database.

Much of the documentation can be found on the Settings > OpenID Connect Generic dashboard page.

Originally based on the plugin provided by shirounagi - https://wordpress.org/plugins/generic-openid-connect/ - but 
has been completely rewritten.

=== Requirements ===

* Requires PHP 5.4+ with the OpenSSL extensions enabled (Defuse encryption library)

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > OpenID Connect and configure to meet your needs

== Frequently Asked Questions ==

= What is the client's Redirect URI? =

Most OAuth2 servers will require whitelisting a set of redirect URIs for security purposes. The Redirect URI provided
by this client is like so:  https://example.com/wp-admin/admin-ajax.php?action=openid-connect-authorize

Replace `example.com` with your domain name and path to WordPress.


== Changelog ==

See changelog.md
