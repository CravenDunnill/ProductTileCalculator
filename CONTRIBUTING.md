# Contributing to Product Tile Calculator

Thank you for your interest in contributing to the Product Tile Calculator module! This document provides guidelines and information for contributors.

## Code of Conduct

Please be respectful and constructive in all interactions. We aim to maintain a welcoming environment for all contributors.

## Getting Started

### Prerequisites

- Magento 2.4.x development environment
- PHP 7.4 or 8.1+
- Composer
- Git

### Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/CravenDunnill/ProductTileCalculator.git
   ```

2. Install in your Magento development environment:
   ```bash
   # Symlink or copy to app/code/CravenDunnill/ProductTileCalculator
   bin/magento module:enable CravenDunnill_ProductTileCalculator
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:flush
   ```

3. Set Magento to developer mode:
   ```bash
   bin/magento deploy:mode:set developer
   ```

## How to Contribute

### Reporting Bugs

1. Check existing issues to avoid duplicates
2. Use the bug report template
3. Include:
   - Clear description
   - Steps to reproduce
   - Expected vs actual behaviour
   - Environment details
   - Relevant logs/screenshots

### Suggesting Features

1. Use the feature request template
2. Explain the use case and benefits
3. Consider backward compatibility
4. Include mockups if applicable

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. Make your changes
4. Test thoroughly
5. Commit with clear messages
6. Push and create a PR

## Coding Standards

### PHP

Follow Magento 2 coding standards:

- PSR-4 autoloading
- Dependency injection via constructor
- Use interfaces where appropriate
- Document public methods with PHPDoc
- Follow Magento's naming conventions

```php
/**
 * Calculate the number of boxes needed for given square meters
 *
 * @param float $squareMeters
 * @param int $productId
 * @return int
 */
public function calculateBoxes(float $squareMeters, int $productId): int
{
    // Implementation
}
```

### JavaScript

- Use RequireJS module pattern
- Follow Magento's JS conventions
- Use jQuery for DOM manipulation
- Add proper event cleanup

```javascript
define([
    'jquery'
], function($) {
    'use strict';

    return function(config) {
        // Implementation
    };
});
```

### Templates (PHTML)

- Minimize PHP logic in templates
- Use view models for data preparation
- Keep JavaScript in separate files when possible
- Use Magento's translation function for strings

```php
<?= $block->escapeHtml(__('Add to Cart')) ?>
```

### CSS

- Use clear class naming
- Consider responsive design
- Avoid `!important` where possible
- Group related styles

## Testing

### Before Submitting

1. **Functional Testing**
   - Test calculator on product pages
   - Verify calculations are accurate
   - Check cart displays correctly
   - Test on multiple browsers

2. **Compatibility Testing**
   - Test on supported Magento versions
   - Test on PHP 7.4 and 8.1
   - Verify no conflicts with common modules

3. **Edge Cases**
   - Products without required attributes
   - Zero/negative quantities
   - Very large quantities
   - Special characters in prices

### Key Test Scenarios

| Scenario | Expected Result |
|----------|-----------------|
| Enter 5 m² | Calculates correct boxes needed |
| Enter 3 boxes | Calculates correct m² coverage |
| Change quantity | Price updates correctly with VAT |
| Product without attributes | Warning message displayed |
| Add to cart | Correct tile quantity added |

## Module Architecture

### Key Components

- **Block/TileCalculator.php**: Main product page logic
- **Helper/Calculator.php**: Core calculation methods
- **view/frontend/templates/**: UI templates
- **view/frontend/web/js/**: JavaScript modules

### Calculation Flow

```
User Input → JavaScript Handler → Calculation Logic → UI Update
                                          ↓
                                    Payment Widgets
```

### Adding New Features

1. Determine the appropriate component (Block, Helper, ViewModel)
2. Follow existing patterns in the codebase
3. Use dependency injection
4. Update related templates if needed
5. Add necessary translations

## Commit Messages

Use clear, descriptive commit messages:

```
Add support for custom VAT rates

- Add configuration option for VAT percentage
- Update Calculator helper to use configured rate
- Update frontend templates to display rate
```

Format:
- First line: Brief description (50 chars max)
- Blank line
- Detailed explanation if needed

## Pull Request Process

1. Fill out the PR template completely
2. Ensure all tests pass
3. Request review from maintainers
4. Address feedback promptly
5. Keep PRs focused on single features/fixes

## Documentation

- Update README.md for user-facing changes
- Add inline comments for complex logic
- Update configuration examples if needed

## Questions?

- Open an issue for questions
- Check existing documentation
- Review similar PRs for examples

## License

By contributing, you agree that your contributions will be licensed under the OSL-3.0 and AFL-3.0 licenses.

---

Thank you for contributing to Product Tile Calculator!
