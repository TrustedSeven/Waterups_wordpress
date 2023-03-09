jQuery(function($) {

	// Toggle quote products
	$(document.body).on('click', '.wccsq-shipping-quote-items', function() {
		$(this).parents('tr').first().find('.wccsq-products-table').toggle();
	});

	// Toggle quote amount field
	$(document.body).on('click', '.edit-quoted-amount', function() {
		$(this).parent().find('.quoted-amount-price, .save-quoted-amount, .cancel-quoted-amount, .edit-quoted-amount').toggle();
		$(this).parent().find('.quote_amount').toggle().select();
	});

	// Save
	$(document.body).on('click', '.save-quoted-amount', function(e) {
		$(this).parents('form').submit();
	});

	// Cancel
	$(document.body).on('click', '.cancel-quoted-amount', function(e) {
		$(this).parent().find('.quote_amount, .quoted-amount-price, .save-quoted-amount, .cancel-quoted-amount, .edit-quoted-amount').toggle();
	});

	$(document.body).on('submit', 'form.shipping-quote-actions', function(e) {
		e.preventDefault();

		var data = $(this).serialize() + '&' + $.param({ nonce: wccsq.nonce, action: 'wccsq_shipping_quote_action' });

		var $this = $(this),
			row = $this.parents('tr').first(),
			quoteID = row.data('quote-id'),
			topOffset = ($this.parents('td').outerHeight() - $this.height()) / 2,
			leftOffset = $this.parents('td')[0].getBoundingClientRect().left - $this.parents('tr')[0].getBoundingClientRect().left + 13;

		$this.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6, top: -topOffset, left: -leftOffset, width: row.width(), height: row.height() } });
		$.post(ajaxurl, data, function(response) {

			if (response.success) {
				if (response.html == null) {
					$('[data-quote-id=' + quoteID + ']').remove();
				} else {
					var rows = $('[data-quote-id=' + quoteID + ']');
					rows.first().replaceWith(response.html)
					rows.remove();
					$this.unblock();
				}
			}
		});
	});


	/**
	 * Settings page
	 */
	showHideSettingFields();
	function showHideSettingFields() {
		var select = $('#woocommerce_custom_shipping_quote_additional_features');
		var hideClasses = $.map(select.find('option'), function(o) { return '.show-if-checkout-behaviour-' + o.value; });
		var showClasses = $.map(select.find('option:selected'), function(o) { return '.show-if-checkout-behaviour-' + o.value; });

		$(hideClasses.join(', ')).hide();
		$(showClasses.join(', ')).show();
	}
	$('#woocommerce_custom_shipping_quote_additional_features').on('change', showHideSettingFields);
});
