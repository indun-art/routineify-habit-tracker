# Routinefy – PHP & MySQL Habit Tracker

Routinefy is a PHP + MySQL habit-tracking mini project with user authentication, habit categories, editing, reminders, completion tracking, analytics and an admin area.

## Requirements
- Apache (XAMPP/WAMP/LAMP)
- PHP 8.0+
- MySQL/MariaDB
- Browser with JavaScript enabled

## Local setup
1. Copy `Routinefy_PHP_MySQL_Final` into your Apache `htdocs` folder.
2. Create/import the database by opening `database.sql` in phpMyAdmin.
3. Check `includes/db.php` and change the MySQL host/database/user/password if your environment is different.
4. Open:
   `http://localhost/Routinefy_PHP_MySQL_Final/`
5. Register a normal account or use the demo admin account below.

## Demo admin
- Email: `admin@routinefy.com`
- Password: `admin123`
- Admin security key: `admin`

## Main features
- Secure PHP session login/logout with role protection.
- User registration and forgot-password OTP demo flow.
- Add, edit, complete and delete habits.
- Habit goals from 1–3650 days.
- Built-in categories plus user-created categories.
- Optional daily browser reminders with a selected time.
- Dashboard shows category, goal, streak and reminder information.
- Analytics uses real database data for the last 7 and 30 days and category distribution.
- Admin dashboard, user management, habit management and system settings.
- Contact messages are stored in MySQL and displayed to admins.
- Admin habit deletion and user-management endpoints are validated server-side.

## Database
`database.sql` is a clean final schema. It creates the `routinefy` database, all required tables, default categories, settings and the demo administrator.

If an older Routinefy database is already installed, back it up first and import the final SQL into a fresh `routinefy` database for the cleanest test.

## Reminder note
Browser reminders require notification permission. They run while the dashboard page is open; this is intentionally a lightweight PHP/JavaScript mini-project implementation, not a background push-notification service.

## Project cleanup
This final package contains only the PHP/MySQL application files and required assets. Old Node/HTML prototype files are not included.
