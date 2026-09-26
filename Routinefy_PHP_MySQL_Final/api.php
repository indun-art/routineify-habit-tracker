<?php
require __DIR__ . "/includes/db.php";
require __DIR__ . "/includes/functions.php";

$route = trim($_GET['route'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];
$b = json_body();

function get_setting(PDO $pdo, string $key, string $default=''): string {
    $q=$pdo->prepare("SELECT setting_value FROM settings WHERE setting_key=?");
    $q->execute([$key]);
    return (string)($q->fetchColumn() ?? $default);
}

function calculate_streak(PDO $pdo,int $habitId):int {
    $q=$pdo->prepare("SELECT date FROM habit_logs WHERE habit_id=? AND completed=1 ORDER BY date DESC");
    $q->execute([$habitId]);
    $dates=$q->fetchAll(PDO::FETCH_COLUMN);
    if(!$dates) return 0;
    $dateSet=array_fill_keys($dates,true);
    $cursor=new DateTime('today');
    $today=$cursor->format('Y-m-d');
    $yesterday=(new DateTime('yesterday'))->format('Y-m-d');
    if(isset($dateSet[$today])) {
        $streak=0;
    } elseif(isset($dateSet[$yesterday])) {
        $cursor=new DateTime('yesterday');
        $streak=0;
    } else return 0;
    while(isset($dateSet[$cursor->format('Y-m-d')])) {
        $streak++;
        $cursor->modify('-1 day');
    }
    return $streak;
}

function habit_rows(PDO $pdo,int $uid):array {
    $q=$pdo->prepare("SELECT h.id,h.name,h.goal_days,h.category_id,
        COALESCE(c.name,'Other') category,
        h.reminder_enabled,h.reminder_time,
        COALESCE((SELECT completed FROM habit_logs l WHERE l.habit_id=h.id AND l.date=CURDATE()),0) done_today,
        COALESCE((SELECT COUNT(*) FROM habit_logs l2 WHERE l2.habit_id=h.id AND l2.completed=1),0) completed_count
        FROM habits h LEFT JOIN categories c ON c.id=h.category_id
        WHERE h.user_id=? ORDER BY h.id DESC");
    $q->execute([$uid]);
    $rows=$q->fetchAll();
    foreach($rows as &$r) {
        $r['streak']=calculate_streak($pdo,(int)$r['id']);
        $r['reminder_enabled']=(int)$r['reminder_enabled'];
        $r['reminder_time']=$r['reminder_time'] ? substr($r['reminder_time'],0,5) : null;
    }
    return $rows;
}

function valid_category_for_user(PDO $pdo, int $categoryId, int $uid): bool {
    $q=$pdo->prepare("SELECT id FROM categories WHERE id=? AND (user_id IS NULL OR user_id=?)");
    $q->execute([$categoryId,$uid]);
    return (bool)$q->fetchColumn();
}

if($route==='auth/register'&&$method==='POST'){
    if(get_setting($pdo,'registration_status','open')!=='open') out_json(['error'=>'Registration is currently closed'],403);
    $n=trim($b['full_name']??''); $e=trim($b['email']??''); $pw=$b['password']??'';
    if(!$n||strlen($n)>100||!filter_var($e,FILTER_VALIDATE_EMAIL)||strlen($pw)<6) out_json(['error'=>'Enter a valid name, email and password (minimum 6 characters).'],422);
    try{
        $q=$pdo->prepare("INSERT INTO users(full_name,email,password,role,status) VALUES(?,?,?,'user','active')");
        $q->execute([$n,$e,password_hash($pw,PASSWORD_DEFAULT)]);
    }catch(Throwable $x){out_json(['error'=>'Email already exists'],409);}
    out_json(['message'=>'Registered']);
}

if($route==='auth/login'&&$method==='POST'){
    $e=trim($b['email']??''); $pw=$b['password']??''; $isAdmin=!empty($b['isAdminLogin']); $key=$b['adminKey']??'';
    if($isAdmin && !password_verify($key,get_setting($pdo,'admin_key_hash',''))) out_json(['error'=>'Invalid admin security key'],401);
    $q=$pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1"); $q->execute([$e]); $u=$q->fetch();
    if(!$u||$u['status']!=='active'||!password_verify($pw,$u['password'])) out_json(['error'=>'Invalid email or password'],401);
    if($isAdmin&&$u['role']!=='admin') out_json(['error'=>'Admin access required'],403);
    if(!$isAdmin&&$u['role']==='admin') out_json(['error'=>'Please use Admin Login for an administrator account.'],403);
    session_regenerate_id(true); unset($u['password'],$u['reset_otp'],$u['reset_expires']);
    $_SESSION['user']=$u;
    out_json(['message'=>'Login successful','user'=>$u,'token'=>'php-session']);
}
if($route==='auth/logout'&&$method==='POST'){$_SESSION=[];session_destroy();out_json(['message'=>'Logged out']);}
if($route==='auth/me'&&$method==='GET'){need_login();out_json($_SESSION['user']);}

if($route==='categories'&&$method==='GET'){
    need_login();
    $q=$pdo->prepare("SELECT id,name FROM categories WHERE user_id IS NULL OR user_id=? ORDER BY user_id IS NOT NULL, name");
    $q->execute([user()['id']]);
    out_json($q->fetchAll());
}
if($route==='categories'&&$method==='POST'){
    need_login();
    $name=trim($b['name']??'');
    if(!$name||strlen($name)>80) out_json(['error'=>'Category name is required'],422);
    try {
        $q=$pdo->prepare("INSERT INTO categories(user_id,name) VALUES(?,?)");
        $q->execute([user()['id'],$name]);
    } catch(Throwable $x) { out_json(['error'=>'Category already exists'],409); }
    out_json(['message'=>'Category created','id'=>$pdo->lastInsertId(),'name'=>$name]);
}

if($route==='habits'&&$method==='GET'){need_login();out_json(habit_rows($pdo,(int)user()['id']));}
if($route==='habits'&&$method==='POST'){
    need_login();
    $uid=(int)user()['id']; $n=trim($b['name']??''); $g=(int)($b['goal_days']??30);
    $categoryId=isset($b['category_id'])&&$b['category_id']!==''?(int)$b['category_id']:null;
    $reminderEnabled=!empty($b['reminder_enabled'])?1:0;
    $reminderTime=trim($b['reminder_time']??'');
    if(!$n||strlen($n)>150) out_json(['error'=>'Habit name is required'],422);
    if($g<1||$g>3650) out_json(['error'=>'Goal must be between 1 and 3650 days'],422);
    if($categoryId!==null&&!valid_category_for_user($pdo,$categoryId,$uid)) out_json(['error'=>'Invalid category'],422);
    if($reminderEnabled && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$reminderTime)) out_json(['error'=>'Choose a valid reminder time'],422);
    if(!$reminderEnabled) $reminderTime=null;
    $q=$pdo->prepare("INSERT INTO habits(user_id,category_id,name,goal_days,reminder_enabled,reminder_time) VALUES(?,?,?,?,?,?)");
    $q->execute([$uid,$categoryId,$n,$g,$reminderEnabled,$reminderTime]);
    out_json(['message'=>'Habit created','id'=>$pdo->lastInsertId()]);
}
if(preg_match('#^habits/(\d+)$#',$route,$m)&&$method==='PUT'){
    need_login();
    $uid=(int)user()['id']; $id=(int)$m[1];
    $q=$pdo->prepare("SELECT id FROM habits WHERE id=? AND user_id=?"); $q->execute([$id,$uid]);
    if(!$q->fetch()) out_json(['error'=>'Habit not found'],404);
    $n=trim($b['name']??''); $g=(int)($b['goal_days']??30);
    $categoryId=isset($b['category_id'])&&$b['category_id']!==''?(int)$b['category_id']:null;
    $reminderEnabled=!empty($b['reminder_enabled'])?1:0; $reminderTime=trim($b['reminder_time']??'');
    if(!$n||strlen($n)>150) out_json(['error'=>'Habit name is required'],422);
    if($g<1||$g>3650) out_json(['error'=>'Goal must be between 1 and 3650 days'],422);
    if($categoryId!==null&&!valid_category_for_user($pdo,$categoryId,$uid)) out_json(['error'=>'Invalid category'],422);
    if($reminderEnabled&&!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$reminderTime)) out_json(['error'=>'Choose a valid reminder time'],422);
    if(!$reminderEnabled)$reminderTime=null;
    $q=$pdo->prepare("UPDATE habits SET name=?,goal_days=?,category_id=?,reminder_enabled=?,reminder_time=? WHERE id=? AND user_id=?");
    $q->execute([$n,$g,$categoryId,$reminderEnabled,$reminderTime,$id,$uid]);
    out_json(['message'=>'Habit updated']);
}
if(preg_match('#^habits/(\d+)/toggle$#',$route,$m)&&$method==='POST'){
    need_login(); $q=$pdo->prepare("SELECT id FROM habits WHERE id=? AND user_id=?"); $q->execute([$m[1],user()['id']]);
    if(!$q->fetch()) out_json(['error'=>'Not found'],404);
    $d=date('Y-m-d'); $q=$pdo->prepare("SELECT id,completed FROM habit_logs WHERE habit_id=? AND date=?"); $q->execute([$m[1],$d]); $x=$q->fetch();
    if($x) $pdo->prepare("UPDATE habit_logs SET completed=? WHERE id=?")->execute([$x['completed']?0:1,$x['id']]);
    else $pdo->prepare("INSERT INTO habit_logs(habit_id,date,completed) VALUES(?,?,1)")->execute([$m[1],$d]);
    out_json(['message'=>'Updated']);
}
if(preg_match('#^habits/(\d+)$#',$route,$m)&&$method==='DELETE'){
    need_login(); $q=$pdo->prepare("DELETE FROM habits WHERE id=? AND user_id=?"); $q->execute([$m[1],user()['id']]); out_json(['message'=>'Deleted']);
}

if($route==='analytics'&&$method==='GET'){
    need_login(); $uid=(int)user()['id'];
    $q=$pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id=?"); $q->execute([$uid]); $total=(int)$q->fetchColumn();
    $trueHighest=0; foreach($total?habit_rows($pdo,$uid):[] as $h) $trueHighest=max($trueHighest,(int)$h['streak']);
    $q=$pdo->prepare("SELECT COALESCE(SUM(l.completed),0) done,COUNT(l.id) total FROM habit_logs l JOIN habits h ON h.id=l.habit_id WHERE h.user_id=?"); $q->execute([$uid]); $a=$q->fetch();
    $rate=((int)$a['total'])?round(((int)$a['done']/(int)$a['total'])*100,1):0;
    $q=$pdo->prepare("SELECT DATE_FORMAT(d.day,'%a') label,d.day,COALESCE(SUM(l.completed),0) completed FROM (SELECT CURDATE() day UNION ALL SELECT DATE_SUB(CURDATE(),INTERVAL 1 DAY) UNION ALL SELECT DATE_SUB(CURDATE(),INTERVAL 2 DAY) UNION ALL SELECT DATE_SUB(CURDATE(),INTERVAL 3 DAY) UNION ALL SELECT DATE_SUB(CURDATE(),INTERVAL 4 DAY) UNION ALL SELECT DATE_SUB(CURDATE(),INTERVAL 5 DAY) UNION ALL SELECT DATE_SUB(CURDATE(),INTERVAL 6 DAY)) d LEFT JOIN (habit_logs l JOIN habits h ON h.id=l.habit_id AND h.user_id=?) ON l.date=d.day GROUP BY d.day ORDER BY d.day");
    $q->execute([$uid]); $weekly=$q->fetchAll();
    $q=$pdo->prepare("SELECT DATE_FORMAT(d.day,'%m-%d') label,d.day,COALESCE(SUM(l.completed),0) completed FROM (SELECT DATE_SUB(CURDATE(),INTERVAL seq.n DAY) day FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29) seq) d LEFT JOIN (habit_logs l JOIN habits h ON h.id=l.habit_id AND h.user_id=?) ON l.date=d.day GROUP BY d.day ORDER BY d.day");
    $q->execute([$uid]); $monthly=$q->fetchAll();
    $q=$pdo->prepare("SELECT COALESCE(c.name,'Other') category,COUNT(*) habits FROM habits h LEFT JOIN categories c ON c.id=h.category_id WHERE h.user_id=? GROUP BY COALESCE(c.name,'Other') ORDER BY habits DESC"); $q->execute([$uid]); $categories=$q->fetchAll();
    out_json(['total_habits'=>$total,'highest_streak'=>$trueHighest,'completion_rate'=>$rate,'weekly'=>$weekly,'monthly'=>$monthly,'categories'=>$categories]);
}

if($route==='contact'&&$method==='POST'){
    $n=trim($b['name']??'');$e=trim($b['email']??'');$m=trim($b['message']??'');
    if(!$n||!filter_var($e,FILTER_VALIDATE_EMAIL)||!$m)out_json(['error'=>'Please fill all fields'],422);
    $q=$pdo->prepare("INSERT INTO messages(name,email,message) VALUES(?,?,?)");$q->execute([$n,$e,$m]);out_json(['message'=>'Message sent']);
}

if($route==='password/request'&&$method==='POST'){
    $e=trim($b['email']??''); if(!filter_var($e,FILTER_VALIDATE_EMAIL)) out_json(['error'=>'Enter a valid email address'],422);
    $q=$pdo->prepare("SELECT id FROM users WHERE email=? AND status='active' LIMIT 1");$q->execute([$e]);$u=$q->fetch();
    if(!$u) out_json(['message'=>'If the account exists, a verification code has been generated.']);
    $otp=(string)random_int(100000,999999);$q=$pdo->prepare("UPDATE users SET reset_otp=?,reset_expires=DATE_ADD(NOW(),INTERVAL 10 MINUTE) WHERE id=?");$q->execute([$otp,$u['id']]);
    $_SESSION['reset_email']=$e; out_json(['message'=>'Verification code generated. For this local demo, your code is: '.$otp]);
}
if($route==='password/reset'&&$method==='POST'){
    $e=trim($b['email']??$_SESSION['reset_email']??'');$otp=trim($b['otp']??'');$pw=$b['password']??'';
    if(!filter_var($e,FILTER_VALIDATE_EMAIL)||!preg_match('/^\d{6}$/',$otp)||strlen($pw)<6)out_json(['error'=>'Invalid reset details'],422);
    $q=$pdo->prepare("SELECT id FROM users WHERE email=? AND reset_otp=? AND reset_expires>=NOW() AND status='active' LIMIT 1");$q->execute([$e,$otp]);$u=$q->fetch();
    if(!$u)out_json(['error'=>'Invalid or expired verification code'],401);
    $q=$pdo->prepare("UPDATE users SET password=?,reset_otp=NULL,reset_expires=NULL WHERE id=?");$q->execute([password_hash($pw,PASSWORD_DEFAULT),$u['id']]);unset($_SESSION['reset_email']);out_json(['message'=>'Password updated successfully']);
}

if($route==='admin/stats'&&$method==='GET'){
    need_admin();
    $users=(int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $habits=(int)$pdo->query("SELECT COUNT(*) FROM habits")->fetchColumn();
    $daily=(int)$pdo->query("SELECT COUNT(*) FROM habit_logs WHERE date=CURDATE() AND completed=1")->fetchColumn();
    $messages=(int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    out_json(['total_users'=>$users,'active_habits'=>$habits,'daily_completions'=>$daily,'messages'=>$messages,'system_status'=>'Online']);
}
if($route==='admin/messages'&&$method==='GET'){need_admin();out_json($pdo->query("SELECT id,name,email,message,DATE_FORMAT(created_at,'%Y-%m-%d %H:%i') created_at FROM messages ORDER BY id DESC LIMIT 20")->fetchAll());}
if($route==='admin/users'&&$method==='POST'){
    need_admin(); $n=trim($b['full_name']??'');$e=trim($b['email']??'');$pw=$b['password']??'';$role=$b['role']??'user';
    if(!$n||!filter_var($e,FILTER_VALIDATE_EMAIL)||strlen($pw)<6||!in_array($role,['user','admin'],true)) out_json(['error'=>'Invalid account details'],422);
    try{$q=$pdo->prepare("INSERT INTO users(full_name,email,password,role,status) VALUES(?,?,?,?,'active')");$q->execute([$n,$e,password_hash($pw,PASSWORD_DEFAULT),$role]);}
    catch(Throwable $x){out_json(['error'=>'Email already exists'],409);} out_json(['message'=>'Account created','id'=>$pdo->lastInsertId()]);
}
if($route==='admin/users'&&$method==='GET'){need_admin();out_json($pdo->query("SELECT id,full_name,email,role,status,DATE_FORMAT(created_at,'%Y-%m-%d') joined_date,created_at FROM users ORDER BY id DESC")->fetchAll());}
if(preg_match('#^admin/users/(\d+)/status$#',$route,$m)&&$method==='PUT'){
    need_admin();$status=$b['status']??'';if($status==='blocked')$status='inactive';if(!in_array($status,['active','inactive'],true))out_json(['error'=>'Invalid status']);
    $q=$pdo->prepare("UPDATE users SET status=? WHERE id=? AND role<>'admin'");$q->execute([$status,$m[1]]);out_json(['message'=>'Status updated']);
}
if(preg_match('#^admin/users/(\d+)$#',$route,$m)&&$method==='PUT'){
    need_admin();$n=trim($b['full_name']??'');$e=trim($b['email']??'');$role=$b['role']??'user';
    if(!$n||!filter_var($e,FILTER_VALIDATE_EMAIL)||!in_array($role,['user','admin'],true))out_json(['error'=>'Invalid user data'],422);
    if((int)$m[1]===(int)user()['id'] && $role!=='admin') out_json(['error'=>'You cannot remove your own admin role'],422);
    try{$q=$pdo->prepare("UPDATE users SET full_name=?,email=?,role=? WHERE id=?");$q->execute([$n,$e,$role,$m[1]]);}catch(Throwable $x){out_json(['error'=>'Email already exists'],409);}out_json(['message'=>'User updated']);
}
if(preg_match('#^admin/users/(\d+)$#',$route,$m)&&$method==='DELETE'){
    need_admin(); if((int)$m[1]===(int)user()['id']) out_json(['error'=>'You cannot delete your own account'],422);
    $q=$pdo->prepare("DELETE FROM users WHERE id=?");$q->execute([$m[1]]);out_json(['message'=>'User deleted']);
}
if($route==='admin/habits'&&$method==='GET'){
    need_admin();
    out_json($pdo->query("SELECT h.id,h.name,COALESCE(c.name,'Other') category,u.full_name created_by,COALESCE((SELECT COUNT(*) FROM habit_logs l WHERE l.habit_id=h.id AND l.completed=1),0) completed_count FROM habits h JOIN users u ON u.id=h.user_id LEFT JOIN categories c ON c.id=h.category_id ORDER BY h.id DESC")->fetchAll());
}
if(preg_match('#^admin/habits/(\d+)$#',$route,$m)&&$method==='DELETE'){
    need_admin();$q=$pdo->prepare("DELETE FROM habits WHERE id=?");$q->execute([(int)$m[1]]);out_json(['message'=>'Habit deleted']);
}
if($route==='admin/settings'&&$method==='GET'){need_admin();out_json(['app_name'=>get_setting($pdo,'app_name','Routinefy Habit Tracker'),'registration_status'=>get_setting($pdo,'registration_status','open')]);}
if($route==='admin/settings'&&$method==='PUT'){
    need_admin();$app=trim($b['app_name']??'');$reg=$b['registration_status']??'open';
    if(!$app||!in_array($reg,['open','closed'],true))out_json(['error'=>'Invalid settings'],422);
    foreach(['app_name'=>$app,'registration_status'=>$reg] as $k=>$v){$q=$pdo->prepare("INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$q->execute([$k,$v]);}
    out_json(['message'=>'Settings saved']);
}

out_json(['error'=>'Unknown route'],404);
