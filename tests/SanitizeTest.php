<?php

namespace FwbRedirectToLanding\Tests;

class SanitizeTest extends TestCase {

	public function test_sanitize_type_allows_301() {
		$this->assertSame( 301, fwb_redirect_to_landing_sanitize_type( 301 ) );
		$this->assertSame( 301, fwb_redirect_to_landing_sanitize_type( '301' ) );
	}

	public function test_sanitize_type_defaults_to_302() {
		$this->assertSame( 302, fwb_redirect_to_landing_sanitize_type( 302 ) );
		$this->assertSame( 302, fwb_redirect_to_landing_sanitize_type( 200 ) );
		$this->assertSame( 302, fwb_redirect_to_landing_sanitize_type( 'nope' ) );
		$this->assertSame( 302, fwb_redirect_to_landing_sanitize_type( null ) );
	}

	public function test_sanitize_url_accepts_http_https() {
		$this->assertSame(
			'https://example.com/landing',
			fwb_redirect_to_landing_sanitize_url( 'https://example.com/landing' )
		);
		$this->assertSame(
			'http://example.com/x',
			fwb_redirect_to_landing_sanitize_url( 'http://example.com/x' )
		);
	}

	public function test_sanitize_url_rejects_invalid() {
		$this->assertSame( '', fwb_redirect_to_landing_sanitize_url( '' ) );
		$this->assertSame( '', fwb_redirect_to_landing_sanitize_url( 'javascript:alert(1)' ) );
		$this->assertSame( '', fwb_redirect_to_landing_sanitize_url( 'ftp://files.example.com' ) );
		$this->assertSame( '', fwb_redirect_to_landing_sanitize_url( 'not-a-url' ) );
	}

	public function test_sanitize_roles_keeps_valid_unique() {
		$result = fwb_redirect_to_landing_sanitize_roles( array( 'administrator', 'editor', 'administrator', 'hacker' ) );
		$this->assertSame( array( 'administrator', 'editor' ), $result );
	}

	public function test_sanitize_roles_rejects_non_array() {
		$this->assertSame( array(), fwb_redirect_to_landing_sanitize_roles( 'administrator' ) );
		$this->assertSame( array(), fwb_redirect_to_landing_sanitize_roles( null ) );
	}
}
