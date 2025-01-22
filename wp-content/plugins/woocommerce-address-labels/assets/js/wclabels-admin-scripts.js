jQuery(document).ready(function($) {

	function resize_preview_table() {
		var paper_size = $('#paper_size').val();
		var paper_orientation = $('#paper_orientation').val();

		switch(paper_size) {
		case 'a4':
			$('#custom_paper_size_width').val('210');
			$('#custom_paper_size_height').val('297');
			break;
		case 'letter':
			$('#custom_paper_size_width').val('216');
			$('#custom_paper_size_height').val('279');
			break;
		}

		var paper_width = $('#custom_paper_size_width').val() != '' ? $('#custom_paper_size_width').val() : 100;
		var paper_height = $('#custom_paper_size_height').val() != '' ? $('#custom_paper_size_height').val() : 100;


		if (paper_orientation == 'landscape') {
			portrait_width = paper_width;
			paper_width = paper_height;
			paper_height = portrait_width;
		}

		// normalize to 300px wide
		var preview_width = 300;
		var multiplier = preview_width / paper_width;
		var preview_height = paper_height * multiplier;

		$('table.wclabels-preview').width(preview_width);
		$('table.wclabels-preview').height(preview_height);

		// PAGE MARGINS
		var top    = $('#page_margins_top').val() * multiplier;
		var bottom = $('#page_margins_bottom').val() * multiplier;
		var left   = $('#page_margins_left').val() * multiplier;
		var right  = $('#page_margins_right').val() * multiplier;


		$('table.wclabels-preview').css("padding-top", top+'px');
		$('table.wclabels-preview').css("padding-bottom", bottom+'px');
		$('table.wclabels-preview').css("padding-left", left+'px');
		$('table.wclabels-preview').css("padding-right", right+'px');

		// LABEL PITCH
		var vertical_pitch   = ($('#vertical_pitch').val() * multiplier ).toFixed(2);
		var horizontal_pitch = ($('#horizontal_pitch').val() * multiplier ).toFixed(2);
		$('table.wclabels-preview').css("border-spacing", horizontal_pitch+'px '+vertical_pitch+'px');
	}

	function create_table() {
		var rows = $('#rows').val();
		var cols = $('#cols').val();
		var table = $('<table class="wclabels-preview"><tbody>');
		for(var r = 0; r < rows; r++)
		{
			var tr = $('<tr>');
			for (var c = 0; c < cols; c++)
				$('<td>&nbsp;</td>').appendTo(tr);
			tr.appendTo(table);
		}

		$('table.wclabels-preview').replaceWith(table);
	}

	function check_size() {
		var paper_size = $('#paper_size').val();
		if (paper_size == 'custom') {
			$( '.paper_size_custom').show();
		} else {
			// $( '#custom_paper_size_width').val('');
			// $( '#custom_paper_size_height').val('');
			$( '.paper_size_custom').hide();
		}
	}

	create_table();
	resize_preview_table();
	check_size();

	$( '.page-layout-setting' ).closest('tr').find('th').first().remove();
	$( '.page-layout-setting' ).closest('td').attr('colspan',2);

	$( '#paper_size, #paper_orientation' ).on('change', function() {
		resize_preview_table();
		check_size();
	});

	let redrawTimeout;
	$( '#rows, #cols, #custom_paper_size_width, #custom_paper_size_height, #page_margins_top, #page_margins_bottom, #page_margins_left, #page_margins_right, #vertical_pitch, #horizontal_pitch' ).on('change keyup', function( event ) {
		// enforce rows & cols minimum value to 1
		if ( this.id == 'rows' || this.id == 'cols' ) {
			if ( parseInt( $( this ).val() ) < 1 ) {
				$( this ).val( 1 );
			}
		}
		clearTimeout( redrawTimeout );
		let duration = event.type == 'change' ? 0 : 1000;
		redrawTimeout = setTimeout( function(){
			create_table();
			resize_preview_table();
		}, duration);
	});

	// Use font preview list instead of regular select list
	$( ".font-family" ).on( "focus mousedown", function( event ) {
		this.blur();
		window.focus();
		event.preventDefault();
		$( this ).closest('table').find(".font-preview-list").toggle();
	});
	
	// Select font when selected in preview
	$( ".font-preview-list li" ).on( "click", function( event ) {
		var selected = $( this ).data('value');
		$family_select = $( this ).closest('table').find(".font-family");
		$family_select.val(selected);
		$family_select.trigger('change');
		$( this ).closest(".font-preview-list").hide();
	});

	// close font preview when clicking outside of selector
	$(document).on( "click", function (e) {
		var container = $(".font-preview-list");
		// console.log(.toString());
		if ( !container.is(e.target) && container.has(e.target).length === 0 && !($( e.target ).hasClass( "font-family" )) ) {
			container.hide();
		}
	});

	// handle available variations per font familty
	$( ".font-family" ).on( "change", function( event ) {
		var $selected_option = $(this).find(':selected')

		// VARIANTS
		var available_variants = $selected_option.data('variants');
		available_variants = available_variants.split(',');

		// fill select options
		$variant_select = $( this ).closest('table').find(".font-variant");
		$variant_select.empty();
		$.each( available_variants, function( i, value ) {
			$variant_select.append($('<option>', { 
				value: value,
				text : value 
			}));
		});
		$variant_select.prop('disabled', false);

		// select current variant
		$variant_select.val( $variant_select.data('current') );

		// // SUBSETS
		// var available_subsets = $selected_option.data('subsets');
		// available_subsets = available_subsets.split(',');

		// // fill select options
		// $subsets_select = $( this ).closest('table').find(".font-subsets");
		// $subsets_select.append($('<option>', { 
		// 	value: 'all',
		// 	text : 'All' 
		// }));
		// $.each( available_subsets, function( i, value ) {
		// 	$subsets_select.append($('<option>', { 
		// 		value: value,
		// 		text : value 
		// 	}));
		// });
		// $subsets_select.prop('disabled', false);

	});
	$( ".font-family" ).trigger('change');


});