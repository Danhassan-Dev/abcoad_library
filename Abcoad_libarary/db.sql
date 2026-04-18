-- OLMS database schema and sample data
CREATE DATABASE IF NOT EXISTS olms;
USE olms;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user'
);

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(150),
  isbn VARCHAR(50),
  availability TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS borrow_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  borrow_date DATE NOT NULL,
  return_date DATE DEFAULT NULL,
  status ENUM('borrowed','returned') DEFAULT 'borrowed',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Sample admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Library Admin', 'admin@example.com', '$2y$10$e0NR6SxqzQZxqv1F8Y6YQe1K5rJwq3v1kz6b1e6zQq9s2Yp1V9K0W', 'admin');

-- Sample user (password: user123)
INSERT INTO users (name, email, password) VALUES
('Test User', 'user@example.com', '$2y$10$zQ9Yh6yA1F8Qe2P3K9d7aO5qv8Yg6h3J7k8L9m0N1b2V3c4D5E6');

INSERT INTO books (title, author, isbn, availability) VALUES
('Introduction to Algorithms', 'Cormen, Leiserson, Rivest, Stein', '0262033844', 1),
('Clean Code', 'Robert C. Martin', '0132350882', 1),
('Computer Networks', 'Andrew S. Tanenbaum', '0132126958', 1);
