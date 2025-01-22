<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WAPL_Post_Type
 *
 * Initialize the WAPL post type
 *
 * @class       WAPL_Post_Type
 * @author     	Jeroen Sormani
 * @package		WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
class WAPL_Post_Type {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );
	}


	/**
	 * Register post type.
	 *
	 * Register the WCAM post type.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Global Labels', 'woocommerce-advanced-product-labels' ),
			'singular_name'      => __( 'Global Label', 'woocommerce-advanced-product-labels' ),
			'add_new'            => __( 'Add New', 'woocommerce-advanced-product-labels' ),
			'add_new_item'       => __( 'Add New Global Label', 'woocommerce-advanced-product-labels' ),
			'edit_item'          => __( 'Edit Global Label', 'woocommerce-advanced-product-labels' ),
			'new_item'           => __( 'New Global Label', 'woocommerce-advanced-product-labels' ),
			'view_item'          => __( 'View Global Label', 'woocommerce-advanced-product-labels' ),
			'search_items'       => __( 'Search Global Labels', 'woocommerce-advanced-product-labels' ),
			'not_found'          => __( 'No Global Labels', 'woocommerce-advanced-product-labels' ),
			'not_found_in_trash' => __( 'No Global Labels found in Trash', 'woocommerce-advanced-product-labels' ),
		);

		register_post_type( 'wapl', array(
			'label'           => 'wapl',
			'show_ui'         => true,
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'rewrite'         => array( 'slug' => 'wapl', 'with_front' => true ),
			'_builtin'        => false,
			'query_var'       => true,
			'supports'        => array( 'title' ),
			'labels'          => $labels,
		) );
	}
}
