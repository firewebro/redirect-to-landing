<?php

namespace FwbRedirectToLanding\Tests;

class CheckRoleAndSkipTest extends TestCase {

	public function test_check_role_false_when_empty_roles() {
		$this->assertFalse( fwb_redirect_to_landing_check_role( array() ) );
		$this->assertFalse( fwb_redirect_to_landing_check_role( 'administrator' ) );
	}

	public function test_check_role_guest_has_no_bypass() {
		$GLOBALS['fwb_test_current_user'] = new \WP_User( array() );
		$this->assertFalse( fwb_redirect_to_landing_check_role( array( 'administrator' ) ) );
	}

	public function test_check_role_admin_bypasses() {
		$GLOBALS['fwb_test_current_user'] = new \WP_User( array( 'administrator' ) );
		$this->assertTrue( fwb_redirect_to_landing_check_role( array( 'administrator' ) ) );
	}

	public function test_check_role_subscriber_does_not_bypass_admin_only() {
		$GLOBALS['fwb_test_current_user'] = new \WP_User( array( 'subscriber' ) );
		$this->assertFalse( fwb_redirect_to_landing_check_role( array( 'administrator' ) ) );
	}

	public function test_should_skip_when_admin() {
		$GLOBALS['fwb_test_is_admin'] = true;
		$this->assertTrue( fwb_redirect_to_landing_should_skip() );
	}

	public function test_should_skip_when_ajax() {
		$GLOBALS['fwb_test_doing_ajax'] = true;
		$this->assertTrue( fwb_redirect_to_landing_should_skip() );
	}

	public function test_should_skip_when_cron() {
		$GLOBALS['fwb_test_doing_cron'] = true;
		$this->assertTrue( fwb_redirect_to_landing_should_skip() );
	}

	/**
	 * REST_REQUEST is a constant; isolate so later tests stay frontend.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_skip_when_rest() {
		define( 'REST_REQUEST', true );
		$this->assertTrue( fwb_redirect_to_landing_should_skip() );
	}

	public function test_should_not_skip_frontend() {
		$this->assertFalse( fwb_redirect_to_landing_should_skip() );
	}
}
