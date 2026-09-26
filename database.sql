CREATE DATABASE IF NOT EXISTS routinefy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE routinefy;

CREATE TABLE users(
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('user','admin') DEFAULT 'user',
 status ENUM('active','inactive') DEFAULT 'active',
 reset_otp VARCHAR(10) NULL,
 reset_expires DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages(
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL,
 message TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE habits(
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 name VARCHAR(150) NOT NULL,
 goal_days INT DEFAULT 30,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE habit_logs(
 id INT AUTO_INCREMENT PRIMARY KEY,
 habit_id INT NOT NULL,
 date DATE NOT NULL,
 completed TINYINT(1) DEFAULT 0,
 UNIQUE KEY uq_habit_date(habit_id,date),
 FOREIGN KEY(habit_id) REFERENCES habits(id) ON DELETE CASCADE
);

CREATE TABLE settings(
 setting_key VARCHAR(100) PRIMARY KEY,
 setting_value VARCHAR(255) NOT NULL
);

INSERT INTO users(full_name,email,password,role,status)
VALUES('Administrator','admin@routinefy.com','$2y$12$/XXl/ECZyEiTCOHYMDmbB.eDdUyWi4oSapFRzUusT3/af/AvGB.3m','admin','active');

INSERT INTO settings(setting_key,setting_value) VALUES
('app_name','Routinefy Habit Tracker'),
('registration_status','open'),
('admin_key_hash','$2y$12$qB3MTVK3wrDburchY97UQOBugMd6hbHPvYUrTWciFCOn0Xy1fYQ/.')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
