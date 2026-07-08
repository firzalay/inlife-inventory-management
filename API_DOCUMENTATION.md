# Dokumentasi RESTful API — InLife Inventory Management

Base URL untuk seluruh request API: `/api`

---

## 1. Autentikasi API

Seluruh endpoint API (kecuali `/api/login`) memerlukan header autentikasi menggunakan token Bearer:
`Authorization: Bearer {token}`

### POST /api/login
Mengautentikasi pengguna dan mengembalikan token akses Sanctum.

- **Request Body (JSON):**
```json
{
  "email": "admin@inventaris.test",
  "password": "password"
}
```
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@inventaris.test",
      "roles": ["Admin"]
    }
  }
}
```
- **Response Error Kredensial Salah (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Kredensial login tidak cocok.",
  "errors": null
}
```

### POST /api/logout
Mencabut token aktif saat ini.
- **Headers:** `Authorization: Bearer {token}`
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Logout berhasil.",
  "data": null
}
```

---

## 2. Endpoint Master Data Barang (Products)

### GET /api/products
Mengambil daftar barang dengan dukungan pencarian, penyaringan kategori, dan paginasi.
- **Query Params:**
  - `search` (string, opsional): cari berdasarkan nama atau kode barang.
  - `category_id` (integer, opsional): saring berdasarkan ID kategori.
  - `page` (integer, opsional): nomor halaman paginasi.
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Daftar barang berhasil diambil.",
  "data": [
    {
      "id": 1,
      "code": "PRD-0001",
      "name": "Laptop Lenovo ThinkPad",
      "category": {
        "id": 2,
        "name": "Elektronik",
        "created_at": "2026-07-04T02:47:59+00:00"
      },
      "stock_baik": 10,
      "stock_rusak": 0,
      "stock_perlu_perbaikan": 0,
      "total_stock": 10,
      "available_stock": 10,
      "location": "Gudang A Rak 2",
      "image_url": "http://localhost:8000/storage/products/thinkpad.png",
      "created_at": "2026-07-04T02:47:59+00:00"
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 12,
    "current_page": 1,
    "last_page": 1
  }
}
```

### GET /api/products/{id}
Mengambil rincian detail barang spesifik berdasarkan ID.
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Rincian barang berhasil diambil.",
  "data": {
    "id": 1,
    "code": "PRD-0001",
    "name": "Laptop Lenovo ThinkPad",
    "category": {
      "id": 2,
      "name": "Elektronik",
      "created_at": "2026-07-04T02:47:59+00:00"
    },
    "stock_baik": 10,
    "stock_rusak": 0,
    "stock_perlu_perbaikan": 0,
    "total_stock": 10,
    "available_stock": 10,
    "location": "Gudang A Rak 2",
    "image_url": "http://localhost:8000/storage/products/thinkpad.png",
    "created_at": "2026-07-04T02:47:59+00:00"
  }
}
```

### POST /api/products
Menambahkan barang baru ke dalam inventaris (Hanya Admin/Staff).
- **Request (Multipart Form Data atau JSON):**
  - `code` (string, required): Kode unik barang.
  - `name` (string, required): Nama barang.
  - `category_id` (integer, required): ID Kategori terdaftar.
  - `stock_baik` (integer, required, min:0): Jumlah stok awal kondisi baik.
  - `stock_rusak` (integer, required, min:0): Jumlah stok awal kondisi rusak.
  - `stock_perlu_perbaikan` (integer, required, min:0): Jumlah stok awal kondisi perlu perbaikan.
  - `location` (string, required): Lokasi penyimpanan fisik.
  - `image` (file, opsional: jpg/png, max 2MB).
- **Response Sukses (201 Created):**
```json
{
  "success": true,
  "message": "Barang berhasil ditambahkan.",
  "data": {
    "id": 2,
    "code": "PRD-0002",
    "name": "Proyektor Epson",
    "category": {
      "id": 2,
      "name": "Elektronik",
      "created_at": "2026-07-04T02:47:59+00:00"
    },
    "stock_baik": 5,
    "stock_rusak": 0,
    "stock_perlu_perbaikan": 0,
    "total_stock": 5,
    "available_stock": 5,
    "location": "Ruang A3",
    "image_url": null,
    "created_at": "2026-07-04T04:12:00+00:00"
  }
}
```

### PUT /api/products/{id}
Memperbarui rincian barang yang sudah terdaftar (Hanya Admin/Staff).
- **Request Body (JSON):**
  - Sama dengan validasi formulir POST.
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Barang berhasil diperbarui.",
  "data": { ... }
}
```

### DELETE /api/products/{id}
Menghapus barang secara soft-delete (Hanya Admin/Staff).
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Barang berhasil dihapus.",
  "data": null
}
```

---

## 3. Endpoint Kategori

### GET /api/categories
Mengambil seluruh daftar kategori terdaftar.
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "",
  "data": [
    {
      "id": 1,
      "name": "Elektronik",
      "created_at": "2026-07-04T02:47:59+00:00"
    }
  ]
}
```

---

## 4. Endpoint Peminjaman (Borrowings)

### GET /api/borrowings
Mengambil daftar seluruh transaksi peminjaman barang dengan filter.
- **Query Params:**
  - `search` (string, opsional): Cari nama peminjam.
  - `status` (string, opsional: `borrowed`/`returned`/`overdue`).
  - `start_date`/`end_date` (date, opsional: rentang tanggal pinjam `YYYY-MM-DD`).
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Daftar transaksi peminjaman berhasil diambil.",
  "data": [
    {
      "id": 1,
      "borrower_name": "Budi Santoso",
      "borrow_date": "2026-07-04",
      "due_date": "2026-07-11",
      "return_date": null,
      "status": "borrowed",
      "computed_status": "borrowed",
      "details": [
        {
          "id": 5,
          "product": {
            "id": 1,
            "code": "PRD-0001",
            "name": "Laptop Lenovo ThinkPad",
            "stock_baik": 7,
            "stock_rusak": 0,
            "stock_perlu_perbaikan": 0,
            "total_stock": 7,
            "available_stock": 7,
            "location": "Gudang A Rak 2",
            "image_url": "http://localhost:8000/storage/products/thinkpad.png"
          },
          "quantity": 3,
          "condition_on_return": null
        }
      ],
      "created_at": "2026-07-04T03:22:00+00:00"
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

### GET /api/borrowings/{id}
Mengambil rincian lengkap dari transaksi peminjaman berdasarkan ID.
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Rincian transaksi peminjaman berhasil diambil.",
  "data": { ... }
}
```

### POST /api/borrowings
Mencatat transaksi peminjaman baru (Hanya Admin/Staff).
- **Request Body (JSON):**
```json
{
  "borrower_name": "Budi Santoso",
  "borrow_date": "2026-07-04",
  "due_date": "2026-07-11",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```
- **Response Sukses (201 Created):**
```json
{
  "success": true,
  "message": "Peminjaman berhasil dicatat.",
  "data": {
    "id": 1,
    "borrower_name": "Budi Santoso",
    "borrow_date": "2026-07-04",
    "due_date": "2026-07-11",
    "return_date": null,
    "status": "borrowed",
    "computed_status": "borrowed",
    "details": [
      {
        "id": 1,
        "product": { ... },
        "quantity": 2,
        "condition_on_return": null
      }
    ]
  }
}
```
- **Response Error Stok Habis (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "The items.0.quantity field is invalid.",
  "errors": {
    "items.0.quantity": [
      "Stok untuk barang 'Laptop Lenovo ThinkPad' tidak mencukupi. Tersedia: 1 unit, diminta: 2 unit."
    ]
  }
}
```

### POST /api/borrowings/{id}/return
Memproses pengembalian seluruh barang peminjaman ke gudang (Hanya Admin/Staff).
- **Request Body (JSON):**
```json
{
  "conditions": {
    "1": "Baik",
    "2": "Rusak"
  }
}
```
*Catatan: Key dalam object conditions adalah ID dari detail peminjaman (`borrowing_details.id`).*
- **Response Sukses (200 OK):**
```json
{
  "success": true,
  "message": "Barang berhasil dikembalikan ke inventaris.",
  "data": {
    "id": 1,
    "borrower_name": "Budi Santoso",
    "status": "returned",
    "computed_status": "returned",
    "return_date": "2026-07-04",
    "details": [
      {
        "id": 1,
        "quantity": 2,
        "condition_on_return": "Baik"
      }
    ]
  }
}
```
