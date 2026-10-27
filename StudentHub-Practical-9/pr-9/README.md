# StudentHub — Practical 8 and Practical 9

This is one continuous StudentHub project folder. Practical 8 files remain in place; Practical 9 is added inside the existing `Pages`, `php`, and `database` folders without creating a separate top-level project folder.

## Practical 9: Secure User Registration

- `Pages/register_secure.php` — registration form using the existing StudentHub theme
- `php/process_secure_registration.php` — backend validation, duplicate checks, prepared inserts, and `password_hash()`
- `php/db_connect_mysqli.php` — MySQLi connection
- `database/studenthub_schema.sql` — includes the Practical 8 schema and Practical 9 `users` table

## Run with XAMPP

1. Extract this single `pr-8` folder into `C:\xampp\htdocs\StudentHub\` (rename the folder to `StudentHub` if needed).
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `database/studenthub_schema.sql`. If the `studenthub` database already exists, importing may report that the database or existing tables already exist; in that case, run only the Practical 9 `CREATE TABLE IF NOT EXISTS users ...` section at the end of the SQL file.
4. Open `http://localhost/StudentHub/Pages/register_secure.php`.
5. Try a new account, then try the same email or username again to test duplicate checks.

Passwords are stored as hashes, not plaintext. Registration uses backend validation, MySQLi prepared statements, and unique database indexes for email and username. Frontend checks improve usability but are not a replacement for server-side validation.
