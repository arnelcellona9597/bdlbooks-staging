<!DOCTYPE html>
<html moznomarginboxes>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style type="text/css">
	<?php include( $this->get_template_path( 'wclabels-styles.css.php' ) ); ?>
	</style>

	<?php if ( isset($this->google_font) ) : ?>
	<link href='<?php echo $this->google_font_url( $this->google_font ); ?>' rel='stylesheet' type='text/css'>
	<style type="text/css">
	html, body {
		font-family: <?php echo $this->google_font['family']; ?>;
		font-weight: <?php echo $this->google_font['weight']; ?>;
		font-style: <?php echo $this->google_font['style']; ?>;
	}
	</style>	
	<?php endif; ?>	

	<?php if ( isset($this->layout_settings['custom_styles']) ) : ?>
	<style type="text/css">
	<?php echo $this->layout_settings['custom_styles']; ?>
	</style>
	<?php endif; ?>	
</head>

<body>


<?php
$cols         = $this->cols;
$rows         = $this->rows;
$label_number = 0;

// using array_values to make sure array is reindexed, for page/label numbering
$labels          = array_values( $this->get_label_data( $this->order_ids ) );
$label_count     = count( $labels );
$labels_per_page = $cols * $rows;
$page_count      = ceil( ( $label_count + $this->offset ) / $labels_per_page );


for ( $page = 0; $page < $page_count; $page++ ) {
	echo '<table class="address-labels" width="100%" height="100%" border="0">';
	$last_height    = 0;
	$current_height = $current_width = 0;
	$current_row    = 0;

	for ( $current_label = 0; $current_label < $labels_per_page; $current_label++ ) {
		$label_number++;
		$current_col = ( ( $label_number - 1 ) % $cols ) + 1;

		if ( 1 === $current_col ) {
			$last_height = $current_height;
			$last_width  = 0;
			$current_row++;
			echo '<tr class="label-row">';
		}

		$current_width = round( $current_col * ( 100 / $cols ) );
		$width         = $current_width - $last_width;
		$last_width    = $current_width;

		$current_height = round( $current_row * ( 100 / $rows ) );
		$height         = $current_height - $last_height;

		printf( '<td width="%s%%" height="%s%%" class="label"><div class="label-wrapper">', $width, $height );

		// check if we need to output offset label (empty) or actual label
		if ( $label_number > $this->offset && isset( $labels[ $label_number - $this->offset - 1 ] ) ) {
			$label = $labels[ $label_number - $this->offset - 1 ];
			extract( $label ); // $label_data, $order_id

            $order = null;
			// load order, prevent membership conflicts
			if ( 'wc_user_membership' !== WPO_WCLABELS()->order_util->get_order_type( $order_id ) ) {
				$order = wc_get_order( $order_id );
			}

			// process label template to display content
			include $this->get_template_path( 'wclabels-label-template.php' );
		} else {
			echo '&nbsp;';
		}
		echo '</div></td>';

		if ( $current_col === $cols ) {
			echo '</tr>';
		}
	}
	echo '</table>';
}

?>


</body>
</html>