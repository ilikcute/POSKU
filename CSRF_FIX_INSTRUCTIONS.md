# Instruksi Perbaikan CSRF Token

## Perubahan yang Sudah Dilakukan:

### 1. ✅ File `.env`
- `SESSION_LIFETIME=120` (dari 1 menit ke 120 menit)
- `SESSION_DOMAIN=null` (dari 127.0.0.1)

### 2. ✅ File `app/Http/Middleware/HandleInertiaRequests.php`
- Menambahkan `'csrf_token' => $request->session()->token()` di shared props
- Token sekarang di-share ke setiap halaman Inertia

### 3. ✅ File `resources/js/bootstrap.js`
- Menambahkan axios interceptor untuk update CSRF token secara dinamis

### 4. ✅ File `resources/js/app.js`
- Menambahkan Inertia router event listener
- Update meta tag CSRF setiap kali navigasi

### 5. ✅ File `resources/js/Pages/Shifts/Authorizations/Index.vue`
- Refactor dari Options API ke Composition API
- Menggunakan `router.delete()` instead of `this.$inertia.delete()`

---

## LANGKAH YANG HARUS DILAKUKAN SEKARANG:

### 1. **Stop Semua Development Server**
```powershell
# Stop PHP server jika berjalan (tekan Ctrl+C di terminal)
# Stop Vite dev server jika berjalan (tekan Ctrl+C di terminal)
```

### 2. **Clear Laravel Cache**
```powershell
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. **Rebuild Vite Assets** (PENTING!)
```powershell
# Install dependencies jika belum
npm install

# Build untuk development
npm run dev

# ATAU build untuk production
npm run build
```

### 4. **Clear Browser Cache**
- Buka DevTools (F12)
- Klik kanan pada tombol refresh browser
- Pilih "Empty Cache and Hard Reload" atau "Hard Refresh"
- ATAU gunakan Incognito/Private mode

### 5. **Restart PHP Server**
```powershell
php artisan serve
```

### 6. **Test di Browser**
- Buka http://127.0.0.1:8000/shifts/authorizations
- Coba delete salah satu authorization
- Seharusnya TIDAK perlu refresh lagi

---

## Debug Jika Masih Bermasalah:

### Cek CSRF Token di Browser Console:
```javascript
// Paste di Browser Console (F12)
console.log('Meta CSRF:', document.querySelector('meta[name="csrf-token"]')?.content);
console.log('Axios CSRF:', window.axios.defaults.headers.common['X-CSRF-TOKEN']);
```

### Cek di Network Tab:
1. Buka DevTools → Network tab
2. Lakukan action (delete, create, update)
3. Cek request headers, pastikan ada:
   - `X-CSRF-TOKEN: <token>`
   - `X-Requested-With: XMLHttpRequest`

### Cek Response dari Server:
- Jika error 419 → CSRF token mismatch (masalah token)
- Jika error 403 → Forbidden (masalah permission)
- Jika error 500 → Server error (cek log)

---

## Catatan Penting:

1. **SESSION_LIFETIME=120** artinya session bertahan 2 jam (120 menit)
2. **CSRF token akan di-refresh otomatis** setiap kali Inertia navigasi
3. **Axios akan selalu menggunakan token terbaru** dari meta tag
4. **Tidak perlu refresh manual lagi!**

## Jika Masih Gagal:

Cek log Laravel:
```powershell
tail -f storage/logs/laravel.log
# Atau di Windows:
Get-Content storage/logs/laravel.log -Wait -Tail 50
```
