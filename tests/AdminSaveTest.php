<?php

namespace FwbRedirectToLanding\Tests;

class AdminSaveTest extends TestCase {

	private function render_settings() {
		ob_start();
		fwb_redirect_to_landing_init();
		return ob_get_clean();
	}

	public function test_dies_without_capability() {
		$GLOBALS['fwb_test_can_manage_options'] = false;

		$this->expectException( \Fwb_Test_Die_Exception::class );
		fwb_redirect_to_landing_init();
	}

	public function test_does_not_save_without_post_flag() {
		$html = $this->render_settings();

		$this->assertSame( array(), $GLOBALS['fwb_test_updated_options'] );
		$this->assertStringContainsString( 'Redirect to landing', $html );
		$this->assertStringNotContainsString( 'Settings saved.', $html );
	}

	public function test_rejects_bad_nonce() {
		$_POST['fwb_rtl_save'] = '1';
		$GLOBALS['fwb_test_nonce_ok'] = false;

		try {
			fwb_redirect_to_landing_init();
			$this->fail( 'Expected nonce failure' );
		} catch ( \Fwb_Test_Nonce_Exception $e ) {
			$this->assertSame( 'fwb_redirect_to_landing_save', $GLOBALS['fwb_test_nonce_checked'] );
			$this->assertSame( array(), $GLOBALS['fwb_test_updated_options'] );
		}
	}

	public function test_saves_sanitized_options() {
		$_POST = array(
			'fwb_rtl_save'  => '1',
			'enableredirect' => '1',
			'redirecturl'   => 'https://example.com/landing',
			'rolebypass'    => array( 'administrator', 'hacker', 'editor' ),
			'redirecttype'  => '301',
		);

		$html = $this->render_settings();

		$this->assertSame( 'fwb_redirect_to_landing_save', $GLOBALS['fwb_test_nonce_checked'] );
		$this->assertSame( 1, $GLOBALS['fwb_test_options']['fwb_redirect_to_landing_enable'] );
		$this->assertSame( 'https://example.com/landing', $GLOBALS['fwb_test_options']['fwb_redirect_to_landing_url'] );
		$this->assertSame( array( 'administrator', 'editor' ), $GLOBALS['fwb_test_options']['fwb_redirect_to_landing_rolebypass'] );
		$this->assertSame( 301, $GLOBALS['fwb_test_options']['fwb_redirect_type'] );
		$this->assertStringContainsString( 'Settings saved.', $html );
	}

	public function test_enable_off_when_checkbox_missing_and_invalid_url_cleared() {
		$_POST = array(
			'fwb_rtl_save' => '1',
			'redirecturl'  => 'javascript:alert(1)',
			'redirecttype' => '200',
		);

		$this->render_settings();

		$this->assertSame( 0, $GLOBALS['fwb_test_options']['fwb_redirect_to_landing_enable'] );
		$this->assertSame( '', $GLOBALS['fwb_test_options']['fwb_redirect_to_landing_url'] );
		$this->assertSame( array(), $GLOBALS['fwb_test_options']['fwb_redirect_to_landing_rolebypass'] );
		$this->assertSame( 302, $GLOBALS['fwb_test_options']['fwb_redirect_type'] );
	}
}
