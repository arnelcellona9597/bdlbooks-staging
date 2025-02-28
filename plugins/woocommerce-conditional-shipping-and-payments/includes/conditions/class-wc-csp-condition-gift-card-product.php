<?php
/**
 * WC_CSP_Condition_Gift_Card_Product class
 *
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card Product Condition.
 *
 * @class    WC_CSP_Condition_Gift_Card_Product
 * @version  2.0.0
 */
class WC_CSP_Condition_Gift_Card_Product extends WC_CSP_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                             = 'gift_card_product';
		$this->title                          = __( 'Gift Card Product', 'woocommerce-conditional-shipping-and-payments' );
		$this->priority                       = 20;
		$this->supported_global_restrictions  = array( 'payment_gateways' );
		$this->supported_product_restrictions = array( 'payment_gateways' );
	}

	/**
	 * Return condition field-specific resolution message which is combined along with others into a single restriction "resolution message".
	 *
	 * @param  array $data  Condition field data.
	 * @param  array $args  Optional arguments passed by restriction.
	 * @return string|false
	 */
	public function get_condition_resolution( $data, $args ) {

		$cart_contents = WC()->cart->get_cart();

		if ( empty( $cart_contents ) ) {
			return false;
		}

		$message = false;

		if ( $this->modifier_is( $data['modifier'], array( 'in' ) ) ) {
			$message = __( 'remove all gift card products from your cart', 'woocommerce-conditional-shipping-and-payments' );
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-in' ) ) ) {
			$message = __( 'add some gift card products to your cart', 'woocommerce-conditional-shipping-and-payments' );
		} elseif ( $this->modifier_is( $data['modifier'], array( 'all-in' ) ) ) {
			$message = __( 'make sure that your cart doesn\'t contain only gift card products', 'woocommerce-conditional-shipping-and-payments' );
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-all-in' ) ) ) {
			$message = __( 'make sure that your cart contains only gift card products', 'woocommerce-conditional-shipping-and-payments' );
		}

		return $message;
	}

	/**
	 * Evaluate if the condition is in effect or not.
	 *
	 * @param  array $data  Condition field data.
	 * @param  array $args  Optional arguments passed by restriction.
	 * @return boolean
	 */
	public function check_condition( $data, $args ) {

		$contains_giftcard_items = false;
		$all_items_giftcards     = true;

		if ( ! empty( $args['order'] ) ) {

			$order       = $args['order'];
			$order_items = $order->get_items( 'line_item' );

			if ( ! empty( $order_items ) ) {

				foreach ( $order_items as $order_item ) {

					$product = $order_item->get_product();

					if ( $product ) {

						if ( WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {

							$contains_giftcard_items = true;

							if ( $this->modifier_is( $data['modifier'], array( 'in', 'not-in' ) ) ) {
								break;
							}
						} else {

							$all_items_giftcards = false;

							if ( $this->modifier_is( $data['modifier'], array( 'all-in', 'not-all-in' ) ) ) {
								break;
							}
						}
					}
				}
			}
		} else {

			$cart_contents = WC()->cart->get_cart();

			if ( ! empty( $cart_contents ) ) {

				foreach ( $cart_contents as $cart_item_key => $cart_item ) {

					$product = $cart_item['data'];

					if ( WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {

						$contains_giftcard_items = true;

						if ( $this->modifier_is( $data['modifier'], array( 'in', 'not-in' ) ) ) {
							break;
						}
					} else {

						$all_items_giftcards = false;

						if ( $this->modifier_is( $data['modifier'], array( 'all-in', 'not-all-in' ) ) ) {
							break;
						}
					}
				}
			}
		}

		if ( $this->modifier_is( $data['modifier'], array( 'in' ) ) && $contains_giftcard_items ) {
			return true;
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-in' ) ) && ! $contains_giftcard_items ) {
			return true;
		} elseif ( $this->modifier_is( $data['modifier'], array( 'all-in' ) ) && $all_items_giftcards ) {
			return true;
		} elseif ( $this->modifier_is( $data['modifier'], array( 'not-all-in' ) ) && ! $all_items_giftcards ) {
			return true;
		}

		return false;
	}

	/**
	 * Validate, process and return condition fields.
	 *
	 * @param  array $posted_condition_data
	 * @return array
	 */
	public function process_admin_fields( $posted_condition_data ) {

		$processed_condition_data                 = array();
		$processed_condition_data['condition_id'] = $this->id;
		$processed_condition_data['modifier']     = stripslashes( $posted_condition_data['modifier'] );

		return $processed_condition_data;
	}

	/**
	 * Get quantity conditions content for admin product-level restriction metaboxes.
	 *
	 * @param  int   $index
	 * @param  int   $condition_index
	 * @param  array $condition_data
	 * @return str
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ) {

		$modifier = '';

		if ( ! empty( $condition_data['modifier'] ) ) {
			$modifier = $condition_data['modifier'];
		}

		?>
		<input type="hidden" name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][condition_id]" value="<?php echo esc_attr( $this->id ); ?>" />
		<div class="condition_row_inner">
			<div class="condition_modifier">
				<div class="sw-enhanced-select">
					<select name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][modifier]">
						<option value="in" <?php selected( $modifier, 'in', true ); ?>><?php esc_html_e( 'in cart', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option value="not-in" <?php selected( $modifier, 'not-in', true ); ?>><?php esc_html_e( 'not in cart', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option value="all-in" <?php selected( $modifier, 'all-in', true ); ?>><?php esc_html_e( 'all cart items', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<option value="not-all-in" <?php selected( $modifier, 'not-all-in', true ); ?>><?php esc_html_e( 'not all cart items', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
					</select>
				</div>
			</div>
			<div class="condition_value condition--disabled"></div>
		</div>
		<?php
	}
}
