const express = require('express');
const cors = require('cors');
const path = require('path');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('./database');

const app = express();
const PORT = process.env.PORT || 3000;
const JWT_SECRET = 'routinefy_super_secret_key_123';

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static frontend files
app.use(express.static(__dirname));

// Middleware to verify JWT token
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (!token) return res.status(401).json({ error: 'Access denied' });

    jwt.verify(token, JWT_SECRET, (err, user) => {
        if (err) return res.status(403).json({ error: 'Invalid token' });
        req.user = user;
        next();
    });
};

const isAdmin = (req, res, next) => {
    if (req.user.role !== 'admin') {
        return res.status(403).json({ error: 'Requires admin privileges' });
    }
    next();
};

// --- AUTHENTICATION APIs ---

app.post('/api/auth/register', async (req, res) => {
    const { fullname, email, password } = req.body;
    if (!fullname || !email || !password) return res.status(400).json({ error: 'All fields required' });

    try {
        const salt = await bcrypt.genSalt(10);
        const hash = await bcrypt.hash(password, salt);

        db.run(`INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)`, [fullname, email, hash], function(err) {
            if (err) {
                if (err.message.includes('UNIQUE constraint failed')) {
                    return res.status(400).json({ error: 'Email already exists' });
                }
                return res.status(500).json({ error: err.message });
            }
            res.status(201).json({ message: 'User registered successfully', id: this.lastID });
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/auth/login', (req, res) => {
    const { email, password, isAdminLogin, adminKey } = req.body;

    db.get(`SELECT * FROM users WHERE email = ?`, [email], async (err, user) => {
        if (err) return res.status(500).json({ error: err.message });
        if (!user) return res.status(401).json({ error: 'Invalid email or password' });
        if (user.status === 'blocked') return res.status(403).json({ error: 'Your account has been blocked' });

        if (isAdminLogin) {
            if (user.role !== 'admin') return res.status(401).json({ error: 'Not an admin account' });
            // For demo purposes, any key can be allowed, or check specifically
            if (adminKey !== 'admin') return res.status(401).json({ error: 'Invalid Security Key' });
        } else {
            if (user.role === 'admin') return res.status(401).json({ error: 'Use admin login' });
        }

        const validPassword = await bcrypt.compare(password, user.password);
        if (!validPassword) return res.status(401).json({ error: 'Invalid email or password' });

        const token = jwt.sign({ id: user.id, email: user.email, role: user.role, name: user.full_name }, JWT_SECRET, { expiresIn: '24h' });
        res.json({ message: 'Login successful', token, user: { id: user.id, name: user.full_name, email: user.email, role: user.role } });
    });
});

// --- USER APIs ---

// Get all habits for logged in user
app.get('/api/habits', authenticateToken, (req, res) => {
    db.all(`SELECT h.*, 
            (SELECT COUNT(*) FROM habit_logs hl WHERE hl.habit_id = h.id) as streak,
            (SELECT COUNT(*) FROM habit_logs hl WHERE hl.habit_id = h.id AND hl.date = CURRENT_DATE) as done_today
            FROM habits h WHERE h.user_id = ? ORDER BY h.id DESC`, [req.user.id], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

// Add new habit
app.post('/api/habits', authenticateToken, (req, res) => {
    const { name, goal_days } = req.body;
    if (!name || !goal_days) return res.status(400).json({ error: 'Name and goal are required' });

    db.run(`INSERT INTO habits (user_id, name, goal_days) VALUES (?, ?, ?)`, [req.user.id, name, goal_days], function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ id: this.lastID, name, goal_days, streak: 0, done_today: 0 });
    });
});

// Delete habit
app.delete('/api/habits/:id', authenticateToken, (req, res) => {
    db.run(`DELETE FROM habits WHERE id = ? AND user_id = ?`, [req.params.id, req.user.id], function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Habit deleted' });
    });
});

// Toggle habit completion for today
app.post('/api/habits/:id/toggle', authenticateToken, (req, res) => {
    const habitId = req.params.id;
    
    // Check if already done today
    db.get(`SELECT * FROM habit_logs WHERE habit_id = ? AND date = CURRENT_DATE`, [habitId], (err, row) => {
        if (err) return res.status(500).json({ error: err.message });
        
        if (row) {
            // Already done, unmark it
            db.run(`DELETE FROM habit_logs WHERE id = ?`, [row.id], (err) => {
                if (err) return res.status(500).json({ error: err.message });
                res.json({ message: 'Habit unmarked for today', done_today: false });
            });
        } else {
            // Not done, mark it
            db.run(`INSERT INTO habit_logs (habit_id) VALUES (?)`, [habitId], (err) => {
                if (err) return res.status(500).json({ error: err.message });
                res.json({ message: 'Habit marked done for today', done_today: true });
            });
        }
    });
});

// Analytics Stats
app.get('/api/analytics', authenticateToken, (req, res) => {
    // Get stats: total active habits, highest streak
    db.get(`SELECT COUNT(*) as total_habits FROM habits WHERE user_id = ?`, [req.user.id], (err, habitStat) => {
        if (err) return res.status(500).json({ error: err.message });
        
        db.get(`SELECT MAX(streak) as max_streak FROM (SELECT COUNT(*) as streak FROM habit_logs hl JOIN habits h ON hl.habit_id = h.id WHERE h.user_id = ? GROUP BY h.id)`, [req.user.id], (err, streakStat) => {
             res.json({
                 total_habits: habitStat.total_habits || 0,
                 highest_streak: streakStat ? streakStat.max_streak || 0 : 0,
                 completion_rate: 80 // dummy logic for frontend
             });
        });
    });
});

// --- ADMIN APIs ---

app.get('/api/admin/users', authenticateToken, isAdmin, (req, res) => {
    db.all(`SELECT id, full_name, email, role, status, joined_date FROM users WHERE role = 'user' ORDER BY id DESC`, [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

app.delete('/api/admin/users/:id', authenticateToken, isAdmin, (req, res) => {
    db.run(`DELETE FROM users WHERE id = ?`, [req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'User deleted' });
    });
});

app.put('/api/admin/users/:id/status', authenticateToken, isAdmin, (req, res) => {
    const { status } = req.body;
    db.run(`UPDATE users SET status = ? WHERE id = ?`, [status, req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'User status updated' });
    });
});

app.put('/api/admin/users/:id', authenticateToken, isAdmin, (req, res) => {
    const { full_name, email } = req.body;
    db.run(`UPDATE users SET full_name = ?, email = ? WHERE id = ?`, [full_name, email, req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'User updated' });
    });
});

app.get('/api/admin/habits', authenticateToken, isAdmin, (req, res) => {
    db.all(`SELECT h.id, h.name, u.full_name as created_by, h.goal_days as category, 
            (SELECT COUNT(*) FROM habit_logs hl WHERE hl.habit_id = h.id) as streak 
            FROM habits h JOIN users u ON h.user_id = u.id ORDER BY h.id DESC`, [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

app.delete('/api/admin/habits/:id', authenticateToken, isAdmin, (req, res) => {
    db.run(`DELETE FROM habits WHERE id = ?`, [req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Habit deleted' });
    });
});

app.get('/api/admin/stats', authenticateToken, isAdmin, (req, res) => {
    db.get(`SELECT COUNT(*) as users FROM users WHERE role = 'user'`, (err, uStat) => {
        db.get(`SELECT COUNT(*) as habits FROM habits`, (err, hStat) => {
            db.get(`SELECT COUNT(*) as completions FROM habit_logs WHERE date = CURRENT_DATE`, (err, cStat) => {
                res.json({
                    total_users: uStat.users,
                    active_habits: hStat.habits,
                    daily_completions: cStat.completions,
                    system_status: 'Operational'
                });
            });
        });
    });
});


app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
