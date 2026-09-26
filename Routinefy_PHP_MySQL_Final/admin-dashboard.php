<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routinefy - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Basic Dashboard Layout & Styling */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            background-color: #1e272e;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
            color: #ff4757;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: #dcdde1;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar ul li a.active, .sidebar ul li a:hover {
            background-color: #ff4757;
            color: #fff;
        }

        .logout-btn {
            background-color: #eb4d4b;
            color: white;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .card h3 {
            font-size: 14px;
            color: #718093;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
            color: #2f3640;
        }

        /* User Table */
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table-container h3 {
            margin-bottom: 15px;
            color: #2f3640;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #dcdde1;
        }

        th {
            background-color: #f8f9fa;
            color: #718093;
        }

        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .status.active {
            background-color: #e1b12c20;
            color: #44bd32;
        }

        .status.blocked {
            background-color: #e8411820;
            color: #c23616;
        }

        .action-btn {
            background: none;
            border: 1px solid #718093;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .action-btn:hover {
            background-color: #ff4757;
            color: #fff;
            border-color: #ff4757;
        }
    </style>
</head>
<body>

    <!-- Sidebar with Updated Navigation Links -->
    <div class="sidebar">
        <div>
            <h2>Routinefy Admin</h2>
            <ul>
                <li><a href="admin-dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="manage-users.php">👥 Manage Users</a></li>
                <li><a href="manage-habits.php">🎯 Manage Habits</a></li>
                <li><a href="settings.php">⚙️ System Settings</a></li>
            </ul>
        </div>
        <a href="#" class="logout-btn" onclick="window.api.logout(); return false;">Log Out</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Admin Overview</h1>
            <p>Welcome back, <strong>Admin</strong>!</p>
        </div>

        <div class="stats-grid">
            <div class="card">
                <h3>TOTAL USERS</h3>
                <p id="statTotalUsers">0</p>
            </div>
            <div class="card">
                <h3>ACTIVE HABITS</h3>
                <p id="statActiveHabits">0</p>
            </div>
            <div class="card">
                <h3>DAILY COMPLETIONS</h3>
                <p id="statDailyCompletions">0</p>
            </div>
            <div class="card">
                <h3>FEEDBACK & MESSAGES</h3>
                <p id="statMessages">0</p>
            </div>
            <div class="card">
                <h3>SYSTEM STATUS</h3>
                <p id="statSystemStatus" style="color: #44bd32; font-size: 20px;">● Loading...</p>
            </div>
        </div>

        <!-- User Feedback / Contact Messages -->
        <div class="table-container" style="margin-bottom:30px;">
            <h3>💬 User Feedback & Messages</h3>
            <p style="color:#718093;margin-bottom:15px;">Messages and suggestions submitted through the Contact & Feedback form.</p>
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Submitted</th></tr></thead>
                <tbody id="adminMessagesList"><tr><td colspan="4">Loading messages...</td></tr></tbody>
            </table>
        </div>

        <!-- User Management Table -->
        <div class="table-container">
            <h3>Recent Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="adminUsersList">
                    <!-- Users will be loaded here dynamically -->
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="js/api.js"></script>
    <script>
        let user = null;
        async function initAdminPage(){ user = await window.api.requireAdminPage(); if(!user) return; loadAdminStats?.(); loadRecentUsers?.(); loadAdminHabits?.(); loadAdminMessages?.(); }


        async function loadAdminStats() {
            const res = await window.api.fetchWithAuth('/admin/stats');
            if (res && res.ok) {
                const data = await res.json();
                document.getElementById('statTotalUsers').innerText = data.total_users;
                document.getElementById('statActiveHabits').innerText = data.active_habits;
                document.getElementById('statDailyCompletions').innerText = data.daily_completions;
                document.getElementById('statMessages').innerText = data.messages || 0;
                document.getElementById('statSystemStatus').innerText = "● " + data.system_status;
            }
        }

        async function loadAdminMessages() {
            const res = await window.api.fetchWithAuth('/admin/messages');
            const tbody = document.getElementById('adminMessagesList');
            if (!res || !res.ok) { tbody.innerHTML = '<tr><td colspan="4">Unable to load messages.</td></tr>'; return; }
            const messages = await res.json();
            tbody.innerHTML = '';
            if (!messages.length) { tbody.innerHTML = '<tr><td colspan="4">No feedback messages yet.</td></tr>'; return; }
            messages.forEach(m => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${escapeHtml(m.name)}</td><td>${escapeHtml(m.email)}</td><td style="max-width:420px;white-space:pre-wrap;word-break:break-word;">${escapeHtml(m.message)}</td><td>${escapeHtml(m.created_at)}</td>`;
                tbody.appendChild(tr);
            });
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
        }

        async function loadRecentUsers() {
            const res = await window.api.fetchWithAuth('/admin/users');
            if (res && res.ok) {
                const users = await res.json();
                const tbody = document.getElementById('adminUsersList');
                tbody.innerHTML = '';
                // Just show up to 5 recent users on dashboard
                users.slice(0, 5).forEach(u => {
                    const statusClass = u.status === 'active' ? 'status active' : 'status blocked';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>#USR-${u.id}</td>
                        <td>${u.full_name}</td>
                        <td>${u.email}</td>
                        <td><span class="${statusClass}">${u.status}</span></td>
                        <td><a href="manage-users.php"><button class="action-btn">Edit</button></a></td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        initAdminPage();
    </script>

</body>
</html>