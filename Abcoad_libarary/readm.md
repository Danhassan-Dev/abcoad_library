# 📚 Library Management System

A comprehensive web-based library management system built with PHP, MySQL, HTML, CSS, and JavaScript.

## 🌟 Features

### Admin Features
- **Dashboard**: View comprehensive statistics including total categories, authors, books, issued books, and registered students
- **Category Management**: Add, update, and delete book categories
- **Author Management**: Manage author information
- **Book Management**: Complete CRUD operations for books with details like title, author, category, ISBN, quantity
- **Issue & Return System**: Issue books to students and track returns with due dates
- **Student Management**: Search and view complete student profiles and borrowing history
- **Account Management**: Update admin password and profile information
- **Overdue Tracking**: Monitor and manage overdue books

### Student Features
- **Registration & Login**: Self-registration with auto-generated unique student ID
- **Dashboard**: Overview of borrowed books, return dates, and available books
- **Browse Books**: Search and filter books by title, author, category, or ISBN
- **Profile Management**: Update personal information and profile picture
- **Password Management**: Change password and recover forgotten passwords
- **Borrowing History**: View complete history of borrowed and returned books
- **Due Date Tracking**: Monitor return dates and receive overdue notifications

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache (XAMPP/WAMP/LAMP)

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- Web browser (Chrome, Firefox, Safari, Edge)

## 🚀 Installation Guide

### Step 1: Install XAMPP/WAMP/LAMP

Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### Step 2: Setup Database

1. Start Apache and MySQL from XAMPP Control Panel
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `library_management`
4. Import the SQL file or run the database schema provided

### Step 3: Configure Database Connection

Edit `config.php` and update these values if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_management');
```

### Step 4: Setup Project Files

1. Copy all project files to your web server directory:
   - For XAMPP: `C:\xampp\htdocs\library`
   - For WAMP: `C:\wamp\www\library`
   - For LAMP: `/var/www/html/library`

2. Ensure proper file permissions (Linux/Mac):
```bash
chmod -R 755 /var/www/html/library
```

### Step 5: Initialize Database

1. Run the SQL schema to create all tables
2. Default admin credentials will be created:
   - **Username**: admin
   - **Password**: password

### Step 6: Access the System

Open your web browser and navigate to:
```
http://localhost/library/
```

## 📁 Project Structure

```
library/
│
├── config.php                 # Database configuration
├── index.php                  # Landing page
├── logout.php                 # Logout functionality
│
├── admin_login