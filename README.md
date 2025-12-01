# FinalProject_WebTech_maame.kome-mensah
Final Project for Web Technologies
# ConsultEASE

ConsultEASE is a secure consultancy booking platform, built with PHP, MySQL, Bootstrap, and JavaScript.

## Main Features
- Role-based access (Client, Consultant, Admin)
- Secure authentication (password_hash, sessions, prepared statements)
- Book and manage appointments
- Business problem & feedback submission
- Admin: manage users, consultants, expertise

## Getting Started

### Local (XAMPP)
1. Clone the repository.
2. Import `/database/schema.sql` and `/database/sample_data.sql` into `consultease_db`.
3. Open XAMPP, ensure Apache and MySQL are running.
4. Open `http://localhost/ConsultEASE/public/`.

### Live Server
See `/docs/setup-instructions.md`.

## Security
- All sensitive operations require login and proper role.
- OWASP: XSS/CSRF/input validation handled both client and server sides.
- Never expose database errors to users; logs on live.

## Run Tests
composer install
vendor/bin/phpunit tests/## Folder Structure
See `/docs/` for ERD, UML, Success Criteria.

## Authors
- Maame Kome-Mensah