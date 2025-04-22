# Laravel App

Laravel Test App use sanctum for token authentication file upload delete from S3 Bucket.

## 🚀 Features

- Laravel 10+
- RESTful API / Web Interface
- Authentication (Sanctum)
- File storage with AWS S3
- Environment-based configuration
- Easily extendable architecture

---

## 🧰 Requirements

- PHP >= 8.2
- Laravel 10+
- Composer
- MySQL
- AWS credentials (if using S3)

---

## 🛠️ Installation

```bash
# Clone the repository
git clone https://github.com/rishigupta121/laravel-test.git
cd your-laravel-app

# Install PHP dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
# Run migrations and seed (optional)
php artisan migrate --seed

# Serve the application
php artisan serve