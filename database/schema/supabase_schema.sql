-- Supabase PostgreSQL schema for attendance security system
-- Required extension for UUID generation
create extension if not exists pgcrypto;

-- Enum definitions
do $$ begin
  create type role_pengguna as enum ('karyawan', 'administrator');
exception
  when duplicate_object then null;
end $$;

do $$ begin
  create type status_presensi as enum ('hadir', 'terlambat', 'izin', 'alpha', 'ditolak');
exception
  when duplicate_object then null;
end $$;

do $$ begin
  create type jenis_log_deteksi as enum (
    'developer_options',
    'mock_app_terdeteksi',
    'koordinat_tidak_konsisten',
    'aman'
  );
exception
  when duplicate_object then null;
end $$;

-- 1) pengguna
create table if not exists pengguna (
  id uuid primary key default gen_random_uuid(),
  nama varchar(120) not null,
  email varchar(150) not null unique,
  password_hash text not null,
  role role_pengguna not null,
  is_active boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 2) karyawan
create table if not exists karyawan (
  id uuid primary key default gen_random_uuid(),
  pengguna_id uuid not null unique,
  nip varchar(30) not null unique,
  departemen varchar(100),
  no_hp varchar(25),
  jabatan varchar(100),
  foto_url text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint fk_karyawan_pengguna
    foreign key (pengguna_id) references pengguna(id)
    on update cascade on delete cascade
);

-- 3) administrator
create table if not exists administrator (
  id uuid primary key default gen_random_uuid(),
  pengguna_id uuid not null unique,
  level_akses varchar(50) not null default 'admin',
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint fk_admin_pengguna
    foreign key (pengguna_id) references pengguna(id)
    on update cascade on delete cascade
);

-- 4) geofencing
create table if not exists geofencing (
  id uuid primary key default gen_random_uuid(),
  nama_lokasi varchar(120) not null,
  latitude numeric(10,7) not null,
  longitude numeric(10,7) not null,
  radius_meter integer not null check (radius_meter > 0),
  aktif boolean not null default true,
  dibuat_oleh_admin_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint fk_geofencing_admin
    foreign key (dibuat_oleh_admin_id) references administrator(id)
    on update cascade on delete set null
);

-- 5) presensi
create table if not exists presensi (
  id uuid primary key default gen_random_uuid(),
  karyawan_id uuid not null,
  geofencing_id uuid not null,
  tanggal date not null,
  check_in_at timestamptz,
  check_out_at timestamptz,
  lat_check_in numeric(10,7),
  lng_check_in numeric(10,7),
  lat_check_out numeric(10,7),
  lng_check_out numeric(10,7),
  jarak_dari_titik_meter numeric(8,2),
  status status_presensi not null default 'hadir',
  sumber_lokasi varchar(20) not null default 'gps',
  device_id varchar(120),
  catatan text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint fk_presensi_karyawan
    foreign key (karyawan_id) references karyawan(id)
    on update cascade on delete restrict,
  constraint fk_presensi_geofencing
    foreign key (geofencing_id) references geofencing(id)
    on update cascade on delete restrict,
  constraint uq_presensi_harian unique (karyawan_id, tanggal)
);

-- 6) log_deteksi
create table if not exists log_deteksi (
  id uuid primary key default gen_random_uuid(),
  presensi_id uuid,
  karyawan_id uuid not null,
  jenis_deteksi jenis_log_deteksi not null,
  developer_options_on boolean not null default false,
  aplikasi_mock_terdeteksi boolean not null default false,
  konsistensi_koordinat_valid boolean not null default true,
  detail_deteksi jsonb,
  detected_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  constraint fk_logdeteksi_presensi
    foreign key (presensi_id) references presensi(id)
    on update cascade on delete set null,
  constraint fk_logdeteksi_karyawan
    foreign key (karyawan_id) references karyawan(id)
    on update cascade on delete cascade
);

-- Recommended indexes
create index if not exists idx_presensi_tanggal on presensi(tanggal);
create index if not exists idx_presensi_karyawan on presensi(karyawan_id);
create index if not exists idx_logdeteksi_karyawan on log_deteksi(karyawan_id);
create index if not exists idx_logdeteksi_detectedat on log_deteksi(detected_at desc);
