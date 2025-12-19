# ConsultEASE — Final Project (Web Technologies)

ConsultEASE is a consultancy marketplace that connects businesses (clients) with vetted professionals (consultants). It is implemented with a 3-tier / MVC-like architecture using PHP (PDO), MySQL/MariaDB, server-side sessions, and a Bootstrap + JavaScript frontend.

This README documents how to run, test, and extend the project, and describes key components and security considerations.

---

## Quick Features (user-visible)
- Role-based access control: Client, Consultant, Admin
- Secure authentication and session management
- Client features: browse consultants, book appointments, messages, booking history, profile, support with ai integration chatbox
- Consultant features: availability management, accept/reject appointments, messages, profile
- Admin features: manage users (suspend/activate), view appointments history, audit log of admin actions
- Messaging system for client/consultant communication (AJAX-powered)
- Audit log for admin actions written to `admin_audit` table

---

## Architecture / File map (3-tier / MVC-style)
- `public/`: public-facing controllers and front-end pages (views and API endpoints)
	- `public/index.php`, `public/login.php`, `public/register.php` — public pages
	- `public/client/`, `public/consultant/`, `public/admin/` — role-specific pages
	- `public/api/*.php` — AJAX API endpoints consumed by frontend (appointments, auth, messages, consultants, admin, etc.)
- `app/controllers/`: server-side controllers that implement request handling and business logic
	- `AuthController.php`, `AppointmentController.php`, `AdminController.php`, `MessageController.php`, `ConsultantController.php`, `ClientController.php`
- `app/utils/`: helpers and utilities
	- `validators.php` — server-side validation
	- `audit.php` — audit log helper (`write_audit`, `fetch_audit`)
	- `helpers.php`, `security.php`, `session.php`
- `app/config/`: configuration (database connection) `database.php`
- `public/assets/`: CSS, JS, images
- `database/`: `schema.sql`, `sample_data.sql`
- `tests/`: PHPUnit tests (examples)

---

## API endpoints (examples)
- `public/api/auth.php` — login, register, logout, update_profile
- `public/api/appointments.php` — book, list, accept/reject, mark completed
- `public/api/messages.php` — inbox, thread, send
- `public/api/consultants.php` — list consultants, details
- `public/api/admin.php` — admin actions (list_users, get_user, update_user_status, approve_consultant)

Use these endpoints from the front-end via `fetch()` or jQuery `$.getJSON`/`$.post` calls. They return JSON for AJAX consumers.

---

## Setup (Local / XAMPP on macOS)
1. Install XAMPP and start Apache + MySQL.
2. Place the project folder under XAMPP's htdocs (e.g. `/Applications/XAMPP/xamppfiles/htdocs/FinalProject_WebTech_maame.kome-mensah`).
3. Create a database (example name `consultease_db`).
4. Import schema and sample data:

```bash
# from project root
# create DB (mysql client)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS consultease_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# import schema and sample data
mysql -u root -p consultease_db < database/schema.sql
mysql -u root -p consultease_db < database/sample_data.sql
```

5. Edit database connection config in `app/config/database.php` to match your MySQL user/password and DB name.
6. Ensure file permissions allow Apache/PHP to read the files.
7. Visit `http://localhost/FinalProject_WebTech_maame.kome-mensah/public/` in your browser.

Notes:
- The project expects the `admin_audit` table for audit logs. The `database/schema.sql` includes this table; if you previously created the DB without it, re-import the schema or run the `CREATE TABLE` statement below.

Admin audit table (if needed):
```sql
CREATE TABLE IF NOT EXISTS admin_audit (
	audit_id INT AUTO_INCREMENT PRIMARY KEY,
	admin_user_id INT NOT NULL,
	action VARCHAR(100) NOT NULL,
	target_type VARCHAR(50) DEFAULT NULL,
	target_id INT DEFAULT NULL,
	details TEXT DEFAULT NULL,
	ip_address VARCHAR(45) DEFAULT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (admin_user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Running Tests
- Install dev dependencies (PHPUnit):

```bash
composer install --dev
composer require --dev phpunit/phpunit
```

- Run PHPUnit tests:

```bash
./vendor/bin/phpunit --configuration phpunit.xml tests
```

Note: tests may assume a test database. Prefer creating a separate test DB or mock DB to avoid polluting production/sample data.

---

## How to test the audit log manually
1. Log in as an admin.
2. Perform an admin action that creates an audit row (e.g. suspend/activate a user via Admin → Manage Users, or approve a consultant).
3. Inspect `admin_audit` table:

```sql
SELECT * FROM admin_audit ORDER BY created_at DESC LIMIT 10;
```

4. Visit `public/admin/audit.php` to view the UI (client-side search and show/hide details available).

Automated test suggestion: add a PHPUnit test that calls `write_audit()` and asserts the DB row was created (see tests/ directory for example tests).

---

## Validation and Security
- Client-side validation: `public/assets/js/validation.js` (email, name, phone, password checks using regular expressions). This improves UX but is not authoritative.
- Server-side validation: `app/utils/validators.php` uses `preg_match()` and `filter_var()` for the same checks — server must enforce these rules.
- Database access: uses PDO with prepared statements across controllers to avoid SQL injection.
- Sessions: secure_session_start() helper is used; suspended users are prevented from logging in (status checks).
- Audit logging: admin actions call `write_audit()` in `app/utils/audit.php`.

Security recommendations (not fully implemented yet):
- Add CSRF tokens for admin POST endpoints and important form submissions.
- Add rate-limiting on authentication endpoints.
- Consider logging failed logins to the audit table or a separate security log.

---

## Developer Notes / Where to change things
- Add new API endpoints under `public/api/` and corresponding controller logic in `app/controllers/`.
- Reuse helpers in `app/utils/` for common tasks (validation, audit, security).
- Frontend templates/pages live under `public/client/`, `public/consultant/`, and `public/admin/`.

Useful files:
- `app/config/database.php` — DB connection (PDO)
- `app/utils/validators.php` — server-side validation rules
- `app/utils/audit.php` — audit helper
- `app/middleware/auth_middleware.php` — authentication guard

---

## Troubleshooting
- Fatal PDO errors about `admin_audit` table? Either run the `CREATE TABLE` SQL above or re-import `database/schema.sql`.
- If pages return JSON errors in browser, check the Apache/PHP error log and `error_log()` entries in PHP (audit helper logs failures there).

---

## Future Improvements
- CSRF protection on POST routes
- Server-side pagination and filtering for admin audit view
- Export audit logs (CSV) and immutable/append-only audit storage
- Expand audit coverage (auth events, failed logins)
- Add PHPUnit tests for controllers and utilities

---

## Contact / Author
- Maame Kome-Mensah

PLEASE CHECK MY DOCUMENT IN THE CANVAS SUBMISISON FOR MORE DOCUMENTATION AND DIAGRAMS 
---

