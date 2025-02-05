<?php
/**
 * Includes the composer Autoloader used for packages and classes in the src/ directory.
 *
 * @since 4.1.0
 */

namespace KoiLab\WC_Newsletter_Subscription;

defined( 'ABSPATH' ) || exit;

/**
 * Autoloader class.
 */
class Autoloader {

	/**
	 * Initializes the autoloader.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	public static function init(): bool {
		$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';

		return ( is_readable( $autoloader ) && ( require $autoloader ) );
	}
}
