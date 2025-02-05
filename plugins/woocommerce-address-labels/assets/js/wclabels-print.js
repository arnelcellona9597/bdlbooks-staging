jQuery(function ($) {
	let datepicker_options = {
		dateFormat: 'yy-mm-dd'
	};
	$('#wclabels-status-export #date-from').datepicker(datepicker_options);
	$('#wclabels-status-export #date-to').datepicker(datepicker_options);

	// select all statuses (checkbox)
	$('.checkall').on('click', function () {
		$(this).closest('fieldset').find(':checkbox').prop('checked', this.checked);
	});

	// click action from plugin settings page
	$("#wclabels-status-export").on('submit', function () {
		$("#wclabels-status-export").attr('target', get_output_target());
		prepare_iframe();
		print_content();
	});

	// click bulk-actions from orders-overview page
	$("#doaction, #doaction2").on('click', function (event) {
		let actionselected = $(this).attr("id").substr(2);
		let action = $('select[name="' + actionselected + '"]').val();

		// execute only if the correct action is selected from orders-overview page
		if (action == "address-labels") {
			event.preventDefault();

			// Get array of checked orders (order_ids)
			let checked = [];
			$('tbody th.check-column input[type="checkbox"]:checked').each(
				function () {
					checked.push($(this).val());
				}
			);
			// convert to JSON
			window.order_ids = JSON.stringify(checked);

			if (wclabels_print.offset == 1) {
				window.offset = $('.wclabels_offset').val();
			} else {
				window.offset = 0;
			}

			prepare_iframe();
			let $google_font_link = $('#wclabels-google-font-css').clone();
			let $print_content_head = $('#wclabels-print-content').contents().find('head');
			$google_font_link.appendTo($print_content_head);
			prepare_form();
			print_content();
		}
	});

	// click order-actions from orders-overview page
	$(".wc_actions .wclabels, .order_actions .wclabels").on('click', function (event) {
		event.preventDefault();
		let order_id = [$(this).data('order-id')];

		if (wclabels_print.offset == 1) {
			// place offset dialog at mouse tip
			$('#wclabels_offset_dialog')
				.show()
				.appendTo('body')
				.css({
					position: "absolute",
					"background-color": "white",
					padding: "6px",
					width: "100px",
					border: "1px solid #ccc",
					top: event.pageY,
					left: event.pageX,
					"margin-left": "-100px",
				});

			$('#wclabels_offset_dialog').find('button')
				.show()
				.data('order_id', order_id);

			// clear input
			$('#wclabels_offset_dialog').find('input').val('');

			$('#wclabels_offset_dialog').append('<input type=hidden class="order_id"/>');
			$('#wclabels_offset_dialog input.order_id').val(order_id);

		} else {
			window.order_ids = [order_id];
			window.offset = '';
			prepare_iframe();
			prepare_form();
			print_content();
		}
	});

	// click action from the popup offset dialog button
	$("#wclabels_offset_dialog button").on('click', function (event) {
		$dialog = $(this).parent();

		// set print variables
		window.order_ids = [$dialog.find('input.order_id').val()];
		window.offset = $dialog.find('input.wclabels_offset').val();

		// hide dialog
		$dialog.hide();

		// print labels
		prepare_iframe();
		prepare_form();
		print_content();
	});

	// Add offset dialog when the setting is selected
	$("select[name='action'], select[name='action2']").on('change', function () {
		let actionselected = $(this).val();
		// alert(actionselected);
		if (actionselected == 'address-labels' && wclabels_print.offset == 1) {
			$('#wclabels_offset_dialog')
				.attr('style', 'clear:both') // reset styles
				.insertAfter('div.tablenav.top')
				.show()

			// make sure button is not shown
			$('#wclabels_offset_dialog').find('button').hide();
			// clear input
			$('#wclabels_offset_dialog').find('input').val('');
		} else {
			$('#wclabels_offset_dialog')
				.appendTo('body')
				.hide();
		}
	});

	// single export from edit-order page
	$(".wclabels-single").on('click', function (event) {
		event.preventDefault();
		window.order_ids = [$(this).attr("data-id")];
		window.offset = $('#wclabels_offset').val();
		prepare_iframe();
		prepare_form();
		print_content();
	});

	function browser_is_compatible() {
		let userAgentString = navigator.userAgent;
		// Detect MSIE
		let msieAgent = userAgentString.indexOf("MSIE") > -1;
		// Detect Opera
		let opercheckgent = (userAgentString.indexOf("Opera") > -1) && (userAgentString.indexOf("Presto") > -1);
		let unsupported_browser = (opercheckgent || msieAgent);
		return unsupported_browser ? false : true;
	}

	function get_output_target() {
		let target = (!browser_is_compatible() || wclabels_print.preview == 'true') ? '_blank' : 'wclabels-print-content';
		return target;
	}

	// create form to send order_ids via POST
	function prepare_form() {
		$('#wclabels-post-form').remove();
		let request_prefix = (wclabels_print.ajaxurl.indexOf("?") != -1) ? '&' : '?';
		let url = wclabels_print.ajaxurl + request_prefix + '&action=wpo_wclabels_print&post_type=' + wclabels_print.post_type + '&_wpnonce=' + wclabels_print.nonce;
		$('body').append('<form action="' + url + '" method="post" target="' + get_output_target() + '" id="wclabels-post-form"></form>');
		$('#wclabels-post-form').append('<input type="hidden" name="order_ids" class="order_ids"/>');
		$('#wclabels-post-form input.order_ids').val(window.order_ids);
		$('#wclabels-post-form').append('<input type="hidden" name="offset" class="offset" value="' + window.offset + '"/>');

		// submit order_ids to preview or iframe
		$('#wclabels-post-form').trigger('submit').remove();
	}

	function prepare_iframe() {
		// remove any previous print contents first!
		$('#wclabels-print-content').remove();
		// prepare iframe: create iframe for print content
		let iframe = '<iframe id="wclabels-print-content" name="wclabels-print-content" style="position:absolute;top:-9999px;left:-9999px;border:0px;overfow:none; z-index:-1"></iframe>';
		$('body').append(iframe);
	}

	function print_content() {
		if (browser_is_compatible() && wclabels_print.preview != 'true') {
			// wait until iframe is loaded, then print
			$("#wclabels-print-content").on("load", function () {
				window.setTimeout(function () {
					frames['wclabels-print-content'].focus();
					frames['wclabels-print-content'].print();
				}, 500);
			});
		}
	}
});