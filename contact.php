<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact & Feedback - Routinefy</title>
<link rel="stylesheet" href="css/style2.css">
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#2f3640;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.container{width:min(680px,100%);background:#fff;padding:32px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.08)}
h1{margin:0 0 8px}.intro{color:#718093;margin-bottom:24px;line-height:1.6}
form{display:grid;gap:14px}input,textarea{width:100%;padding:12px 14px;border:1px solid #dcdde1;border-radius:8px;font:inherit;box-sizing:border-box}textarea{min-height:150px;resize:vertical}button{border:0;background:#1e272e;color:#fff;padding:12px 18px;border-radius:8px;font-weight:700;cursor:pointer}button:hover{opacity:.9}#msg{margin-top:16px;padding:12px;border-radius:8px;display:none}.success{background:#e8f8ee;color:#218c4b}.error{background:#fff0f0;color:#c23616}.back{display:inline-block;margin-bottom:20px;color:#2f3640;text-decoration:none}
</style>
</head>
<body>
<div class="container">
<a class="back" href="dashboard.php">← Back to Dashboard</a>
<h1>Share Your Feedback 💬</h1>
<p class="intro">We'd love to hear your ideas, suggestions, questions, or any problems you experienced while using Routinefy. Your feedback helps us improve the system.</p>
<form id="f">
<input name="name" placeholder="Your name" required maxlength="100">
<input name="email" type="email" placeholder="Your email" required maxlength="190">
<textarea name="message" placeholder="Tell us what you think, suggest an improvement, or report a problem..." required></textarea>
<button type="submit">Send Feedback</button>
</form>
<p id="msg"></p>
</div>
<script>
const f=document.getElementById('f'),msg=document.getElementById('msg');
f.onsubmit=async e=>{e.preventDefault();msg.style.display='none';let x=Object.fromEntries(new FormData(f));try{let r=await fetch('api.php?route=contact',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(x)});let d=await r.json();msg.textContent=d.message||d.error||'Something went wrong';msg.className=r.ok?'success':'error';msg.style.display='block';if(r.ok)f.reset()}catch(err){msg.textContent='Unable to send your feedback. Please try again.';msg.className='error';msg.style.display='block'}};
</script>
</body>
</html>
