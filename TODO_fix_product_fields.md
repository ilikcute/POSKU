# TODO: Fix Product Model, Controller, and Import Features

## Completed Fixes:
- [x] Add 'stock' to Product model fillable array
- [x] Add 'stock' => 'integer' and 'max_stock_alert' => 'integer' to Product model casts
- [x] Update ProductController store method validation:
  - Change 'min_wholesale_qty' from nullable to required
  - Add 'stock' => 'nullable|integer|min:0'
  - Add 'max_stock_alert' => 'nullable|integer|min:0'
  - Add 'reorder' => 'nullable|string'
- [x] Update ProductController update method validation with same changes
- [x] Fix ProductTemplateExport dummy data to include all fields in correct order:
  - Added missing 'stock', 'max_stock_alert', 'reorder' fields
  - Corrected order to match migration schema
- [x] Refactor ProductsImport to use ToCollection for better control over import process
- [x] Update ProductController import method to save history only for replaced products (matching product_code) and replace all products with imported data

## Summary of Changes:
- Ensured all fields from the products migration are properly handled in the model and controller
- Fixed validation rules to match migration constraints (e.g., min_wholesale_qty is now required)
- Added proper casting for integer fields in the model
- Corrected the import template dummy data order to prevent user confusion and import errors
- Improved import logic to only historize replaced products and properly replace all products with imported data
