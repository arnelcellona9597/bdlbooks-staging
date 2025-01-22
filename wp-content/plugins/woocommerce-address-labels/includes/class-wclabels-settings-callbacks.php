<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'WPO_Settings_Callbacks' ) ) {
	include( 'class-wpo-settings-callbacks.php' );
}

if ( !class_exists( 'WPO_WCLabels_Settings_Callbacks' ) ) :

class WPO_WCLabels_Settings_Callbacks extends WPO_Settings_Callbacks {
	/**
	 * Page layout setting callback.
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function page_layout( $args ) {
		// get main settings array
		$option = get_option( $args['option_name'] );

		$rows             = isset($option['rows'])?$option['rows']:'';
		$cols             = isset($option['cols'])?$option['cols']:'';
		$vertical_pitch   = isset($option['vertical_pitch'])?$option['vertical_pitch']:'';
		$horizontal_pitch = isset($option['horizontal_pitch'])?$option['horizontal_pitch']:'';
		?>
		<table class="page-layout-setting">
			<tbody>
				<tr>
					<td></td>
					<td><?php _e( 'Columns', 'wpo_wclabels' ); ?>:<br><input type="text" id="cols" name="wpo_wclabels_layout_settings[cols]" value="<?php echo $cols; ?>" size="5"></td>
					<td></td>
				</tr>
				<tr>
					<td>
						<?php _e( 'Rows', 'wpo_wclabels' ); ?>:<br>
						<input type="text" id="rows" name="wpo_wclabels_layout_settings[rows]" value="<?php echo $rows; ?>" size="5">
					</td>
					<td class="wclabels-preview-wrapper">
						<table class="wclabels-preview">
							<tr>
								<td></td>
							</tr>
						</table>
					</td>
					<td class="margin-settings">
						<strong><?php _e( 'Label spacing', 'wpo_wclabels' ); ?>:</strong>
						<table class="between-margins">
							<tr>
								<th><?php _e( 'Vertical', 'wpo_wclabels' ); ?></th>
								<td><input type="text" id="vertical_pitch" name="wpo_wclabels_layout_settings[vertical_pitch]" value="<?php echo $vertical_pitch; ?>" size="5">mm</td>
							</tr>
							<tr>
								<th><?php _e( 'Horizontal', 'wpo_wclabels' ); ?></th>
								<td><input type="text" id="horizontal_pitch" name="wpo_wclabels_layout_settings[horizontal_pitch]" value="<?php echo $horizontal_pitch; ?>" size="5">mm</td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<?php

	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	}

	/**
	 * Label contents section callback.
	 *
	 * @return void.
	 */
	public function contents_section() {
		echo __( 'Here you can modify the address formatting on the labels and/or add custom fields.', 'wpo_wclabels').'<br/>';
		echo __( 'You can use the following placeholders in addition to regular text and html tags (like h1, h2, b):', 'wpo_wclabels').'<br/>';
		?>
		<table style="background-color:#eee;border:1px solid #aaa; margin:1em; padding:1em;">
			<tr>
				<th style="text-align:left; padding:5px 5px 0 5px;"><?php _e( 'Billing fields', 'wpo_wclabels' ); ?></th>
				<th style="text-align:left; padding:5px 5px 0 5px;"><?php _e( 'Shipping fields', 'wpo_wclabels' ); ?></th>
				<th style="text-align:left; padding:5px 5px 0 5px;"><?php _e( 'Other data', 'wpo_wclabels' ); ?></th>
			</tr>
			<tr>
				<td style="vertical-align:top; padding:5px;">
					[billing_address]<br/>
					[billing_first_name]<br/>
					[billing_last_name]<br/>
					[billing_company]<br/>
					[billing_address_1]<br/>
					[billing_address_2]<br/>
					[billing_city]<br/>
					[billing_postcode]<br/>
					[billing_country]<br/>
					[billing_country_code]<br/>
					[billing_state]<br/>
					[billing_state_code]<br/>
					[billing_email]<br/>
					[billing_phone]
				</td>
				<td style="vertical-align:top; padding:5px;">
					[shipping_address]<br/>
					[shipping_first_name]<br/>
					[shipping_last_name]<br/>
					[shipping_company]<br/>
					[shipping_address_1]<br/>
					[shipping_address_2]<br/>
					[shipping_city]<br/>
					[shipping_postcode]<br/>
					[shipping_country]<br/>
					[shipping_country_code]<br/>
					[shipping_state]<br/>
					[shipping_state_code]
				</td>
				<td style="vertical-align:top; padding:5px;">
					[order_total]<br/>
					[order_weight]<br/>
					[order_number]<br/>
					[order_date]<br/>
					[order_time]<br/>
					[order_items]<br/>
					[order_items_sku]<br/>
					[order_items_full]<br/>
					[shipping_method]<br/>
					[shipping_notes]<br/>
					[customer_note]<br/>
					[date]<br/>
					[total_qty]<br/>
					[sku_list]<br/>
					[qr_code]<br/>
					[site_title]<br/>
					[custom_field_name]<br/>
					[order_barcode]<br/>
					[wc_order_barcode]
				</td>
			</tr>
		</table>
		<div>
			<p><code>[order_barcode]</code> <?php printf(
					/* translators: %s: plugin name */
					__( 'Compatible only with the %s plugin from WP Overnight.', 'wpo_wclabels' ),
					'<a href="https://wpovernight.com/downloads/woocommerce-ultimate-barcodes/" target="_blank">WooCommerce Ultimate Barcodes</a>'
				); ?></p>
			<p><code>[wc_order_barcode]</code> <?php _e( 'Compatible only with the WooCommerce Order Barcodes plugin.', 'wpo_wclabels' ); ?></p>
		</div>
		<?php
	}

	public function image_placeholders( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		printf('<table class="image-placeholders" data-uploader_title="%s" data-uploader_button_text="%s">', __( 'Select or upload an image', 'wpo_wclabels' ), __( 'Select image', 'wpo_wclabels' ) );

		$input_format = '<input type="hidden" name="%1$s[%2$s][]" value="%3$s" %4$s/>';
		$placeholder_row_format = '<tr class="%s" data-id="%d"><td class="image-placeholder-thumbnail">%s<span class="dashicons dashicons-no remove-placeholder"></td><td class="image-placeholder">%s %s</span></td></tr>';

		$input = sprintf( $input_format, $option_name, $id, 0, 'disabled="disabled"');
		printf($placeholder_row_format, "placeholder-row-template", 0, '', '', $input);
		
		if (!empty($current) && is_array($current)) {
			foreach ($current as $image_id) {
				$image_id = intval($image_id);
				$thumbnail = wp_get_attachment_image( $image_id, 'full' );
				if (empty($thumbnail)) {
					continue;
				}

				$placeholder = sprintf('<code>[label_image_placeholder id=%d]</code>', $image_id);
				$input = sprintf( $input_format, $option_name, $id, $image_id, '');
				printf($placeholder_row_format, '', $image_id, $thumbnail, $placeholder, $input );
			}
		}
		printf('<tr class="action-buttons"><td colspan="2"><a class="button create-image-placeholder">%s</a></td></tr>', __( 'Create image placeholder', 'wpo_wclabels' ) );
		echo '</table>';
	}

	/**
	 * Google webfonts selector.
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function google_webfonts( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		// get google fonts
		$google_webfonts = $this->get_google_fonts();
		// echo '<pre>';var_dump($google_webfonts);echo '</pre>';die();
		?>
		<table class="font">
			<tr>
				<td><?php _e( 'Font family', 'wpo_wclabels' ); ?></td>
				<td>
					<?php
					printf('<select name="%1$s[%2$s][family]" class="font-family">', $option_name, $id);
						$family = __( 'Browser default', 'wpo_wclabels' );
						$family_css = 'sans-serif';
						$variants = '-';
						$subsets = '';
						$current_family = isset($current['family'])?$current['family']:'';
						printf('<option style="font-family: %1$s" value="%1$s" data-variants="%2$s" data-subsets="%3$s" %4$s>%5$s</option>', $family_css, $variants, $subsets, selected( $current_family, $family_css, false ), $family);

						foreach ($google_webfonts as $key => $font) {
							// $family = "{$font['family']}, $font['category']";
							$variants = implode(',', $font['variants']);
							$subsets = implode(',', $font['subsets']);
							$current_family = isset($current['family'])?$current['family']:'';
							printf('<option style="font-family: %1$s" value="%1$s" data-variants="%2$s" data-subsets="%3$s" %4$s>%1$s</option>', $font['family'], $variants, $subsets, selected( $current_family, $font['family'], false ));
						}
						?>
					</select>
					
					<!-- Preview fonts -->
					<ul class="font-preview-list" style="display:none">
						<li style="font-family: sans-serif" data-value="sans-serif"><?php _e( 'Browser default', 'wpo_wclabels' ); ?></li>
						<?php
						foreach ($google_webfonts as $key => $font) {
							printf('<li style="font-family: %1$s" data-value="%1$s">%1$s</li>', $font['family']);
						}
						?>
					</ul>
					
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Variant', 'wpo_wclabels' ); ?></td>
				<td>
					<?php
					$current_variant = isset($current['variant'])?$current['variant']:'';
					printf('<select name="%1$s[%2$s][variant]" class="font-variant" data-current="%3$s">', $option_name, $id, $current_variant);
					?>
						<option value="none">-</option>
					</select>
				</td>
			</tr>
			<!--
			<tr>
				<td><?php _e( 'Subsets', 'wpo_wclabels' ); ?></td>
				<td>
					<select name="font-subsets" class="font-subsets" disabled>
						<option value="none">-</option>
					</select>
				</td>
			</tr>
			-->
		</table>

		<?php

	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	
	}

	public function get_google_fonts () {
		$google_webfonts = json_decode( file_get_contents( WPO_WCLABELS()->plugin_path() . '/assets/data/google-webfonts.json'), true);
		unset($google_webfonts['kind']);
		foreach ($google_webfonts['items'] as $key => $font ) {
			if (isset($font['category'])) {
				$google_webfonts[$font['category']][$key] = $font;
			}
		}
		unset($google_webfonts['items']);

		// only take top 10/5/3 of category
		foreach ($google_webfonts as $category => $fonts) {
			if (in_array($category, array('display','handwriting'))) {
				$font_count = 5;
			} elseif ($category == 'monospace') {
				$font_count = 3;
			} else {
				$font_count = 10;
			}
			$google_webfonts[$category] = array_slice($fonts, 0, $font_count);
		}
		
		// remove category top level
		$google_webfonts = $this->array_dechunk( $google_webfonts );
		// sort alphabetically
		asort( $google_webfonts );

		return $google_webfonts;
	}

	public function array_dechunk( $array ){
		if ( !$array || !is_array( $array ) ) return '';
		$dechunked = array();
		foreach ( $array as $key => $chunk ) {
			$dechunked = array_merge( $dechunked, $chunk );
		}
		return $dechunked;
	}

	public function get_default_fonts () {
		# code...
	}

	public function get_fonts_url () {
		// get google fonts
		$google_webfonts = $this->get_google_fonts();
		// create array with urlencoded font family
		$font_families = array();
		foreach ($google_webfonts as $key => $font) {
			$font_families[] = rawurlencode($font['family']);
		}
		// sort alphabetically
		// asort($font_families);

		$protocol = is_ssl() ? 'https://' : 'http://';
		$gfonts_url = $protocol . 'fonts.googleapis.com/css';
		$url = $gfonts_url . '?family=' . implode( '|', $font_families );

		return $url;
	}
}


endif; // class_exists

return new WPO_WCLabels_Settings_Callbacks();