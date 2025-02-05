// Thanks to Mike Jolley!
// http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/

jQuery(document).ready(function($) {
		
	// Uploading files
	var file_frame;
	$('#wpo-wclabels-settings .image-placeholders').on('click', '.create-image-placeholder', function( event ){
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		$placeholders_table = $(this).closest('table');
		 
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: $placeholders_table.data( 'uploader_title' ),
			button: {
				text: $placeholders_table.data( 'uploader_button_text' ),
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});
	 
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// console.log(attachment);

			if ( $placeholders_table.find("tr[data-id='" + attachment.id + "']").length ) {
				return; // already there
			}

			$placeholder_row = $placeholders_table.find('.placeholder-row-template').clone().removeClass('placeholder-row-template');
			// $placeholder_row.appendTo('.image-placeholders');
			$placeholder_row.data('id',attachment.id).attr('data-id',attachment.id);
			$placeholder_row.find('input').val(attachment.id).prop("disabled", false);

			$placeholder_row.find('.image-placeholder-thumbnail').prepend('<img src="'+attachment.url+'">');
			$placeholder_row.find('.image-placeholder').prepend('<code>[label_image_placeholder id='+attachment.id+']</code>');
			$placeholder_row.insertBefore($placeholders_table.find('.action-buttons'));
			// set the value of the input field to the attachment id
			$placeholder_row.find('input').val(attachment.id);
		});
	 
		// Finally, open the modal
		file_frame.open();
	});
  
	$('#wpo-wclabels-settings').on('click', '.remove-placeholder', function( event ){
		// get corresponding input fields
		$(this).closest('tr').remove();

	});		
});