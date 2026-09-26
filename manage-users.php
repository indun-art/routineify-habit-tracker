<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routinefy - Manage Users</title>
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
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        
        .search-bar { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        
        .table-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #dcdde1; }
        th { background-color: #f8f9fa; color: #718093; }

        .btn-action { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; margin-right: 5px; }
        .btn-edit { background-color: #3742fa; color: #fff; }
        .btn-edit:hover { background-color: #2f3542; }
        .btn-delete { background-color: #ff4757; color: #fff; }
        .btn-delete:hover { background-color: #e03847; }

        /* Edit Popup Modal Styling */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; top: 0; 
            width: 100%; height: 100%; 
            background-color: rgba(0,0,0,0.5); 
            justify-content: center; 
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .modal-content h2 { margin-bottom: 15px; color: #2f3640; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }

        .modal-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-save { background-color: #2ed573; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-cancel { background-color: #747d8c; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>Routinefy Admin</h2>
            <ul>
                <li><a href="admin-dashboard.php">📊 Dashboard</a></li>
                <li><a href="manage-users.php" class="active">👥 Manage Users</a></li>
                <li><a href="manage-habits.php">🎯 Manage Habits</a></li>
                <li><a href="settings.php">⚙️ System Settings</a></li>
            </ul>
        </div>
        <a href="#" class="logout-btn" onclick="window.api.logout(); return false;">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <div><h1>User Management</h1><small class="text-muted">Create, edit, block and delete accounts</small></div>
            <div><button class="btn-action btn-edit" onclick="openCreateModal()">+ Add Account</button>
            <input type="text" class="search-bar" placeholder="Search user by name or email...">
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Users dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Popup Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2>Edit User Details</h2>
            <form id="editUserForm">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="editEmail" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="editRole" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px">
                        <option value="user">User</option><option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="button" class="btn-save" onclick="saveUserChanges()">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="createModal">
        <div class="modal-content">
            <h2>Create New Account</h2>
            <form id="createUserForm">
                <div class="form-group"><label>Full Name</label><input id="createName" required></div>
                <div class="form-group"><label>Email</label><input id="createEmail" type="email" required></div>
                <div class="form-group"><label>Temporary Password</label><input id="createPassword" type="password" minlength="6" required></div>
                <div class="form-group"><label>Role</label><select id="createRole" style="width:100%;padding:8px"><option value="user">User</option><option value="admin">Admin</option></select></div>
                <div class="modal-buttons"><button type="button" class="btn-cancel" onclick="closeCreateModal()">Cancel</button><button type="submit" class="btn-save">Create Account</button></div>
            </form>
        </div>
    </div>

    <script src="js/api.js"></script>
    <script>
        let user = null;
        async function initAdminPage(){ user = await window.api.requireAdminPage(); if(user) loadUsers(); }
        

        let currentRowId = null;

        async function loadUsers() {
            const res = await window.api.fetchWithAuth('/admin/users');
            if (res && res.ok) {
                const users = await res.json();
                const tbody = document.getElementById('userTableBody');
                tbody.innerHTML = '';
                users.forEach(u => {
                    const statusAction = u.status === 'active' ? 'Block' : 'Unblock';
                    const newStatus = u.status === 'active' ? 'blocked' : 'active';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>#USR-${u.id}</td>
                        <td class="user-name">${u.full_name}</td>
                        <td class="user-email">${u.email}</td>
                        <td>${u.role}</td>
                        <td>${u.status}</td>
                        <td>${u.joined_date}</td>
                        <td>
                            <button class="btn-action btn-edit" onclick="openEditModal(${u.id}, decodeURIComponent('${encodeURIComponent(u.full_name)}'), decodeURIComponent('${encodeURIComponent(u.email)}'), '${u.role}')">Edit</button>
                            <button class="btn-action" style="background-color: #f39c12; color: #fff;" onclick="toggleStatus(${u.id}, '${newStatus}')">${statusAction}</button>
                            <button class="btn-action btn-delete" onclick="deleteUser(${u.id})">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        async function toggleStatus(id, status) {
            if (confirm(`Are you sure you want to change user status to ${status}?`)) {
                const res = await window.api.fetchWithAuth(`/admin/users/${id}/status`, {
                    method: 'PUT',
                    body: JSON.stringify({ status })
                });
                if (res && res.ok) loadUsers();
            }
        }

        async function deleteUser(id) {
            if (confirm("Are you sure you want to delete this user?")) {
                const res = await window.api.fetchWithAuth(`/admin/users/${id}`, { method: 'DELETE' });
                if (res && res.ok) loadUsers();
            }
        }

        function openEditModal(id, name, email, role='user') {
            currentRowId = id;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value = role;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        async function saveUserChanges() {
            const newName = document.getElementById('editName').value;
            const newEmail = document.getElementById('editEmail').value;

            if (newName && newEmail && currentRowId) {
                const res = await window.api.fetchWithAuth(`/admin/users/${currentRowId}`, {
                    method: 'PUT',
                    body: JSON.stringify({ full_name: newName, email: newEmail, role: document.getElementById('editRole').value })
                });
                if (res && res.ok) {
                    closeModal();
                    loadUsers();
                } else {
                    alert("Update failed");
                }
            } else {
                alert("Please fill in all fields.");
            }
        }

        function openCreateModal(){document.getElementById('createModal').style.display='flex';}
        function closeCreateModal(){document.getElementById('createModal').style.display='none';}
        document.getElementById('createUserForm').addEventListener('submit', async e=>{
            e.preventDefault();
            const res=await window.api.fetchWithAuth('/admin/users',{method:'POST',body:JSON.stringify({
                full_name:document.getElementById('createName').value.trim(),
                email:document.getElementById('createEmail').value.trim(),
                password:document.getElementById('createPassword').value,
                role:document.getElementById('createRole').value
            })});
            const d=await res.json();
            if(res.ok){alert('Account created successfully');e.target.reset();closeCreateModal();loadUsers();}else alert(d.error||'Failed to create account');
        });
        document.querySelector('.search-bar').addEventListener('input', e=>{
            const q=e.target.value.toLowerCase();
            document.querySelectorAll('#userTableBody tr').forEach(r=>r.style.display=r.innerText.toLowerCase().includes(q)?'':'none');
        });
        initAdminPage();
    </script>

</body>
</html>