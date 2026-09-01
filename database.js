const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const bcrypt = require('bcryptjs');

const dbPath = path.resolve(__dirname, 'database.sqlite');

const db = new sqlite3.Database(dbPath, (err) => {
    if (err) {
        console.error('Error opening database', err.message);
    } else {
        console.log('Connected to the SQLite database.');
        
        // Create tables
        db.serialize(() => {
            db.run(`CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'user',
                status TEXT DEFAULT 'active',
                joined_date TEXT DEFAULT CURRENT_DATE
            )`);

            db.run(`CREATE TABLE IF NOT EXISTS habits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                goal_days TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )`);

            db.run(`CREATE TABLE IF NOT EXISTS habit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                habit_id INTEGER NOT NULL,
                date TEXT DEFAULT CURRENT_DATE,
                completed INTEGER DEFAULT 1,
                FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE
            )`);

            // Check if admin exists, if not create default admin
            db.get("SELECT * FROM users WHERE role = 'admin'", async (err, row) => {
                if (!row) {
                    const salt = await bcrypt.genSalt(10);
                    const hash = await bcrypt.hash("admin123", salt);
                    // Default admin: admin@routinefy.com / admin123
                    db.run("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)", 
                        ['System Admin', 'admin@routinefy.com', hash, 'admin']);
                    console.log('Default admin created: admin@routinefy.com / admin123');
                }
            });
        });
    }
});

module.exports = db;
