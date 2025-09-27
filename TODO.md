# TODO: Sync Product Stock from Stocks Table

## Tasks
- [x] Add syncStock() method to Product model (app/Models/Product.php)
- [x] Modify ProductController store() method: Remove stock validation, call syncStock() after create
- [x] Modify ProductController update() method: Remove stock validation, call syncStock() after update
- [x] Modify ProductController import() method: Unset stock from data, call syncStock() after updateOrCreate
- [x] Modify ProductsImport collection() method: Unset stock from processed data
- [x] Test the changes: Create/update product, import products, verify stock syncs from stocks table
