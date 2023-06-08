(function ($) {
	"use strict";

	const checkAvailablePGS = () => {
		$(".woofastcheck-select-product")
			.find("input:checked")
			.each(function () {
				$("#selected_payment_gateway").val($(this).data("pgs"));

				return;
				const available_pgs = $(this).data("pgs").split(",");

				if (available_pgs.length === 1 && available_pgs[0] === "") {
					$(".wc_payment_method").each(function () {
						$(this).show();
					});

					$(".wc_payment_methods")
						.find(".wc_payment_method:visible:first")
						.show()
						.find("input")
						.prop("checked", true);

					return;
				}

				$(".wc_payment_methods")
					.find("li.wc_payment_method")
					.each(function () {
						$(this).hide();
					});

				$(available_pgs).each(function (index, value) {
					console.log({ value });
					$(".wc_payment_method.payment_method_" + value).show();
				});

				$(".wc_payment_methods")
					.find(".wc_payment_method:visible:first input")
					.prop("checked", true);
			});
	};

	$(document).ready(function () {
		checkAvailablePGS();

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
						checkAvailablePGS();

						setTimeout(function () {
							// Trigger event so themes can refresh other areas.
							$(document.body).trigger("wc_fragments_loaded");
							$(document.body).trigger("update_checkout");
						}, 200);
					}
				},
			});
		});

		$("body").on("change", "input[name='payment_method']", function () {
			$(document.body).trigger("update_checkout");
		});

		$(document.body).trigger("update_checkout");
	});
})(jQuery);
