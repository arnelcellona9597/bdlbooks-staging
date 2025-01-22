<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$labels = wapl_get_advanced_product_labels( array( 'post_status' => array( 'draft', 'publish' ) ) );

?><tr valign='top'>
	<th scope='row' class='titledesc'><?php
		_e( 'Labels', 'woocommerce-advanced-product-labels' ); ?><br />
	</th>
	<td class='forminp' id='woocommerce-advanced-product-labels-overview'>

		<table class='wpc-conditions-post-table wpc-sortable-post-table widefat striped'>
			<thead>
				<tr>
					<th style='width: 17px;' class="column-cb check-column"></th>
					<th style='padding-left: 10px;' class="column-primary"><?php _e( 'Title', 'woocommerce-advanced-product-labels' ); ?></th>
					<th class=""><?php _e( 'Enabled', 'woocommerce-advanced-product-labels' ); ?></th>
					<th style='padding-left: 10px;'><?php _e( 'Text', 'woocommerce-advanced-product-labels' ); ?></th>
					<th style='padding-left: 10px;'><?php _e( 'Type', 'woocommerce-advanced-product-labels' ); ?></th>
				</tr>
			</thead>
			<tbody><?php

				$i = 0;
				foreach ( $labels as $label ) :

					$settings = get_post_meta( $label->ID, '_wapl_global_label', true );
					$alt      = ( $i++ ) % 2 == 0 ? 'alternate' : '';
					?><tr data-id="<?php echo absint( $label->ID ); ?>">

						<td class='sort' width="1%">
							<input type='hidden' name='sort[]' value='<?php echo absint( $label->ID ); ?>' />
						</td>
						<td class="column-primary">
							<strong>
								<a href='<?php echo admin_url( 'admin.php?page=wc-settings&tab=advanced-product-labels&id=' . absint( $label->ID ) ); ?>' class='row-title' title='<?php _e( 'Edit Label', 'woocommerce-advanced-product-labels' ); ?>'><?php
									echo _draft_or_post_title( $label->ID );
								?></a><?php
							?></strong>
							<div class='row-actions'>
								<span class='edit'>
									<a href='<?php echo admin_url( 'admin.php?page=wc-settings&tab=advanced-product-labels&id=' . absint( $label->ID ) ); ?>' title='<?php _e( 'Edit Label', 'woocommerce-advanced-product-labels' ); ?>'><?php
										_e( 'Edit', 'woocommerce-advanced-product-labels' ); ?>
									</a>
									 |
								</span>
								<span class='trash'>
									<a href='<?php echo get_delete_post_link( $label->ID ); ?>' title='<?php _e( 'Delete Label', 'woocommerce-advanced-product-labels' ); ?>'><?php
										_e( 'Delete', 'woocommerce-advanced-product-labels' );
									?></a>
								</span>
							</div>
						</td>
						<td class="enabled" width="1%">
							<a class="wpc-toggle-enabled" onclick="return false;"><?php
								if ( $label->post_status == 'publish' ) {
									echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="' . __( 'Enabled', 'woocommerce' ) . '">' . esc_attr__( 'Yes', 'woocommerce' ) . '</span>';
								} else {
									echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="' . __( 'Disabled', 'woocommerce' ) . '">' . esc_attr__( 'No', 'woocommerce' ) . '</span>';
								}
							?></a>
						</td>
						<td><?php echo wp_kses_post( $settings['text'] ?? '' ); ?></td>
						<td><?php echo esc_html( wapl_get_label_types( $settings['type'] ?? 'label' ) ); ?></td>

					</tr><?php

				endforeach;

				if ( empty( $labels ) ) :

					?><tr>
						<td colspan='5' style="display: table-cell;"><?php _e( 'There are no Labels. Yet...', 'woocommerce-advanced-product-labels' ); ?></td>
					</tr><?php

				endif;

			?></tbody>
			<tfoot>
				<tr>
					<td colspan='5' style='padding-left: 10px; display: table-cell;'>
						<a href='<?php echo admin_url( 'admin.php?page=wc-settings&tab=advanced-product-labels&id=new' ); ?>' class='add button'><?php _e( 'Add Product Label', 'woocommerce-advanced-product-labels' ); ?></a>
					</td>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>
