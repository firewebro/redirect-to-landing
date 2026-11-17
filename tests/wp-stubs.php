<?php
/**
 * Minimal WordPress stubs for unit tests.
 */

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $value ) {
		return rtrim( (string) $value, '/\\' );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );
		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		return filter_var( $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'wp_http_validate_url' ) ) {
	function wp_http_validate_url( $url ) {
		if ( ! is_string( $url ) || '' === $url ) {
			return false;
		}
		$parts = parse_url( $url );
		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return false;
		}
		return in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ? $url : false;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin() {
		return ! empty( $GLOBALS['fwb_test_is_admin'] );
	}
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	function wp_doing_ajax() {
		return ! empty( $GLOBALS['fwb_test_doing_ajax'] );
	}
}

if ( ! function_exists( 'wp_doing_cron' ) ) {
	function wp_doing_cron() {
		return ! empty( $GLOBALS['fwb_test_doing_cron'] );
	}
}

if ( ! class_exists( 'WP_Roles' ) ) {
	class WP_Roles {
		public $roles = array(
			'administrator' => array( 'name' => 'Administrator' ),
			'editor'        => array( 'name' => 'Editor' ),
			'subscriber'    => array( 'name' => 'Subscriber' ),
		);
	}
}

if ( ! function_exists( 'wp_roles' ) ) {
	function wp_roles() {
		static $roles = null;
		if ( null === $roles ) {
			$roles = new WP_Roles();
		}
		return $roles;
	}
}

if ( ! class_exists( 'WP_User' ) ) {
	class WP_User {
		public $roles = array();

		public function __construct( $roles = array() ) {
			$this->roles = $roles;
		}
	}
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	function wp_get_current_user() {
		if ( isset( $GLOBALS['fwb_test_current_user'] ) && $GLOBALS['fwb_test_current_user'] instanceof WP_User ) {
			return $GLOBALS['fwb_test_current_user'];
		}
		return new WP_User();
	}
}

if ( ! function_exists( 'get_userdata' ) ) {
	function get_userdata( $user_id ) {
		return false;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		if ( isset( $GLOBALS['fwb_test_options'] ) && array_key_exists( $name, $GLOBALS['fwb_test_options'] ) ) {
			return $GLOBALS['fwb_test_options'][ $name ];
		}
		return $default;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		if ( ! isset( $GLOBALS['fwb_test_filters'] ) || ! is_array( $GLOBALS['fwb_test_filters'] ) ) {
			$GLOBALS['fwb_test_filters'] = array();
		}
		$GLOBALS['fwb_test_filters'][] = $hook;
		return true;
	}
}

if ( ! class_exists( 'Fwb_Test_Redirect_Exception' ) ) {
	class Fwb_Test_Redirect_Exception extends RuntimeException {
		public $location;
		public $status;

		public function __construct( $location, $status ) {
			$this->location = $location;
			$this->status   = $status;
			parent::__construct( 'redirect' );
		}
	}
}

if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( $location, $status = 302, $x_redirect_by = 'WordPress' ) {
		throw new Fwb_Test_Redirect_Exception( $location, $status );
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $cap ) {
		return ! empty( $GLOBALS['fwb_test_can_manage_options'] ) && 'manage_options' === $cap;
	}
}

if ( ! class_exists( 'Fwb_Test_Die_Exception' ) ) {
	class Fwb_Test_Die_Exception extends RuntimeException {}
}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '' ) {
		throw new Fwb_Test_Die_Exception( (string) $message );
	}
}

if ( ! class_exists( 'Fwb_Test_Nonce_Exception' ) ) {
	class Fwb_Test_Nonce_Exception extends RuntimeException {}
}

if ( ! function_exists( 'check_admin_referer' ) ) {
	function check_admin_referer( $action = -1, $query_arg = '_wpnonce' ) {
		$ok = ! empty( $GLOBALS['fwb_test_nonce_ok'] );
		$GLOBALS['fwb_test_nonce_checked'] = $action;
		if ( ! $ok ) {
			throw new Fwb_Test_Nonce_Exception( 'bad nonce' );
		}
		return 1;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value, $autoload = null ) {
		if ( ! isset( $GLOBALS['fwb_test_options'] ) || ! is_array( $GLOBALS['fwb_test_options'] ) ) {
			$GLOBALS['fwb_test_options'] = array();
		}
		$GLOBALS['fwb_test_options'][ $option ] = $value;
		if ( ! isset( $GLOBALS['fwb_test_updated_options'] ) || ! is_array( $GLOBALS['fwb_test_updated_options'] ) ) {
			$GLOBALS['fwb_test_updated_options'] = array();
		}
		$GLOBALS['fwb_test_updated_options'][] = array(
			'option'   => $option,
			'value'    => $value,
			'autoload' => $autoload,
		);
		return true;
	}
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	function get_plugin_data( $plugin_file, $markup = true, $translate = true ) {
		return array(
			'Name'        => 'Redirect to landing',
			'Description' => 'WordPress Plugin for redirecting public website traffic to a landing page.',
		);
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		$html = '<input type="hidden" name="_wpnonce" value="test" />';
		if ( $echo ) {
			echo $html;
		}
		return $html;
	}
}

if ( ! function_exists( 'checked' ) ) {
	function checked( $checked, $current = true, $echo = true ) {
		$result = (string) $checked === (string) $current ? ' checked="checked"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current = true, $echo = true ) {
		$result = (string) $selected === (string) $current ? ' selected="selected"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'submit_button' ) ) {
	function submit_button( $text = 'Save Changes', $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {
		echo '<p class="submit"><input type="submit" name="' . esc_attr( $name ) . '" value="' . esc_attr( $text ) . '" /></p>';
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'translate_user_role' ) ) {
	function translate_user_role( $name ) {
		return $name;
	}
}
