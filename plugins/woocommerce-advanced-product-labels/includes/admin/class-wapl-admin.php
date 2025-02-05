<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin class.
 *
 * Handle all admin related functions.
 *
 * @author     	Jeroen Sormani
 * @version		1.0.0
 */
class WAPL_Admin {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add settings page
		add_action( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Initialize class
		add_action( 'admin_init', array( $this, 'init' ) );
	}


	/**
	 * Initialise hooks.
	 *
	 * @since 1.1.6
	 */
	public function init() {
		global $pagenow;
		if ( 'plugins.php' == $pagenow ) {
			add_filter( 'plugin_action_links_' . plugin_basename( WooCommerce_Advanced_Product_Labels()->file ), array( $this, 'add_plugin_action_links' ), 10, 2 );
		}
	}


	/**
	 * Add settings page.
	 *
	 * Register the settings page to WooCommerce.
	 *
	 * @since 1.3.0
	 *
	 * @param  array $pages List of existing settings pages.
	 * @return array        List of modified settings pages.
	 */
	public function add_settings_page( $pages ) {
		$pages[] = include __DIR__ . '/class-wapl-settings-page.php';

		return $pages;
	}


	/**
	 * Admin scripts.
	 *
	 * Enqueue admin javascript and stylesheets.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style( 'woocommerce-advanced-product-labels-front-end', plugins_url( '/assets/front-end/css/woocommerce-advanced-product-labels.min.css', WooCommerce_Advanced_Product_Labels()->file ), array(), WooCommerce_Advanced_Product_Labels()->version );
		wp_register_style( 'woocommerce-advanced-product-labels', plugins_url( '/assets/admin/css/woocommerce-advanced-product-labels.min.css', WooCommerce_Advanced_Product_Labels()->file ), array( 'wp-color-picker' ), WooCommerce_Advanced_Product_Labels()->version );

		wp_register_script( 'woocommerce-advanced-product-labels', plugins_url( '/assets/admin/js/woocommerce-advanced-product-labels' . $suffix . '.js', WooCommerce_Advanced_Product_Labels()->file ), array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker', 'code-editor' ), WooCommerce_Advanced_Product_Labels()->version );

		// Only load scripts on relevant pages
		if ( $this->is_wapl_page() ) {
			wp_localize_script( 'woocommerce-advanced-product-labels', 'wapl', array(
				'types' => array_keys( wapl_get_label_types() ),
				'colors' => array_keys( wapl_get_label_styles() ),
				'codeEditorSettings' => array(
					'codeEditor' => wp_enqueue_code_editor( array(
						'type' => 'text/css',
					) ),
				)
			) );

			wp_localize_script( 'wp-conditions', 'wpc2', array(
				'action_prefix' => 'wapl_',
			) );

			wp_enqueue_style( 'woocommerce-advanced-product-labels-front-end' );
			wp_enqueue_style( 'woocommerce-advanced-product-labels' );
			wp_enqueue_script( 'woocommerce-advanced-product-labels' );
			wp_enqueue_script( 'wp-conditions' );
		}
	}


	/**
	 * Plugin action links.
	 *
	 * Add links to the plugins.php page below the plugin name
	 * and besides the 'activate', 'edit', 'delete' action links.
	 *
	 * @since 1.1.8
	 *
	 * @param  array  $links List of existing links.
	 * @param  string $file  Name of the current plugin being looped.
	 * @return array         List of modified links.
	 */
	public function add_plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( WooCommerce_Advanced_Product_Labels()->file ) ) {
			$links = array_merge( array(
				'<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=labels' ) ) . '">' . __( 'Settings', 'woocommerce-advanced-product-labels' ) . '</a>'
			), $links );
		}

		return $links;
	}


	/**
	 * Check WAPL admin page.
	 *
	 * Returns true when current view is related to WAPL.
	 *
	 * @return bool
	 */
	public function is_wapl_page() {
		if (
			( isset( $_REQUEST['post'] ) && in_array( get_post_type( $_REQUEST['post'] ), array( 'wapl', 'product' ) ) ) ||
			( isset( $_REQUEST['post_type'] ) && in_array( $_REQUEST['post_type'], array( 'wapl', 'product' ) ) ) ||
			( isset( $_REQUEST['tab'] ) && in_array( $_REQUEST['tab'], array( 'advanced-product-labels' ) ) ) )
		{
			return true;
		}

		return false;
	}


}
