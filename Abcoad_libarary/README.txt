OLMS - Minimal PHP Online Library Management System
--------------------------------------------------

How to run locally (XAMPP / WAMP / LAMP):
1. Import the SQL file 'db.sql' into your MySQL server (or run it via phpMyAdmin).
   - The file creates database 'olms' and sample data.
2. Place the 'olms' folder in your web server's document root (e.g., C:/xampp/htdocs/).
3. Edit 'db.php' if your DB credentials differ.
4. Start Apache and MySQL, then visit: http://localhost/olms/index.php

Default accounts:
- Admin: admin@example.com  password: admin123
- User : user@example.com   password: user123

Notes:
- Passwords in db.sql are bcrypt hashes for the example passwords above.
- This is a minimal starter project intended for learning and local testing.
- For production, implement stronger security: prepared statements (used), CSRF protection, HTTPS, input sanitization, and better error handling.
