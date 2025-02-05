<?php
/**
 * Abstract integration class.
 *
 * @since 4.1.0
 */

namespace KoiLab\WC_Newsletter_Subscription\Internals\Blocks;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Blocks integration class.
 */
abstract class Integration implements IntegrationInterface {

	/**
	 * Integration name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Frontend scripts.
	 *
	 * @var array
	 */
	protected $scripts = array();

	/**
	 * Editor scripts.
	 *
	 * @var array
	 */
	protected $editor_scripts = array();

	/**
	 * Gets the integration name.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Gets an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles(): array {
		return $this->scripts;
	}

	/**
	 * Gets an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles(): array {
		return $this->editor_scripts;
	}

	/**
	 * Registers a script.
	 *
	 * @since 4.1.0
	 *
	 * @param string $handle      Script handle.
	 * @param string $script_file Script file.
	 * @param string $context     Optional. Script context. Accepts 'both', 'editor', or 'frontend'. Default 'both'.
	 */
	protected function register_script( string $handle, string $script_file, string $context = 'both' ) {
		$script_asset_file = WC_NEWSLETTER_SUBSCRIPTION_PATH . str_replace( '.js', '.asset.php', $script_file );
		$script_asset      = file_exists( $script_asset_file )
			? require $script_asset_file
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_file ),
			);

		wp_register_script(
			$handle,
			WC_NEWSLETTER_SUBSCRIPTION_URL . $script_file,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			$handle,
			'woocommerce-aweber-newsletter-subscription',
			WC_NEWSLETTER_SUBSCRIPTION_PATH . 'languages'
		);

		$this->enqueue_script( $handle, $context );
	}

	/**
	 * Enqueues the script in the given context.
	 *
	 * @since 4.1.0
	 *
	 * @param string $handle  Script handle.
	 * @param string $context Script context. Accepts 'both', 'editor', or 'frontend'. Default 'both'.
	 */
	protected function enqueue_script( string $handle, string $context = '' ) {
		if ( 'editor' === $context || 'both' === $context ) {
			$this->editor_scripts[] = $handle;
		}

		if ( 'frontend' === $context || 'both' === $context ) {
			$this->scripts[] = $handle;
		}
	}

	/**
	 * Gets the file version.
	 *
	 * @since 4.1.0
	 *
	 * @param string $file Local path to the file.
	 * @return string
	 */
	protected function get_file_version( string $file ): string {
		// Use the modified time in dev mode.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return WC_NEWSLETTER_SUBSCRIPTION_VERSION;
	}
}
