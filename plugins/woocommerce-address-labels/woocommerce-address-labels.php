<?php
/**
 * Plugin Name:          WooCommerce Print Address Labels
 * Plugin URI:           https://wpovernight.com/downloads/woocommerce-print-address-labels/
 * Description:          Print out address labels for selected orders straight from WooCommerce
 * Version:              2.0.8
 * Author:               WP Overnight
 * Author URI:           https://wpovernight.com
 * License:              GPLv2 or later
 * License URI:          https://opensource.org/licenses/gpl-license.php
 * Text Domain:          wpo_wclabels
 * Domain Path:          /languages
 * WC requires at least: 3.0
 * WC tested up to:      9.0
 */

if ( ! class_exists( 'WooCommerce_Address_Labels' ) ) {

	class WooCommerce_Address_Labels {
		/**
		 * @var string
		 */
		public $version = '2.0.8';

		/**
		 * @var WPO_Updater
		 */
		private $updater = null;

		/**
		 * @var string
		 */
		public $plugin_basename;

		/**
		 * @var WooCommerce_Address_Labels_Writepanels
		 */
		public $writepanels;

		/**
		 * @var WooCommerce_Address_Labels_Settings
		 */
		public $settings;

		/**
		 * @var WooCommerce_Address_Labels_Print
		 */
		public $print;

		/**
		 * @var WPO_WCLABEL_Order_Util
		 */
		public $order_util;

		/**
		 * @var WooCommerce_Address_Labels
		 */
		protected static $_instance = null;

		/**
		 * Main Plugin Instance
		 *
		 * Ensures only one instance of plugin is loaded or can be loaded.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->plugin_basename = plugin_basename( __FILE__ );

			$this->define( 'WPO_WCLABELS_VERSION', $this->version );

			// load the localisation & classes
			add_action( 'plugins_loaded', array( $this, 'translations' ) ); // or use init?
			add_action( 'init', array( $this, 'load_classes' ) );

			add_action( 'init', array( $this, 'load_updater' ), 0 );

			// run lifecycle methods
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				// check if upgrading from versionless (1.3.1 and older)
				if ( get_option( 'wpo_wclabels_template_settings' ) && ! get_option( 'wpo_wclabels_version' ) ) {
					// tag 'versionless', so that we can apply necessary upgrade settings
					add_option( 'wpo_wclabels_version', 'versionless' );
				}

				add_action( 'wp_loaded', array( $this, 'do_install' ) );
			}

			// HPOS compatibility
			add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatible' ) );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Run the updater scripts from the Sidekick
		 * @return void
		 */
		public function load_updater() {
			// Init updater data
			$item_name    = 'WooCommerce Print Address Labels';
			$file         = __FILE__;
			$license_slug = 'wclabels_license';
			$version      = WPO_WCLABELS_VERSION;
			$author       = 'WP Overnight';

			// load updater
			if ( class_exists( 'WPO_Updater' ) ) { // WP Overnight Sidekick plugin
				$this->updater = new WPO_Updater( $item_name, $file, $license_slug, $version, $author );
			} else { // bundled updater
				$updater_helper_file = $this->plugin_path() . '/updater/update-helper.php';

				if ( ! class_exists( 'WPO_Update_Helper' ) && file_exists( $updater_helper_file ) ) {
					include_once $updater_helper_file;
				}

				if ( class_exists( 'WPO_Update_Helper' ) ) {
					$this->updater = new WPO_Update_Helper( $item_name, $file, $license_slug, $version, $author );
				}
			}

			// if no Sidekick and no license, show notice in plugin settings page
			if ( is_callable( array( $this->updater, 'license_is_active' ) ) && ! $this->updater->license_is_active() ) {
				add_action( 'wpo_wclabels_before_settings_tab_content', array( $this, 'no_active_license_message' ), 1, 1 );
			}
		}

		public function no_active_license_message( $current_tab ) {
			if( class_exists('WPO_Updater') ) {
				$activation_url = esc_url_raw( network_admin_url( 'admin.php?page=wpo-license-page' ) );
			} else {
				$activation_url = esc_url_raw( network_admin_url( 'plugins.php?s=WooCommerce+Print+Address+Labels' ) );
			}
			?>
			<div class="notice notice-warning inline">
				<p>
					<?php printf(
						/* translators: click here*/
						__( "Your license has not been activated on this site, %s to enter your license key.", 'wpo_wclabels' ), '<a href="'.$activation_url.'">'.__( 'click here', 'wpo_wclabels' ).'</a>' );
						?>
				</p>
			</div>
			<?php
		}

		/**
		 * Load the translation / textdomain files
		 */
		public function translations() {
			load_plugin_textdomain( 'wpo_wclabels', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
		}

		/**
		 * Load the main plugin classes and functions
		 */
		public function includes() {
			include_once 'includes/wclabels-settings.php';
			include_once 'includes/wclabels-writepanels.php';
			include_once 'includes/wclabels-print.php';

			$this->order_util = include_once 'includes/compatibility/class-wclabels-order-util.php';
		}


		/**
		 * Instantiate classes when woocommerce is activated
		 */
		public function load_classes() {
			if ( $this->is_woocommerce_activated() === false ) {
				add_action( 'admin_notices', array( $this, 'need_woocommerce' ) );

				return;
			}

			if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
				add_action( 'admin_notices', array( $this, 'required_php_version' ) );

				return;
			}

			// all systems ready - GO!
			$this->includes();
			$this->settings    = new WooCommerce_Address_Labels_Settings();
			$this->writepanels = new WooCommerce_Address_Labels_Writepanels();
			$this->print       = new WooCommerce_Address_Labels_Print();
		}

		/**
		 * Check if woocommerce is activated
		 */
		public function is_woocommerce_activated() {
			$blog_plugins = get_option( 'active_plugins', array() );
			$site_plugins = get_site_option( 'active_sitewide_plugins', array() );

			if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * WooCommerce not active notice.
		 *
		 * @return string Fallack notice.
		 */
		 
		public function need_woocommerce() {
			/* translators: <a> tags */
			$error = sprintf( __( 'WooCommerce Address Labels requires %1$sWooCommerce%2$s to be installed & activated!' , 'wpo_wclabels' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );

			$message = '<div class="error"><p>' . $error . '</p></div>';
		
			echo $message;
		}
		
		/**
		 * Declares WooCommerce HPOS compatibility.
		 *
		 * @return void
		 */
		public function woocommerce_hpos_compatible() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
		
		/**
		 * PHP version requirement notice
		 */
		
		public function required_php_version() {
			$error = __( 'WooCommerce Address Labels requires PHP 5.3 or higher (5.6 or higher recommended).', 'wpo_wcsre' );
			$how_to_update = __( 'How to update your PHP version', 'wpo_wcsre' );
			$message = sprintf('<div class="error"><p>%s</p><p><a href="%s">%s</a></p></div>', $error, 'http://docs.wpovernight.com/general/how-to-update-your-php-version/', $how_to_update);
		
			echo $message;
		}

		/** Lifecycle methods *******************************************************
		 * Because register_activation_hook only runs when the plugin is manually
		 * activated by the user, we're checking the current version against the
		 * version stored in the database
		****************************************************************************/

		/**
		 * Handles version checking
		 */
		public function do_install() {
			$version_setting = 'wpo_wclabels_version';
			$installed_version = get_option( $version_setting );

			// installed version lower than plugin version?
			if ( $installed_version == 'versionless' || version_compare( $installed_version, $this->version, '<' ) ) {

				if ( ! $installed_version ) {
					$this->install();
				} else {
					$this->upgrade( $installed_version );
				}

				// new version number
				update_option( $version_setting, $this->version );
			}
		}


		/**
		 * Plugin install method. Perform any installation tasks here
		 */
		protected function install() {
			// stub
		}

		/**
		 * Plugin upgrade method.  Perform any required upgrades here
		 *
		 * @param string $installed_version the currently installed version
		 */
		protected function upgrade( $installed_version ) {
			// 1.4.0 split settings / copy old settings to new
			if ( version_compare( $installed_version, '1.4.0', '<' ) ) {
				$old_settings = get_option( 'wpo_wclabels_template_settings' );

				// get list of new setting => old setting
				$settings_reference = array(
					'wpo_wclabels_interface_settings'	=> array(
						'preview'			=> 'preview',
						'offset'			=> 'offset',
					),
					'wpo_wclabels_layout_settings'		=> array(
						'paper_size'		=> 'paper_size',
						'custom_paper_size'	=> 'custom_paper_size',
						'paper_orientation'	=> 'paper_orientation',
						'cols'				=> 'cols',
						'rows'				=> 'rows',
						'custom_styles'		=> 'custom_styles',
					),
					'wpo_wclabels_label_settings'		=> array(
						'address_data'		=> 'address_data',
						'remove_whitespace'	=> 'remove_whitespace',
						'block_width'		=> 'block_width',
						'font_size'			=> 'font_size',
						'remove_whitespace'	=> 'remove_whitespace',
					),
				);

				// iterate over $settings_reference to convert settings
				foreach ($settings_reference as $option_name => $reference) {
					$new_settings = array();
					foreach ($reference as $new_setting => $old_setting) {
						if (isset($old_settings[$old_setting])) {
							$new_settings[$new_setting] = $old_settings[$old_setting];
						}
					}
					if (!empty($new_settings)) {
						update_option( $option_name, $new_settings );
					}
				}
				
			}
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}		
	}
		
	/**
	 * Returns the main plugin instance to prevent the need to use globals.
	 *
	 * @since  2.0
	 * @return WooCommerce_Address_Labels
	 */
	function WPO_WCLABELS() {
		return WooCommerce_Address_Labels::instance();
	}

	WPO_WCLABELS(); // load plugin
	$wpo_wclabels = WPO_WCLABELS(); // for backwards compatibility

}