<?php
/*
Plugin Name: WP Chatbot Pro
Plugin URI:  https://www.holithemes.com/wp-chatbot/
Description: Add Messenger to your website, Chatbot or live Chat using Facebook Messenger
Version:     3.4-pro-beta-2
Author:      HoliThemes
Author URI:  https://www.holithemes.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-chatbot
*/


if ( ! defined( 'ABSPATH' ) ) exit;


// define HTCC_PLUGIN_FILE
if ( ! defined( 'HTCC_PLUGIN_FILE' ) ) {
	define( 'HTCC_PLUGIN_FILE', __FILE__ );
}

/**
 * if premium set to true
 * and change add suffix to name, version
 * for wp.org - remove the pro folders
 */
if ( ! defined( 'HTCC_PRO' ) ) {
	define( 'HTCC_PRO', 'true' );
}

// include main file
require_once 'inc/class-ht-cc.php';

// create instance for the main file - HT_CCW
function ht_cc() {
	return HT_CC::instance();
}

ht_cc();