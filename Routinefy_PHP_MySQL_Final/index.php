<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routinefy - Sign In</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Tabs Toggle Styles */
        .login-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            background: none;
            border: none;
            font-size: 15px;
            font-weight: bold;
            color: #718093;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            color: #ff4757;
            border-bottom-color: #ff4757;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Admin Specific Button Styling */
        .btn-admin-submit {
            background-color: #ff4757;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-admin-submit:hover {
            background-color: #e03847;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1 class="brand-logo">Routinefy</h1>
        
        <div class="card-outer">
            <div class="card-inner">

                
                <div class="login-tabs">
                    <button class="tab-btn active" onclick="switchTab('user')">User Login</button>
                    <button class="tab-btn" onclick="switchTab('admin')">🔑 Admin Login</button>
                </div>

                
                <div id="user-tab" class="tab-content active">
                    <form id="userLoginForm">
                        <div class="input-group">
                            <label for="user-email">Email</label>
                            <input type="email" id="user-email" placeholder="user@example.com" required>
                        </div>

                        <div class="input-group">
                            <label for="user-password">Password</label>
                            <input type="password" id="user-password" placeholder="••••••••" required>
                        </div>
                         
                        <button type="submit" class="btn-submit">Sign In</button>
                    </form>

                    <div class="links-container">
                        <a href="forgot-password.php" class="link-item">Forgot password?</a>
                        <a href="signup.php" class="link-item">Don't have an account? Sign Up</a>
                    </div>
                </div>

                <!-- 2. ADMIN LOGIN FORM -->
                <div id="admin-tab" class="tab-content">
                    <form id="adminLoginForm">
                        <div class="input-group">
                            <label for="admin-email">Admin Email / Username</label>
                            <input type="email" id="admin-email" placeholder="admin@routinefy.com" required>
                        </div>

                        <div class="input-group">
                            <label for="admin-password">Password</label>
                            <input type="password" id="admin-password" placeholder="••••••••" required>
                        </div>

                        <div class="input-group">
                            <label for="security-key">Security Key / PIN</label>
                            <input type="password" id="security-key" placeholder="Enter Admin Key" required>
                        </div>
                         
                        <button type="submit" class="btn-admin-submit">Sign In as Admin</button>
                    </form>

                    <div class="links-container" style="text-align: center; margin-top: 15px;">
                        <span style="font-size: 12px; color: #888;">Restricted Access for System Administrators Only</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    
    <script src="js/api.js"></script>
    <script>
        function switchTab(tabType) {
            const userTab = document.getElementById('user-tab');
            const adminTab = document.getElementById('admin-tab');
            const tabs = document.querySelectorAll('.tab-btn');
            const isUser = tabType === 'user';
            userTab.classList.toggle('active', isUser);
            adminTab.classList.toggle('active', !isUser);
            tabs[0].classList.toggle('active', isUser);
            tabs[1].classList.toggle('active', !isUser);
        }

        document.getElementById('userLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const r = await window.api.apiLogin(
                document.getElementById('user-email').value.trim(),
                document.getElementById('user-password').value,
                false
            );
            const data = await r.json();
            if (r.ok) location.href = data.user.role === 'admin' ? 'admin-dashboard.php' : 'dashboard.php';
            else alert(data.error || 'Login failed');
        });

        document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const r = await window.api.apiLogin(
                document.getElementById('admin-email').value.trim(),
                document.getElementById('admin-password').value,
                true,
                document.getElementById('security-key').value
            );
            const data = await r.json();
            if (r.ok) location.href = 'admin-dashboard.php';
            else alert(data.error || 'Admin login failed');
        });
    </script>
</body>
</html>
