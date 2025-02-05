<?php
/**
 * WooCommerce Shipping Settings
 *
 * @package     WooCommerce\Admin
 * @version     2.6.0
 */

use Automattic\Jetpack\Constants;
use Automattic\WooCommerce\Internal\Admin\WCAdminAssets;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WAPL_Settings_Product_Labels', false ) ) {
	return new WAPL_Settings_Product_Labels();
}

/**
 * WC_Settings_Shipping.
 */
class WAPL_Settings_Product_Labels extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'advanced-product-labels';
		$this->label = __( 'Product Labels', 'woocommerce-advanced-product-labels' );

		// Table field type
		add_action( 'woocommerce_admin_field_advanced_product_labels', array( $this, 'generate_advanced_product_labels_field' ) );

		parent::__construct();
	}

	/**
	 * Get own sections.
	 *
	 * @return array
	 */
	protected function get_own_sections() {
		return array(
			'' => __( 'Product Labels', 'woocommerce-advanced-product-labels' ),
//			'options' => __( 'Options', 'woocommerce-advanced-product-labels' ),
		);
	}

	/**
	 * Get settings for the default section.
	 *
	 * The original implementation of 'get_settings' was returning the settings for the "Options" section
	 * when the supplied value for $current_section was ''.
	 *
	 * @return array
	 */
	protected function get_settings_for_default_section() {
		return $this->get_settings_for_options_section();
	}

	/**
	 * Get settings for the options section.
	 *
	 * @return array
	 */
	protected function get_settings_for_options_section() {
		return apply_filters( 'woocommerce_wapl_settings', array(

			array(
				'title' => __( 'Advanced Product Labels', 'woocommerce-advanced-product-labels' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'wapl_general',
			),

			array(
				'title'    => __( 'Enable/Disable', 'woocommerce-advanced-product-labels' ),
				'desc'     => __( 'When disabled you will still be able to add/modify labels, but none will be displayed on the front.', 'woocommerce-advanced-product-labels' ),
				'id'       => 'enable_wapl',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			),

			array(
				'title'    => __( 'Show on the detail page', 'woocommerce-advanced-product-labels' ),
				'desc'     => __( 'Show the product labels also on product detail pages.', 'woocommerce-advanced-product-labels' ),
				'id'       => 'show_wapl_on_detail_pages',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false
			),

			array(
				'title' => __( 'Advanced Product Labels', 'woocommerce-advanced-product-labels' ),
				'type'  => 'advanced_product_labels',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'wapl_end'
			),

		) );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		switch ( $current_section ) {
			case '':
			default:
				if ( isset( $_GET['id'] ) && $_GET['id'] == 'new' ) {
					$new_id = wp_insert_post( array(
						'post_type' => 'wapl',
						'post_status' => 'publish',
					) );
					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=advanced-product-labels&id=' . $new_id ) );
					die;
				} elseif ( isset( $_GET['id'] ) ) {
					$this->edit_label_screen( $_GET['id']);
				} else {
					parent::output();
				}
				break;

			case 'other-section-id' :

				break;
		}
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		switch ( $current_section ) {
			case 'options':

				break;
			case '':
			default:
				if ( isset( $_GET['id'] ) ) {
					$this->save_label();
				} else {
					$this->save_settings_for_current_section();
					$this->do_update_options_action();
				}
				break;
		}
	}

	/**
	 * Render edit label screen.
	 */
	protected function edit_label_screen( $label_id ) {
		$label = get_post( $label_id );

		if ( ! $label ) {
			wp_die( esc_html__( 'Invalid label!', 'woocommerce-advanced-product-labels' ) );
		}

		wp_enqueue_media();

		$preview_product_id = get_option( 'wapl_preview_product_id', 0 );

		if ( $preview_product_id ) {
			$preview_product = wc_get_product( $preview_product_id );
			$GLOBALS['product'] = $preview_product;
			ob_start();
				woocommerce_template_loop_product_thumbnail();
			$image = ob_get_clean();
		} else {
			$image_size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
			$image      = wc_placeholder_img( $image_size, array() );
		}

		require_once plugin_dir_path( __FILE__ ) . './class-wapl-condition.php'; // Class needs to be loaded
		include_once dirname( __FILE__ ) . '/views/html-admin-page-product-label.php';
	}


	/**
	 * Save label.
	 */
	protected function save_label() {
		$id = (int) $_GET['id'];

		if ( ! isset( $_POST['wapl_global_label_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wapl_global_label_meta_box_nonce'], 'wapl_global_label_meta_box' ) ) {
			return $id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $id;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $id;
		}

		$posted = wp_parse_args( $_POST, array(
			'title'            => '',
			'_wapl_label'      => array(),
		) );

		// Post
		wp_update_post( array(
			'ID'         => $id,
			'post_title' => sanitize_text_field( $posted['post_title'] ),
		) );


		$label = array(
			'type'                          => in_array( $posted['_wapl_label']['type'], array_keys( wapl_get_label_types() ) ) ? $posted['_wapl_label']['type'] : '',
			'text'                          => wp_kses_post( $posted['_wapl_label']['text'] ),
			'style'                         => in_array( $posted['_wapl_label']['style'], array_keys( wapl_get_label_styles() ) ) ? $posted['_wapl_label']['style'] : '',
			'align'                         => sanitize_key( $posted['_wapl_label']['align'] ),
			'position'                      => wc_clean( $posted['_wapl_label']['position'] ),
			'label_custom_background_color' => sanitize_text_field( $posted['_wapl_label']['label_custom_background_color'] ),
			'label_custom_text_color'       => sanitize_text_field( $posted['_wapl_label']['label_custom_text_color'] ),
			'custom_image'                  => absint( $posted['_wapl_label']['custom_image'] ),
			'conditions'                    => wpc_sanitize_conditions( $_POST['conditions'] ),
			'enable_advanced'               => isset( $posted['_wapl_label']['enable_advanced'] ),
			'custom_css'                    => sanitize_textarea_field( $posted['_wapl_label']['custom_css'] ),
		);

		if ( $label['type'] !== 'custom' ) {
			$label['custom_image'] = '';
		}

		update_post_meta( $id, '_wapl_global_label', $label );

		// Thumbnail ID
		if ( isset( $_POST['product_id'] ) ) {
			update_option( 'wapl_preview_product_id', absint( $_POST['product_id'] ), false );
		}

		do_action( 'wapl_save_label', $id, $label );

		// Clear cache
		wp_cache_delete( 'global_labels', 'woocommerce-advanced-product-labels' );
	}


	/**
	 * Table field type.
	 *
	 * Load and render table as a field type.
	 */
	public function generate_advanced_product_labels_field() {
		ob_start();
			require_once plugin_dir_path( __FILE__ ) . 'views/html-admin-page-product-labels-table.php';
		ob_end_flush();
	}
}

return new WAPL_Settings_Product_Labels();
