/**
 * CravenDunnill ProductTileCalculator Extension - Box Price Update
 */
define([
	'jquery',
	'mage/translate',
	'Magento_Catalog/js/price-utils'
], function ($, $t, priceUtils) {
	'use strict';

	return function (config) {
		$(document).ready(function() {
			console.log('Box Price Update Module initialized');
			
			// Function to update box price display
			function updateBoxPrice() {
				$('.item-info').each(function() {
					var $cartItem = $(this);
					var $boxPriceValue = $cartItem.find('.box-price-value');
					var $qtyInput = $cartItem.find('[data-role="cart-item-qty"]');
					
					// Only proceed if this is a tile calculator product with box price
					if ($boxPriceValue.length === 0 || $qtyInput.length === 0) {
						return;
					}
					
					// Get base price data
					var basePricePerBox = parseFloat($boxPriceValue.data('box-price'));
					var tilesPerBox = parseInt($boxPriceValue.data('tiles-per-box'));
					
					if (isNaN(basePricePerBox) || isNaN(tilesPerBox) || tilesPerBox <= 0) {
						return;
					}
					
					// Get current quantity
					var currentQty = parseInt($qtyInput.val()) || 1;
					
					// Calculate number of boxes (for display purposes)
					var boxes = Math.floor(currentQty / tilesPerBox);
					if (boxes < 1) boxes = 1;
					
					// Update the label to show how many boxes
					var boxText = (boxes === 1) ? $t('Price per box:') : $t('Price per box (×%1):', boxes);
					$boxPriceValue.prev('.box-price-label').text(boxText);
					
					console.log('Box price updated for product with', boxes, 'boxes');
				});
			}
			
			// On quantity change
			$(document).on('change', '[data-role="cart-item-qty"]', function() {
				// Use setTimeout to ensure that we update after any Magento event processing
				setTimeout(updateBoxPrice, 500);
			});
			
			// Initial update
			setTimeout(updateBoxPrice, 1000);
			
			// Handle cart update events
			$(document).on('ajax:updateCartItemQty', function() {
				setTimeout(updateBoxPrice, 1000);
			});
		});
	};
});