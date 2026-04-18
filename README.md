📚 Library Management System – Documentation
1. Overview

The Library Management System (LMS) is a web-based application designed to automate and streamline library operations. It enables efficient management of books, users, and borrowing processes through separate interfaces for administrators and students.

2. User Roles
2.1 Administrator

The administrator has full control over the system, including managing books, users, and transactions.

2.2 Student

Students can browse books, borrow materials, and manage their personal profiles.

3. Core Features
3.1 Authentication System
Secure login for administrators and students
Password hashing using bcrypt
Session-based authentication
Auto-generated unique Student IDs
Optional password recovery
4. Admin Functionalities
4.1 Dashboard

Displays key system statistics:

Total categories
Total authors
Total books
Issued books
Registered students
4.2 Category Management
Create, update, and delete categories
Organize books by subject
4.3 Author Management
Add and manage author records
Store details (name, email, nationality, biography)
Link authors to books
4.4 Book Management
Add, update, and delete books
Store details such as title, ISBN, category, author, and quantity
Track available and issued copies
Search and filter books
4.5 Book Issuing & Returning
Issue books to students
Automatic due date calculation (14 days)
Track issue and return status
Identify overdue books
Update inventory automatically
4.6 Student Management
View and search students
Access full student profiles
Monitor borrowing history
4.7 Book Request Management
View and manage student requests
Approve or reject requests
Track request history
4.8 Admin Account Management
Update profile
Change password securely
5. Student Functionalities
5.1 Registration & Login
Self-registration system
Auto-generated Student ID (e.g., STU2026XXXX)
Secure authentication
5.2 Dashboard
View borrowed books
Check available books
Receive overdue notifications
5.3 Book Browsing
Search books by title, author, or category
View book details and availability
5.4 Issued Books
Track current borrowed books
View issue and due dates
Monitor overdue status
5.5 Borrowing History
View past transactions
Track returned and overdue books
5.6 Profile Management
Update personal information
Upload profile picture (optional)
5.7 Password Management
Change password securely
Password validation and recovery (optional)
5.8 Book Requests
Request unavailable books
Track request status
6. Technical Specifications
6.1 Technology Stack
Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: MySQL
Server: Apache (XAMPP/WAMP)
6.2 Database Tables
admins
students
categories
authors
books
issued_books
book_requests
6.3 System Features
Relational database with foreign keys
Prepared statements for SQL queries
Input validation and sanitization
Responsive design
Real-time updates
7. User Interface
7.1 Design
Clean, modern layout
Card-based structure
Color-coded statuses:
Green: Available/Returned
Blue: Active
Red: Overdue
7.2 User Experience
Easy navigation
Fast operations
Search and filtering
Clear notifications
8. Security Features
Password hashing (bcrypt)
SQL injection prevention
XSS protection
Role-based access control
Session management
9. Installation Requirements
PHP 7.4+
MySQL 5.7+
Apache Server
Modern web browser
10. Benefits
For Administrators
Centralized control
Automated processes
Accurate tracking
For Students
Easy access to books
Borrowing history tracking
Notifications and reminders
For Institutions
Improved efficiency
Reduced manual work
Better resource management
11. Use Cases
Schools
Colleges
Public libraries
Corporate libraries
12. Maintenance
Regular backups
System updates
Technical support
Conclusion

The Library Management System provides a scalable and efficient solution for modern library operations, improving accessibility, accuracy, and overall user experience.
