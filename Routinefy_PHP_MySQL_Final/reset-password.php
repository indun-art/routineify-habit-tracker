<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Routinefy - Update Password</title><link rel="stylesheet" href="style.css"></head>
<body><div class="login-container"><h1 class="brand-logo">Routinefy</h1><div class="card-outer"><div class="card-inner">
<h2 style="margin-bottom:20px;font-size:1.5rem;">Enter Verification Code</h2>
<form id="resetForm">
<div class="input-group"><label>Email</label><input type="email" id="email" required></div>
<div class="input-group"><label>Verification Code (OTP)</label><input type="text" id="otp" pattern="\d{6}" maxlength="6" required></div>
<div class="input-group"><label>New Password</label><input type="password" id="new-password" minlength="6" required></div>
<div class="input-group"><label>Confirm New Password</label><input type="password" id="confirm-password" minlength="6" required></div>
<button class="btn-submit">Update Password</button></form><p id="msg" style="margin-top:15px"></p>
</div></div></div>
<script src="js/api.js"></script><script>
document.getElementById('email').value=new URLSearchParams(location.search).get('email')||'';
document.getElementById('resetForm').addEventListener('submit',async e=>{e.preventDefault();const p=document.getElementById('new-password').value;if(p!==document.getElementById('confirm-password').value){alert('Passwords do not match');return;}const r=await api.fetchWithAuth('/password/reset',{method:'POST',body:JSON.stringify({email:document.getElementById('email').value.trim(),otp:document.getElementById('otp').value,password:p})});const d=await r.json();if(r.ok){alert(d.message);location.href='index.php';}else document.getElementById('msg').innerText=d.error||'Reset failed';});
</script></body></html>