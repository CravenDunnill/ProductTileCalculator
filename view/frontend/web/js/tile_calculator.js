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
			pricePerM2 = config.pricePerM2,
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
			$m2Input.val(m2.toFixed(2));
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
			$areaCovered.text(m2.toFixed(2));
			$totalPrice.html(formatPrice(boxes * pricePerBox));
		}
		
		/**
		 * Update the area covered based on the current number of boxes
		 */
		function updateAreaCovered() {
			var boxes = parseInt($boxesInput.val()) || 1;
			var area = calculateM2FromBoxes(boxes);
			$areaCovered.text(area.toFixed(2));
		}

		/**
		 * Apply the calculated quantity to the product form
		 */
		function applyToCart() {
			var boxes = parseInt($boxesInput.val()) || 1;
			$qtyInput.val(boxes).trigger('change');
		}

		/**
		 * Increase m² value
		 */
		function increaseM2() {
			var currentValue = parseFloat($m2Input.val()) || 0;
			$m2Input.val((currentValue + 0.1).toFixed(1));
			updateFromM2();
		}
		
		/**
		 * Decrease m² value
		 */
		function decreaseM2() {
			var currentValue = parseFloat($m2Input.val()) || 0;
			if (currentValue > 0.1) {
				$m2Input.val((currentValue - 0.1).toFixed(1));
				updateFromM2();
			}
		}
		
		/**
		 * Increase boxes value
		 */
		function increaseBoxes() {
			var currentValue = parseInt($boxesInput.val()) || 0;
			$boxesInput.val(currentValue + 1);
			updateFromBoxes();
		}
		
		/**
		 * Decrease boxes value
		 */
		function decreaseBoxes() {
			var currentValue = parseInt($boxesInput.val()) || 0;
			if (currentValue > 1) {
				$boxesInput.val(currentValue - 1);
				updateFromBoxes();
			}
		}

		// Event Bindings
		$m2Input.on('input change', updateFromM2);
		$boxesInput.on('input change', updateFromBoxes);
		
		// Direct event binding for buttons
		$increaseM2Btn.on('click', increaseM2);
		$decreaseM2Btn.on('click', decreaseM2);
		$increaseBoxesBtn.on('click', increaseBoxes);
		$decreaseBoxesBtn.on('click', decreaseBoxes);
		$applyBtn.on('click', applyToCart);

		// Initial calculation
		updateFromBoxes();
		updateAreaCovered();
		
		console.log('Tile calculator initialized');
		
		// Debug info
		console.log('Button selectors:', {
			increaseM2: $increaseM2Btn.length,
			decreaseM2: $decreaseM2Btn.length,
			increaseBoxes: $increaseBoxesBtn.length,
			decreaseBoxes: $decreaseBoxesBtn.length
		});
	};
});