jQuery(function($) {

	// Contact link
	$(document.body).on('click', '.wccsq-contact-link', function(e) {
		e.preventDefault();
		var $this = $(this);

		// Loading totals
		$( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

		// Package ID
		var package_index = $this.parents('.woocommerce-shipping-methods').find('.shipping_method').first().attr('data-index');

		var data = $('form.woocommerce-checkout').serialize() + '&action=wccsq_request_shipping_quote&nonce=' + wccsq.nonce + '&cart=' + wccsq.is_cart + '&package=' + parseInt(package_index);
		jQuery.post(wccsq.ajaxurl, data, function(response) {
			clear_notices();

			$(document.body).trigger('wccsq-quote-requested', response);

			if (response.success) {
				$this.parents('.wccsq-quote-description').addClass('wccsq-quote-requested');
				showPopup(); // Show info popup

				if (response.update_cart) {
					$(document.body).trigger('update_checkout');
					$(document.body).trigger('wc_update_cart');
				}
			} else if (response.notice) {
				show_notice(response.notice)

				// Scroll to notices
				$('html, body').animate({scrollTop: ($('.woocommerce-notices-wrapper').offset().top - 100)}, 1000);
			}

			// Unblock - only when not updating cart. Keeping the block to make it appear its one continues load
			if (!response.update_cart) {
				$('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').unblock();
			}
		});

		return false;
	});

	// Refresh order totals
	$(document.body).on('click', '.wccsq-refresh-link', function(e) {
		e.preventDefault();

		$(document.body).trigger('update_checkout');
		$(document.body).trigger('wc_update_cart');
	});

	// Modal/popup
	$(document.body).on('click', '.wccsq-open-popup', function(e) {
		e.preventDefault();

		showPopup(); // Show popup
	});

	// Modal/popup
	$(document.body).on('click', '.wccsq-popup-bg, .wccsq-close', function(e) {
		hidePopup(); // Hide popup
	});

	// Hide on ESC
	$(document.body).on('keydown', function(e) {
		if ((e.which || e.keyCode) == 27) {
			hidePopup(); // Hide popup
		}
	});

	function hidePopup() {
		$('.wccsq-popup-bg').hide();
		$('.wccsq-popup').hide();
	}

	function showPopup() {
		$('.wccsq-popup-bg').show();
		$('.wccsq-popup').show();
	}

	function show_notice(html_element, $target) {
		if (!$target) {
			$target = $('.woocommerce-notices-wrapper:first') || $('.cart-empty').closest('.woocommerce') || $('.woocommerce-cart-form');
		}
		$target.prepend(html_element);
	}

	function clear_notices() {
		$('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();
	}

});
