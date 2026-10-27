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


## Practical 11 — Student Management CRUD with Search and Filter

### Features
- Admin-only student management page: `Pages/students_manage.php`.
- Create, view, update, and delete student records using MySQLi prepared statements.
- Search across student name, email, and course; filter by course.
- Duplicate email check before insert/update, backend validation, HTML5 frontend validation, CSRF protection, and escaped output.
- Delete action requires browser confirmation and shows success/failure feedback.
- The admin dashboard includes a link to Student Management. The existing StudentHub theme and CSS remain in use.

### Setup and testing
1. Import `database/studenthub_schema.sql` for a fresh database, or use your existing `studenthub` database from earlier practicals. Practical 11 uses the existing `students` and `courses` tables; it does not require a new table.
2. Start Apache and MySQL in XAMPP and sign in using an account whose `users.role` is `admin`.
3. Open `http://localhost/StudentHub/pr-8/Pages/students_manage.php` (adjust the URL if your extracted folder name differs).
4. Add a student, search by name/email/course, filter by course, edit a record, and delete a test record after confirming the prompt.
5. Student accounts cannot access this page; it requires the admin role.

### Notes
- This module uses the existing `students` table from `database/studenthub_schema.sql`. Course options come from the `courses` table.
- Use test data when checking delete functionality. Keep administrator access limited to trusted accounts.


## Practical 12 — Event Management CRUD with Poster Upload

### Features
- Admin-only event management page: `Pages/events_manage.php`.
- Create, view, update, and delete events using MySQLi prepared statements.
- Search by title, description, or category; filter by event status.
- Event status options: upcoming, completed, and cancelled.
- Optional poster upload with server-side MIME/image validation; accepted formats are JPG/JPEG, PNG, and WEBP, maximum size 2 MB. Files receive random names and are not stored in the database; only the generated filename is stored.
- Existing poster is retained if no replacement is uploaded. Replaced/deleted posters are removed from the upload folder.
- CSRF validation, escaped output, confirmation before deletion, and clear success/error messages.
- Admin dashboard now links directly to Event Management. The existing StudentHub theme is retained.

### Setup in XAMPP
1. Start Apache and MySQL.
2. If you already have the StudentHub database from Practical 9–11, open phpMyAdmin, select the `studenthub` database, and import `database/practical12_events_migration.sql`. For a fresh database, `database/studenthub_schema.sql` now also includes the events table.
3. Ensure the `pr-8/uploads/event-posters/` folder is writable by Apache. The project includes an `.htaccess` file to block script execution in the poster directory.
4. Sign in with an account whose `users.role` is `admin`.
5. Open `http://localhost/StudentHub/pr-8/Pages/events_manage.php` (adjust the URL if your folder name differs).
6. Test adding an event with a valid image, rejecting a non-image or file larger than 2 MB, editing/replacing a poster, searching/filtering, and deleting a test event.

**Note:** The `events.html` page remains the existing Practical 8 public-facing event page; the new admin CRUD module is `events_manage.php`.


## Practical 13 — PHP JSON API and Fetch-Based CRUD

### Features
- JSON API endpoint: `api/events.php` supports `GET` to list/search/filter events and `POST` JSON actions `create`, `update`, and `delete`.
- Fetch-powered admin UI: `Pages/events_api_crud.php` performs list, add, update, delete, search, and status filtering without a full page reload.
- JSON responses include `success`, `message`, and `data`/`id` where relevant. HTTP status codes distinguish validation, authorization, and server errors.
- Admin-only access, secure session checks, CSRF header validation, MySQLi prepared statements, server-side input validation, and safe DOM text rendering.
- Loading states, success/error feedback, and a confirmation prompt before deletion.
- The existing StudentHub theme and single `pr-8` project folder are retained. Practical 12's poster-upload CRUD remains available at `Pages/events_manage.php`; the Fetch-based module does not upload posters.

### Setup and testing in XAMPP
1. Use the same `studenthub` database from Practical 12. If it is a fresh setup, import `database/studenthub_schema.sql`; if using the earlier project database, make sure `events` exists by importing `database/practical12_events_migration.sql` once.
2. Start Apache and MySQL, then sign in with an account whose `users.role` is `admin`.
3. Open `http://localhost/StudentHub/pr-8/Pages/events_api_crud.php`. There is also a link to this page from the admin dashboard.
4. Test loading records, searching by title/description/category, filtering by status, creating an event, editing it, and deleting a test event after confirming. Check the browser Network tab to see JSON responses from `api/events.php`.
5. Requests must come from the authenticated admin page and include its `X-CSRF-Token` header. Direct API calls without a session or valid token are rejected.
