<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Routinefy - Forgot Password</title><link rel="stylesheet" href="style.css"></head>
<body><div class="login-container"><h1 class="brand-logo">Routinefy</h1><div class="card-outer"><div class="card-inner">
<h2 style="margin-bottom:20px;font-size:1.5rem;">Reset Your Password</h2>
<p style="margin-bottom:25px;color:#666;font-size:.95rem;">Enter your registered email to generate a verification code.</p>
<form id="resetRequest"><div class="input-group"><label>Email Address</label><input type="email" id="reset-email" required></div><button class="btn-submit">Send Verification Code</button></form>
<p id="msg" style="margin-top:15px"></p><div class="links-container"><a href="index.php" class="link-item">Back to Sign In</a></div>
</div></div></div>
<script src="js/api.js"></script><script>
document.getElementById('resetRequest').addEventListener('submit',async e=>{e.preventDefault();const email=document.getElementById('reset-email').value.trim();const r=await api.fetchWithAuth('/password/request',{method:'POST',body:JSON.stringify({email})});const d=await r.json();if(r.ok){alert(d.message);location.href='reset-password.php?email='+encodeURIComponent(email);}else document.getElementById('msg').innerText=d.error||'Request failed';});
</script></body></html>