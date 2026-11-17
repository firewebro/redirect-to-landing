<?php
/**
 * PHPUnit bootstrap for unit tests (Brain Monkey, no live WordPress).
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

require_once __DIR__ . '/wp-stubs.php';
require_once dirname( __DIR__ ) . '/fwb-redirect-to-landing/fwb-redirect-to-landing.php';
