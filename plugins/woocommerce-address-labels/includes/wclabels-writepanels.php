<?php

if ( !class_exists( 'WooCommerce_Address_Labels_Writepanels' ) ) {

	class WooCommerce_Address_Labels_Writepanels {

		/**
		 * @var array
		 */
		public $interface_settings;

		public function __construct() {
			$this->interface_settings = get_option( 'wpo_wclabels_interface_settings', array() );

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '>=' ) ) {
				add_action( 'bulk_actions-edit-shop_order', array( $this, 'bulk_actions' ), 20 );
				add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'bulk_actions' ), 20 ); // WC 7.1+
				add_action( 'bulk_actions-edit-shop_subscription', array( $this, 'bulk_actions' ), 20 );
				add_action( 'bulk_actions-woocommerce_page_wc-orders--shop_subscription', array( $this, 'bulk_actions' ), 20 ); // WC 7.1+
				add_action( 'bulk_actions-edit-wc_user_membership', array( $this, 'bulk_actions' ), 20 );
				add_action( 'bulk_actions-woocommerce_page_wc-orders--wc_user_membership', array( $this, 'bulk_actions' ), 20 ); // WC 7.1+
				add_action( 'bulk_actions-edit-toplevel_page_wcv-vendor-orders', array( $this, 'bulk_actions' ), 20 );
			} else {
				add_action( 'admin_footer', array( $this, 'bulk_actions_js' ) );
			}
			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
			add_action( 'add_meta_boxes_shop_order', array( $this, 'add_box' ) );
			add_action( 'add_meta_boxes_woocommerce_page_wc-orders', array( $this, 'add_box' ) );
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'order_actions_button' ) );
			add_action( 'admin_footer', array( $this, 'offset_dialog' ) );
		}

		/**
		 * Add actions to menu, WP3.5+
		 */
		public function bulk_actions( $actions ) {
			foreach ( $this->get_bulk_actions() as $action => $title ) {
				$actions[$action] = $title;
			}
			return $actions;
		}

		public function get_bulk_actions() {
			$actions = array();
			$actions['address-labels'] = __( 'Print Address Labels', 'wpo_wclabels' );
	
			return apply_filters( 'wpo_wclabels_bulk_actions', $actions );
		}

		/**
		 * Add print address labels action to bulk action drop down menu
		 *
		 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031
		 *
		 * @access public
		 * @return void
		 */
		public function bulk_actions_js() {
			$screen = get_current_screen();

			if (
				! is_null( $screen ) &&
				in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders', 'edit-shop_subscription', 'edit-wc_user_membership', 'toplevel_page_wcv-vendor-orders', ), true )
            ) {
				?>
				<script type="text/javascript">
				jQuery(document).ready(function($) {
					<?php foreach ( $this->get_bulk_actions() as $action => $title ) { ?>
						$('<option>')
							.val('<?php echo $action; ?>')
							.html('<?php echo esc_attr( $title ); ?>')
							.appendTo("select[name='action'], select[name='action2']");
					<?php }	?>
				});
				</script>
				<?php
			}
		}

		/**
		 * Single address label from order actions
		 */
		public function order_actions_button ($order) {
			if ( empty( $order ) ) {
				return;
			}

			$order_id = $order->get_id();
			$url      = 'edit.php?&action=wclabels&order_ids='.$order_id.'&offset=';
			$alt      = esc_attr__( 'Print Address Labels', 'wpo_wclabels' );

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '>=' ) ) {
				$button_content = __( 'Print Address Labels', 'wpo_wclabels' );
				$style          = '';
			} else {
				$button_content = '<span class="dashicons dashicons-tickets-alt"></span>';
				$style          = 'height: 2em!important;text-align:center; padding:0; width: auto; min-width: 2em;';
			}
			printf( '<a href="%1$s" class="button tips wclabels" target="_blank" alt="%2$s" data-tip="%2$s" data-order-id="%4$s" style="%5$s">%3$s</a>', $url, $alt, $button_content, $order_id, $style );
		}

		public function offset_dialog () {
			$screen = get_current_screen();

			if (
				! is_null( $screen ) &&
				in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders', 'edit-shop_subscription', 'edit-wc_user_membership', 'toplevel_page_wcv-vendor-orders' ), true )
			) {
				?>
				<div id="wclabels_offset_dialog" style="display:none;">
					<?php _e( 'Labels to skip', 'wpo_wclabels' ); ?>:
					<input type="text" size="2" class="wclabels_offset">
					<img src="<?php echo WPO_WCLABELS()->plugin_url() . '/assets/images/wclabels-offset-icon.png'; ?>" id="wclabels-offset-icon" style="vertical-align: middle;">
					<button class="button" style="display:none; margin-top: 4px"><?php _e( 'Print', 'wpo_wclabels' ); ?></button>
				</div>
				<?php
			}
		}

		/**
		 * JS for print action & CSS (Google font preloading)
		 */
		public function load_scripts_styles ( $hook ) {
			$screen = get_current_screen();

			if (
                ( ! is_null( $screen ) && in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders', 'edit-shop_subscription', 'edit-wc_user_membership', 'toplevel_page_wcv-vendor-orders' ), true ) ) ||
				$hook === WPO_WCLABELS()->settings->options_page_hook
			) {
				wp_enqueue_style(
					'wclabels-admin-styles',
					WPO_WCLABELS()->plugin_url() . '/assets/css/wclabels-admin-styles.css',
					array(),
					WPO_WCLABELS_VERSION
				);

				wp_enqueue_script(
					'wclabels-print',
					WPO_WCLABELS()->plugin_url() . '/assets/js/wclabels-print.js',
					array( 'jquery', 'jquery-ui-datepicker' ),
					WPO_WCLABELS_VERSION
				);

                $post_type = in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ), true ) ? 'shop_order' : get_post_type();

				wp_localize_script(
					'wclabels-print',
					'wclabels_print',
					array(
						'ajaxurl'      => admin_url( 'admin-ajax.php' ),
						'nonce'        => wp_create_nonce( 'wpo_wclabels_print' ),
						'preview'      => isset( $this->interface_settings['preview'] ) ? 'true' : 'false',
						'offset'       => isset( $this->interface_settings['offset'] ) ? 1 : '',
						'offset_icon'  => WPO_WCLABELS()->plugin_url() . '/assets/images/wclabels-offset-icon.png',
						'offset_label' => __( 'Labels to skip', 'wpo_wclabels' ),
						'post_type'    => apply_filters( 'wpo_wclabels_print_post_type', $post_type ),
					)
				);
			}

			if (
				( ! is_null( $screen ) && in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders', 'edit-shop_subscription', 'edit-wc_user_membership' ), true ) ) ||
				'woocommerce_page_wpo_wclabels_options_page' === $hook
			) {
				if ( isset( $wpo_wclabels->print->label_settings['font'] ) && $wpo_wclabels->print->label_settings['font']['family'] != 'sans-serif' ) {
					$google_font_url = $wpo_wclabels->print->google_font_url( $wpo_wclabels->print->label_settings['font'] );
					wp_enqueue_style( 'wclabels-google-font', $google_font_url );
				}
			}

			if ( str_contains( $hook, 'wpo_wclabels_options_page' ) ) {
				wp_enqueue_media();
				wp_enqueue_script(
					'wclabels-image-placeholders',
					WPO_WCLABELS()->plugin_url() . '/assets/js/wclabels-image-placeholders.js',
					array( 'jquery' ),
					WPO_WCLABELS_VERSION //version
				);
			}
		}
		/**
		 * Add the meta box on the single order page
		 */
		public function add_box() {
			add_meta_box( 'wpo_wclabels-box', __( 'Print Address Labels', 'wpo_wclabels' ), array( $this, 'create_box_content' ), WPO_WCLABELS()->order_util->custom_order_table_screen(), 'side' );
		}

		/**
		 * Create the meta box content on the single order page
		 */
		public function create_box_content( $post_or_order_object ) {
			$object = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

			$url   = 'edit.php?&action=wclabels&order_ids=' . $object->get_id() . '&offset=';
			$alt   = esc_attr__( 'Print Address Labels', 'wpo_wclabels' );
			$title = __( 'Print Labels', 'wpo_wclabels' );

			// show offset option if enabled
			if ( isset( $this->interface_settings['offset'] ) ) {
				?>
				<?php _e( 'Labels to skip', 'wpo_wclabels' ); ?>:<br/>
				<input type="text" id="wclabels_offset" name="wclabels_offset" size="2" />
				<img src="<?php echo WPO_WCLABELS()->plugin_url() . '/assets/images/'; ?>wclabels-offset-icon.png" id="wclabels-offset-icon" style="vertical-align: middle;"><br/>
				<?php
			} else {
				?>
				<input type="text" id="wclabels_offset" name="wclabels_offset" size="2" value="0" hidden/>
				<?php
			}

			?>
			<ul class="wpo_wclabels-actions">
				<li><a href="<?php echo $url; ?>" class="button wclabels-single" target="_blank" alt="<?php echo $alt; ?>" data-id="<?php echo $object->get_id(); ?>"><?php echo $title; ?></a></li>
			</ul>
			<?php
		}

	} // end class
} // end class_exists