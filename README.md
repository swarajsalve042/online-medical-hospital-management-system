# Online Medical & Hospital Management System

A PHP and MySQL web application demonstrating routine hospital administration workflows: patient registration, doctor directory, appointments, medical notes, invoices, payments, and summary reporting.

> **Portfolio/demo project.** This is a learning application, not a certified Electronic Health Record (EHR), medical device, accounting system, or production-ready hospital platform. Use fictional data only. Do not enter real patient information.

## Features
- Session-based staff login and logout.
- Dashboard with patient, doctor, appointment, and billing summaries.
- Patient registration and searchable patient list.
- Doctor directory.
- Appointment scheduling with status tracking.
- Medical description/visit notes linked to a patient and doctor.
- Invoice creation with line-item description, amount, and payment status.
- Basic administration reports.
- MySQL persistence using PDO prepared statements.
- CSRF protection on write forms, output escaping, and password hashing.

## Technology
PHP 8.1+, MySQL 8.0+/MariaDB, PDO, HTML5, CSS3.

## Run locally
1. Install PHP 8.1+ with PDO MySQL and MySQL/MariaDB.
2. Create a database and import `database/schema.sql`.
3. Copy `config/database.example.php` to `config/database.php` and set local credentials.
4. From the project root, run `php -S 127.0.0.1:8000 -t public`.
5. Visit `http://127.0.0.1:8000/setup.php` once to create a demo staff account. Delete or disable `public/setup.php` after setup.
6. Sign in using the credentials displayed by the setup page and change the password immediately.

## Project structure
- `public/` — web entry points and pages.
- `app/` — shared authentication, security, and layout helpers.
- `config/` — local database configuration template.
- `database/` — schema and demo seed data.
- `docs/` — architecture and operational notes.
- `tests/` — manual verification checklist.

## Security notes
- Never commit `config/database.php`, real credentials, or real patient data.
- Use HTTPS, strong unique passwords, least-privilege database credentials, backups, access logging, and a security review before any real deployment.
- The included demo setup account is for local testing only.
- This project does not claim compliance with any healthcare privacy regulation.

## Verification status
Source files and schema are provided for local setup. Runtime behavior must be verified in a PHP/MySQL environment; no live deployment or database execution is claimed by this repository initialization.
