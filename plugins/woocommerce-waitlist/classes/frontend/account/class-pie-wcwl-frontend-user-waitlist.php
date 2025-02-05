<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Pie_WCWL_Frontend_User_Waitlist' ) ) {
	/**
	 * Handles the user account for displaying their personal waitlist items
	 *
	 * @package  WooCommerce Waitlist
	 */
	class Pie_WCWL_Frontend_User_Waitlist {

		/**
		 * Products the user is currently on a waitlist for
		 *
		 * @var
		 */
		public $products;

		/**
		 * Products user is currently on an archive for
		 *
		 * @var
		 */
		public $archives;

		/**
		 * Initialise frontend waitlist for user
		 */
		public function init() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			if ( isset( $_GET['remove_waitlist'] ) && is_numeric( $_GET['remove_waitlist'] ) && ! wc_has_notice( __( 'You have been removed from the waitlist for this product', 'woocommerce-waitlist' ) ) ) {
				wc_add_notice( __( 'You have been removed from the waitlist for this product', 'woocommerce-waitlist' ) );
			}
			if ( isset( $_GET['remove_archives'] ) && 'true' === $_GET['remove_archives'] && ! wc_has_notice( __( 'You have been removed from all waitlist archives.', 'woocommerce-waitlist' ) ) ) {
				wc_add_notice( __( 'You have been removed from all waitlist archives.', 'woocommerce-waitlist' ) );
			}
		}

		/**
		 * Load up required JS
		 */
		public function enqueue_scripts() {
			wp_enqueue_script( 'wcwl_frontend_account', WCWL_ENQUEUE_PATH . '/includes/js/src/wcwl_account.min.js', array(), WCWL_VERSION, true );
			/**
			 * Filter the shortcode text displayed when a user has no waitlists
			 * 
			 * @since 2.4.0
			 */
			$shortcode_text = apply_filters( 'wcwl_shortcode_no_waitlists_text', __( 'You have not yet joined the waitlist for any products.', 'woocommerce-waitlist' ) );
			/* translators: %1$s opening <a> tag links to WooCommerce shop, %2$s closing <a> tag */
			$link_text = sprintf( __( '%1$sVisit shop now!%2$s', 'woocommerce-waitlist' ), '<a href="' . wc_get_page_permalink( 'shop' ) . '">', '</a>' );
			/**
			 * Filter the link text displayed when directing a user to the shop
			 * 
			 * @since 2.4.0
			 */
			$visit_shop_text = apply_filters( 'wcwl_shortcode_visit_shop_text', $link_text );
			$data = array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'user_id'          => get_current_user_id(),
				'no_waitlist_html' => '<p>' . $shortcode_text . '</p><p>' . $visit_shop_text . '</p><hr>',

			);
			wp_localize_script( 'wcwl_frontend_account', 'wcwl_account', $data );
		}

		/**
		 * Output the HTML to display a list of products the current user is on the waitlist for
		 */
		public function display_users_waitlists() {
			$user = get_user_by( 'id', get_current_user_id() );
			if ( ! $user ) {
				return;
			}
			wc_get_template(
				'waitlist-user-waitlist.php',
				array(
					'title'    => __( 'Your Waitlists', 'woocommerce-waitlist' ),
					'products' => WooCommerce_Waitlist_Plugin::get_waitlist_products_for_user( $user ),
					'archives' => WooCommerce_Waitlist_Plugin::get_waitlist_archives_for_user( $user ),
				),
				'',
				WooCommerce_Waitlist_Plugin::$path . 'templates/'
			);
		}

		/**
		 * Add query parameter to current URL to ensure user is removed from product as required
		 *
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public static function get_remove_link( WC_Product $product ) {
			if ( ! $product ) {
				return '';
			}
			$current_url = trailingslashit( self::get_current_url() );
			$url         = add_query_arg( 'remove_waitlist', $product->get_id(), $current_url );

			return $url;
		}

		/**
		 * Add query parameter to current URL to ensure user is removed from all archives
		 *
		 * @return string
		 */
		public static function get_unarchive_link() {
			$current_url = trailingslashit( self::get_current_url() );
			$url         = add_query_arg( 'remove_archives', 'true', $current_url );

			return $url;
		}

		/**
		 * Return the current URL string
		 *
		 * @return string|void
		 */
		public static function get_current_url() {
			global $wp;

			return home_url( add_query_arg( array(), $wp->request ) );
		}
	}
}
