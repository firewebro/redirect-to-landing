<?php

namespace FwbRedirectToLanding\Tests;

class NormalizePathTest extends TestCase {

	public function test_normalize_full_url() {
		$this->assertSame(
			'/landing',
			fwb_redirect_to_landing_normalize_path( 'https://example.com/landing/' )
		);
	}

	public function test_normalize_empty_is_root() {
		$this->assertSame( '/', fwb_redirect_to_landing_normalize_path( '' ) );
		$this->assertSame( '/', fwb_redirect_to_landing_normalize_path( 'https://example.com' ) );
	}

	public function test_normalize_is_case_insensitive() {
		$this->assertSame(
			'/shop',
			fwb_redirect_to_landing_normalize_path( 'https://example.com/Shop' )
		);
	}

	public function test_current_path_from_request_uri() {
		$_SERVER['REQUEST_URI'] = '/about/?utm=1';
		$this->assertSame( '/about', fwb_redirect_to_landing_current_path() );
		unset( $_SERVER['REQUEST_URI'] );
	}

	public function test_current_path_default() {
		unset( $_SERVER['REQUEST_URI'] );
		$this->assertSame( '/', fwb_redirect_to_landing_current_path() );
	}
}
