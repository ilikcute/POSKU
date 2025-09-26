# TODO: Add Clear Products & Customers Feature to Promotions

## Frontend Changes (Index.vue)
- [x] Modify index query to include products_count and customer_types_count
- [x] Add display in table view: columns for Produk and Customer
- [x] Add display in card view: lines for Produk and Customer
- [x] Add "Kosongkan Produk & Customer" button in actions
- [x] Add clearProductsCustomers method with confirmation

## Backend Changes
- [x] Add route: Route::patch('promotions/{promotion}/clear', [PromotionController::class, 'clear'])->name('promotions.clear');
- [x] Add clear method in PromotionController to sync empty arrays for products and customerTypes
- [x] Test the clear functionality
