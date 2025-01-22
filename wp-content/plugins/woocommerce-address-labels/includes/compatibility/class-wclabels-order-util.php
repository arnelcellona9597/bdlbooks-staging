<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPO_WCLABEL_Order_Util' ) ) :

class WPO_WCLABEL_Order_Util {

	/**
	 * @var WPO_WCLABEL_Order_Util
	 */
	protected static $_instance = null;

	/**
	 * @var OrderUtil|false
	 */
	public $wc_order_util_class_object;

	/**
	 * Construct.
	 */
	public function __construct() {
		$this->wc_order_util_class_object = $this->get_wc_order_util_class();
	}

	/**
	 * Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Function to check woocommerce OrderUtil class is exists or not.
	 */
	public function get_wc_order_util_class() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return \Automattic\WooCommerce\Utilities\OrderUtil::class;
		}

		return false;
	}

	/**
	 * Function to get order type.
	 *
	 * @param int $order_id
	 *
	 * @return false|string
	 */
	public function get_order_type( $order_id ) {
		if ( $this->wc_order_util_class_object && is_callable( array( $this->wc_order_util_class_object, 'get_order_type' ) ) ) {
			return $this->wc_order_util_class_object::get_order_type( intval( $order_id ) );
		}

		return get_post_type( intval( $order_id ) );
	}

	/**
	 * Function to check id HPOS functionality is enabled or not.
	 */
	public function custom_orders_table_usage_is_enabled() {
		if ( $this->wc_order_util_class_object && is_callable( array( $this->wc_order_util_class_object, 'custom_orders_table_usage_is_enabled' ) ) ) {
			return $this->wc_order_util_class_object::custom_orders_table_usage_is_enabled();
		}

		return false;
	}

	/**
	 * Function to check screen.
	 */
	public function custom_order_table_screen() {
		return $this->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
	}

} // end class

endif;

return new WPO_WCLABEL_Order_Util();
