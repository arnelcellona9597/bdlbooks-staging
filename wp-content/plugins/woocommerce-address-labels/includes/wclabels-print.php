<?php

if ( !class_exists( 'WooCommerce_Address_Labels_Print' ) ) {

	class WooCommerce_Address_Labels_Print {

		/**
		 * @var array
		 */
		public $order_ids;

		/**
		 * @var string
		 */
		public $template_base_path;

		/**
		 * @var string
		 */
		public $template_base_url;

		/**
		 * @var array
		 */
		public $layout_settings;

		/**
		 * @var array
		 */
		public $interface_settings;

		/**
		 * @var array
		 */
		public $label_settings;

		/**
		 * @var int
		 */
		public $offset;

		/**
		 * @var string
		 */
		public $paper_size;

		/**
		 * @var float|int
		 */
		public $page_width;

		/**
		 * @var float|int
		 */
		public $page_height;

		/**
		 * @var array
		 */
		public $page_margins;

		/**
		 * @var float|int
		 */
		public $label_width;

		/**
		 * @var float|int
		 */
		public $label_height;

		/**
		 * @var int
		 */
		public $cols;

		/**
		 * @var int
		 */
		public $rows;

		/**
		 * @var float|int
		 */
		public $vertical_pitch;

		/**
		 * @var float|int
		 */
		public $horizontal_pitch;

		/**
		 * @var string
		 */
		public $font_size;

		/**
		 * @var array
		 */
		public $google_font;

		/**
		 * @var string
		 */
		public $block_width;

		/**
		 * @var string
		 */
		public $border_collapse;

		/**
		 * Construct.
		 */				
		public function __construct() {
			$this->template_base_path = WPO_WCLABELS()->plugin_path() . '/templates/';
			$this->template_base_url  = WPO_WCLABELS()->plugin_url() . '/templates/';
			$this->layout_settings    = get_option( 'wpo_wclabels_layout_settings', array() );
			$this->interface_settings = get_option( 'wpo_wclabels_interface_settings', array() );
			$this->label_settings     = get_option( 'wpo_wclabels_label_settings', array() );

			add_action( 'wp_ajax_wpo_wclabels_print', array($this, 'print_labels' ));
			add_action( 'wp_ajax_wpo_wclabels_qr_code', array($this, 'qr_code' ));
			add_action( 'admin_init', array( $this, 'status_export' ) );

			add_filter( 'wpo_wclabels_check_privs', array( $this, 'wcvendors_privs' ), 10, 2 );
		}

		/**
		 * Print labels for selected orders
		 *
		 * @access public
		 * @return void
		 */
		public function print_labels() {
			// Check the nonce
			if( empty( $_GET['action'] ) || ! is_user_logged_in() || !check_admin_referer( $_GET['action'] ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wclabels' ) );
			}

			// disable wp lazy loading images introduced in version 5.5
			add_filter( 'wp_lazy_loading_enabled', '__return_false', 999 );

			// Check if all parameters are set
			if( empty( $_GET['order_ids'] ) && empty( $_POST['order_ids'] ) ) {
				wp_die( __( 'Some of the export parameters are missing.', 'wpo_wclabels' ) );
			}

			// get order ids from post, fallback to get
			if ( isset( $_POST['order_ids'] ) ) {
				$order_ids = (array) json_decode(stripslashes($_POST['order_ids']));
			} elseif ( isset( $_GET['order_ids'] ) ) {
				$order_ids = (array) $_GET['order_ids'];
			}

			if ( empty($order_ids) ) {
				die( __('You have not selected any orders!', 'wpo_wclabels') );
			}

			$this->order_ids = apply_filters( 'wpo_wclabels_order_ids', $order_ids );
			// echo '<pre>';var_dump($this->order_ids);die('</pre>');

			// Check the user privileges
			if( apply_filters( 'wpo_wclabels_check_privs', !current_user_can( 'manage_woocommerce_orders' ) && !current_user_can( 'edit_shop_orders' ), $this->order_ids ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wclabels' ) );
			}

			// store order_ids in GET for backwards template compatibility
			$_GET['order_ids'] = implode('x', $order_ids);

			$this->prepare_settings();	

			$page_template = WPO_WCLABELS()->plugin_path() . '/includes/wclabels-page-template.php';
			$this->offset = isset($_REQUEST['offset']) ? (int) $_REQUEST['offset'] : 0;

			// ensure utf8
			header('Content-Type: text/html; charset=utf-8');

			echo $this->get_template( $page_template );

			die();
		}

		public function get_label_data ($order_ids) {
			$address_format = ! empty($this->label_settings['address_data']) ? $this->label_settings['address_data'] : '[shipping_address]';
			if (!isset($this->label_settings['disable_nl2br'])) {
				$address_format = nl2br($address_format);
			}

			$label_data = array();

			foreach ($order_ids as $order_id) {
				$preset_address_data = array();
				if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'wc_user_membership' ) {
					// we don't have an order to work with
					$order = NULL;

					// instead we get the user data and format it for use in the make_replacements() method
					$plan_id = $order_id;
					$plan = wc_memberships_get_user_membership( $plan_id );
					$user_id = $plan->user_id;
					$preset_address_data[] = $this->get_address_data_from_user( $user_id );
				} else {
					// get label data from order
					$order = wc_get_order( $order_id );

					// skip/filter out virtual and/or downloadable
					if (isset($_REQUEST['wclabels_exclude_virtual']) || isset($_REQUEST['wclabels_exclude_downloadable'])) {
						$virtual_downloadable_order = $this->is_virtual_downloadable( $order );
						if ($virtual_downloadable_order['is_virtual'] == true && isset($_REQUEST['wclabels_exclude_virtual'])) {
							continue;
						}
						if ($virtual_downloadable_order['is_downloadable'] == true && isset($_REQUEST['wclabels_exclude_downloadable'])) {
							continue;
						}
					}

					// check for multiple addresses
					$shipping_packages = $order->get_meta( '_shipping_packages' );
					if ( !empty($shipping_packages) && is_array($shipping_packages) && count($shipping_packages) > 1 ) {
						foreach ($shipping_packages as $package_key => $package) {

							// check meta key for old and new version of multiple address plugin
							$address_key = array_key_exists( 'destination', $package ) ? 'destination' : 'full_address';

							if (empty($package[$address_key])) {
								$preset_address_data[] = array();
								continue;
							}
							$package_address_data = array();
							$package_address_data['shipping_address'] = WC()->countries->get_formatted_address( $package[$address_key] );

							// only create label if we have an address for this package
							if (empty($package_address_data['shipping_address'])) {
								continue;
							}

							// prepend '_shipping_' to package address data
							foreach ($package[$address_key] as $address_key => $address_value) {
								$package_address_data['_shipping_'.$address_key] = $address_value;
							}
							// use parent address first/last name for package if not entered
							$parent_fallback_keys = array( '_shipping_first_name', '_shipping_last_name', '_shipping_company' );
							foreach ( $parent_fallback_keys as $parent_fallback_key ) {
								if ( empty( $package_address_data[$parent_fallback_key] ) ) {
									unset($package_address_data[$parent_fallback_key]);
								}
							}

							// split items per address
							if (!empty($package['contents'])) {
								$package_address_data['order_items'] = '<ul class="order-items">';
								foreach ($package['contents'] as $package_product) {
									$package_address_data['order_items'] .= sprintf('<li><span class="qty">%sx</span> <span class="item-name">%s</span></li>', $package_product['quantity'], $package_product['data']->get_name());
								}
								$package_address_data['order_items'] .= "</ul>";
							}

							$preset_address_data[] = apply_filters( 'wpo_wclabel_package_address_data', $package_address_data, $package, $package_key );
						}
					} else {
						$preset_address_data[] = array(); // no preset address data for regular orders
					}
				}

				// replace placeholders
				foreach ($preset_address_data as $address_data) {
					$address_data = $this->make_replacements( apply_filters( 'wpo_wclabel_address_format', $address_format, $order ), $order, $address_data );
					$label_data[] = array(
						'order_id'		=> $order_id,
						'label_data'	=> $address_data
					);
				}
			}

			return apply_filters( 'wpo_wclabel_label_data', $label_data, $order_ids );
		}

		/**
		 * Get address data from user (id or object)
		 */
		public function get_address_data_from_user( $user ) {
			if (!is_object($user)) {
				// Get user details
				$user = get_userdata( $user );
			}

			$address_data = array();
			
			// billing address & shipping address
			$address_types = array( 'billing', 'shipping');
			foreach ($address_types as $type) {
				$address = apply_filters( 'woocommerce_my_account_my_address_formatted_address', array(
					'first_name'  => get_user_meta( $user->ID, $type . '_first_name', true ),
					'last_name'   => get_user_meta( $user->ID, $type . '_last_name', true ),
					'company'     => get_user_meta( $user->ID, $type . '_company', true ),
					'address_1'   => get_user_meta( $user->ID, $type . '_address_1', true ),
					'address_2'   => get_user_meta( $user->ID, $type . '_address_2', true ),
					'city'        => get_user_meta( $user->ID, $type . '_city', true ),
					'state'       => get_user_meta( $user->ID, $type . '_state', true ),
					'postcode'    => get_user_meta( $user->ID, $type . '_postcode', true ),
					'country'     => get_user_meta( $user->ID, $type . '_country', true )
				), $user->ID, $type );

				// formatted address
				$address_data[ $type.'_address' ] = WC()->countries->get_formatted_address( $address );
				// address parts (correct key)
				foreach ($address as $address_key => $address_value) {
					$address_data["_{$type}_{$address_key}"] = $address_value;
				}
			}

			return $address_data;
		}

		/**
		 * Prepare settings for quick/easy usage in template
		 */
		public function prepare_settings () {
			// prepare paper size data for style.css
			if ( !empty( $this->layout_settings['ignore_page_size_orientation'] ) ) {
				$this->paper_size = 'auto';
			} elseif ($this->layout_settings['paper_size'] == 'custom') {
				switch ($this->layout_settings['paper_orientation']) {
					case 'portrait':
						$this->paper_size = $this->layout_settings['custom_paper_size']['width'] .'mm '. $this->layout_settings['custom_paper_size']['height'].'mm';
						break;
					case 'landscape':
						$this->paper_size = $this->layout_settings['custom_paper_size']['height'] .'mm '. $this->layout_settings['custom_paper_size']['width'].'mm';
						break;
					default:
						$this->paper_size = $this->layout_settings['custom_paper_size']['width'] .'mm '. $this->layout_settings['custom_paper_size']['height'].'mm';
						break;
				}
			} else {
				$this->paper_size = $this->layout_settings['paper_size'] .' '. $this->layout_settings['paper_orientation'];
			}

			// Get page size
			switch ($this->layout_settings['paper_size']) {
				case 'a4':
					$this->page_width = 210;
					$this->page_height = 297;
					break;
				case 'letter':
					$this->page_width = 216;
					$this->page_height = 279;
					break;
				case 'custom':
					$this->page_width = $this->layout_settings['custom_paper_size']['width'];
					$this->page_height = $this->layout_settings['custom_paper_size']['height'];
			}

			// Calculate label size
			$this->cols = !empty( $this->layout_settings['cols'] ) ? max( intval($this->layout_settings['cols']), 1 ) : 1;
			$this->rows = !empty( $this->layout_settings['rows'] ) ? max( intval($this->layout_settings['rows']), 1 ) : 1;
			switch ($this->layout_settings['paper_orientation']) {
				case 'portrait':
					$this->label_height = $this->page_height / $this->rows;
					$this->label_width = $this->page_width / $this->cols;
					break;
				case 'landscape':
					$this->label_height = $this->page_width / $this->rows;
					$this->label_width = $this->page_height / $this->cols;
					break;					
				default:
					$this->label_height = $this->page_height / $this->rows;
					$this->label_width = $this->page_width / $this->cols;
					break;
			}

			// get page margins
			$this->page_margins = array(
				'top'		=> !empty($this->layout_settings['page_margins']['top']) ? $this->layout_settings['page_margins']['top'] : '0',
				'bottom'	=> !empty($this->layout_settings['page_margins']['bottom']) ? $this->layout_settings['page_margins']['bottom'] : '0',
				'left'		=> !empty($this->layout_settings['page_margins']['left']) ? $this->layout_settings['page_margins']['left'] : '0',
				'right'		=> !empty($this->layout_settings['page_margins']['right']) ? $this->layout_settings['page_margins']['right'] : '0',
			);
			// trim spaces
			foreach ($this->page_margins as &$page_margin) {
				$page_margin = trim($page_margin);
			}

			// get font size (default to 12px)
			$this->font_size = isset($this->label_settings['font_size'])?$this->label_settings['font_size'] . 'pt':'12px';

			// google font?
			if (isset($this->label_settings['font']) && $this->label_settings['font']['family'] != 'sans-serif') {
				$this->google_font = $this->label_settings['font'];
				if ( isset( $this->google_font['variant'] ) ) {
					$this->google_font['style'] = strpos($this->google_font['variant'], 'italic') !== false ? 'italic' : '';
					$font_weight = preg_replace("/[^0-9]/", "", $this->google_font['variant']);
					$this->google_font['weight'] = !empty($font_weight) ? $font_weight : 'normal';
				} else {
					$this->google_font['style'] = 'normal';
					$this->google_font['weight'] = 'normal';
				}
			}

			// block width (default to 5cm)
			$this->block_width = isset($this->label_settings['block_width'])?$this->label_settings['block_width']:'5cm';
			// trim & remove spaces
			$this->block_width = trim( str_replace( ' ', '', $this->block_width ) );

			// label spacing
			$this->vertical_pitch = isset($this->layout_settings['vertical_pitch'])?$this->layout_settings['vertical_pitch']:0;
			$this->horizontal_pitch = isset($this->layout_settings['horizontal_pitch'])?$this->layout_settings['horizontal_pitch']:0;
			if ($this->vertical_pitch == 0 && $this->horizontal_pitch == 0) {
				$this->border_collapse = 'collapse';
			} else {
				$this->border_collapse = 'separate';
			}
		}

		public function google_font_url ( $chosen_font ) {
			// echo '<pre>';var_dump($font);echo '</pre>';die();
			// get google font data
			$google_webfonts = json_decode( file_get_contents( WPO_WCLABELS()->plugin_path() .'/assets/data/google-webfonts.json'), true);
			unset($google_webfonts['kind']);
			foreach ($google_webfonts['items'] as $key => $font ) {
				if (isset($font['family']) && $font['family'] == $chosen_font['family']) {
					$subsets = isset($font['subsets'])?implode(',', $font['subsets']):'';
					$variants = isset($font['variants'])?implode(',', $font['variants']):'';
				}
			}

			$protocol = is_ssl() ? 'https://' : 'http://';
			$gfonts_url = $protocol . 'fonts.googleapis.com/css';
			$url = $gfonts_url . '?family=' . rawurlencode($chosen_font['family']);
			if (!empty($variants)) {
				$url .= ":{$variants}";
			}
			if (!empty($subsets)) {
				$url .= "&subsets={$subsets}";
			}

			return $url;
		}

		/**
		 * Print labels for orders with specific status and/or date
		 *
		 * @access public
		 * @return void
		 */
		public function status_export() {
			if ( empty( $_POST ) ) {
				return;
			}

			$nonce = isset( $_POST['wpo_wclabels_nonce'] ) ? sanitize_text_field( $_POST['wpo_wclabels_nonce'] ) : null;
			if ( ! wp_verify_nonce( $nonce, 'wpo_wclabels_export') ) {
				return;
			}

			if ( !isset($_POST['status_filter']) ) {
				die( __('No orders found!', 'wpo_wclabels') );
			}

			// disable wp lazy loading images introduced in version 5.5
			add_filter( 'wp_lazy_loading_enabled', '__return_false', 999 );
		
			// die(print_r($_POST, true)); //DEBUG: check posted data
			$this->order_ids = $order_ids = $this->get_orders_by_status( $_POST['status_filter'] );
			$this->offset = isset($_REQUEST['wclabels_offset']) ? (int) $_REQUEST['wclabels_offset'] : 0;
			$_GET['order_ids'] = implode('x', $order_ids);

			if ( empty($_GET['order_ids']) ) {
				die( __('No orders found!', 'wpo_wclabels') );
			}
			// die(print_r($order_ids, true)); //DEBUG: order_ids

			// ensure utf8
			header('Content-Type: text/html; charset=utf-8');

			$this->prepare_settings();	

			// output address labels
			$page_template = WPO_WCLABELS()->plugin_path() . '/includes/wclabels-page-template.php';
			echo $this->get_template( $page_template );

			die();
		}

		public function get_orders_by_status( $statuses ) {
			$args = array(
				'status'  => $statuses,
				'return'  => 'ids',
				'type'    => 'shop_order',
				'limit'   => -1,
				'orderby' => 'id',
				'order'   => 'ASC',
			);

			// get in utc timestamp for WC3.1+
			$utc_timestamp = version_compare( WOOCOMMERCE_VERSION, '3.1', '>=' ) ? true : false;
			// get dates from input
			$date_after    = $this->get_date_string_from_input( 'date-from', 'hour-from', 'minute-from', false, $utc_timestamp );
			$date_before   = $this->get_date_string_from_input( 'date-to', 'hour-to', 'minute-to', true, $utc_timestamp );
			
			if ( version_compare( WOOCOMMERCE_VERSION, '3.1', '>=' ) ) {
				// WC3.1+
				if ( $date_after && ! $date_before ) {
					// after date
					$args['date_created'] = '>='.$date_after;
				} elseif ( $date_before ) {
					if ( ! $date_after ) {
						// before date
						$args['date_created'] = '<='.$date_before;
					} else {
						// between dates
						$args['date_created'] = $date_after.'...'.$date_before;
					}
				}
			} else {
				// WC3.0
				if ( $date_after ) {
					$args['date_after'] = $date_after;
				}
				if ( $date_before ) {
					$args['date_before'] = $date_before;
				}
			}

			$order_ids = wc_get_orders( $args );
			$order_ids = apply_filters( 'wpo_wclabels_order_ids', $order_ids );
			
			return $order_ids;
		}

		/**
		 * Return evaluated template contents
		 */
		public function get_template( $file ) {
			ob_start();
			if (file_exists($file)) {
				include($file);
			}
			return ob_get_clean();
		}

		/**
		 * Get the template path for a file. locate by file existience
		 * and then return the corresponding file path.
		 */
		public function get_template_path( $name ) {
			$plugin_template_path = $this->template_base_path;
			$child_theme_template_path = get_stylesheet_directory() . '/woocommerce/labels/';
			$theme_template_path = get_template_directory() . '/woocommerce/labels/';
	
			if( file_exists( $child_theme_template_path . $name ) ) {
				$filepath = $child_theme_template_path . $name;
			} elseif( file_exists( $theme_template_path . $name ) ) {
				$filepath = $theme_template_path . $name;
			} else {
				$filepath = $plugin_template_path . $name;
			}
			
			return apply_filters( 'wclabels_custom_template_path', $filepath, $name );
		}

		/**
		 * Get the template path for a file. locate by file existience
		 * and then return the corresponding file path.
		 */
		public function get_template_url( $name ) {
			// paths for file_exists check
			$child_theme_template_path = get_stylesheet_directory() . '/woocommerce/labels/';
			$theme_template_path = get_template_directory() . '/woocommerce/labels/';

			$plugin_template_url = $this->template_base_url;
			$child_theme_template_url = get_stylesheet_directory_uri() . '/woocommerce/labels/';
			$theme_template_url = get_template_directory_uri() . '/woocommerce/labels/';

			if( file_exists( $child_theme_template_path . $name ) ) {
				$fileurl = $child_theme_template_url . $name;
			} elseif( file_exists( $theme_template_path . $name ) ) {
				$fileurl = $theme_template_url . $name;
			} else {
				$fileurl = $plugin_template_url . $name;
			}
			
			return apply_filters( 'wclabels_custom_template_url', $fileurl, $name );
		}

		/**
		 * Format address by replacing placeholders with order data
		 */
		public function make_replacements ( $text, $order = '', $address_data = array() ) {
			$text_format = $text;
			// make an index of placeholders used in the text
			preg_match_all('/\[.*?\]/', $text, $placeholders_used);
			$placeholders_used = array_shift($placeholders_used); // we only need the first match set

			// load countries & states
			$countries = new WC_Countries;

			// loop through placeholders and make replacements
			foreach ($placeholders_used as $placeholder) {
				$placeholder_clean = trim($placeholder,"[]");
				if ( strpos($placeholder_clean, 'label_image_placeholder') !== false ) {
					// strip arguments
					$_img_args = trim( str_replace( 'label_image_placeholder', '', $placeholder_clean) );
					$img_args = array();
					if (!empty($_img_args)) {
						$_img_args = explode(' ', $_img_args);
						foreach ($_img_args as $img_arg) {
							$arg_extract = explode('=', $img_arg);
							$img_args[$arg_extract[0]] = trim( $arg_extract[1], '"' );
						}
					}
					if ( isset($img_args['width']) || isset($img_args['height']) ) {
						$width = isset($img_args['width']) ? $img_args['width'] : 'auto';
						$height = isset($img_args['height']) ? $img_args['height'] : 'auto';
						$attributes = array('style' => "width: {$width}; height: {$height};");
					} else {
						$attributes = array('style' => "max-width: 100%; height: auto;");
					}
					$image = wp_get_attachment_image( intval($img_args['id']), 'full', false, $attributes );

					$text = $this->replace_placeholder($placeholder, $image, $text, $placeholder_clean, $order );
					continue;
				}

				// Passed address data replacements
				if (!empty($address_data)) {
					// special treatment for country & state
					$country_placeholders = array( 'shipping_country', 'billing_country' );
					$state_placeholders = array( 'shipping_state', 'billing_state' );
					foreach ( array_merge($country_placeholders, $state_placeholders) as $country_state_placeholder ) {
						if ( strpos( $placeholder_clean, $country_state_placeholder ) !== false ) {
							// check if formatting is needed
							if ( strpos($placeholder_clean, '_code') !== false ) {
								// no country or state formatting
								$placeholder_clean = str_replace('_code', '', $placeholder_clean);
								$format = false;
							} else {
								$format = true;
							}

							$country_or_state = $address_data[$placeholder_clean];

							if ($format === true) {
								// format country or state
								if (in_array($placeholder_clean, $country_placeholders)) {
									$country_or_state = ( $country_or_state && isset( $countries->countries[ $country_or_state ] ) ) ? $countries->countries[ $country_or_state ] : $country_or_state;
								} elseif (in_array($placeholder_clean, $state_placeholders)) {
									// get country for address
									$country_placeholder_clean = str_replace( 'state', 'country', $placeholder_clean );
									$country = $address_data[$country_placeholder_clean];
									$country_or_state = ( $country && $country_or_state && isset( $countries->states[ $country ][ $country_or_state ] ) ) ? $countries->states[ $country ][ $country_or_state ] : $country_or_state;
								}
							}

							if ( !empty( $country_or_state ) ) {
								$text = $this->replace_placeholder($placeholder, $country_or_state, $text, $placeholder_clean, $order );
								continue 2;
							}
						}
					}

					if (array_key_exists($placeholder_clean, $address_data)) {
						$text = $this->replace_placeholder($placeholder, $address_data[$placeholder_clean], $text, $placeholder_clean, $order );
						continue;
					} elseif (array_key_exists("_{$placeholder_clean}", $address_data)) {
						$text = $this->replace_placeholder($placeholder, $address_data["_{$placeholder_clean}"], $text, $placeholder_clean, $order );
						continue;
					}
				}				

				// Order data replacements only when $order is available
				if (!empty($order)) {
					// special treatment for country & state
					$country_placeholders = array( 'shipping_country', 'billing_country' );
					$state_placeholders = array( 'shipping_state', 'billing_state' );
					foreach ( array_merge($country_placeholders, $state_placeholders) as $country_state_placeholder ) {
						if ( strpos( $placeholder_clean, $country_state_placeholder ) !== false ) {
							// check if formatting is needed
							if ( strpos($placeholder_clean, '_code') !== false ) {
								// no country or state formatting
								$placeholder_clean = str_replace('_code', '', $placeholder_clean);
								$format = false;
							} else {
								$format = true;
							}

							$country_or_state = is_callable( array( $order, "get_{$placeholder_clean}" ) ) ? call_user_func( array( $order, "get_{$placeholder_clean}" ) ) : false;

							if ($format === true) {
								// format country or state
								if (in_array($placeholder_clean, $country_placeholders)) {
									$country_or_state = ( $country_or_state && isset( $countries->countries[ $country_or_state ] ) ) ? $countries->countries[ $country_or_state ] : $country_or_state;
								} elseif (in_array($placeholder_clean, $state_placeholders)) {
									// get country for address
									$callback         = 'get_'.str_replace( 'state', 'country', $placeholder_clean );
									$country          = call_user_func( array( $order, $callback ) );
									$country_or_state = ( $country && $country_or_state && isset( $countries->states[ $country ][ $country_or_state ] ) ) ? $countries->states[ $country ][ $country_or_state ] : $country_or_state;
								}
							}

							if ( !empty( $country_or_state ) ) {
								$text = $this->replace_placeholder($placeholder, $country_or_state, $text, $placeholder_clean, $order );
								continue 2;
							}
						}
					}

					// Custom placeholders
					$custom = '';
					switch ($placeholder_clean) {
						case 'site_title':
							$custom = get_bloginfo();
							break;
						case 'shipping_notes':
						case 'customer_note':
							if ( is_callable( array( $order, 'get_customer_note' ) ) ) {
								$custom = $order->get_customer_note();
								if ( ! empty( $custom ) ) {
									$custom = wpautop( wptexturize( $custom ) );
								}
							} else {
								$custom = '';
							}
							break;
						case 'order_number':
							if ( is_callable( array( $order, 'get_order_number' ) ) ) {
								$custom = ltrim( $order->get_order_number(), '#' );
							} else {
								$custom = '';
							}
							break;
						case 'date':
							$custom = date_i18n( get_option( 'date_format' ) );
							break;
						case 'order_date':
							if ( is_callable( array( $order, 'get_date_created' ) ) ) {
								$order_date = $order->get_date_created();
								$custom     = $order_date->date_i18n( wc_date_format() );
							} else {
								$custom     = '';
							}
							break;
						case 'order_time':
							if ( is_callable( array( $order, 'get_date_created' ) ) ) {
								$order_date = $order->get_date_created();
								$custom     = $order_date->date_i18n( wc_time_format() );
							} else {
								$custom     = '';
							}
							break;
						case 'shipping_method':
							if ( is_callable( array( $order, 'get_shipping_method' ) ) ) {
								$custom = $order->get_shipping_method();
							} else {
								$custom = '';
							}
							break;
						case 'order_total':
							if ( is_callable( array( $order, 'get_formatted_order_total' ) ) ) {
								$custom = $order->get_formatted_order_total();
							} else {
								$custom = '';
							}
							break;
						case 'order_items':
							$custom = $this->get_item_list( $order, 'basic' );
							break;
						case 'order_items_sku':
							$custom = $this->get_item_list( $order, 'sku' );
							break;
						case 'order_items_full':
							$custom = $this->get_item_list( $order, 'full' );
							break;
						case 'sku_list':
							$custom = $this->get_sku_list( $order );
							break;
						case 'order_weight':
							$custom = $this->get_order_weight( $order );
							break;
						case 'total_qty':
							$items = $order->get_items();
							$total_qty = 0;
							if( sizeof( $items ) > 0 ) {
								foreach( $items as $item ) {
									$total_qty += $item['qty'];
								}
							}
							$custom = $total_qty;
							break;
						case 'order_barcode':
							if ( ! empty( $order ) && function_exists( 'wcub_get_barcode' ) ) {
								$barcode = wcub_get_barcode( $order );
								if( $barcode->exists() ) {
									$custom = sprintf( '<div class="order-barcode">%s</div>', $barcode->get_output() );
								} else {
									$custom = '';
								}
							} else {
								$custom = '';
							}
							break;
						case 'wc_order_barcode':
							if ( function_exists('WC_Order_Barcodes') && is_callable( array( WC_Order_Barcodes(), 'display_barcode' ) ) ) {
								if ( defined( 'WC_ORDER_BARCODES_VERSION' ) && version_compare( WC_ORDER_BARCODES_VERSION, '1.3.23', '>=' ) ) {
									$barcode = WC_Order_Barcodes()->display_barcode( $order->get_id(), true ); // image
								} elseif ( defined( 'WC_ORDER_BARCODES_VERSION' ) && version_compare( WC_ORDER_BARCODES_VERSION, '1.3.19', '>=' ) ) {
									$barcode = WC_Order_Barcodes()->display_barcode( $order->get_id() );       // HTML
								} else {
									ob_start();
									WC_Order_Barcodes()->display_barcode( $order->get_id() );
									$barcode = ob_end_clean();
								}
								$barcode = str_replace( array( "\r", "\n" ), '', $barcode );
								$custom = sprintf( '<div class="wc-order-barcode">%s</div>', $barcode );
							}
							break;
						default:
							break;
					}
					if ( !empty( $custom ) ) {
						$text = $this->replace_placeholder($placeholder, $custom, $text, $placeholder_clean, $order );
						continue;
					}

					// Order Properties
					// convert shorthand placeholders first
					if (in_array($placeholder_clean, array('shipping_address', 'billing_address'))) {
						$placeholder_clean = "formatted_{$placeholder_clean}";
					} elseif ( $placeholder_clean == 'payment_method' ) {
						$placeholder_clean = 'payment_method_title';
					}

					if ( is_callable( array( $order, "get_{$placeholder_clean}" ) ) ) {
						$prop = trim( call_user_func( array( $order, "get_{$placeholder_clean}" ) ) );
						if ( ! empty( $prop ) ) {
							$text = $this->replace_placeholder( $placeholder, $prop, $text, $placeholder_clean, $order );
							continue;
						}
					}

					// Order Meta
					if ( !$this->is_order_prop( $placeholder_clean ) ) {
						$meta = $order->get_meta( $placeholder_clean );
						if ( !empty( $meta ) ) {
							$text = $this->replace_placeholder($placeholder, $meta, $text, $placeholder_clean, $order );
							continue;
						} elseif ( substr( $placeholder_clean, 0, 1 ) !== '_' ) {
							// Fallback to hidden meta
							$meta = $order->get_meta( "_{$placeholder_clean}" );
							if ( !empty( $meta ) && !( substr( $meta, 0, 6 ) == 'field_' && class_exists('ACF') ) ) {
								$text = $this->replace_placeholder($placeholder, $meta, $text, $placeholder_clean, $order );
								$text = str_replace($placeholder, $meta, $text);
								continue;
							}
						}
					}
				}
			}

			// check qr tag
			if ( strpos($text, '[qr_code') !== false ) {
				// get qr code tag (with arguments)
				preg_match('/\[qr_code.*?\]/', $text, $qr_tag);
				$qr_tag = array_shift($qr_tag); // we only need the first match set

				// strip arguments
				$_qr_args = trim( str_replace( array('[qr_code',']'), '', $qr_tag) );
				$qr_args = array();
				if (!empty($_qr_args)) {
					$_qr_args = explode(' ', $_qr_args);
					foreach ($_qr_args as $qr_arg) {
						$arg_extract = explode('=', $qr_arg);
						$qr_args[$arg_extract[0]] = $arg_extract[1];
					}
				}
				// build qr code image url
				if ( is_callable( array( $order, 'get_formatted_shipping_address' ) ) ) {
					$qr_address = str_replace( $qr_tag, '', $order->get_formatted_shipping_address() );
					$qr_data    = urlencode( trim( preg_replace( '#<br\s*/?>#i', "\n", $qr_address ) ) );
					$size       = isset( $qr_args['size'] ) ? $qr_args['size'] : '200';

					// $qr_url = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$qr_data}&choe=UTF-8";
					$qr_code_args = array(
						'action' => 'wpo_wclabels_qr_code',
						'code'   => $qr_data,
					);
					$qr_url = wp_nonce_url( add_query_arg( $qr_code_args, admin_url( "admin-ajax.php" ) ), 'wpo_wclabels_qr_code' );

					// create image tag
					$qr_image = sprintf( '<img src="%s" class="qr-code" style="width:%spx; height:auto;"/>', esc_url( $qr_url ), $size );

					// replace qr_code placeholder
					$text = str_replace( $qr_tag, $qr_image, $text );
				}				
			}

			// remove empty placeholder lines, but preserve user-defined empty lines
			if (isset($this->label_settings['remove_whitespace'])) {
				// break formatted address into lines
				$text = explode("\n", $text);
				// loop through address lines and check if only placeholders (remove HTML formatting first)
				foreach ($text as $key => $text_line) {
					// strip html tags for checking
					$clean_line = trim(strip_tags($text_line));
					// clean zero-width spaces
					$clean_line = str_replace("\xE2\x80\x8B", "", $clean_line);
					if (empty($clean_line)) {
						continue; // user defined newline!
					}
					// check without leftover placeholders
					$clean_line = trim( str_replace($placeholders_used, '', $clean_line) );

					// remove empty lines
					if (empty($clean_line)) {
						unset($text[$key]);
					}
				}

				// glue address lines back together
				$text = implode("\n", $text);
			}

			// remove leftover placeholders
			$text = str_replace($placeholders_used, '', $text);

			return apply_filters( 'wclabels_formatted_address', $text, $text_format, $order, $address_data );
		}

		public function qr_code() {
			if ( !check_admin_referer( $_GET['action'] ) ) {
				die();
			}
			if (!class_exists('WPO_WCLABELS_QRcode')) {
				include_once( 'phpqrcode.php' );
			}
			$code = isset($_GET['code']) ? $_GET['code'] : '';
			WPO_WCLABELS_QRcode::png(sanitize_textarea_field( $code ), null, WPO_WCLABELS_QR_ECLEVEL_L, 4 );
			die();
		}

		/**
		 * Filtered wrapper for str_replace
		 * @param  string $placeholder       placeholder tag
		 * @param  string $replacement       text to write over placeholder
		 * @param  string $text              full text with placeholders
		 * @param  string $placeholder_clean placeholder without any parentheses
		 * @param  object $order             \WC_Order
		 * @return string                    text with placeholder replaced
		 */
		public function replace_placeholder( $placeholder, $replacement, $text, $placeholder_clean, $order ) {
			// backwards compatibility for old meta array filter (DEPRECATED!)
			// Use wclabels_address_placeholder_replacement instead of the this
			$meta = apply_filters( 'wclabels_formatted_address_replacement_data', array( $placeholder_clean => $replacement ), $order );
			$replacement = $meta[$placeholder_clean];
			// ... and with underscore prefix
			$meta = apply_filters( 'wclabels_formatted_address_replacement_data', array( "_{$placeholder_clean}" => $replacement ), $order );
			$replacement = $meta["_{$placeholder_clean}"];

			$replacement = apply_filters( 'wclabels_address_placeholder_replacement', $replacement, $placeholder, $placeholder_clean, $order );

			// make replacement
			$text = str_replace($placeholder, $replacement, $text);

			// echo '<pre>';var_dump($text);echo '</pre>';die();

			return $text;
		}

		public function is_order_prop( $key ) {
			// Taken from WC class
			$order_props = array(
				// Abstract order props
				'parent_id',
				'status',
				'currency',
				'version',
				'prices_include_tax',
				'date_created',
				'date_modified',
				'discount_total',
				'discount_tax',
				'shipping_total',
				'shipping_tax',
				'cart_tax',
				'total',
				'total_tax',
				// Order props
				'customer_id',
				'order_key',
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_state',
				'billing_postcode',
				'billing_country',
				'billing_email',
				'billing_phone',
				'shipping_first_name',
				'shipping_last_name',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_state',
				'shipping_postcode',
				'shipping_country',
				'payment_method',
				'payment_method_title',
				'transaction_id',
				'customer_ip_address',
				'customer_user_agent',
				'created_via',
				'customer_note',
				'date_completed',
				'date_paid',
				'cart_hash',
			);
			return in_array($key, $order_props);
		}

		public function is_virtual_downloadable( $order ) {
			// assume it's true until we find an item that is not
			$is_virtual = $is_downloadable = true;

			// loop through items
			$items = $order->get_items();
			foreach ($items as $item_id => $item) {
				if ( is_callable( array( $item, 'get_product' ) ) ) { // WC4.4+
					$product = $item->get_product();
				} else {
					$product = $order->get_product_from_item( $item );
				}
				// for non-existing products, assume it's not virtual/downloadable
				if (!$product) {
					$is_virtual = $is_downloadable = false;
					break;
				}

				if (!$product->is_downloadable()) {
					$is_downloadable = false;
				}

				if (!$product->is_virtual()) {
					$is_virtual = false;
				}
			}

			return compact('is_virtual','is_downloadable');
		}

		public function get_item_list( $order, $format = 'basic' ) {
			$items = $order->get_items();
			if ( count( $items ) < 1 ) {
				return false;
			} else {
				ob_start();
				?>
				<ul class="order-items">
					<?php
						foreach ( $items as $item_id => $item ) {
							if ( ! empty( $product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : $order->get_product_from_item( $item ) ) ) {
								if ( ( $product->is_downloadable() !== false && isset( $_REQUEST['wclabels_exclude_downloadable'] ) ) || ( $product->is_virtual() !== false && isset( $_REQUEST['wclabels_exclude_virtual'] ) ) ) {
									continue;
								}
							}

							$qty  = $item->get_quantity();
							$name = $item->get_name();

							printf( '<li class="%s">', apply_filters( 'wclabels_item_class', 'item-' . $item_id, $item, $order ) ); // opening li tag

							if ( $format == 'basic' ) {
								printf( '<span class="qty">%1$sx</span> <span class="item-name">%2$s</span>', $qty, $name );
							} else {
								$product  = $item->get_product();
								$qty_html = "<span class='qty'>{$qty}x</span>";
								if ( is_object( $product ) && ( $sku = $product->get_sku() ) ) {
									$sku_html  = "<span class='sku'>{$sku}</span>";
									$name_html = "<span class='item-name'> - {$name}</span>";							
								} else {
									$sku_html  = '';
									$name_html = "<span class='item-name'>{$name}</span>";							
								}
								if ( $format == 'full' ) {
									$meta_html = wc_display_item_meta( $item, array( 'echo' => false ) );
								} else {
									$meta_html = '';
								}
								echo "{$qty_html} {$sku_html}{$name_html}{$meta_html}";
							}
							
							echo "</li>"; // closing li tag
						} // endforeach
					?>
				</ul>
				<?php
				return apply_filters( 'wclabels_placeholder_order_items', ob_get_clean(), $order );
			}
		}

		public function get_sku_list( $order ) {
			if ( $items = $order->get_items() ) {
				ob_start();
				?>
				<ul class="sku-list">
				<?php foreach( $items as $item_id => $item ) {
					$product = $item->get_product();
					if ( is_object($product) && $sku = $product->get_sku() ) {
						printf( '<li>%s</li>', $sku );
					}
					?>
				<?php } //endforeach ?>
				</ul>
				<?php
				return apply_filters( 'wclabels_placeholder_order_items_sku', ob_get_clean(), $order );
			}
		}

		public function get_order_weight( $order ) {
			// calculate total weight
			$total_weight = 0;
			$items = $order->get_items();
			if( sizeof( $items ) > 0 ) {
				foreach ( $items as $item ) {
					if ( is_callable( array( $item, 'get_product' ) ) ) { // WC4.4+
						$product = $item->get_product();
					} else {
						$product = $order->get_product_from_item( $item );
					}
					if ( !empty($product) && is_numeric($product->get_weight()) ) {
						$total_weight += (int) $item['qty'] * $product->get_weight();
					}
				}
			}

			return $total_weight;
		}

		/**
		 * Checks if current user is a vendor and if he/she is allowed to access data from this order
		 * @param  bool  $not_allowed result of privileges check (true = not allowed)
		 * @param  array $order_ids   array of order_ids
		 * @return bool               not allowed
		 */
		public function wcvendors_privs ( $not_allowed, $order_ids ) {
			// check if user is vendor
			if ( $not_allowed && in_array('vendor', $GLOBALS['current_user']->roles) ) {
				// get all vendor orders by vendor products
				// method is non-static, ignore for now: @
				$vendor_products = @WCV_Vendor_Order_Page::get_vendor_products( get_current_user_id() );
				$product_ids = array();
				foreach ($vendor_products as $_product) {
					$product_ids[] = $_product->ID;
				}
				$vendor_orders = @WCV_Vendor_Order_Page::get_orders_for_vendor_products( $product_ids );
				// extract order_id from objects
				$vendor_order_ids = array();
				foreach ($vendor_orders as $vendor_order) {
					$vendor_order_ids[] = $vendor_order->order_id;
				}

				// check if all order_ids are from vendor
				foreach ($order_ids as $order_id) {
					if (!in_array( $order_id, $vendor_order_ids)) {
						// one of the order_ids is not from the vendor!
						return true; // not allowed!
					}
				}

				// if we got here, that means the user is a vendor and all orders belong to this vendor
				return false; // allowed!
			} else {
				return $not_allowed; // preserve original check result
			}
		}

		public function get_date_string_from_input( $date_key, $hour_key, $minute_key, $include_minute = false, $utc_timestamp = false  ) {
			$date   = isset( $_REQUEST[$date_key] ) ? sanitize_text_field( $_REQUEST[$date_key] ) : null;
			$hour   = isset( $_REQUEST[$hour_key] ) ? sanitize_text_field( $_REQUEST[$hour_key] ) : null;
			$minute = isset( $_REQUEST[$minute_key] ) ? sanitize_text_field( $_REQUEST[$minute_key] ) : null;

			if ( $date_key == 'date-to' ) {
				// store last export date & time
				update_option( 'wpo_wclabels_last_export', array( 'date' => $date, 'hour' => $hour, 'minute' => $minute ) );
			}

			if ( empty( $date ) ) {
				return false;
			}

			if ( ! empty( $hour ) ) {
				$seconds = $include_minute ? '59' : '00';
				$date    = sprintf( "%s %02d:%02d:%02d", $date, $hour, $minute, $seconds );
			}

			if ( $utc_timestamp ) {
				// Convert local WP timezone to UTC.
				if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $date, $date_bits ) ) {
					$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
					$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
				} else {
					$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $date ) ) ) );
				}
				$date = $timestamp;
			}

			return $date;
		}

	} // end class
} // end class_exists
