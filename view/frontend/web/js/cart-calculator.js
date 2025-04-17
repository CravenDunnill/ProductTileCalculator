/**
 * CravenDunnill ProductTileCalculator Extension - Cart Functionality
 */
define([
	'jquery',
	'mage/translate',
	'Magento_Checkout/js/action/get-totals',
	'Magento_Customer/js/customer-data'
], function ($, $t, getTotalsAction, customerData) {
	'use strict';

	return function(config) {
		$(document).ready(function() {
			// Utility functions
			function calculateBoxesFromTiles(tilesQty, boxQty) {
				if (boxQty <= 0) {
					return tilesQty;
				}
				return Math.floor(tilesQty / boxQty);
			}
			
			function getBoxText(boxes) {
				return boxes === 1 ? $t('box') : $t('boxes');
			}
			
			function formatBoxQuantity(boxes) {
				return boxes + ' ' + getBoxText(boxes);
			}
			
			// Observe tile quantity changes in cart
			$(document).on('change', '[data-role="tile-calculator-qty"]', function() {
				var $input = $(this);
				var newQty = parseInt($input.val());
				var boxQty = parseInt($input.data('box-qty'));
				
				if (isNaN(newQty) || newQty < 0) {
					return;
				}
				
				// Calculate boxes and update display
				var boxes = calculateBoxesFromTiles(newQty, boxQty);
				var $infoContainer = $input.closest('.tile-calculator-cart-qty').find('.box-info');
				
				$infoContainer.find('.box-qty-display').text(formatBoxQuantity(boxes));
				
				// Trigger standard cart update
				setTimeout(function() {
					$input.trigger('change.cart');
				}, 50);
			});
			
			// Add box quantity controls to mini cart
			function updateMiniCart() {
				// Find tile calculator items in mini cart
				$('.minicart-items .product-item').each(function() {
					var $item = $(this);
					var $qtyInput = $item.find('.cart-item-qty');
					
					// Check if this is a tile calculator product
					if ($qtyInput.data('box-qty')) {
						var boxQty = parseInt($qtyInput.data('box-qty'));
						var tilesQty = parseInt($qtyInput.val());
						
						if (!isNaN(boxQty) && !isNaN(tilesQty) && boxQty > 0) {
							var boxes = calculateBoxesFromTiles(tilesQty, boxQty);
							
							// Add box info if not already present
							if ($item.find('.tile-box-info').length === 0) {
								$qtyInput.after(
									'<div class="tile-box-info">' +
									'<span class="box-qty-display">' + formatBoxQuantity(boxes) + '</span>' +
									'</div>'
								);
							} else {
								// Update existing box info
								$item.find('.box-qty-display').text(formatBoxQuantity(boxes));
							}
						}
					}
				});
			}
			
			// Watch for mini cart updates
			var cartData = customerData.get('cart');
			cartData.subscribe(function() {
				setTimeout(updateMiniCart, 500);
			});
			
			// Initial call
			updateMiniCart();
		});
	};
});