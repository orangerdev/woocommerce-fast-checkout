(function ($) {
	"use strict";

	$(document).ready(function () {
		$("body").on("change", ".woofastcheck-select-product input", function () {
			const value = $(this).val();
			$.ajax({
				url: woofastcheck.addtocart.url,
				type: "POST",
				data: {
					"wc-ajax": "add_to_cart",
					product_id: value,
					product_sku: $(this).data("sku"),
					quantity: 1,
				},
				success: function (data) {
					if (data && data.fragments) {
						// Replace fragments
						$.each(data.fragments, function (key, value) {
							$(key).replaceWith(value);
						});

						// Trigger event so themes can refresh other areas.
						$(document.body).trigger("wc_fragments_loaded");
						$(document.body).trigger("update_checkout");
					}
				},
			});
		});

		$("body").on("click", ".woofastcheck-next-checkout-button", function () {
			$(".woofastcheck-select-product").hide();
			$(".woofastcheck-checkout-form").fadeIn("fast");
		});

		$("body").on("click", ".woofastcheck-back-product-button", function () {
			$(".woofastcheck-select-product").fadeIn("fast");
			$(".woofastcheck-checkout-form").hide("fast");
		});
	});
})(jQuery);
