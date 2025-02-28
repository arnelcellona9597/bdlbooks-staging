<?php
/**
 * Initialise waitlist on the frontend product pages and load shortcode.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Pie_WCWL_Frontend_Init' ) ) {
	class Pie_WCWL_Frontend_Init {

		/**
		 * Is WPML loaded?
		 */
		public $has_wpml = false;

		/**
		 * Hooks up the frontend initialisation and any functions that need to run before the 'init' hook
		 */
		public function init() {
			global $sitepress;
			$this->has_wpml = isset( $sitepress );
			add_action( 'wp', array( $this, 'frontend_init' ) );
			add_shortcode( 'woocommerce_waitlist', array( $this, 'output_waitlist_elements' ) );
			if ( isset( $_GET['wcwl_remove_user'] ) && isset( $_GET['product_id'] ) && isset( $_GET['key'] ) && is_email( $_GET['wcwl_remove_user'] )  ) {
				add_action( 'wp', array( $this, 'remove_user_from_waitlist' ) );
			}
			if ( isset( $_GET['wcwl_user_optin'] ) && isset( $_GET['product_id'] ) && isset( $_GET['key'] ) && is_email( $_GET['wcwl_user_optin'] )  ) {
				add_action( 'wp', array( $this, 'handle_user_double_optin_request' ) );
			}
			// Compatibility.
			add_filter( 'wc_get_template', array( $this, 'check_theme_directory_for_waitlist_template' ), 10, 5 );
			// Login Redirects.
			add_filter( 'woocommerce_registration_redirect', array( $this, 'registration_redirect' ) );
			add_filter( 'woocommerce_login_redirect', array( $this, 'login_redirect' ) );
			// Quickview.
			add_action( 'woocommerce_single_product_summary', array( $this, 'output_elements_for_quick_view_simple' ), 35 );
			add_filter( 'woocommerce_get_stock_html', array( $this, 'output_elements_for_quick_view_variation' ), 10, 2 );
		}

		/**
		 * Check requirements and run initialise if waitlist is enabled
		 */
		public function frontend_init() {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
			$this->load_files();
			$this->load_classes();
		}

		/**
		 * Enqueue scripts and styles for the frontend if user is on a product page
		 *
		 * @return void
		 * @since  1.3
		 */
		public function frontend_enqueue_scripts() {
			wp_enqueue_script( 'wcwl_frontend', WCWL_ENQUEUE_PATH . '/includes/js/src/wcwl_frontend.min.js', array(), WCWL_VERSION, true );
			wp_localize_script( 'wcwl_frontend', 'wcwl_data', array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'user_id'							  => get_current_user_id(),
				/**
				 * Filter the loading message text
				 * 
				 * @since 2.4.0
				 */
				'loading_message'       => apply_filters( 'wcwl_loading_message', __( 'Loading', 'woocommerce-waitlist' ) ),
				/**
				 * Filter the success message text
				 * 
				 * @since 2.4.0
				 */
				'email_error_message'   => apply_filters( 'wcwl_email_error_message', __( 'Please enter a valid email address', 'woocommerce-waitlist' ) ),
				/**
				 * Filter the optin error message text
				 * 
				 * @since 2.4.0
				 */
				'optin_error_message'   => apply_filters( 'wcwl_optin_error_message', __( 'Please select the box to consent to the terms before continuing', 'woocommerce-waitlist' ) ),
				/**
				 * Filter the no checkbox selected for grouped products error message text
				 * 
				 * @since 2.4.0
				 */
				'no_checkboxes_message' => apply_filters( 'wcwl_no_checkbox_grouped_message', __( 'Please select at least one product to join the waitlist before continuing', 'woocommerce-waitlist' ) ),
			) );
			wp_enqueue_style( 'wcwl_frontend', WCWL_ENQUEUE_PATH . '/includes/css/src/wcwl_frontend.min.css', array(), WCWL_VERSION );
			wp_enqueue_style( 'dashicons' );
		}

		/**
		 * Load required frontend files
		 */
		public function load_files() {
			require_once 'account/class-pie-wcwl-frontend-user-waitlist.php';
			require_once 'account/class-pie-wcwl-frontend-shortcode.php';
			require_once 'product-types/class-pie-wcwl-frontend-simple.php';
			require_once 'product-types/class-pie-wcwl-frontend-variable.php';
			require_once 'product-types/class-pie-wcwl-frontend-grouped.php';
			$class = new Pie_WCWL_Frontend_Shortcode();
			$class->init();
		}

		/**
		 * Initiate required classes
		 */
		public function load_classes() {
			if ( self::is_ajax_variation_request() ) {
				$product_id = isset( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : 0;
				if ( $this->has_wpml ) {
					$product_id = self::get_main_product_id( $product_id );
				} else {
					$product_id = $product_id;
				}
				$wc_product = wc_get_product( $product_id );
				if ( $wc_product ) {
					$this->load_class( $wc_product );
					return;
				}
			}
			/**
			 * Filter whether to show waitlist elements on the account page
			 * 
			 * @since 2.4.0
			 */
			if ( is_account_page() && apply_filters( 'wcwl_enable_waitlist_account_tab', true ) ) {
				require_once 'account/class-pie-wcwl-frontend-account.php';
				$class = new Pie_WCWL_Frontend_Account();
				$class->init();
				return;
			}
			if ( is_archive() && 'yes' === get_option( 'woocommerce_waitlist_show_on_shop' ) ) {
				require_once 'product-types/class-pie-wcwl-frontend-shop.php';
				$class = new Pie_WCWL_Frontend_Shop();
				$class->init();
				return;
			}
			global $post, $product;
			if ( isset( $post->ID ) ) {
				/**
				 * Filter whether to show waitlist elements on the page
				 * 
				 * @since 2.4.0
				 */
				$show_on_page = apply_filters( 'wcwl_show_waitlist_elements_for_page', true, $post->ID );
				if ( ! $show_on_page ) {
					return;
				}
				if ( is_product() || $product ) {
					$wc_product = wc_get_product( $post->ID );
					if ( $wc_product ) {
						$this->load_class( $wc_product );
						return;
					}
				}
				if ( isset( $post->post_content ) && ! empty( $post->post_content ) ) {
					if ( strstr( $post->post_content, '[product_page' ) ) {
						$wc_product = $this->find_product_from_shortcode( $post->post_content );
						if ( $wc_product ) {
							$this->load_class( $wc_product );
							return;
						}
					}
					if ( strstr( $post->post_content, '[products' ) && 'yes' == get_option( 'woocommerce_waitlist_show_on_shop' ) ) {
						require_once 'product-types/class-pie-wcwl-frontend-shop.php';
						$class = new Pie_WCWL_Frontend_Shop();
						$class->init();
						return;
					}
					if ( strstr( $post->post_content, '[product_category' ) && 'yes' == get_option( 'woocommerce_waitlist_show_on_shop' ) ) {
						require_once 'product-types/class-pie-wcwl-frontend-shop.php';
						$class = new Pie_WCWL_Frontend_Shop();
						$class->init();
						return;
					}
				}
				if ( function_exists( 'tribe_is_event' ) && tribe_is_event( $post->ID ) && 'yes' == get_option( 'woocommerce_waitlist_events' ) ) {
					require_once 'product-types/class-pie-wcwl-frontend-event.php';
					$class = new Pie_WCWL_Frontend_Event();
					$class->init();
					return;
				}
			}
		}

		/**
		 * Load required class for product type
		 *
		 * @param WC_Product $product
		 */
		public function load_class( WC_Product $product ) {
			if ( $product && array_key_exists( $product->get_type(), WooCommerce_Waitlist_Plugin::$allowed_product_types ) ) {
				$class = WooCommerce_Waitlist_Plugin::$allowed_product_types[ $product->get_type() ];
				require_once $class['filepath'];
				$loaded_class = new $class['class']();
				$loaded_class->init( $product );
			}
		}

		/**
		 * Use the WC shortcode to determine the current product waitlist to load
		 *
		 * @param string $content
		 * @return WC_Product/bool
		 */
		public function find_product_from_shortcode( $content ) {
			$content_after_shortcode    = substr( $content, strpos( $content, '[product_page' ) + 13 );
			$content_before_closing_tag = strtok( $content_after_shortcode, ']' );
			$ids                        = explode( 'id=', $content_before_closing_tag );
			$id                         = isset( $ids[1] ) ? filter_var( $ids[1], FILTER_SANITIZE_NUMBER_INT ) : '';
			if ( is_numeric( $id ) ) {
				return wc_get_product( $id );
			}
			$skus = explode( 'sku=', $content_before_closing_tag );
			if ( isset( $skus[1] ) && $skus[1] ) {
				return wc_get_product( wc_get_product_id_by_sku( str_replace( '"', '', $skus[1] ) ) );
			}
			return false;
		}

		/**
		 * Is WooCommerce performing an ajax request to return a child variation
		 *
		 * @return bool
		 */
		public static function is_ajax_variation_request() {
			if ( isset( $_REQUEST['wc-ajax'] ) && 'get_variation' == $_REQUEST['wc-ajax'] ) {
				return true;
			}

			return false;
		}

		/**
		 * Return the main product for the given translated product ID
		 *
		 * @param $product_id
		 *
		 * @return int
		 */
		public static function get_main_product_id( $product_id ) {
			global $woocommerce_wpml;
			if ( isset( $woocommerce_wpml->products ) && $woocommerce_wpml->products ) {
				return $woocommerce_wpml->products->get_original_product_id( $product_id );
			}

			return $product_id;
		}

		/**
		 * Output the HTML to display waitlist elements for the given product
		 *
		 * @param $atts
		 *
		 * @return string|void
		 */
		public function output_waitlist_elements( $atts ) {
			$atts       = shortcode_atts( array(
				'product_id' => 0,
			), $atts, 'woocommerce_waitlist' );
			$product_id = absint( $atts['product_id'] );
			if ( ! $product_id ) {
				global $product;
				if ( $product ) {
					$product_id = $product->get_id();
				}
			}
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				$error = 'Failed to load waitlist template: Product not found';
				wcwl_add_log( $error, $product_id );
			} elseif ( ! $this->product_supports_shortcode( $product ) ) {
				$error = 'Failed to load waitlist template: Product type is not supported (only simple products or variations can be loaded with the shortcode)';
				wcwl_add_log( $error, $product_id );
			} else {
				$this->load_class( $product );
				return wcwl_get_waitlist_fields( $product_id );
			}
		}

		/**
		 * Does the shortcode support the given product type
		 * By default, only simple/variations are supported
		 *
		 * @param WC_Product $product
		 * @return bool
		 */
		public function product_supports_shortcode( WC_Product $product ) {
			$supported = false;
			if ( WooCommerce_Waitlist_Plugin::is_simple( $product ) ) {
				$supported = true;
			} elseif ( WooCommerce_Waitlist_Plugin::is_variation( $product ) ) {
				$supported = true;
			}
			/**
			 * Filter whether the shortcode supports the given product type
			 * 
			 * @since 2.4.0
			 */
			return apply_filters( 'wcwl_shortcode_supports_product_type', $supported, $product );
		}

		/**
		 * Remove user from waitlist when requested via email
		 *
		 * @return void
		 */
		public function remove_user_from_waitlist() {
			$email      = isset( $_GET['wcwl_remove_user'] ) ? sanitize_email( $_GET['wcwl_remove_user'] ) : '';
			$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
			$key        = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
			if ( ! hash_equals( hash_hmac( 'sha256', $email . '|' . $product_id, get_the_guid( $product_id ) . $email . 'woocommerce-waitlist-join' ), $key ) ) {
				wc_add_notice( __( 'Sorry, there was a problem with your request, please contact a site administrator for assistance.', 'woocommerce-waitlist' ), 'error' );
			}
			$response = wcwl_remove_user_from_waitlist( $email, $product_id );
			if ( ! is_wp_error( $response ) ) {
				WC_Emails::instance();
				/**
				 * Triggers to send an email to a customer when they are removed from the waitlist
				 * 
				 * @hooked Pie_WCWL_Waitlist_Left_Email::trigger
				 * @since 2.4.0
				 */
				do_action( 'wcwl_left_mailout_send_email', $email, $product_id );
			}
			wc_add_notice( __( 'Your email address has been removed from this product.', 'woocommerce-waitlist' ) );
			wcwl_remove_user_from_archive( $email, $product_id );
		}

		/**
		 * Handle user's double optin request to join the waitlist
		 */
		public function handle_user_double_optin_request() {
			$email          = isset( $_GET['wcwl_user_optin'] ) ? sanitize_email( $_GET['wcwl_user_optin'] ) : '';
			$product_id     = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
			$product_string = isset( $_GET['products'] ) ? sanitize_text_field( $_GET['products'] ) : '';
			$products 	    = explode( ',', $product_string );
			$lang           = isset( $_GET['lang'] ) ? sanitize_text_field( $_GET['lang'] ) : '';
			$key		    = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
			if ( ! get_transient( $key ) ) {
				wc_add_notice( __( 'Sorry your authorisation code has expired, please try again.  If you continue to experience issues please contact us.', 'woocommerce-waitlist' ), 'error' );
				return;
			}
			if ( ! hash_equals( hash_hmac( 'sha256', $email . '|' . $product_id, get_the_guid( $product_id ) . $email . 'woocommerce-waitlist-optin' ), $key ) ) {
				wc_add_notice( __( 'Sorry, there was a problem with your request, please contact a site administrator for assistance.', 'woocommerce-waitlist' ), 'error' );
				return;
			}
			delete_transient( $key );
			if ( $product_string && $products && ! empty( $products ) ) {
				$response = 'success';
				foreach ( $products as $product_id ) {
					if ( ! $product_id ) {
						continue;
					}
					$response = wcwl_add_user_to_waitlist( $email, $product_id, $lang );
					if ( is_wp_error( $response ) ) {
						/* translators: %s: error message */
						wc_add_notice( sprintf( __( 'Sorry, there was a problem with your request: %s', 'woocommerce-waitlist' ), $response->get_error_message() ), 'error' );
						break;
					}
				}
				if ( ! is_wp_error( $response ) ) {
						wc_add_notice( __( 'You have been added to the waitlist for the selected products.', 'woocommerce-waitlist' ) );
				}
			} else {
				$response = wcwl_add_user_to_waitlist( $email, $product_id, $lang );
				if ( is_wp_error( $response ) ) {
					/* translators: %s: error message */
					wc_add_notice( sprintf( __( 'Sorry, there was a problem with your request: %s', 'woocommerce-waitlist' ), $response->get_error_message() ), 'error' );
				} else {
					wc_add_notice( __( 'You have been added to the waitlist for this product.', 'woocommerce-waitlist' ) );
				}
			}
		}

		/**
		 * If the template is not located in the woocommerce directory of the theme, check the root directory
		 * Ensures backwards compatibility
		 *
		 * @param $located
		 * @param $template_name
		 * @param $args
		 * @param $template_path
		 * @param $default_path
		 *
		 * @return $located
		 */
		public function check_theme_directory_for_waitlist_template( $located, $template_name, $args, $template_path, $default_path ) {
			// Is this a waitlist template?
			if ( WooCommerce_Waitlist_Plugin::$path . 'templates/' !== $default_path ) {
				return $located;
			}
			// Are we still trying to load from the default path?
			if ( $located && $default_path && strpos( $located, $default_path ) !== false ) {
				// Check the theme directory
				$located = locate_template( array(
					$template_path . $template_name,
					$template_name,
				) );
				if ( ! $located ) {
					$located = $default_path . $template_name;
				}
			}

			return $located;
		}

		/**
		 * Redirect user back to the product after logging in
		 */
		public function login_redirect( $redirect ) {
			return isset( $_GET['wcwl_redirect'] ) ? esc_url_raw( $_GET['wcwl_redirect'] ) : $redirect;
		}

		/*
		 * Redirect user back to the product after registering
		 */
		public function registration_redirect( $redirect ) {
			return isset( $_GET['wcwl_redirect'] ) ? esc_url_raw( $_GET['wcwl_redirect'] ) : $redirect;
		}

		/**
		 * Output waitlist elements for quickview on simple products
		 *
		 * @return void
		 * @todo does this need to echo? using get_template_part in the wcwl_get_waitlist_fields function should avoid phpcs conflict
		 */
		public function output_elements_for_quick_view_simple() {
			if ( ! isset( $_REQUEST['wc-api'] ) || 'WC_Quick_View' !== $_REQUEST['wc-api'] ) {
				return;
			}
			global $product;
			if ( $product && 'simple' === $product->get_type() ) {
				// phpcs ignore due to template having sufficient escaping and being directly output
				echo wcwl_get_waitlist_fields( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Output waitlist elements for quickview on simple products
		 *
		 * @return string $html
		 */
		public function output_elements_for_quick_view_variation( $html, $product ) {
			if ( ! isset( $_REQUEST['wc-api'] ) || 'WC_Quick_View' !== $_REQUEST['wc-api'] ) {
				return $html;
			}
			if ( $product && 'variation' === $product->get_type() ) {
				$elements = wcwl_get_waitlist_fields( $product->get_id() );
				$html .= $elements;
			}
			return $html;
		}
	}
}
