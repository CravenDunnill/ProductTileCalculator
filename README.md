# Product Tile Calculator for Magento 2

A comprehensive Magento 2 module that provides an interactive tile calculator for product pages, allowing customers to easily calculate quantities, coverage areas, and pricing for tile/flooring products.

## Features

### Interactive Tile Calculator
- Order tiles by **square meters (m²)** or **number of boxes**
- Real-time bidirectional conversion between units
- Increment/decrement buttons for easy quantity adjustment
- Displays total area coverage and tile count

### Intelligent Pricing System
- Calculates prices based on m² or box quantities
- Supports special/promotional pricing with strikethrough display
- Automatic VAT (20%) calculation
- Dynamic price updates as quantities change

### Payment Integration
- **Klarna** financing widget with dynamic price updates
- **PayPal Pay Later** widget integration
- Both widgets update in real-time as customers adjust quantities

### Shopping Cart Integration
- Custom cart item renderer showing:
  - Number of boxes ordered
  - Area coverage in m²
  - Price per m² with special pricing indicators

### Delivery Confirmation
- Modal confirmation for products with special lead times
- Stock status and delivery time information
- Customer acknowledgment before adding to cart

## Requirements

- **Magento**: 2.4.x (Framework 103.0.*)
- **PHP**: 7.4 or 8.1+
- **Magento Modules**: Magento_Catalog, Magento_Checkout

## Installation

### Via Composer (Recommended)

```bash
composer require cravendunnill/module-product-tile-calculator
bin/magento module:enable CravenDunnill_ProductTileCalculator
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### Manual Installation

1. Create directory `app/code/CravenDunnill/ProductTileCalculator`
2. Copy module files to that directory
3. Run Magento setup commands:

```bash
bin/magento module:enable CravenDunnill_ProductTileCalculator
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Configuration

### Required Product Attributes

The module automatically creates the following product attributes during installation:

| Attribute | Code | Description |
|-----------|------|-------------|
| Box Quantity | `box_quantity` | Number of tiles per box |
| Tiles per m² | `tile_per_m2` | How many tiles cover 1 square meter |
| Price per m² | `price_m2` | Price per square meter (excluding VAT) |
| Special Price per m² | `special_price_m2` | Optional promotional price per m² |

These attributes are organized under the **"Tile Calculator"** group in the product admin.

### Product Setup

For the calculator to display on a product page:

1. Product must be a **Simple Product** type
2. Product must use the **"Tiles"** attribute set
3. All required attributes (`box_quantity`, `tile_per_m2`, `price_m2`) must have values

## Usage

### Customer Experience

1. Customer navigates to a tile product page
2. Calculator displays with default of 1 box
3. Customer can adjust quantity via:
   - Direct input in m² field
   - Direct input in boxes field
   - +/- increment buttons
4. All calculations update in real-time:
   - Required boxes for selected m²
   - m² covered by selected boxes
   - Total tiles in order
   - Total price including VAT
5. Payment widgets (Klarna/PayPal) update automatically
6. Customer clicks "Add to Cart"
7. Cart displays tile-specific information

### Admin Configuration

1. Navigate to **Catalog > Products**
2. Edit a product using the "Tiles" attribute set
3. Fill in the Tile Calculator attributes:
   - **Box Quantity**: e.g., `10` (tiles per box)
   - **Tiles per m²**: e.g., `25` (tiles needed per m²)
   - **Price per m²**: e.g., `35.00` (ex. VAT)
   - **Special Price per m²**: (optional) e.g., `29.99`

## Module Structure

```
CravenDunnill/ProductTileCalculator/
├── Block/
│   ├── TileCalculator.php              # Main product page block
│   └── Cart/Item/Renderer.php          # Cart item display
├── Controller/Index/Index.php          # Route controller
├── Helper/Calculator.php               # Core calculation logic
├── ViewModel/
│   ├── ProductAttributeViewModel.php   # Attribute utilities
│   └── TileDetails.php                 # Cart item wrapper
├── Setup/Patch/Data/
│   └── EnsureAttributesVisibility.php  # Attribute setup
├── view/frontend/
│   ├── layout/                         # XML layout files
│   ├── templates/                      # PHTML templates
│   └── web/                            # JS and CSS assets
└── etc/                                # Module configuration
```

## Technical Details

### Calculation Logic

The module handles conversions between three units:

- **Tiles**: Base unit for Magento cart
- **Boxes**: Customer-friendly ordering unit
- **m² (Square Meters)**: Area coverage unit

**Key formulas:**
- `boxes = ceil(m² × tiles_per_m² / box_quantity)`
- `m² = (boxes × box_quantity) / tiles_per_m²`
- `tiles = boxes × box_quantity`

### VAT Handling

All `price_m2` values are stored **excluding VAT**. The module automatically applies a 20% VAT rate for display and calculations:

```
display_price = price_m2 × 1.20
```

### JavaScript Events

The calculator uses event-driven architecture:

- `input` events on quantity fields
- `click` events on increment/decrement buttons
- Custom events for payment widget updates

## Customization

### Styling

Override CSS by creating a custom theme:

```
app/design/frontend/[Vendor]/[Theme]/CravenDunnill_ProductTileCalculator/web/css/tile_calculator.css
```

### Templates

Override templates in your theme:

```
app/design/frontend/[Vendor]/[Theme]/CravenDunnill_ProductTileCalculator/templates/
```

### Layout

Modify layout XML in your theme:

```
app/design/frontend/[Vendor]/[Theme]/CravenDunnill_ProductTileCalculator/layout/
```

## Troubleshooting

### Calculator Not Displaying

1. Verify product is a **Simple** type
2. Confirm product uses **"Tiles"** attribute set
3. Check all required attributes have values
4. Clear Magento cache: `bin/magento cache:flush`

### Prices Not Updating

1. Check browser console for JavaScript errors
2. Verify `price_m2` attribute has a value
3. Ensure RequireJS is loading the calculator module

### Attributes Missing in Admin

Run the data patch manually:

```bash
bin/magento setup:upgrade
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

This module is licensed under:
- [Open Software License 3.0 (OSL-3.0)](https://opensource.org/licenses/OSL-3.0)
- [Academic Free License 3.0 (AFL-3.0)](https://opensource.org/licenses/AFL-3.0)

## Support

- **Issues**: [GitHub Issues](../../issues)
- **Pull Requests**: [GitHub Pull Requests](../../pulls)

## Credits

Developed by [Craven Dunnill](https://www.cravendunnill.co.uk/) for their tile e-commerce platform.
