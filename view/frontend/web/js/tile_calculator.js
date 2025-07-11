/**
 * CravenDunnill ProductTileCalculator Extension
 */
define([
	'jquery',
	'mage/translate',
	'Magento_Catalog/js/price-utils'
], function ($, $t, priceUtils) {
	'use strict';

	return function (config) {
		var boxQuantity = config.boxQuantity,
			tilePerM2 = config.tilePerM2,
			pricePerM2 = config.pricePerM2, // Now includes VAT
			pricePerBox = config.pricePerBox,
			priceFormat = config.priceFormat,
			
			// DOM elements
			$m2Input = $('[data-role="m2-input"]'),
			$boxesInput = $('[data-role="boxes-input"]'),
			$increaseM2Btn = $('[data-role="increase-m2"]'),
			$decreaseM2Btn = $('[data-role="decrease-m2"]'),
			$increaseBoxesBtn = $('[data-role="increase-boxes"]'),
			$decreaseBoxesBtn = $('[data-role="decrease-boxes"]'),
			$applyBtn = $('[data-role="apply-calculator"]'),
			$boxesNeeded = $('#boxes-needed'),
			$areaCovered = $('#area-covered'),
			$totalPrice = $('#total-price'),
			$qtyInput = $('#qty');

		/**
		 * Format price according to Magento's price format
		 * 
		 * @param {number} price
		 * @returns {string}
		 */
		function formatPrice(price) {
			return priceUtils.formatPrice(price, priceFormat);
		}

		/**
		 * Calculate m² from boxes
		 * 
		 * @param {number} boxes
		 * @returns {number}
		 */
		function calculateM2FromBoxes(boxes) {
			var tilesInBoxes = boxes * boxQuantity;
			return tilesInBoxes / tilePerM2;
		}

		/**
		 * Calculate boxes from m²
		 * 
		 * @param {number} squareMeters
		 * @returns {number}
		 */
		function calculateBoxesFromM2(squareMeters) {
			var tilesNeeded = squareMeters * tilePerM2;
			return Math.ceil(tilesNeeded / boxQuantity);
		}

		/**
		 * Update calculation from m² input
		 */
		function updateFromM2() {
			var m2 = parseFloat($m2Input.val()) || 0,
				boxes = calculateBoxesFromM2(m2);
			
			if (m2 <= 0) {
				m2 = 0.1;
				$m2Input.val(m2);
			}
			
			$boxesInput.val(boxes);
			updateResults(boxes, m2);
		}

		/**
		 * Update calculation from boxes input
		 */
		function updateFromBoxes() {
			var boxes = parseInt($boxesInput.val()) || 0,
				m2;
			
			if (boxes <= 0) {
				boxes = 1;
				$boxesInput.val(boxes);
			}
			
			m2 = calculateM2FromBoxes(boxes);
			$m2Input.val(m2.toFixed(3));
			updateResults(boxes, m2);
		}

		/**
		 * Update the calculation results
		 * 
		 * @param {number} boxes
		 * @param {number} m2
		 */
		function updateResults(boxes, m2) {
			$boxesNeeded.text(boxes);
			$areaCovered.text(m2.toFixed(3));
			
			// Calculate and display the total price (inc. VAT)
			$totalPrice.html(formatPrice(boxes * pricePerBox));
		}

		/**
		 * Apply the calculated quantity to the product form
		 */
		function applyToCart() {
			var boxes = parseInt($boxesInput.val()) || 1;
			var tilesPerBox = boxQuantity;
			var totalTiles = boxes * tilesPerBox;
			
			$qtyInput.val(totalTiles);
			
			// Enable the add to cart button if it exists
			var addToCartBtn = document.getElementById('product-addtocart-button');
			if (addToCartBtn) {
				addToCartBtn.disabled = false;
			}
		}

		/**
		 * Increase m² value
		 * @param {Event} e
		 */
		function increaseM2(e) {
			e.preventDefault();
			e.stopPropagation();
			var currentValue = parseFloat($m2Input.val()) || 0;
			$m2Input.val((currentValue + 0.1).toFixed(3));
			updateFromM2();
			return false;
		}
		
		/**
		 * Decrease m² value
		 * @param {Event} e
		 */
		function decreaseM2(e) {
			e.preventDefault();
			e.stopPropagation();
			var currentValue = parseFloat($m2Input.val()) || 0;
			if (currentValue > 0.1) {
				$m2Input.val((currentValue - 0.1).toFixed(3));
				updateFromM2();
			}
			return false;
		}
		
		/**
		 * Increase boxes value
		 * @param {Event} e
		 */
		function increaseBoxes(e) {
			e.preventDefault();
			e.stopPropagation();
			var currentValue = parseInt($boxesInput.val()) || 0;
			$boxesInput.val(currentValue + 1);
			updateFromBoxes();
			return false;
		}
		
		/**
		 * Decrease boxes value
		 * @param {Event} e
		 */
		function decreaseBoxes(e) {
			e.preventDefault();
			e.stopPropagation();
			var currentValue = parseInt($boxesInput.val()) || 0;
			if (currentValue > 1) {
				$boxesInput.val(currentValue - 1);
				updateFromBoxes();
			}
			return false;
		}

		// First, unbind any existing event handlers to avoid duplicates
		$m2Input.off('input change');
		$boxesInput.off('input change');
		$increaseM2Btn.off('click');
		$decreaseM2Btn.off('click');
		$increaseBoxesBtn.off('click');
		$decreaseBoxesBtn.off('click');
		$applyBtn.off('click');
		
		// Now bind our event handlers
		$m2Input.on('input change', updateFromM2);
		$boxesInput.on('input change', updateFromBoxes);
		$increaseM2Btn.on('click', increaseM2);
		$decreaseM2Btn.on('click', decreaseM2);
		$increaseBoxesBtn.on('click', increaseBoxes);
		$decreaseBoxesBtn.on('click', decreaseBoxes);
		$applyBtn.on('click', applyToCart);

		// Initial calculation
		updateFromBoxes();
		
		console.log('Tile calculator initialized with boxQuantity:', boxQuantity, 'pricePerM2 (inc. VAT):', pricePerM2);
	};
});