# Web Laravel - Tahap 1 (Struktur & Setup)

## Struktur Folder
```text
web_laravel/
  app/
    Http/
      Controllers/
        Api/                  # Controller endpoint API untuk mobile app
        Dashboard/            # Controller dashboard administrator
    Models/                   # Eloquent model
    Services/                 # Business service (presensi, geofence, anti-mock validation)
    Repositories/             # Abstraksi akses data/query kompleks
  bootstrap/                  # Bootstrap framework
  config/                     # Konfigurasi aplikasi (database, auth, dll)
  database/
    migrations/               # File migrasi schema Laravel
    seeders/                  # Seeder data awal
    schema/                   # SQL schema referensi Supabase
  public/                     # Entry point web (index.php, aset publik)
  resources/
    views/
      dashboard/              # Blade view dashboard admin
    js/                       # Script frontend dashboard
    css/                      # Style dashboard
  routes/
    api.php                   # Route API untuk aplikasi mobile
    web.php                   # Route web dashboard administrator
  storage/                    # Log, cache, upload
  tests/                      # Unit/Feature test
  composer.json               # Dependency PHP
  package.json                # Dependency frontend dashboard
  .env.example                # Variabel environment template
```

## Keterangan Dependency PHP (composer.json)
- `laravel/framework`: Framework utama Laravel.
- `laravel/sanctum`: Autentikasi token API untuk mobile app.
- `supabase/supabase-php`: Client Supabase untuk akses service Supabase API.
- `guzzlehttp/guzzle`: HTTP client untuk integrasi service eksternal.
- `doctrine/dbal`: Membantu operasi perubahan skema database yang lebih fleksibel.

## Keterangan Dependency Frontend (package.json)
- `vite`: Bundler frontend modern untuk dashboard.
- `laravel-vite-plugin`: Integrasi Vite dengan Laravel.
- `axios`: HTTP client frontend untuk request data dashboard.
- `alpinejs`: Interaktivitas UI ringan pada Blade dashboard.
- `tailwindcss`, `postcss`, `autoprefixer`: Styling dashboard responsif.

## Catatan Koneksi Supabase
- Koneksi PostgreSQL Supabase diletakkan di `.env` dan dibaca oleh `config/database.php`.
- API URL dan service key Supabase disimpan di env agar tidak hardcoded.
