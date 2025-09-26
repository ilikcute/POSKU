# TODO: Add Clear Products & Customers Feature to Promotions

## Frontend Changes (Index.vue)
- [ ] Modify index query to include products_count and customer_types_count
- [ ] Add display in table view: columns for Produk and Customer
- [ ] Add display in card view: lines for Produk and Customer
- [ ] Add "Kosongkan Produk & Customer" button in actions
- [ ] Add clearProductsCustomers method with confirmation

## Backend Changes
- [ ] Add route: Route::patch('promotions/{promotion}/clear', [PromotionController::class, 'clear'])->name('promotions.clear');
- [ ] Add clear method in PromotionController to sync empty arrays for products and customerTypes
- [ ] Test the clear functionality
