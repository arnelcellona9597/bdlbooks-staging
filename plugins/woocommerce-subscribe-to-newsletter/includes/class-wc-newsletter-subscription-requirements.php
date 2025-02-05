<?php
/**
 * WooCommerce Newsletter Subscription requirements
 *
 * @package WC_Newsletter_Subscription
 * @since   3.0.0
 */

/**
 * Class WC_Newsletter_Subscription_Requirements
 */
class WC_Newsletter_Subscription_Requirements {

	/**
	 * Minimum PHP version required.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Minimum WordPress version required.
	 */
	const MINIMUM_WP_VERSION = '5.0';

	/**
	 * Minimum WooCommerce version required.
	 */
	const MINIMUM_WC_VERSION = '4.0';

	/**
	 * Requirements errors.
	 *
	 * @var array
	 */
	private static $errors = array();

	/**
	 * Init.
	 *
	 * @since 3.0.0
	 */
	public static function init() {
		self::check_requirements();

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
	}

	/**
	 * Checks the plugin requirements.
	 *
	 * @since 3.0.0
	 */
	protected static function check_requirements() {
		if ( ! self::is_php_compatible() ) {
			self::$errors[] = sprintf(
				/* translators: 1: Minimum PHP version 2: Current PHP version */
				_x( '<strong>WooCommerce Subscribe to Newsletter</strong> requires PHP %1$s or higher. You are using version %2$s', 'admin notice', 'woocommerce-subscribe-to-newsletter' ),
				self::MINIMUM_PHP_VERSION,
				PHP_VERSION
			);
		} elseif ( ! self::is_wp_compatible() ) {
			self::$errors[] = sprintf(
				/* translators: 1: Minimum WordPress version 2: Current WordPress version */
				_x( '<strong>WooCommerce Subscribe to Newsletter</strong> requires WordPress %1$s or higher. You are using version %2$s', 'admin notice', 'woocommerce-subscribe-to-newsletter' ),
				self::MINIMUM_WP_VERSION,
				get_bloginfo( 'version' )
			);
		} elseif ( ! self::is_wc_active() ) {
			self::$errors[] = _x( '<strong>WooCommerce Subscribe to Newsletter</strong> requires WooCommerce to be activated to work.', 'admin notice', 'woocommerce-subscribe-to-newsletter' );
		} elseif ( ! self::is_wc_compatible() ) {
			self::$errors[] = sprintf(
				/* translators: 1: Minimum WooCommerce version 2: Current WooCommerce version */
				_x( '<strong>WooCommerce Subscribe to Newsletter</strong> requires WooCommerce %1$s or higher. You are using version %2$s', 'admin notice', 'woocommerce-subscribe-to-newsletter' ),
				self::MINIMUM_WC_VERSION,
				get_option( 'woocommerce_db_version' )
			);
		}
	}

	/**
	 * Gets if the minimum PHP version requirement is satisfied.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_php_compatible() {
		return ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' ) );
	}

	/**
	 * Gets if the minimum WordPress version requirement is satisfied.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_wp_compatible() {
		return ( version_compare( get_bloginfo( 'version' ), self::MINIMUM_WP_VERSION, '>=' ) );
	}

	/**
	 * Gets if the minimum WooCommerce version requirement is satisfied.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_wc_compatible() {
		return ( version_compare( get_option( 'woocommerce_db_version' ), self::MINIMUM_WC_VERSION, '>=' ) );
	}

	/**
	 * Gets if the WooCommerce plugin is active.
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	public static function is_wc_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Outputs the plugin requirements errors.
	 *
	 * @since 3.0.0
	 */
	public static function admin_notices() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		foreach ( self::$errors as $error ) {
			printf( '<div class="error"><p>%s</p></div>', wp_kses_post( $error ) );
		}
	}

	/**
	 * Gets if the plugin requirements are satisfied.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function are_satisfied() {
		return empty( self::$errors );
	}
}

WC_Newsletter_Subscription_Requirements::init();
