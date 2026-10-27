# StudentHub — Practical 8, 9 and 10

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


## Practical 10 — Secure Login / Logout

### Features
- Password verification with `password_verify()` and optional password hash rehashing.
- Session ID regeneration after successful login and secure HttpOnly/SameSite session cookies.
- Protected dashboard routes and role-based admin/student redirection.
- 20-minute inactivity timeout.
- Optional 30-day remember-me token stored as a hash in MySQL and rotated after restoration.
- CSRF token validation for login and logout; prepared statements for database operations.

### Setup in XAMPP
1. Start Apache and MySQL.
2. For a fresh setup, import `database/studenthub_schema.sql` in phpMyAdmin. If your database already exists from Practical 9, import `database/practical10_auth_migration.sql` instead. Run its `ALTER TABLE` statement only once; if the `role` column already exists, skip that statement and run the `CREATE TABLE remember_tokens` section.
3. Register an account at `http://localhost/StudentHub/pr-8/Pages/register_secure.php`.
4. Log in at `http://localhost/StudentHub/pr-8/Pages/login_secure.php` using either email or username.
5. All new accounts default to `student`. To test admin routing, run `UPDATE users SET role='admin' WHERE email='your-admin-email@example.com';` in phpMyAdmin.
6. Use the Logout button on the protected dashboard. `dashboard.php`, `student_dashboard.php`, and `admin_dashboard.php` require an authenticated session and the appropriate role.

### Test cases
- Correct credentials: user is redirected to the dashboard for their role.
- Wrong password or unknown user: generic login error is displayed.
- Direct access to a protected dashboard while logged out: redirected to secure login.
- Student visiting the admin dashboard: access is denied and redirected to their own dashboard.
- Remember me: closes/reopens browser within 30 days to restore the session; token rotates after use.
- Inactivity: after 20 minutes, session expires and login is required again.

**Security note:** `secure` cookie flags are enabled automatically when the site is served over HTTPS. XAMPP localhost normally uses HTTP; use HTTPS in production. Keep the admin promotion SQL limited to a trusted administrator.
