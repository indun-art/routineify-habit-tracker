# Routinefy - PHP & MySQL Habit Tracker

## Setup
1. Create/import `database.sql` in MySQL/phpMyAdmin.
2. Edit `includes/db.php` with your MySQL host, database, username and password.
3. Put the project inside your Apache `htdocs` folder.
4. Open `http://localhost/Routinefy_PHP_MySQL_Final/`.

## Demo admin
- Email: `admin@routinefy.com`
- Password: `admin123`
- Admin security key: `admin` (stored as a password hash in the database seed).

## Important
If you already imported an older Routinefy database, the new `database.sql` adds reset/settings columns and tables. For a clean demo, drop the old `routinefy` database and import the new SQL.

## Fixed in this version
- Real login/session verification and role-based page protection.
- Normal users cannot enter admin pages; admins are routed to the admin dashboard.
- Admin user creation with role selection.
- Admin user edit now includes role.
- Search works in user management.
- System Settings now saves/loads.
- Forgot-password flow now validates an OTP and updates the password.
- Analytics charts now use real database data instead of hard-coded demo numbers.
- Highest streak is calculated as a consecutive streak.
- Habit goal is a numeric field and is validated server-side.
- Admin security key is stored as a hash rather than plaintext.
- PHP syntax checked across all PHP files.
