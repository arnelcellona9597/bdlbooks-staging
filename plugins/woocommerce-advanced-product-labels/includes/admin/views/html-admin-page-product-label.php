<h2>
	<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=advanced-product-labels' ); ?>"><?php _e( 'Advanced Product Labels', 'woocommerce-advanced-product-labels' ); ?></a> &gt;
	<?php echo esc_html( $label->post_title ); ?>
</h2>

<div class="wapl-title-text-wrap" id="titlediv">
	<legend class="screen-reader-text"><span><?php echo wp_kses_post( __( 'title', 'woocommerce-advanced-product-labels' ) ); ?></span></legend>
	<input class="input-text regular-input wapl-title-text"
		   type="text"
		   name="post_title"
		   id="title"
		   value="<?php echo esc_attr( $label->post_title ); ?>"
		   placeholder="<?php echo esc_attr( __( 'Add title', 'woocommerce-advanced-product-labels' ) ); ?>" />
</div>

<div class="wapl-meta-box-wrap" style="margin-right: 300px;" id="poststuff">
	<div id="wapl_conditions" class="postbox ">
		<h2 class="" style="border-bottom: 1px solid #eee;"><span><?php _e( 'Conditions', 'woocommerce-advanced-product-labels' ); ?></span></h2>
		<div class="inside"><?php
			require_once plugin_dir_path( __FILE__ ) . 'html-admin-page-product-label-conditions.php';
		?></div>
	</div>

	<div id="wapl_settings" class="postbox ">
		<h2 class="" style="border-bottom: 1px solid #eee;"><span><?php _e( 'Settings', 'woocommerce-advanced-product-labels' ); ?></span></h2>
		<div class="inside"><?php
			require_once plugin_dir_path( __FILE__ ) . 'html-admin-page-product-label-settings.php';
		?></div>
	</div>
</div>
