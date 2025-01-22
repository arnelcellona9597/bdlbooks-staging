<?php
/**
 * Blocks handler.
 *
 * @since 4.1.0
 */

namespace KoiLab\WC_Newsletter_Subscription\Blocks;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

/**
 * Blocks class.
 */
class Blocks {

	/**
	 * Init.
	 *
	 * @since 4.1.0
	 */
	public static function init() {
		add_action( 'woocommerce_blocks_checkout_block_registration', array( __CLASS__, 'register_checkout_blocks' ) );
		add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'blocks_loaded' ) );
	}

	/**
	 * Registers checkout blocks.
	 *
	 * @since 4.1.0
	 *
	 * @param IntegrationRegistry $integration_registry Integration registry instance.
	 */
	public static function register_checkout_blocks( IntegrationRegistry $integration_registry ) {
		$integration_registry->register( new Checkout_Integration() );
	}

	/**
	 * On blocks loaded.
	 *
	 * @since 4.1.0
	 */
	public static function blocks_loaded() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => 'wc-newsletter-subscription',
				'schema_callback' => function () {
					return array(
						'subscribe_to_newsletter' => array(
							'description' => __( 'Subscribe to the newsletter', 'woocommerce-subscribe-to-newsletter' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
						),
					);
				},
			)
		);
	}
}
