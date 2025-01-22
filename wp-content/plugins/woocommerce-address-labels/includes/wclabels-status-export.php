<form method="post" action="" id="wclabels-status-export">
	<?php wp_nonce_field( 'wpo_wclabels_export', 'wpo_wclabels_nonce' ); ?>

	<table class="form-table">
		<tr>
			<td width="180px"><?php _e( 'From', 'wpo_wclabels' ); ?></td>
			<td>
				<?php $last_export = get_option( 'wpo_wclabels_last_export', array('date'=>'','hour'=>'','minute'=>'') ); ?>
				<input type="text" id="date-from" name="date-from" value="<?php echo $last_export['date']; ?>" size="10" autocomplete="off"> <?php _e( 'at', 'wpo_wclabels' ); ?>
				<input type="text" class="hour" placeholder="h" name="hour-from" id="hour-from" maxlength="2" size="2" value="<?php echo $last_export['hour']; ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})">:<input type="text" class="minute" placeholder="m" name="minute-from" id="minute-from" maxlength="2" size="2" value="<?php echo $last_export['minute']; ?>" pattern="[0-5]{1}[0-9]{1}"> (<?php _e( 'optional', 'wpo_wclabels' ); ?>)
			</td>
		</tr>
		<tr>
			<td><?php _e( 'To', 'wpo_wclabels' ); ?></td>
			<td>
				<?php $now = array('date'=>date_i18n('Y-m-d'),'hour'=>date_i18n('H'),'minute'=>date_i18n('i')); ?>
				<input type="text" id="date-to" name="date-to" value="<?php echo $now['date']; ?>" size="10" autocomplete="off"> <?php _e( 'at', 'wpo_wclabels' ); ?>
				<input type="text" class="hour" placeholder="h" name="hour-to" id="hour-to" maxlength="2" size="2" value="<?php echo $now['hour']; ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})">:<input type="text" class="minute" placeholder="m" name="minute-to" id="minute-to" maxlength="2" size="2" value="<?php echo $now['minute']; ?>" pattern="[0-5]{1}[0-9]{1}">
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php _e( 'Filter status', 'wpo_wclabels' ); ?>
			</td>
			<td>
				<fieldset>
					<input type="checkbox" class="checkall" /><?php _e('All statuses', 'wpo_wclabels' ); ?><br />
					<hr/ style="width:100px;text-align:left;margin-left:0;height: 1px;border: 0; border-top: 1px solid #ccc;padding: 0;">
					<?php
					// get list of WooCommerce statuses
					$statuses = wc_get_order_statuses();
					foreach ( $statuses as $status_slug => $status ) {
						// $status_slug   = 'wc-' === substr( $status_slug, 0, 3 ) ? substr( $status_slug, 3 ) : $status_slug;
						$order_statuses[$status_slug] = $status;
					}

					// list status checkboxes
					foreach ($order_statuses as $status_slug => $status) {
						printf('<input type="checkbox" id="status_filter[]" name="status_filter[]" value="%s" /> %s<br />', $status_slug, $status);
					}
					?>
				</fieldset>
			</td>
		</tr>
		<?php if (isset($this->interface_settings['offset'])) : // show offset option if enabled ?>
		<tr>
			<td><?php _e( 'Labels to skip', 'wpo_wclabels' ); ?></td>
			<td>
				<input type="text" id="wclabels_offset" name="wclabels_offset" size="2" />
				<img src="<?php echo WPO_WCLABELS()->plugin_url() . '/assets/images/'; ?>wclabels-offset-icon.png" id="wclabels-offset-icon" style="vertical-align: middle;"><br/>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td><?php _e( 'Exclude virtual products', 'wpo_wclabels' ); ?></td>
			<td>
				<input type="checkbox" id="wclabels_is_virtual" name="wclabels_exclude_virtual"/>
			</td>
		</tr>
		<tr>
			<td><?php _e( 'Exclude downloadable products', 'wpo_wclabels' ); ?></td>
			<td>
				<input type="checkbox" id="wclabels_is_downloadable" name="wclabels_exclude_downloadable"/>
			</td>
		</tr>
		<?php
			// Allow 3rd parties to append custom settings
			do_action( 'wpo_wclabels_export_after_settings' );
		?>
	</table>

	<?php if (!isset($this->interface_settings['offset'])) : // hidden offset option if not enabled ?>
		<input type="text" id="wclabels_offset" name="wclabels_offset" size="2" value="0" hidden/>
	<?php endif; ?>

	<?php submit_button( __( 'Print address labels', 'wpo_wclabels' ) ); ?>
</form>
<p style="width: 500px; max-width: 100%; padding: 10px; background-color:white; border:1px solid #ccc;"><em><?php _e('Only exporting a few addresses? You can also print your labels by selecting them in the WooCommerce order overview and then select "Print address labels" from the bulk actions!', 'wpo_wclabels' ); ?></em></p>