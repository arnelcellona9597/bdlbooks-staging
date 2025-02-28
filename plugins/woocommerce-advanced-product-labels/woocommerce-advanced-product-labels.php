<?php
/**
 * Plugin Name:    WooCommerce Advanced Product Labels
 * Plugin URI:     https://woo.com/products/woocommerce-advanced-product-labels/
 * Description:    Create product labels to increase visibility of your products, add information and increase conversion rate with just a few clicks!
 * Version:        1.3.0
 * Author:         Jeroen Sormani
 * Author URI:     https://jeroensormani.com/
 * Text Domain:    woocommerce-advanced-product-labels
 *
 * WC requires at least: 6.0.0
 * WC tested up to:      8.6
 * Woo: 609121:d3f3fab18b6f605e093a15361e5dd486
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Class Woocommerce_Advanced_Product_Labels
 *
 * Main WAPL class, add filters and handling all other files
 *
 * @class       Woocommerce_Advanced_Product_Labels
 * @version     1.0.0
 * @author      Jeroen Sormani
 */
class Woocommerce_Advanced_Product_Labels {


	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version = '1.3.0';


	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 * @var string $file Plugin file path.
	 */
	public $file = __FILE__;


	/**
	 * Instance of WooCommerce_Advanced_Product_Labels.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var object $instance The instance of WAPL.
	 */
	private static $instance;

	/**
	 * @var WAPL_Admin
	 */
	public $admin;

	/**
	 * @var WAPL_Post_Type
	 */
	public $post_type;

	/**
	 * @var WAPL_Single_Labels
	 */
	public $single_labels;

	/**
	 * @var WAPL_Ajax
	 */
	public $ajax;

	/**
	 * @var WAPL_Global_Labels
	 */
	public $global_labels;

	/**
	 * @var WAPL_Match_Conditions
	 */
	public $matcher;

	/**
	 * @var WAPL_Hook_Check
	 */
	public $hook_check;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 * @return object Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Check if WooCommerce is active
		if ( ! $this->is_woocommerce_active() )return;

		if ( version_compare( PHP_VERSION, '7.0', 'lt' ) ) {
			return add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
		}

		// Autoload
		require __DIR__ . '/vendor/autoload.php';

		// Load style script / admin style script
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'thumbnail_enqueue_scripts' ) );

		if ( is_admin() ) {
			$this->admin = new WAPL_Admin();
		}

		// AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->ajax = new WAPL_Ajax();
		}

		// Setup classes
		$this->post_type     = new WAPL_Post_Type();
		$this->single_labels = new WAPL_Single_Labels();
		$this->global_labels = new WAPL_Global_Labels();
		$this->matcher       = new WAPL_Match_Conditions();
		$this->hook_check    = new WAPL_Hook_Check();

		// Load textdomain
		$this->load_textdomain();

		// Declare HPOS compatibility
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );
	}


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue javascript and stylesheets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_style( 'woocommerce-advanced-product-labels', plugins_url( '/assets/front-end/css/woocommerce-advanced-product-labels.min.css', __FILE__ ), array(), $this->version );

		if ( is_woocommerce() || is_post_type_archive( 'product' ) || is_shop() || is_product() ) {
			wp_enqueue_style( 'woocommerce-advanced-product-labels' );
		}
	}


	/**
	 * Enqueue scripts.
	 *
	 * Double ensure style script is enqueued when outputting products. This can help with
	 * cases where a page is not identified as a WC page.
	 *
	 * @since 1.1.7
	 */
	public function thumbnail_enqueue_scripts() {
		wp_enqueue_style( 'woocommerce-advanced-product-labels' );
	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-advanced-product-labels', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Display PHP 7.0 required notice.
	 *
	 * Display a notice when the required PHP version is not met.
	 *
	 * @since 1.0.6
	 */
	public function php_version_notice() {
		?><div class='updated'>
			<p><?php echo sprintf( __( 'Advanced Product Labels requires PHP 7.0 or higher and your current PHP version is %s. Please (contact your host to) update your PHP version.', 'woocommerce-advanced-product-labels' ), PHP_VERSION ); ?></p>
		</div><?php
	}

	/**
	 * Is WooCommerce active.
	 *
	 * @since 1.3.0
	 *
	 * @return bool True when WooCommerce is active.
	 */
	private function is_woocommerce_active() {
		// Load plugin.php if it's not already loaded.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( \is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}

		return false;
	}
}


if ( ! function_exists( 'WooCommerce_Advanced_Product_Labels' ) ) {

	/**
	 * The main function responsible for returning the Woocommerce_Advanced_Product_Labels object.
	 *
	 * Use this function like you would a global variable, except without needing to declare the global.
	 *
	 * Example: <?php WooCommerce_Advanced_Product_Labels()->method_name(); ?>
	 *
	 * @return object Woocommerce_Advanced_Product_Labels class object.
	 * @since 1.0.0
	 *
	 */
	function WooCommerce_Advanced_Product_Labels() {
		return Woocommerce_Advanced_Product_Labels::instance();
	}

}
WooCommerce_Advanced_Product_Labels()->init();
