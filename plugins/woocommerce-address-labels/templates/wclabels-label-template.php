<?php
//***************************************************\\
//******* EDIT THE CONTENTS OF THE DIV BELOW ********\\
//******* TO CUSTOMIZE THE LABEL FORMATTING  ********\\
//***************************************************\\

// $label_data contains the data as defined in the plugin settings.
// The WooCommerce order object is already loaded into $order, so
// you can use that to get more data.
// use echo $label_number.'/'.$order_count; to number the labels
// (per print batch)
?>
<?php do_action('wpo_wclabel_before_address_block', $order); ?>
<div class="address-block">
	<?php do_action('wpo_wclabel_before_label_data', $order); ?>
	<?php echo $label_data; ?>
	<?php do_action('wpo_wclabel_after_label_data', $order); ?>
</div>
<?php do_action('wpo_wclabel_after_address_block', $order); ?>
