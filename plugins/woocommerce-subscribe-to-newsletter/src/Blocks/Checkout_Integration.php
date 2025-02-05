<?php
/**
 * Checkout block integration.
 *
 * @since 4.1.0
 */

namespace KoiLab\WC_Newsletter_Subscription\Blocks;

defined( 'ABSPATH' ) || exit;

use KoiLab\WC_Newsletter_Subscription\Internals\Blocks\Integration;

/**
 * Blocks integration class.
 */
class Checkout_Integration extends Integration {

	/**
	 * Integration name.
	 *
	 * @var string
	 */
	protected $name = 'wc-newsletter-subscription-checkout';

	/**
	 * Initializes the integration.
	 *
	 * @since 4.1.0
	 */
	public function initialize() {
		$block_path = 'assets/blocks/checkout/';

		$this->register_script( 'wc-newsletter-subscription-checkout-block', $block_path . 'index.js' );
		$this->register_script( 'wc-newsletter-subscription-checkout-block-view', $block_path . 'view.js', 'frontend' );
	}

	/**
	 * Gets an array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data(): array {
		return array(
			'display' => wc_newsletter_subscription_provider_has_list(),
			'checked' => ( 'checked' === get_option( 'woocommerce_newsletter_checkbox_status' ) ),
			'label'   => wc_newsletter_subscription_get_checkbox_label(),
		);
	}
}
