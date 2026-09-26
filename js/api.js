const API_BASE_URL='api.php?route=';

function makeResponse(data,status=200){return {ok:status>=200&&status<300,status,json:async()=>data};}
async function fetchWithAuth(route,options={}){
  try{
    const clean=String(route||'').replace(/^\//,'');
    const headers={...(options.headers||{})};
    if(!headers['Content-Type'] && options.body) headers['Content-Type']='application/json';
    const r=await fetch(API_BASE_URL+clean,{...options,headers,credentials:'same-origin'});
    const d=await r.json().catch(()=>({error:'Invalid server response'}));
    if(r.status===401 && clean!=='auth/me'){
      localStorage.removeItem('user'); localStorage.removeItem('token');
      if(!location.pathname.endsWith('/index.php') && !location.pathname.endsWith('/')) location.href='index.php';
    }
    return makeResponse(d,r.status);
  }catch(e){return makeResponse({error:'Error connecting to server'},0);}
}
async function apiFetch(route,options={}){return fetchWithAuth(route,options)}
async function apiLogin(email,password,isAdminLogin=false,adminKey=''){
  const r=await fetchWithAuth('/auth/login',{method:'POST',body:JSON.stringify({email,password,isAdminLogin,adminKey})});
  if(r.ok){const d=await r.json();localStorage.setItem('token',d.token||'php-session');localStorage.setItem('user',JSON.stringify(d.user));}
  return r;
}
async function apiRegister(data){return fetchWithAuth('/auth/register',{method:'POST',body:JSON.stringify(data)})}
async function apiLogout(){
  const r=await fetchWithAuth('/auth/logout',{method:'POST'});
  localStorage.removeItem('user');localStorage.removeItem('token');location.href='index.php';return r;
}
async function currentUser(){
  const r=await fetchWithAuth('/auth/me');
  if(!r.ok)return null;
  const d=await r.json();
  localStorage.setItem('user',JSON.stringify(d));
  return d;
}
async function requireRole(role){
  const u=await currentUser();
  if(!u){location.href='index.php';return null;}
  if(role && u.role!==role){location.href=u.role==='admin'?'admin-dashboard.php':'dashboard.php';return null;}
  return u;
}
async function requireUserPage(){return requireRole('user');}
async function requireAdminPage(){return requireRole('admin');}
async function fetchHabits(){return fetchWithAuth('/habits')}
async function createHabit(d){return fetchWithAuth('/habits',{method:'POST',body:JSON.stringify(d)})}
async function toggleHabit(id){return fetchWithAuth('/habits/'+id+'/toggle',{method:'POST'})}
async function deleteHabit(id){return fetchWithAuth('/habits/'+id,{method:'DELETE'})}
window.api={API_BASE_URL,fetchWithAuth,apiFetch,apiLogin,apiRegister,apiLogout,logout:apiLogout,currentUser,requireRole,requireUserPage,requireAdminPage,fetchHabits,createHabit,toggleHabit,deleteHabit,checkAuth:function(){const u=JSON.parse(localStorage.getItem('user')||'null');if(!u){location.href='index.php';return false}return true}};
