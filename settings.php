<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routinefy - System Settings</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        
        .sidebar { width: 250px; background-color: #1e272e; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar h2 { font-size: 22px; margin-bottom: 30px; color: #ff4757; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 15px; }
        .sidebar ul li a { color: #dcdde1; text-decoration: none; font-size: 16px; display: block; padding: 10px; border-radius: 5px; }
        .sidebar ul li a.active, .sidebar ul li a:hover { background-color: #ff4757; color: #fff; }
        .logout-btn { background-color: #eb4d4b; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; }

        .main-content { flex: 1; padding: 30px; }
        .settings-card { background: #fff; padding: 25px; border-radius: 10px; max-width: 600px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }

        .btn-save { background-color: #ff4757; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>Routinefy Admin</h2>
            <ul>
                <li><a href="admin-dashboard.php">📊 Dashboard</a></li>
                <li><a href="manage-users.php">👥 Manage Users</a></li>
                <li><a href="manage-habits.php">🎯 Manage Habits</a></li>
                <li><a href="settings.php" class="active">⚙️ System Settings</a></li>
            </ul>
        </div>
        <a href="logout.php" class="logout-btn">Log Out</a>
    </div>

    <div class="main-content">
        <h1 style="margin-bottom: 25px;">System Settings</h1>

        <div class="settings-card">
            <form id="settingsForm">
                <div class="form-group">
                    <label>App Name</label>
                    <input type="text" id="appName" value="Routinefy Habit Tracker" required>
                </div>

                <div class="form-group">
                    <label>Admin Security PIN Change</label>
                    <input type="password" id="securityPin" placeholder="Optional — demo security key is configured in the server">
                </div>

                <div class="form-group">
                    <label>User Registration Status</label>
                    <select id="registrationStatus">
                        <option value="open">Open (Anyone can register)</option>
                        <option value="closed">Closed (Maintenance Mode)</option>
                    </select>
                </div>

                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>
    </div>

<script src="js/api.js"></script>
<script>
async function initSettings(){
    const user=await window.api.requireAdminPage(); if(!user)return;
    const res=await window.api.fetchWithAuth('/admin/settings');
    if(res.ok){const d=await res.json();document.getElementById('appName').value=d.app_name;document.getElementById('registrationStatus').value=d.registration_status;}
}
document.getElementById('settingsForm').addEventListener('submit',async e=>{
    e.preventDefault();
    const res=await window.api.fetchWithAuth('/admin/settings',{method:'PUT',body:JSON.stringify({
        app_name:document.getElementById('appName').value.trim(),
        registration_status:document.getElementById('registrationStatus').value
    })});
    const d=await res.json(); alert(d.message||d.error||'Done');
});
initSettings();
</script>
</body>
</html>