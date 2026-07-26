# MySQL Setup

Project ini mendukung dua opsi MySQL untuk development lokal.

## Opsi 1 - MySQL dari Laragon/XAMPP

Gunakan opsi ini jika MySQL lokal sudah berjalan di port `3306`.

Konfigurasi `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=distribusi_mbg
DB_USERNAME=root
DB_PASSWORD=
```

Buat database:

```sql
CREATE DATABASE distribusi_mbg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lalu jalankan:

```powershell
php artisan migrate
```

## Opsi 2 - MySQL via Docker Compose

Gunakan opsi ini jika ingin database ikut project dan tidak bergantung pada Laragon/XAMPP.

Jalankan container:

```powershell
docker compose up -d mysql
```

Jika ingin membuka phpMyAdmin:

```powershell
docker compose up -d phpmyadmin
```

phpMyAdmin tersedia di:

```text
http://localhost:8081
```

Credential Docker:

```text
Host dari Laravel: 127.0.0.1
Port dari Laravel: 3307
Database: distribusi_mbg
Username: distribusi_mbg
Password: distribusi_mbg

Root username: root
Root password: root
```

Copy konfigurasi Docker:

```powershell
Copy-Item .env.docker.example .env
php artisan key:generate
php artisan migrate
```

## Kenapa port Docker memakai 3307?

Port host `3307` dipakai agar tidak bentrok dengan Laragon/XAMPP yang biasanya memakai port `3306`.

Di dalam container MySQL tetap berjalan di port `3306`, tetapi dari Laravel di host Windows aksesnya melalui `127.0.0.1:3307`.

## Stop database Docker

```powershell
docker compose down
```

Jika ingin menghapus data database Docker juga:

```powershell
docker compose down -v
```

Gunakan `down -v` hanya jika benar-benar ingin menghapus data database lokal.
