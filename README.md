# 🌱 Routinefy — Habit Tracker

> Build better habits. Stay consistent. Become better every day.

Routinefy is a web-based Habit Tracking System designed to help users build healthy routines and maintain consistency in their daily lives.

The application allows users to create personal habits, track their daily progress, monitor streaks, and understand their performance through simple visual analytics.

Routinefy also includes an Admin Dashboard that provides administrators with tools to manage users, habits, and system information.

---

 ✨ What is Routinefy?

Building a habit is easy.

Staying consistent is the difficult part.

Routinefy is designed to make that process simpler by giving users one place to create their routines, record their progress, and see how consistently they are following their habits.

Instead of relying on notes or remembering everything manually, users can use Routinefy to keep track of their everyday habits and progress.

---

 🚀 Features

# 👤 For Users

Account Management

* Create a new account
* Secure login and logout
* Password-protected user accounts
* Password recovery and verification

Habit Management

* Create personal habits
* View active habits
* Mark habits as completed
* Manage existing habits
* Delete habits when they are no longer needed

Progress Tracking

* Track completed habits
* Monitor habit streaks
* View habit completion progress
* Analyze personal performance

Communication

* Contact the system through the contact form

---

# 🛠️ For Administrators

Routinefy provides a dedicated administration area for managing the application.

Administrators can:

* View registered users
* Manage user accounts
* Block or unblock users
* Manage habits
* View system information
* Manage application settings
* Monitor overall system activity

---

 📊 Habit Progress & Analytics

Routinefy transforms habit activity into useful progress information.

Users can view their habit performance through the analytics section, making it easier to understand:

* Habit completion
* Daily progress
* Consistency
* Streaks
* Overall habit activity

This helps users recognize their progress and stay motivated to maintain their routines.

---

 🔐 Authentication & Security

Routinefy uses a secure authentication system to protect user accounts.

The application includes:

* Password hashing
* Session-based authentication
* User and administrator roles
* Protected pages
* Server-side validation
* Prepared SQL statements

Passwords are not stored as plain text in the database.

---

 🧩 Technology Stack

Routinefy is developed using the technologies required for the ICT 1209 – Web Technologies mini project.

| Technology       | Used For                  |
| ---------------- | ------------------------- |
| HTML5        | Page structure            |
| CSS3         | Styling and layouts       |
| Bootstrap 5  | Responsive user interface |
| JavaScript   | Interactive functionality |
| PHP 8        | Backend development       |
| MySQL        | Database management       |
| Git & GitHub | Version control           |

---

 🗂️ Project Structure

```text
Routinefy/
│
├── index.php
├── signup.php
├── dashboard.php
├── analytics.php
├── contact.php
│
├── admin-dashboard.php
├── manage-users.php
├── manage-habits.php
├── settings.php
│
├── api.php
├── database.sql
├── README.md
│
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── includes/
│   ├── db.php
│   └── functions.php
│
├── css/
├── js/
└── images/
```

---

 🗄️ Database

Routinefy uses MySQL to store and manage application data.

The project includes a `database.sql` file containing the database structure required by the application.

The database is designed to support user accounts, habits, habit activity, and other system information.

---

 ⚙️ How It Works

The basic user journey is simple:

```text
Create Account
      ↓
     Login
      ↓
Dashboard
      ↓
Create Habits
      ↓
Complete Habits
      ↓
Track Progress
      ↓
View Analytics
```

Administrators have a separate dashboard for managing the system.

```text
Admin Login
     ↓
Admin Dashboard
     ↓
Manage Users
     ↓
Manage Habits
     ↓
System Management
```

---

 💻 Installation

# 1. Run XAMPP

Start:

```text
Apache
MySQL
```

# 2. Add the Project

Copy the Routinefy project folder into:

```text
C:\xampp\htdocs\
```

# 3. Create the Database

Open phpMyAdmin and create the project database.

Import:

```text
database.sql
```

# 4. Configure the Connection

Open:

```text
includes/db.php
```

and configure the MySQL connection according to your local XAMPP setup.

# 5. Launch Routinefy

Open the project using:

```text
http://localhost/Routinefy/
```

---

 🎨 User Experience

Routinefy focuses on keeping the interface simple and easy to understand.

The system uses a responsive layout so that users can access their habits and progress from different screen sizes, including desktop, tablet, and mobile devices.

Bootstrap 5 is used together with custom CSS to create the application's interface and responsive components.

---

 🎓 Academic Project

Routinefy – Habit Tracker was developed as an Interactive Web Application Development project for:

ICT 1209 – Web Technologies
Department of ICT
Rajarata University of Sri Lanka

The project demonstrates the practical use of frontend development, JavaScript interaction, PHP backend programming, MySQL database management, authentication, and GitHub version control.

---

 👥 Development Team

Project: Routinefy – Habit Tracker

Developed by:

* Indunil Udith
* Induwara Akash

---

# 🌱 Routinefy

Create habits. Track progress. Build consistency.
