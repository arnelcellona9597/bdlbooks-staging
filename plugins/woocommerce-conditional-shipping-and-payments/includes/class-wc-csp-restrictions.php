<?php
/**
 * WC_CSP_Restrictions class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrictions.
 *
 * Loads restriction classes via hooks and prepares them for use.
 *
 * @class    WC_CSP_Restrictions
 * @version  2.0.0
 */
class WC_CSP_Restrictions {

	/** @var array Array of registered restriction classes. */
	public $restrictions;

	public function __construct() {

		$load_restrictions = apply_filters(
			'woocommerce_csp_restrictions',
			array(
				'WC_CSP_Restrict_Payment_Gateways',     // Restrict payment gateways based on product constraints.
				'WC_CSP_Restrict_Shipping_Methods',     // Restrict shipping methods based on product constraints.
				'WC_CSP_Restrict_Shipping_Countries',   // Restrict shipping countries based on product constraints.
			)
		);

		// Load cart restrictions.
		foreach ( $load_restrictions as $restriction ) {

			$restriction = new $restriction();

			$this->restrictions[ $restriction->id ] = $restriction;
		}

		// Validate add-to-cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 10, 6 );

		// Validate cart.
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_cart' ), 10 );

		// Validate cart update.
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'validate_cart_update' ), 10, 4 );

		// Validate checkout.
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout' ), 100 );

		// Update order details when changing the billing e-mail.
		add_action( 'woocommerce_billing_fields', array( $this, 'maybe_update_totals_on_billing_email_change' ), 100 );
	}

	/**
	 * Modify checkout field data to update order details when changing the 'billing email' field and a global 'customer' condition exists.
	 *
	 * @since  1.4.0
	 * @param  array $billing_field_data
	 * @return array
	 */
	public function maybe_update_totals_on_billing_email_change( $billing_field_data ) {

		if ( isset( $billing_field_data['billing_email'] ) ) {
			if ( WC_CSP()->conditions->is_active( 'customer' ) ) {
				$billing_field_data['billing_email']['class'][] = 'update_totals_on_change';
			}
		}

		return $billing_field_data;
	}

	/**
	 * Get restriction class by restriction_id.
	 *
	 * @param  string $restriction_id
	 * @return WC_CSP_Restriction
	 */
	public function get_restriction( $restriction_id ) {

		if ( ! empty( $this->restrictions[ $restriction_id ] ) ) {
			return $this->restrictions[ $restriction_id ];
		}

		return false;
	}

	/**
	 * Get all registered restrictions by supported validation type.
	 *
	 * @param  string $validation_type
	 * @return array
	 */
	public function get_restrictions( $validation_type = '' ) {

		$restrictions = array();

		foreach ( $this->restrictions as $id => $restriction ) {
			if ( $validation_type === '' || in_array( $validation_type, $restriction->get_validation_types() ) ) {
				$restrictions[ $id ] = $restriction;
			}
		}

		return apply_filters( 'woocommerce_csp_get_restrictions', $restrictions, $validation_type );
	}

	/**
	 * Get all registered restrictions that have admin product metabox options.
	 *
	 * @return array
	 */
	public function get_admin_product_field_restrictions() {

		$restriction_titles = array();

		foreach ( $this->restrictions as $id => $restriction ) {
			if ( $restriction->has_admin_product_fields() ) {
				$restriction_titles[ $id ] = $restriction;
			}
		}

		return apply_filters( 'woocommerce_csp_get_admin_product_field_restrictions', $restriction_titles );
	}

	/**
	 * Get all registered restrictions that have global settings.
	 *
	 * @return array
	 */
	public function get_admin_global_field_restrictions() {

		$restriction_titles = array();

		foreach ( $this->restrictions as $id => $restriction ) {
			if ( $restriction->has_admin_global_fields() ) {
				$restriction_titles[ $id ] = $restriction;
			}
		}

		return apply_filters( 'woocommerce_csp_get_admin_global_field_restrictions', $restriction_titles );
	}

	/**
	 * Add-to-cart validation ('woocommerce_add_to_cart_validation' filter) for all restrictions that implement the 'WC_CSP_Add_To_Cart_Restriction' interface.
	 *
	 * @param  bool   $add
	 * @param  int    $product_id
	 * @param  int    $product_quantity
	 * @param  string $variation_id
	 * @param  array  $variations
	 * @param  array  $cart_item_data
	 * @return bool
	 */
	public function validate_add_to_cart( $add, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		$add_to_cart_restrictions = $this->get_restrictions( 'add-to-cart' );

		if ( ! empty( $add_to_cart_restrictions ) ) {

			foreach ( $add_to_cart_restrictions as $restriction ) {

				$result = $restriction->validate_add_to_cart();

				if ( $result->has_messages() ) {

					foreach ( $result->get_messages() as $message ) {
						wc_add_notice( $message['text'], $message['type'] );
					}

					$add = false;
				}
			}
		}

		return $add;
	}


	/**
	 * Cart validation ('check_cart_items' action) for all restrictions that implement the 'WC_CSP_Cart_Restriction' interface.
	 *
	 * @return void
	 */
	public function validate_cart() {

		$cart_restrictions = $this->get_restrictions( 'cart' );

		if ( ! empty( $cart_restrictions ) ) {

			foreach ( $cart_restrictions as $restriction ) {

				$result = $restriction->validate_cart();

				if ( $result->has_messages() ) {

					foreach ( $result->get_messages() as $message ) {
						wc_add_notice( $message['text'], $message['type'] );
					}
				}
			}
		}
	}

	/**
	 * Update cart validation ('update_cart_validation' filter) for all restrictions that implement the 'WC_CSP_Update_Cart_Restriction' interface.
	 *
	 * @param  bool $passed
	 * @param  str  $cart_item_key
	 * @param  str  $cart_item_values
	 * @param  int  $quantity
	 * @return bool
	 */
	public function validate_cart_update( $passed, $cart_item_key, $cart_item_values, $quantity ) {

		$cart_update_restrictions = $this->get_restrictions( 'cart-update' );

		if ( ! empty( $cart_update_restrictions ) ) {

			foreach ( $cart_update_restrictions as $restriction ) {

				$result = $restriction->validate_cart_update( $passed, $cart_item_key, $cart_item_values, $quantity );

				if ( $result->has_messages() ) {

					foreach ( $result->get_messages() as $message ) {
						wc_add_notice( $message['text'], $message['type'] );
					}

					$passed = false;
				}
			}
		}

		return $passed;
	}

	/**
	 * Checkout validation ('woocommerce_after_checkout_validation' filter) for all restrictions that implement the 'WC_CSP_Checkout_Restriction' interface.
	 *
	 * @param  array $posted
	 * @return void
	 */
	public function validate_checkout( $posted ) {

		$checkout_restrictions = $this->get_restrictions( 'checkout' );

		if ( ! empty( $checkout_restrictions ) ) {

			foreach ( $checkout_restrictions as $restriction ) {

				$result = $restriction->validate_checkout( $posted );

				if ( $result->has_messages() ) {

					foreach ( $result->get_messages() as $message ) {
						wc_add_notice( $message['text'], $message['type'] );
					}
				}
			}
		}
	}
}
