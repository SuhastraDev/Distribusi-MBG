# Deployment VPS + GitHub Actions

Dokumen ini menjelaskan setup CI/CD untuk deploy aplikasi Laravel Distribusi MBG ke VPS Ubuntu.

## Target VPS

- SSH user: `ubuntu`
- Host/IP: `43.153.227.63`
- Domain: DuckDNS, contoh format `nama-domain.duckdns.org`
- Deploy path rekomendasi: `/var/www/distribusi-mbg`

Jangan simpan password VPS di repository. Untuk GitHub Actions, gunakan SSH key khusus deploy.

## Workflow yang tersedia

- `.github/workflows/ci.yml`
  - Jalan saat push dan pull request ke `main`.
  - Install dependency PHP dan Node.
  - Menjalankan Pint, build Vite, dan test Laravel.

- `.github/workflows/deploy-production.yml`
  - Jalan otomatis saat push ke `main`.
  - Tetap bisa dijalankan manual dari tab Actions.
  - Menjalankan verifikasi dulu.
  - Deploy ke VPS via SSH jika verifikasi lulus.

## GitHub Secrets yang harus dibuat

Buka repository GitHub:

`Settings > Secrets and variables > Actions > New repository secret`

Tambahkan:

| Secret | Isi |
| --- | --- |
| `VPS_HOST` | `43.153.227.63` |
| `VPS_USER` | `ubuntu` |
| `VPS_SSH_PRIVATE_KEY` | Private key deploy khusus GitHub Actions |
| `DEPLOY_PATH` | `/var/www/distribusi-mbg` |

Tambahkan juga repository/environment variable:

| Variable | Isi |
| --- | --- |
| `PRODUCTION_URL` | `https://nama-domain.duckdns.org` |

## Membuat SSH key deploy

Jalankan di laptop lokal:

```bash
ssh-keygen -t ed25519 -C "github-actions-distribusi-mbg" -f distribusi_mbg_deploy_key
```

Hasilnya:

- `distribusi_mbg_deploy_key` untuk isi secret `VPS_SSH_PRIVATE_KEY`.
- `distribusi_mbg_deploy_key.pub` untuk ditambahkan ke VPS.

Masuk ke VPS:

```bash
ssh ubuntu@43.153.227.63
```

Tambahkan public key:

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

Paste isi file `distribusi_mbg_deploy_key.pub` ke `authorized_keys`.

## Setup awal VPS

Masuk ke VPS:

```bash
ssh ubuntu@43.153.227.63
```

Install kebutuhan dasar:

```bash
sudo apt update
sudo apt install -y nginx mysql-server git unzip curl
```

Install PHP 8.3 dan extension Laravel:

```bash
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl
```

Install Composer:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
rm composer-setup.php
```

Install Node.js 22:

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

## Clone project di VPS

```bash
sudo mkdir -p /var/www/distribusi-mbg
sudo chown -R ubuntu:www-data /var/www/distribusi-mbg
git clone https://github.com/SuhastraDev/Distribusi-MBG.git /var/www/distribusi-mbg
cd /var/www/distribusi-mbg
```

Install dependency dan buat `.env` production:

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```bash
nano .env
```

Minimal ubah:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://nama-domain.duckdns.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=distribusi_mbg
DB_USERNAME=distribusi_mbg
DB_PASSWORD=password_database_yang_kuat
```

## Setup database production

```bash
sudo mysql
```

Jalankan SQL berikut, ganti password:

```sql
CREATE DATABASE distribusi_mbg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'distribusi_mbg'@'localhost' IDENTIFIED BY 'password_database_yang_kuat';
GRANT ALL PRIVILEGES ON distribusi_mbg.* TO 'distribusi_mbg'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Lanjutkan:

```bash
php artisan migrate --force
php artisan storage:link
sudo chown -R ubuntu:www-data /var/www/distribusi-mbg
sudo chmod -R ug+rw storage bootstrap/cache
```

## Nginx untuk DuckDNS

Buat config:

```bash
sudo nano /etc/nginx/sites-available/distribusi-mbg
```

Isi:

```nginx
server {
    listen 80;
    server_name nama-domain.duckdns.org;
    root /var/www/distribusi-mbg/public;

    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan:

```bash
sudo ln -s /etc/nginx/sites-available/distribusi-mbg /etc/nginx/sites-enabled/distribusi-mbg
sudo nginx -t
sudo systemctl reload nginx
```

## HTTPS dengan Certbot

Pastikan DuckDNS sudah mengarah ke IP `43.153.227.63`, lalu:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d nama-domain.duckdns.org
```

## Cara deploy setelah setup awal

Setelah secrets dan VPS siap:

1. Push perubahan ke branch `main`, atau buka tab GitHub Actions.
2. Jika manual, pilih workflow `Deploy Production`.
3. Klik `Run workflow`.

Workflow akan:

1. Menjalankan test dan build.
2. SSH ke VPS.
3. Pull kode terbaru.
4. Install dependency production.
5. Build asset.
6. Jalankan migration.
7. Cache config, route, dan view Laravel.
