<?php
/**
 * WC_CSP_Core_Compatibility class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions related to core back-compatibility.
 *
 * @class    WC_CSP_Core_Compatibility
 * @version  2.0.0
 */
class WC_CSP_Core_Compatibility {

	/**
	 * Cache 'gte' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Cache 'gt' comparison results for WP version.
	 *
	 * @since  1.5.9
	 * @var    array
	 */
	private static $is_wp_version_gt = array();

	/**
	 * Cache 'gte' comparison results for WP version.
	 *
	 * @since  1.5.9
	 * @var    array
	 */
	private static $is_wp_version_gte = array();

	/**
	 * Cache block based checkout detection result.
	 *
	 * @since  1.13.0
	 * @var    array
	 */
	private static $is_block_based_checkout = null;

	/**
	 * Current REST request stack.
	 * An array containing WP_REST_Request instances.
	 *
	 * @since 1.15.8
	 *
	 * @var array
	 */
	private static $requests = array();

	/**
	 * Initialization and hooks.
	 */
	public static function init() {

		// Save current rest request. Is there a better way to get it?
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'save_rest_request' ), 10, 3 );
		add_filter( 'woocommerce_hydration_dispatch_request', array( __CLASS__, 'save_hydration_request' ), 10, 2 );
		add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'pop_rest_request' ), PHP_INT_MAX );
		add_filter( 'woocommerce_hydration_request_after_callbacks', array( __CLASS__, 'pop_rest_request' ), PHP_INT_MAX );
	}

	/*
	|--------------------------------------------------------------------------
	| Callbacks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Pops the current request from the execution stack.
	 *
	 * @since  1.15.8
	 *
	 * @param  WP_REST_Response     $response
	 * @param  WP_REST_Server|array $handler
	 * @param  WP_REST_Request      $request
	 * @return mixed
	 */
	public static function pop_rest_request( $response ) {
		if ( ! empty( self::$requests ) && is_array( self::$requests ) ) {
			array_pop( self::$requests );
		}

		return $response;
	}

	/**
	 * Saves the current hydration request.
	 *
	 * @since  1.15.8
	 *
	 * @param  mixed           $result
	 * @param  WP_REST_Request $request
	 * @return mixed
	 */
	public static function save_hydration_request( $result, $request ) {
		if ( ! is_array( self::$requests ) ) {
			self::$requests = array();
		}

		self::$requests[] = $request;
		return $result;
	}

	/**
	 * Saves the current rest request.
	 *
	 * @since  1.13.0
	 *
	 * @param  mixed           $result
	 * @param  WP_REST_Server  $server
	 * @param  WP_REST_Request $request
	 * @return mixed
	 */
	public static function save_rest_request( $result, $server, $request ) {
		if ( ! is_array( self::$requests ) ) {
			self::$requests = array();
		}

		self::$requests[] = $request;
		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| WC version handling.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @since  1.0.4
	 *
	 * @return string
	 */
	public static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  1.2.5
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @since  1.0.4
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is lower than or equal $version.
	 *
	 * @since  1.4.0
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_lte( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<=' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is lower than $version.
	 *
	 * @since  1.4.0
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_lt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  1.5.9
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wp_version_gt( $version ) {
		if ( ! isset( self::$is_wp_version_gt[ $version ] ) ) {
			global $wp_version;
			self::$is_wp_version_gt[ $version ] = $wp_version && version_compare( WC_CSP()->plugin_version( true, $wp_version ), $version, '>' );
		}
		return self::$is_wp_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  1.5.9
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wp_version_gte( $version ) {
		if ( ! isset( self::$is_wp_version_gte[ $version ] ) ) {
			global $wp_version;
			self::$is_wp_version_gte[ $version ] = $wp_version && version_compare( WC_CSP()->plugin_version( true, $wp_version ), $version, '>=' );
		}
		return self::$is_wp_version_gte[ $version ];
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Back-compat wrapper for 'get_parent_id' with fallback to 'get_id'.
	 *
	 * @since  1.2.5
	 *
	 * @param  WC_Product $product
	 * @return mixed
	 */
	public static function get_product_id( $product ) {
		$parent_id = $product->get_parent_id();
		return $parent_id ? $parent_id : $product->get_id();
	}

	/**
	 * Return product title with attributes -- if variation.
	 *
	 * @since  1.5.8
	 *
	 * @param  WC_Product_Variation|int $product
	 *
	 * @return string
	 */
	public static function get_name( $product ) {

		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product ) {
			return false;
		}

		$title = $product->get_title();
		$name  = $title;

		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			$description = wc_get_formatted_variation( $product, true );
			$name        = sprintf( _x( '%1$s &ndash; %2$s', 'variation title followed by attributes', 'woocommerce-conditional-shipping-and-payments' ), $title, $description );
		}

		return $name;
	}

	/**
	 * Back-compat wrapper for 'is_rest_api_request'.
	 *
	 * @since  1.13.0
	 *
	 * @return boolean
	 */
	public static function is_rest_api_request() {

		if ( false !== self::get_api_request() ) {
			return true;
		}

		return method_exists( WC(), 'is_rest_api_request' ) ? WC()->is_rest_api_request() : defined( 'REST_REQUEST' );
	}

	/*
	|--------------------------------------------------------------------------
	| Utilities.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Whether this is a Store/REST API request.
	 *
	 * @since  1.13.0
	 *
	 * @return boolean
	 */
	public static function is_api_request() {
		return self::is_store_api_request() || self::is_rest_api_request();
	}

	/**
	 * Returns the current Store/REST API request or false.
	 *
	 * @since  1.13.0
	 *
	 * @return WP_REST_Request|false
	 */
	public static function get_api_request() {
		if ( empty( self::$requests ) || ! is_array( self::$requests ) ) {
			return false;
		}

		return end( self::$requests );
	}

	/**
	 * Whether this is a Store API request.
	 *
	 * @since  1.13.0
	 *
	 * @param  string $route
	 * @return boolean
	 */
	public static function is_store_api_request( $route = '', $method = '' ) {

		// Check the request URI.
		$request = self::get_api_request();

		if ( false !== $request && strpos( $request->get_route(), 'wc/store' ) !== false ) {

			$check_route  = ! empty( $route );
			$check_method = ! empty( $method );

			if ( ! $check_route && ! $check_method ) {
				// Generic store api question.
				return true;
			}

			$route_result  = ! $check_route || strpos( $request->get_route(), $route ) !== false;
			$method_result = ! $check_method || strtolower( $request->get_method() ) === strtolower( $method );

			return $route_result && $method_result;
		}

		return false;
	}

	/**
	 * Whether the checkout page contains the checkout block.
	 *
	 * @since  1.13.0
	 *
	 * @param  string $route
	 * @return boolean
	 */
	public static function is_block_based_checkout() {

		if ( ! WC_CSP_Compatibility::is_module_loaded( 'blocks' ) ) {
			return false;
		}

		if ( is_null( self::$is_block_based_checkout ) ) {

			self::$is_block_based_checkout = false;

			$checkout_block_data = class_exists( 'WC_Blocks_Utils' ) ? WC_Blocks_Utils::get_blocks_from_page( 'woocommerce/checkout', 'checkout' ) : false;

			if ( ! empty( $checkout_block_data ) ) {
				self::$is_block_based_checkout = true;
			}
		}

		return self::$is_block_based_checkout;
	}
}

WC_CSP_Core_Compatibility::init();
