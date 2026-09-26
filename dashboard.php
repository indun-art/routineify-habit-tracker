<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Routinefy - Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style2.css">
<style>
.habit-row{transition:.2s ease}.habit-row:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.06)}
.habit-meta{font-size:.85rem}.reminder-on{color:#7c3aed;font-weight:600}.empty-state{padding:45px 20px;text-align:center;background:#f8f9fa;border-radius:12px}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1050;align-items:center;justify-content:center;padding:15px}.modal-content{width:min(560px,100%);max-height:90vh;overflow:auto}
</style>
</head>
<body class="dashboard-body d-flex flex-column min-vh-100">
<header class="dashboard-header bg-white shadow-sm py-3 px-4 d-flex justify-content-between align-items-center">
<h1 class="logo m-0 fw-bold fs-3">Routinefy</h1>
<nav class="nav-links d-flex gap-3 align-items-center"><a href="#" class="btn btn-dark btn-sm rounded-pill px-3 active">Dashboard</a><a href="analytics.php" class="nav-link pt-1 text-dark">Analytics</a><a href="#" class="nav-link pt-1 text-dark" onclick="window.api.logout();return false;">Log out</a></nav>
</header>
<main class="dashboard-container container my-5 flex-grow-1">
<div class="welcome-row d-flex justify-content-between align-items-center mb-4 gap-3"><div><h2 class="fs-4 m-0" id="welcomeMsg">Welcome User 👋</h2><small class="text-muted">Build small routines and keep your streak alive.</small></div><button class="btn btn-dark btn-add">+ Add New Habit</button></div>
<div id="habitsList" class="d-flex flex-column gap-3"></div>
</main>

<div id="habitModal" class="modal-overlay"><div class="modal-content bg-white p-4 rounded shadow-lg"><div class="modal-header border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center"><h3 id="modalTitle" class="m-0 fs-5">Add New Habit</h3><button type="button" class="btn-close close-btn"></button></div>
<form id="habitForm"><input type="hidden" id="editingHabitId">
<div class="mb-3"><label for="habitName" class="form-label fw-semibold">Habit Name</label><input type="text" id="habitName" class="form-control" maxlength="150" placeholder="E.g., Read Books, Gym" required></div>
<div class="row g-3"><div class="col-md-6"><label for="habitDays" class="form-label fw-semibold">Goal (Days)</label><input type="number" id="habitDays" class="form-control" min="1" max="3650" value="30" required></div><div class="col-md-6"><label for="habitCategory" class="form-label fw-semibold">Category</label><div class="input-group"><select id="habitCategory" class="form-select"></select><button type="button" class="btn btn-outline-secondary" id="newCategoryBtn" title="Create category">+</button></div></div></div>
<div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" id="reminderEnabled"><label class="form-check-label fw-semibold" for="reminderEnabled">Enable daily reminder</label></div>
<div class="mt-2" id="reminderTimeWrap" style="display:none"><label for="reminderTime" class="form-label">Reminder time</label><input type="time" id="reminderTime" class="form-control"></div>
<div class="d-flex gap-2 mt-4"><button type="button" class="btn btn-light flex-fill close-btn">Cancel</button><button type="submit" class="btn btn-dark flex-fill" id="saveHabitBtn">Add Habit</button></div>
</form></div></div>

<footer class="bg-white text-center py-3 border-top mt-auto shadow-sm"><div class="container"><p class="m-0 text-muted small">&copy; 2026 Routinefy. Built for ICT 1209 Mini Project - Rajarata University of Sri Lanka.</p></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><script src="js/api.js"></script>
<script>
let user=null, habits=[], categories=[]; const modal=document.getElementById('habitModal');
const esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
async function initDashboard(){user=await window.api.requireUserPage();if(!user)return;document.getElementById('welcomeMsg').textContent=`Welcome ${user.full_name} 👋`;await loadCategories();await loadHabits();}
async function loadCategories(){const r=await window.api.fetchWithAuth('/categories');if(!r.ok)return;categories=await r.json();renderCategoryOptions();}
function renderCategoryOptions(selected=''){const s=document.getElementById('habitCategory');s.innerHTML=categories.map(c=>`<option value="${c.id}">${esc(c.name)}</option>`).join('');if(selected)s.value=String(selected);}
async function createCategory(){const name=prompt('New category name:');if(!name||!name.trim())return;const r=await window.api.fetchWithAuth('/categories',{method:'POST',body:JSON.stringify({name:name.trim()})});const d=await r.json();if(!r.ok){alert(d.error||'Could not create category');return}await loadCategories();document.getElementById('habitCategory').value=String(d.id);}
async function loadHabits(){const r=await window.api.fetchWithAuth('/habits');if(!r.ok)return;habits=await r.json();const box=document.getElementById('habitsList');box.innerHTML='';if(!habits.length){box.innerHTML='<div class="empty-state"><div class="fs-1">🌱</div><h3 class="mt-2">No habits yet</h3><p class="text-muted">Create your first habit and start your routine.</p><button class="btn btn-dark btn-add">+ Add New Habit</button></div>';box.querySelector('.btn-add').onclick=()=>openAddModal();return;}
habits.forEach(h=>{const done=Number(h.done_today)>0;const row=document.createElement('div');row.className='habit-row p-3 bg-light rounded d-flex flex-wrap align-items-center gap-3 border-start border-4 border-secondary';row.style.opacity=done?.65:1;row.innerHTML=`<div class="flex-grow-1" style="min-width:220px"><div class="fw-semibold fs-5 ${done?'text-decoration-line-through':''}">${esc(h.name)}</div><div class="habit-meta text-muted mt-1"><span class="badge text-bg-light border">${esc(h.category)}</span> · Goal ${h.goal_days} days · 🔥 ${h.streak} day streak${h.reminder_enabled?' · <span class="reminder-on">⏰ '+esc(h.reminder_time)+'</span>':''}</div></div><div class="d-flex gap-2"><button class="btn ${done?'btn-success':'btn-dark'} btn-sm" onclick="toggleDone(${h.id})">${done?'✓ Done':'Mark as Done'}</button><button class="btn btn-outline-secondary btn-sm" onclick="openEditModal(${h.id})">Edit</button><button class="btn btn-outline-danger btn-sm" onclick="deleteHabit(${h.id})">Delete</button></div>`;box.appendChild(row);});scheduleReminderChecks();}
async function toggleDone(id){const r=await window.api.fetchWithAuth(`/habits/${id}/toggle`,{method:'POST'});if(r.ok)loadHabits();}
async function deleteHabit(id){if(!confirm('Are you sure you want to delete this habit?'))return;const r=await window.api.fetchWithAuth(`/habits/${id}`,{method:'DELETE'});if(r.ok)loadHabits();else{const d=await r.json();alert(d.error||'Delete failed');}}
function resetForm(){document.getElementById('habitForm').reset();document.getElementById('editingHabitId').value='';document.getElementById('habitDays').value=30;document.getElementById('modalTitle').textContent='Add New Habit';document.getElementById('saveHabitBtn').textContent='Add Habit';document.getElementById('reminderTimeWrap').style.display='none';renderCategoryOptions();}
function openAddModal(){resetForm();modal.style.display='flex';}
function openEditModal(id){const h=habits.find(x=>Number(x.id)===Number(id));if(!h)return;document.getElementById('editingHabitId').value=h.id;document.getElementById('habitName').value=h.name;document.getElementById('habitDays').value=h.goal_days;renderCategoryOptions(h.category_id);document.getElementById('reminderEnabled').checked=Number(h.reminder_enabled)===1;document.getElementById('reminderTime').value=h.reminder_time||'';document.getElementById('reminderTimeWrap').style.display=h.reminder_enabled?'block':'none';document.getElementById('modalTitle').textContent='Edit Habit';document.getElementById('saveHabitBtn').textContent='Save Changes';modal.style.display='flex';}
function closeModal(){modal.style.display='none';}
document.querySelectorAll('.close-btn').forEach(b=>b.addEventListener('click',closeModal));document.querySelector('.btn-add').addEventListener('click',openAddModal);window.addEventListener('click',e=>{if(e.target===modal)closeModal();});document.getElementById('newCategoryBtn').addEventListener('click',createCategory);document.getElementById('reminderEnabled').addEventListener('change',e=>document.getElementById('reminderTimeWrap').style.display=e.target.checked?'block':'none');
document.getElementById('habitForm').addEventListener('submit',async e=>{e.preventDefault();const id=document.getElementById('editingHabitId').value;const payload={name:document.getElementById('habitName').value.trim(),goal_days:document.getElementById('habitDays').value,category_id:document.getElementById('habitCategory').value,reminder_enabled:document.getElementById('reminderEnabled').checked,reminder_time:document.getElementById('reminderTime').value};const r=await window.api.fetchWithAuth(id?`/habits/${id}`:'/habits',{method:id?'PUT':'POST',body:JSON.stringify(payload)});const d=await r.json();if(r.ok){closeModal();await loadHabits();}else alert(d.error||'Could not save habit');});
function requestNotificationPermission(){if('Notification'in window&&Notification.permission==='default')Notification.requestPermission().catch(()=>{});}
function scheduleReminderChecks(){if(!('Notification'in window))return;requestNotificationPermission();const keyDate=new Date().toISOString().slice(0,10),time=new Date().toTimeString().slice(0,5);habits.filter(h=>Number(h.reminder_enabled)&&h.reminder_time===time&&!Number(h.done_today)).forEach(h=>{const key=`routinefy-reminder-${h.id}-${keyDate}`;if(localStorage.getItem(key))return;if(Notification.permission==='granted'){new Notification('Routinefy Reminder',{body:`Time for: ${h.name}`});localStorage.setItem(key,'1');}});}
setInterval(scheduleReminderChecks,30000);initDashboard();
</script></body></html>
