# TODO: Fix POSKU Program Issues

## Backend Fixes
- [ ] Fix syntax errors in routes/web.php: Correct "NaNare" to "Route::middleware", complete permission groups for suppliers, members, salesmen
- [ ] Fix syntax in PurchaseController.php and SaleController.php: Close Rule::in arrays, remove NaN
- [ ] Fix promotion expiry logic: Check PromotionFactory defaults, ensure strict date filtering in getPriceWithPromotion
- [ ] Update Product.php: Add round( , 0) for integer prices in getPriceWithPromotion
- [ ] Add API route in routes/api.php for price check endpoint

## Frontend Fixes
- [ ] Create missing Vue page: resources/js/Pages/Purchases/Create.vue with purchase form

## Test Fixes
- [ ] Update NewPromotionTest.php: Use precise dates for expiry test
- [ ] Run full php artisan test suite after fixes

## Verification
- [ ] Run php artisan migrate:fresh --seed
- [ ] Use browser_action to test UI flows: login, dashboard, sales/purchase creation, promotion application
- [ ] Confirm WhatsApp integration placeholder (optional: install Twilio SDK)
