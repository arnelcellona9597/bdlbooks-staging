<?php
/**
 * Plugin Name: Subscribe to Newsletter for WooCommerce
 * Plugin URI: https://woo.com/products/newsletter-subscription/
 * Description: Allow users to subscribe to your newsletter during checkout, when registering on your site, or via a sidebar widget.
 * Version: 4.1.1
 * Author: Kestrel
 * Author URI: https://kestrelwp.com
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Text Domain: woocommerce-subscribe-to-newsletter
 * Domain Path: /languages/
 *
 * WC requires at least: 4.0
 * WC tested up to: 8.5
 * Woo: 18605:9b4ddf6c5bcc84c116ede70d840805fe
 *
 * Copyright: (c) 2011-2024 Kestrel [hey@kestrelwp.com]
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WC_Newsletter_Subscription
 * @since   2.3.5
 */

defined( 'ABSPATH' ) || exit;

// Load the class autoloader.
require __DIR__ . '/src/Autoloader.php';

if ( ! \KoiLab\WC_Newsletter_Subscription\Autoloader::init() ) {
	return;
}

/**
 * Plugin requirements.
 */
if ( ! class_exists( 'WC_Newsletter_Subscription_Requirements', false ) ) {
	require_once __DIR__ . '/includes/class-wc-newsletter-subscription-requirements.php';
}

if ( ! WC_Newsletter_Subscription_Requirements::are_satisfied() ) {
	return;
}

// Define plugin file constant.
if ( ! defined( 'WC_NEWSLETTER_SUBSCRIPTION_FILE' ) ) {
	define( 'WC_NEWSLETTER_SUBSCRIPTION_FILE', __FILE__ );
}

// Include the main plugin class.
if ( ! class_exists( 'WC_Subscribe_To_Newsletter' ) ) {
	include_once dirname( WC_NEWSLETTER_SUBSCRIPTION_FILE ) . '/includes/class-wc-subscribe-to-newsletter.php';
}

// Global for backwards compatibility.
$GLOBALS['WC_Subscribe_To_Newsletter'] = WC_Subscribe_To_Newsletter::instance();
