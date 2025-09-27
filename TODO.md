# TODO: Fix Product Import Conflict with Store

## Overview
The product import process fails to replace products in the table due to foreign key constraints and aggressive truncation. The store method works fine, but import truncates the entire table, causing FK violations. Replace with targeted updateOrCreate logic.

## Steps
- [x] Update `app/Http/Controllers/ProductController.php` import method:
  - Remove truncate.
  - Use updateOrCreate per row based on product_code.
  - Add transaction for atomicity.
  - Save history only for updated products.
  - Enhance logging.
- [x] Update `app/Imports/ProductsImport.php`:
  - Add validation for foreign keys (category_id, etc.) and uniques (barcode).
  - Return validation errors if any.
- [x] Test import with sample Excel (fixed type error in ProductsImport return).
- [x] Verify no FK errors, products replaced/created, history saved.
