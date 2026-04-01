/* Smart Duplicator – Admin JS */
(function ($) {
	'use strict';

	$(function () {
		// Highlight checkbox card when checked
		$('.smart-dup-checkbox-label input[type="checkbox"]').on('change', function () {
			$(this).closest('.smart-dup-checkbox-label').toggleClass('is-checked', this.checked);
		}).trigger('change');
	});

}(jQuery));
