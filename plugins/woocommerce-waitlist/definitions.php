<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! defined( 'WCWL_VERSION' ) ) {
	define( 'WCWL_VERSION', '2.4.16' );
}
if ( ! defined( 'WCWL_SLUG' ) ) {
	define( 'WCWL_SLUG', 'woocommerce_waitlist' );
}
if ( ! defined( 'WCWL_ENQUEUE_PATH' ) ) {
	define( 'WCWL_ENQUEUE_PATH', plugins_url( '', __FILE__ ) );
}
if ( ! defined( 'WCWL_FILE_PATH' ) ) {
	define( 'WCWL_FILE_PATH', plugin_dir_path( __FILE__ ) );
}
