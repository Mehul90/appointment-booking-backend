# Appointment Booking Backend

This is a Symfony-based backend API for managing appointments and participants. It is designed to prevent scheduling conflicts by ensuring that the same participant cannot book multiple appointments at the same time.

## 🛠️ Built With

- [Symfony](https://symfony.com/) (version 7.2)
- PHP 8.2+
- MySQL
- Composer

## 📦 Features

- Create new appointments
- Register participants for appointments
- Prevent double-booking: the same participant cannot book multiple appointments at the same time

## 🚀 Getting Started

These instructions will help you set up the project on your local machine.

### Prerequisites

Ensure the following are installed:

- PHP 8.2 or higher
- Composer
- Symfony CLI (optional but recommended)
- MySQL database
- Git

### Installation

##### Clone the repository
```bash
git clone https://github.com/Mehul90/appointment-booking-backend.git
cd appointment-booking-backend
```

##### Install PHP dependencies:
```bash
composer install
```

##### Create and configure environment variables:
```bash
cp .env .env.local
```

##### Set up the database:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

##### Start the Symfony local web server:
```bash
symfony server:start
```
##### OR use PHP's built-in server:
```bash
php -S 127.0.0.1:8000 -t public
```
