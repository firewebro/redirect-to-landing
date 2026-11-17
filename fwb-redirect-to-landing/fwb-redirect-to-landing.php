<?php
/*
Plugin Name: Redirect to landing
Description: WordPress Plugin for redirecting public website traffic to a landing page.
Author: FireWeb
AuthorURI: https://fireweb.ro
Version: 1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: fwb-redirect-to-landing
*/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request should skip frontend redirect.
 *
 * @return bool
 */
function fwb_redirect_to_landing_should_skip() {
	if ( is_admin() ) {
		return true;
	}
	if ( wp_doing_ajax() ) {
		return true;
	}
	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return true;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}
	return false;
}

/**
 * Normalize a URL path for anti-loop comparison.
 *
 * @param string $url URL or path.
 * @return string
 */
function fwb_redirect_to_landing_normalize_path( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		$path = '/';
	}
	$path = untrailingslashit( strtolower( $path ) );
	return '' === $path ? '/' : $path;
}

/**
 * Current request path.
 *
 * @return string
 */
function fwb_redirect_to_landing_current_path() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	return fwb_redirect_to_landing_normalize_path( $request_uri );
}

/**
 * Sanitize bypass roles against registered WordPress roles.
 *
 * @param mixed $roles Raw roles.
 * @return string[]
 */
function fwb_redirect_to_landing_sanitize_roles( $roles ) {
	if ( ! is_array( $roles ) ) {
		return array();
	}
	$valid = array_keys( wp_roles()->roles );
	$clean = array();
	foreach ( $roles as $role ) {
		$role = sanitize_key( $role );
		if ( in_array( $role, $valid, true ) ) {
			$clean[] = $role;
		}
	}
	return array_values( array_unique( $clean ) );
}

/**
 * Sanitize redirect type to 301 or 302.
 *
 * @param mixed $type Raw type.
 * @return int
 */
function fwb_redirect_to_landing_sanitize_type( $type ) {
	$type = absint( $type );
	return 301 === $type ? 301 : 302;
}

/**
 * Sanitize destination URL (http/https only).
 *
 * @param mixed $url Raw URL.
 * @return string
 */
function fwb_redirect_to_landing_sanitize_url( $url ) {
	$url = esc_url_raw( wp_unslash( (string) $url ) );
	if ( '' === $url || ! wp_http_validate_url( $url ) ) {
		return '';
	}
	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
	if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
		return '';
	}
	return $url;
}

/**
 * Check if the current (or given) user has a bypass role.
 *
 * @param mixed    $roles   Allowed roles.
 * @param int|null $user_id Optional user ID.
 * @return bool
 */
function fwb_redirect_to_landing_check_role( $roles, $user_id = null ) {
	if ( ! is_array( $roles ) || empty( $roles ) ) {
		return false;
	}
	if ( $user_id ) {
		$user = get_userdata( $user_id );
	} else {
		$user = wp_get_current_user();
	}
	if ( ! $user instanceof WP_User || empty( $user->roles ) || ! is_array( $user->roles ) ) {
		return false;
	}
	foreach ( $user->roles as $role ) {
		if ( in_array( $role, $roles, true ) ) {
			return true;
		}
	}
	return false;
}

// Redirect function, pluggable.
if ( ! function_exists( 'fwb_redirect_to_landing' ) ) {
	function fwb_redirect_to_landing() {
		if ( fwb_redirect_to_landing_should_skip() ) {
			return;
		}

		$enableredirect_option = (bool) get_option( 'fwb_redirect_to_landing_enable', false );
		$redirecturl_option    = get_option( 'fwb_redirect_to_landing_url', '' );
		$redirecttype_option   = fwb_redirect_to_landing_sanitize_type( get_option( 'fwb_redirect_type', 302 ) );
		$rolebypass_option     = get_option( 'fwb_redirect_to_landing_rolebypass', array( 'administrator' ) );

		if ( ! is_array( $rolebypass_option ) ) {
			$rolebypass_option = array();
		}

		$redirecturl_option = fwb_redirect_to_landing_sanitize_url( $redirecturl_option );
		if ( ! $enableredirect_option || '' === $redirecturl_option ) {
			return;
		}

		$current_path  = fwb_redirect_to_landing_current_path();
		$redirect_path = fwb_redirect_to_landing_normalize_path( $redirecturl_option );
		if ( $current_path === $redirect_path ) {
			return;
		}

		if ( fwb_redirect_to_landing_check_role( $rolebypass_option ) ) {
			return;
		}

		$host = wp_parse_url( $redirecturl_option, PHP_URL_HOST );
		if ( is_string( $host ) && '' !== $host ) {
			add_filter(
				'allowed_redirect_hosts',
				static function ( $hosts ) use ( $host ) {
					$hosts[] = $host;
					return $hosts;
				}
			);
		}

		wp_safe_redirect( $redirecturl_option, $redirecttype_option );
		exit;
	}
}

add_action( 'template_redirect', 'fwb_redirect_to_landing' );

add_action( 'admin_menu', 'fwb_redirect_to_landing_menu' );
function fwb_redirect_to_landing_menu() {
	add_options_page(
		__( 'Redirect to landing page', 'fwb-redirect-to-landing' ),
		__( 'Redirect to landing', 'fwb-redirect-to-landing' ),
		'manage_options',
		'fwb-redirect-to-landing',
		'fwb_redirect_to_landing_init'
	);
}

function fwb_redirect_to_landing_init() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'fwb-redirect-to-landing' ) );
	}

	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$plugin_data = get_plugin_data( __FILE__ );

	$saved = false;
	if ( isset( $_POST['fwb_rtl_save'] ) ) {
		check_admin_referer( 'fwb_redirect_to_landing_save' );

		$enable = isset( $_POST['enableredirect'] ) ? 1 : 0;
		update_option( 'fwb_redirect_to_landing_enable', $enable, true );

		$url = isset( $_POST['redirecturl'] ) ? fwb_redirect_to_landing_sanitize_url( $_POST['redirecturl'] ) : '';
		update_option( 'fwb_redirect_to_landing_url', $url, true );

		$roles = isset( $_POST['rolebypass'] ) ? fwb_redirect_to_landing_sanitize_roles( wp_unslash( $_POST['rolebypass'] ) ) : array();
		update_option( 'fwb_redirect_to_landing_rolebypass', $roles, false );

		$type = isset( $_POST['redirecttype'] ) ? fwb_redirect_to_landing_sanitize_type( wp_unslash( $_POST['redirecttype'] ) ) : 302;
		update_option( 'fwb_redirect_type', $type, true );

		$saved = true;
	}

	$enableredirect_option = (bool) get_option( 'fwb_redirect_to_landing_enable', false );
	$redirecturl_option    = get_option( 'fwb_redirect_to_landing_url', '' );
	$rolebypass_option     = get_option( 'fwb_redirect_to_landing_rolebypass', array( 'administrator' ) );
	$redirecttype_option   = fwb_redirect_to_landing_sanitize_type( get_option( 'fwb_redirect_type', 302 ) );

	if ( ! is_array( $rolebypass_option ) ) {
		$rolebypass_option = array();
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html( $plugin_data['Name'] ) . '</h1>';
	echo '<div class="description">' . esc_html( $plugin_data['Description'] ) . '</div>';

	if ( $saved ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'fwb-redirect-to-landing' ) . '</p></div>';
	}

	echo '<form method="post" action="">';
	wp_nonce_field( 'fwb_redirect_to_landing_save' );
	echo '<input type="hidden" name="fwb_rtl_save" value="1" />';
	echo '<table class="form-table" role="presentation">';

	echo '<tr>';
	echo '<th scope="row"><label for="enableredirect">' . esc_html__( 'Enable Redirect', 'fwb-redirect-to-landing' ) . '</label></th>';
	echo '<td><input name="enableredirect" type="checkbox" id="enableredirect" value="1"' . checked( $enableredirect_option, true, false ) . ' /> ' . esc_html__( 'Check to enable.', 'fwb-redirect-to-landing' ) . '</td>';
	echo '</tr>';

	echo '<tr>';
	echo '<th scope="row"><label for="redirecttype">' . esc_html__( 'Redirection Type', 'fwb-redirect-to-landing' ) . '</label></th>';
	echo '<td><select name="redirecttype" id="redirecttype">';
	echo '<option value="302"' . selected( $redirecttype_option, 302, false ) . '>' . esc_html__( 'Temporary redirect (302)', 'fwb-redirect-to-landing' ) . '</option>';
	echo '<option value="301"' . selected( $redirecttype_option, 301, false ) . '>' . esc_html__( 'Permanent redirect (301)', 'fwb-redirect-to-landing' ) . '</option>';
	echo '</select></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<th scope="row"><label for="rolebypass">' . esc_html__( 'Role Bypass', 'fwb-redirect-to-landing' ) . '</label></th>';
	echo '<td><select name="rolebypass[]" id="rolebypass" multiple>';
	foreach ( wp_roles()->roles as $role => $roledetails ) {
		$label = isset( $roledetails['name'] ) ? translate_user_role( $roledetails['name'] ) : $role;
		echo '<option value="' . esc_attr( $role ) . '"' . selected( in_array( $role, $rolebypass_option, true ), true, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<th scope="row"><label for="redirecturl">' . esc_html__( 'Redirect Url', 'fwb-redirect-to-landing' ) . '</label></th>';
	echo '<td><input name="redirecturl" type="url" id="redirecturl" aria-describedby="tagline-description" value="' . esc_attr( (string) $redirecturl_option ) . '" class="regular-text" />';
	echo '<p class="description" id="tagline-description">https://example.com/landing</p></td>';
	echo '</tr>';

	echo '</table>';
	submit_button( __( 'Save Changes', 'fwb-redirect-to-landing' ) );
	echo '</form></div>';
}
