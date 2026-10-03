# StudentHub - Practical 8

Practical 8 adds MySQL database connectivity using PHP PDO to the existing StudentHub project.

## Main Practical 8 files

- `php/db_connect.php` - PDO connection to MySQL
- `database/studenthub_schema.sql` - creates the `studenthub` database and its `courses` and `students` tables
- `Pages/register_db.html` - database registration form
- `php/process_registration_db.php` - validates the form and inserts a student using a PDO prepared statement
- `php/view_students_db.php` - reads students using PDO and an INNER JOIN with the courses table

## XAMPP setup

1. Put the project folder inside `C:\xampp\htdocs\StudentHub\`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open phpMyAdmin from XAMPP.
4. Import `database/studenthub_schema.sql`.
5. Open `http://localhost/StudentHub/Pages/register_db.html`.
6. Submit a student record.
7. Open `http://localhost/StudentHub/php/view_students_db.php`.

## Practical 8 concepts used

- MySQL database
- Tables and primary keys
- Foreign key relationship
- PHP PDO
- Prepared statements
- INSERT
- SELECT
- INNER JOIN
- Fetching records with `fetchAll(PDO::FETCH_ASSOC)`

The original Practical 7 CSV registration files are kept so the previous practical remains available. Practical 8 uses a separate MySQL registration page so both practicals can be demonstrated in the same StudentHub project.
