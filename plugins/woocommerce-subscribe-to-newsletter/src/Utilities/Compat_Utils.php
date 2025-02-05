<?php
/**
 * Compatibility utilities.
 *
 * @since 4.1.0
 */

namespace KoiLab\WC_Newsletter_Subscription\Utilities;

/**
 * Class Compat_Utils.
 */
class Compat_Utils {

	/**
	 * Checks if the default checkout page is using the Checkout block.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	public static function is_checkout_block_default() {
		if ( class_exists( '\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils' ) ) {
			return \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
		}

		return false;
	}
}
