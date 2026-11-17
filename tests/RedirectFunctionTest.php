<?php

namespace FwbRedirectToLanding\Tests;

class RedirectFunctionTest extends TestCase {

	private function enable_redirect( $url = 'https://example.com/landing', $type = 302, $roles = array( 'administrator' ) ) {
		$GLOBALS['fwb_test_options'] = array(
			'fwb_redirect_to_landing_enable'     => 1,
			'fwb_redirect_to_landing_url'        => $url,
			'fwb_redirect_type'                  => $type,
			'fwb_redirect_to_landing_rolebypass' => $roles,
		);
	}

	public function test_skips_when_admin() {
		$this->enable_redirect();
		$GLOBALS['fwb_test_is_admin'] = true;
		$_SERVER['REQUEST_URI']       = '/shop/';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	public function test_skips_when_cron() {
		$this->enable_redirect();
		$GLOBALS['fwb_test_doing_cron'] = true;
		$_SERVER['REQUEST_URI']         = '/shop/';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	/**
	 * REST_REQUEST is a constant; isolate so later tests stay frontend.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_skips_when_rest() {
		$this->enable_redirect();
		define( 'REST_REQUEST', true );
		$_SERVER['REQUEST_URI'] = '/shop/';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	public function test_returns_when_disabled() {
		$this->enable_redirect();
		$GLOBALS['fwb_test_options']['fwb_redirect_to_landing_enable'] = 0;
		$_SERVER['REQUEST_URI'] = '/shop/';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	public function test_returns_when_url_empty() {
		$this->enable_redirect( '' );
		$_SERVER['REQUEST_URI'] = '/shop/';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	public function test_returns_on_same_path_anti_loop() {
		$this->enable_redirect( 'https://example.com/landing' );
		$_SERVER['REQUEST_URI'] = '/landing/?utm=1';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	public function test_returns_when_role_bypasses() {
		$this->enable_redirect();
		$GLOBALS['fwb_test_current_user'] = new \WP_User( array( 'administrator' ) );
		$_SERVER['REQUEST_URI']           = '/shop/';

		fwb_redirect_to_landing();

		$this->assertTrue( true );
	}

	public function test_redirects_guest_on_frontend() {
		$this->enable_redirect( 'https://example.com/landing', 301 );
		$_SERVER['REQUEST_URI'] = '/shop/';

		try {
			fwb_redirect_to_landing();
			$this->fail( 'Expected redirect' );
		} catch ( \Fwb_Test_Redirect_Exception $e ) {
			$this->assertSame( 'https://example.com/landing', $e->location );
			$this->assertSame( 301, $e->status );
			$this->assertContains( 'allowed_redirect_hosts', $GLOBALS['fwb_test_filters'] );
		}
	}
}
