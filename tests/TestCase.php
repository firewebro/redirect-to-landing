<?php

namespace FwbRedirectToLanding\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['fwb_test_is_admin']     = false;
		$GLOBALS['fwb_test_doing_ajax']   = false;
		$GLOBALS['fwb_test_doing_cron']   = false;
		$GLOBALS['fwb_test_current_user'] = new \WP_User();
		$GLOBALS['fwb_test_options']             = array();
		$GLOBALS['fwb_test_filters']             = array();
		$GLOBALS['fwb_test_can_manage_options']  = true;
		$GLOBALS['fwb_test_nonce_ok']            = true;
		$GLOBALS['fwb_test_nonce_checked']       = null;
		$GLOBALS['fwb_test_updated_options']     = array();
		$_POST                                   = array();
		unset( $_SERVER['REQUEST_URI'] );
	}

	protected function tearDown(): void {
		unset(
			$GLOBALS['fwb_test_is_admin'],
			$GLOBALS['fwb_test_doing_ajax'],
			$GLOBALS['fwb_test_doing_cron'],
			$GLOBALS['fwb_test_current_user'],
			$GLOBALS['fwb_test_options'],
			$GLOBALS['fwb_test_filters'],
			$GLOBALS['fwb_test_can_manage_options'],
			$GLOBALS['fwb_test_nonce_ok'],
			$GLOBALS['fwb_test_nonce_checked'],
			$GLOBALS['fwb_test_updated_options'],
			$_SERVER['REQUEST_URI']
		);
		$_POST = array();
		parent::tearDown();
	}
}
