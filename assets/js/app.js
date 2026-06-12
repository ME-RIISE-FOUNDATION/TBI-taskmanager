'use strict';

// ── Config ────────────────────────────────────────────────────
const ADMIN_ROLES   = ['CEO', 'COO'];
const TASK_STATUSES = ['Pending', 'In Progress', 'Completed', 'Approved', 'Rejected'];
const PRIORITIES    = ['High', 'Medium', 'Low'];
const DESIGNATIONS  = ['CEO', 'COO', 'Software Associate', 'Finance Associate', 'Innovation Associate', 'Supporting Staff'];

// ── DB (localStorage) ─────────────────────────────────────────
const DB = {
  _get(key)       { try { return JSON.parse(localStorage.getItem('tbi_' + key) || '[]'); } catch { return []; } },
  _set(key, data) { localStorage.setItem('tbi_' + key, JSON.stringify(data)); },
  getAll(entity)                           { return this._get(entity); },
  findOne(entity, field, value)            { return this._get(entity).find(r => r[field] === value) || null; },
  findMany(entity, field, value)           { return this._get(entity).filter(r => r[field] === value); },
  append(entity, record)                   { const d = this._get(entity); d.push(record); this._set(entity, d); },
  updateById(entity, idField, idVal, upd)  { this._set(entity, this._get(entity).map(r => r[idField] === idVal ? {...r, ...upd} : r)); },
  deleteById(entity, idField, idVal)       { this._set(entity, this._get(entity).filter(r => r[idField] !== idVal)); },
};

// ── Seed ──────────────────────────────────────────────────────
function seedIfNeeded() {
  if (localStorage.getItem('tbi_initialized')) return;

  DB._set('employees', [
    {"Employee_ID":"EMP_001","Name":"Dr. Geetha Kiran A",  "Designation":"CEO",                 "Email":"ceotbimeriise@mcehassan.ac.in","Phone":"+91 98765 43210","Photo_URL":"","Status":"Active"},
    {"Employee_ID":"EMP_002","Name":"Dr. Mohana Lakshmi J","Designation":"COO",                 "Email":"cootbimeriise@mcehassan.ac.in","Phone":"+91 98765 43211","Photo_URL":"","Status":"Active"},
    {"Employee_ID":"EMP_003","Name":"Mr. Darshan H D",     "Designation":"Software Associate",  "Email":"satbimeriise@mcehassan.ac.in", "Phone":"+91 98765 43212","Photo_URL":"","Status":"Active"},
    {"Employee_ID":"EMP_004","Name":"Miss. Ramya K V",     "Designation":"Finance Associate",   "Email":"fatbimeriise@mcehassan.ac.in", "Phone":"+91 98765 43213","Photo_URL":"","Status":"Active"},
    {"Employee_ID":"EMP_005","Name":"Ms. Madhurya H V",    "Designation":"Innovation Associate","Email":"iatbimeriise@mcehassan.ac.in", "Phone":"+91 98765 43214","Photo_URL":"","Status":"Active"},
    {"Employee_ID":"EMP_006","Name":"Ms. Deeksha M S",     "Designation":"Supporting Staff",    "Email":"sstbimeriise@mcehassan.ac.in", "Phone":"+91 98765 43215","Photo_URL":"","Status":"Active"}
  ]);

  DB._set('users', [
    {"User_ID":"USR_001","Username":"geetha",  "Password":"Admin@123",   "Designation":"CEO",                 "Employee_ID":"EMP_001","Email":"ceotbimeriise@mcehassan.ac.in","Name":"Dr. Geetha Kiran A"},
    {"User_ID":"USR_002","Username":"mohana",  "Password":"Admin@123",   "Designation":"COO",                 "Employee_ID":"EMP_002","Email":"cootbimeriise@mcehassan.ac.in","Name":"Dr. Mohana Lakshmi J"},
    {"User_ID":"USR_003","Username":"darshan", "Password":"Employee@123","Designation":"Software Associate",  "Employee_ID":"EMP_003","Email":"satbimeriise@mcehassan.ac.in","Name":"Mr. Darshan H D"},
    {"User_ID":"USR_004","Username":"ramya",   "Password":"Employee@123","Designation":"Finance Associate",   "Employee_ID":"EMP_004","Email":"fatbimeriise@mcehassan.ac.in","Name":"Miss. Ramya K V"},
    {"User_ID":"USR_005","Username":"madhurya","Password":"Employee@123","Designation":"Innovation Associate","Employee_ID":"EMP_005","Email":"iatbimeriise@mcehassan.ac.in","Name":"Ms. Madhurya H V"},
    {"User_ID":"USR_006","Username":"deeksha", "Password":"Employee@123","Designation":"Supporting Staff",    "Employee_ID":"EMP_006","Email":"sstbimeriise@mcehassan.ac.in","Name":"Ms. Deeksha M S"}
  ]);

  DB._set('tasks', [
    {"Task_ID":"TSK_20260604_367924","Employee_ID":"EMP_003","Task_Title":"Registration Form","Description":"Design a Professional Events Registration form for all programs ( NAIN 2.0, TBI-MCE, MRF, UBA ,RGEP , IIC )","Priority":"High","Assigned_Date":"2026-05-21","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_850917","Employee_ID":"EMP_003","Task_Title":"Certificate Generate - Achal to give access to certify files. Schedule meeting with Achal + CEO","Description":"Achal to give a access to certify files. Schedule meeting with Achal + CEO to plan and complete updates","Priority":"High","Assigned_Date":"2026-05-21","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_353294","Employee_ID":"EMP_003","Task_Title":"Prepare a Task Scheduler with admin access to ceomeriise@mcehassan.ac.in","Description":"It must go live from June 1. It can be a separate web page. Later we can integrate with meriise.org","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_640267","Employee_ID":"EMP_003","Task_Title":"Get a tab meriise.org exclusive for courses","Description":"1. Fundamentals of MATLAB - Under Microengineering. 2. Web Designing, Power BI & Elevate from concept to create through Innovation DTP - Under MSME. 3. Excel Essentials - General. 4.DSA - Under MicroEngineering","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_149014","Employee_ID":"EMP_004","Task_Title":"Complete the verification and updation of ME-RIISE FOUNDATION Event file","Description":"","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_850189","Employee_ID":"EMP_004","Task_Title":"Talk to Mona and prepare a Budget for office supplies under TBI","Description":"A list is prepared by Deeksha, get it from her","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_829081","Employee_ID":"EMP_004","Task_Title":"Take the bill file of NAIN 2.0 and update the UC annextures","Description":"Also front page of UC is to be done. Take guidance from Mona and complete it.","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_392279","Employee_ID":"EMP_005","Task_Title":"Updating website with event details (regular). End-to-end documentation for all events.","Description":"Update Ignite Idea to Proto 4.0","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_942678","Employee_ID":"EMP_005","Task_Title":"Show scanned folder of all events (Apr 2025–Apr 2026) and update TBI file","Description":"Show scanned folder of all events for ME-RIISE, NAIN 2.0, RGEP. TBI file: Update complete recruitment details.","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_786349","Employee_ID":"EMP_005","Task_Title":"Prepare event report of Innovation Catalyst.","Description":"","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_785897","Employee_ID":"EMP_005","Task_Title":"Update the 2nd session of DSA course documents. Ignite Idea to Proto 4.0.","Description":"","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_330988","Employee_ID":"EMP_005","Task_Title":"Prepare report and filing of documents as per checklist by 3:00 pm.","Description":"Get filing done by Deeksha. Needs to be completed and reviewed by Mona by 11 am.","Priority":"High","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_049742","Employee_ID":"EMP_006","Task_Title":"Keep log book outside immediately after coming.","Description":"Daily Task","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-30","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_754978","Employee_ID":"EMP_006","Task_Title":"Keep movement register immediately after coming.","Description":"Daily Tasks","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-30","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_692604","Employee_ID":"EMP_006","Task_Title":"Scan official documents/letters; update in Drive and mail.","Description":"Daily Tasks","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-30","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_211322","Employee_ID":"EMP_006","Task_Title":"Write from-and-to register for all documents and letters.","Description":"Daily Tasks","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-30","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_452394","Employee_ID":"EMP_006","Task_Title":"Check for charge in all laptops.","Description":"Daily Tasks","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-30","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_677348","Employee_ID":"EMP_006","Task_Title":"Complete scanning and updation of events.","Description":"Complete scanning and updation of events. Ensure all prepared documents are uploaded to Drive.","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""},
    {"Task_ID":"TSK_20260604_018465","Employee_ID":"EMP_006","Task_Title":"DSA – Microengineering file to be prepared.","Description":"Add flyer, registration details, curriculum, week 1 details. Take details from Darshan. Get reviewed by Mona.","Priority":"Medium","Assigned_Date":"2026-06-04","Deadline":"2026-06-04","Status":"Pending","Days_Pending":0,"Assigned_By":"Dr. Geetha Kiran A","File_URL":"","Notes":""}
  ]);

  DB._set('approvals', []);
  DB._set('notifications', []);
  localStorage.setItem('tbi_initialized', '1');
}

// ── Auth ──────────────────────────────────────────────────────
const Auth = {
  SESSION_KEY: 'tbi_session',
  getSession()    { try { return JSON.parse(sessionStorage.getItem(this.SESSION_KEY) || 'null'); } catch { return null; } },
  setSession(u)   { sessionStorage.setItem(this.SESSION_KEY, JSON.stringify({user_id:u.User_ID, username:u.Username, name:u.Name, designation:u.Designation, employee_id:u.Employee_ID, email:u.Email})); },
  isLoggedIn()    { return !!this.getSession(); },
  isAdmin()       { const s = this.getSession(); return s && ADMIN_ROLES.includes(s.designation); },
  login(username, password) {
    const user = DB.findOne('users', 'Username', username);
    if (user && user.Password === password) { this.setSession(user); return true; }
    return false;
  },
  logout() { sessionStorage.removeItem(this.SESSION_KEY); window.location.href = rootPath() + 'index.html'; },
  require() {
    if (!this.isLoggedIn()) { window.location.href = rootPath() + 'index.html'; return false; }
    return true;
  },
  requireAdmin() {
    if (!this.require()) return false;
    if (!this.isAdmin()) { window.location.href = rootPath() + 'employee/dashboard.html'; return false; }
    return true;
  },
};

// ── Utilities ─────────────────────────────────────────────────
const Utils = {
  esc(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  },
  formatDate(date) {
    if (!date) return '—';
    try { return new Date(date).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}); }
    catch { return date; }
  },
  today() { return new Date().toISOString().split('T')[0]; },
  daysPending(deadline) {
    if (!deadline) return 0;
    const now = new Date(); now.setHours(0,0,0,0);
    const dl  = new Date(deadline); dl.setHours(0,0,0,0);
    return now > dl ? Math.floor((now - dl) / 86400000) : 0;
  },
  isOverdue(deadline, status) {
    if (['Approved','Completed','Rejected'].includes(status)) return false;
    return this.daysPending(deadline) > 0;
  },
  priorityBadge(p) {
    const map = {High:'danger', Medium:'warning', Low:'success'};
    return `<span class="badge bg-${map[p]||'secondary'}">${this.esc(p)}</span>`;
  },
  statusBadge(s) {
    const map = {'Pending':'secondary','In Progress':'primary','Completed':'info','Approved':'success','Rejected':'danger'};
    return `<span class="badge bg-${map[s]||'secondary'}">${this.esc(s)}</span>`;
  },
  generateId(prefix) {
    const d = new Date();
    const date = `${d.getFullYear()}${String(d.getMonth()+1).padStart(2,'0')}${String(d.getDate()).padStart(2,'0')}`;
    const rand = Math.random().toString(36).substr(2,6).toUpperCase();
    return `${prefix.toUpperCase()}_${date}_${rand}`;
  },
  initials(name) { return (name||'U').split(' ').map(w=>w[0]?.toUpperCase()||'').join('').slice(0,2) || 'U'; },
  employeeStats(tasks, employeeId) {
    const mine      = tasks.filter(t => t.Employee_ID === employeeId);
    const total     = mine.length;
    const completed = mine.filter(t => ['Completed','Approved'].includes(t.Status)).length;
    const approved  = mine.filter(t => t.Status === 'Approved').length;
    const pending   = mine.filter(t => ['Pending','In Progress'].includes(t.Status)).length;
    const rejected  = mine.filter(t => t.Status === 'Rejected').length;
    const overdue   = mine.filter(t => this.isOverdue(t.Deadline, t.Status)).length;
    const approvalPct = total > 0 ? Math.round((approved / total) * 100) : 0;
    return {total, completed, approved, pending, rejected, overdue, approvalPct};
  },
  pctColor(pct) { return pct >= 70 ? 'success' : pct >= 40 ? 'warning' : 'danger'; },
  desigIcon(d) {
    return {'CEO':'bi-person-badge-fill','COO':'bi-person-workspace','Software Associate':'bi-code-slash',
            'Finance Associate':'bi-currency-rupee','Innovation Associate':'bi-lightbulb'}[d] || 'bi-person';
  },
};

// ── Root path ─────────────────────────────────────────────────
function rootPath() {
  const p = window.location.pathname;
  return (p.includes('/admin/') || p.includes('/employee/')) ? '../' : '';
}

// ── Flash ─────────────────────────────────────────────────────
function setFlash(type, msg) { sessionStorage.setItem('tbi_flash', JSON.stringify({type, msg})); }
function getFlash() {
  const f = sessionStorage.getItem('tbi_flash');
  if (f) { sessionStorage.removeItem('tbi_flash'); return JSON.parse(f); }
  return null;
}
function showFlash(type, msg) {
  const area = document.getElementById('flashArea');
  if (!area) return;
  const t = type === 'error' ? 'danger' : type;
  area.innerHTML = `<div class="alert alert-${t} alert-dismissible fade show mb-0">${Utils.esc(msg)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
  setTimeout(() => area.querySelector('.btn-close')?.click(), 5000);
}

// ── Sidebar & Topbar rendering ────────────────────────────────
function renderShell(title, requireAdmin = false) {
  seedIfNeeded();
  if (requireAdmin ? !Auth.requireAdmin() : !Auth.require()) return false;

  const session = Auth.getSession();
  const root    = rootPath();
  const isAdm   = Auth.isAdmin();
  const curPage = window.location.pathname.split('/').pop();
  const curDir  = window.location.pathname.includes('/admin/') ? 'admin' : window.location.pathname.includes('/employee/') ? 'employee' : '';
  const act = (dir, page) => curDir === dir && curPage === page ? 'active' : '';

  const pendingApprovals = DB.getAll('approvals').filter(a => a.Status === 'Pending').length;
  const aprBadge = pendingApprovals > 0 ? `<span class="nav-badge">${pendingApprovals}</span>` : '';

  const taskActive = (curDir === 'admin' && (curPage === 'tasks.html' || curPage === 'create_task.html')) ? 'active' : '';

  const nav = isAdm ? `
    <div class="sidebar-section">Main</div>
    <a href="${root}admin/dashboard.html" class="${act('admin','dashboard.html')}"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="${root}admin/tasks.html" class="${taskActive}"><i class="bi bi-list-task"></i> Task Management</a>
    <a href="${root}admin/employees.html" class="${act('admin','employees.html')}"><i class="bi bi-people-fill"></i> Employees</a>
    <a href="${root}admin/approvals.html" class="${act('admin','approvals.html')}"><i class="bi bi-check2-circle"></i> Approvals ${aprBadge}</a>
    <div class="sidebar-section">Reports</div>
    <a href="${root}admin/analytics.html" class="${act('admin','analytics.html')}"><i class="bi bi-bar-chart-fill"></i> Analytics</a>
    <a href="${root}admin/reports.html" class="${act('admin','reports.html')}"><i class="bi bi-file-earmark-bar-graph-fill"></i> Reports</a>
  ` : `
    <div class="sidebar-section">My Workspace</div>
    <a href="${root}employee/dashboard.html" class="${act('employee','dashboard.html')}"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="${root}employee/tasks.html" class="${act('employee','tasks.html')}"><i class="bi bi-list-task"></i> My Tasks</a>
    <a href="${root}employee/profile.html" class="${act('employee','profile.html')}"><i class="bi bi-person-fill"></i> Profile</a>
  `;

  document.getElementById('sidebarContainer').innerHTML = `
    <aside class="tbi-sidebar" id="sidebar">
      <a class="sidebar-brand" href="${root}${isAdm?'admin':'employee'}/dashboard.html">
        <img src="${root}assets/images/logo.svg" alt="TBI-MCE" onerror="this.style.display='none'">
        <div><div class="sb-title">TBI – MCE Hassan</div><div class="sb-sub">Task Manager</div></div>
      </a>
      <nav class="sidebar-nav">${nav}</nav>
      <div class="sidebar-user">
        <div class="su-avatar">${(session.name||'U')[0].toUpperCase()}</div>
        <div class="su-info">
          <div class="su-name">${Utils.esc(session.name)}</div>
          <div class="su-role">${Utils.esc(session.designation)}</div>
        </div>
        <a href="#" class="su-logout" title="Logout" onclick="Auth.logout();return false;"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
  `;

  document.getElementById('topbarContainer').innerHTML = `
    <header class="tbi-topbar">
      <button class="topbar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
      <div class="topbar-title">${Utils.esc(title)}</div>
      <div class="topbar-actions">
        <div class="dropdown">
          <a class="notif-btn" href="#" data-bs-toggle="dropdown" id="notifBell" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="notif-count d-none" id="notifBadge">0</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end notif-dropdown p-1" style="min-width:310px">
            <li><div class="dropdown-header">Notifications</div></li>
            <li id="notifList"><div class="dropdown-item-text text-center py-2">Loading…</div></li>
          </ul>
        </div>
      </div>
    </header>
    <div class="flash-area" id="flashArea"></div>
  `;

  // Sidebar toggle
  document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
    document.getElementById('sidebarOverlay')?.classList.toggle('active');
  });
  document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebarOverlay')?.classList.remove('active');
  });

  // Flash
  const flash = getFlash();
  if (flash) showFlash(flash.type, flash.msg);

  // Notifications
  loadNotifications();
  setInterval(loadNotifications, 60000);

  // Chart defaults
  if (typeof Chart !== 'undefined') {
    Chart.defaults.color = 'rgba(180,220,255,0.80)';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.08)';
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
    Chart.defaults.font.size = 12;
    if (Chart.defaults.plugins?.legend) Chart.defaults.plugins.legend.labels = {color:'rgba(180,220,255,0.80)',boxWidth:12,padding:10};
    if (Chart.defaults.plugins?.tooltip) Object.assign(Chart.defaults.plugins.tooltip, {backgroundColor:'rgba(5,16,40,0.95)',titleColor:'#e8f4ff',bodyColor:'rgba(180,220,255,0.80)',borderColor:'rgba(255,255,255,0.10)',borderWidth:1,padding:10,cornerRadius:10});
    if (Chart.defaults.scale) { Chart.defaults.scale.grid = {color:'rgba(255,255,255,0.06)'}; Chart.defaults.scale.ticks = {color:'rgba(180,220,255,0.65)'}; }
  }

  return true;
}

// ── Notifications ─────────────────────────────────────────────
function loadNotifications() {
  const session = Auth.getSession();
  if (!session) return;
  const badge  = document.getElementById('notifBadge');
  const list   = document.getElementById('notifList');
  if (!badge || !list) return;

  const notifs = DB.findMany('notifications', 'User_ID', session.employee_id);
  const unread = notifs.filter(n => n.Read_Status === 'unread');

  if (unread.length > 0) { badge.textContent = unread.length > 9 ? '9+' : unread.length; badge.classList.remove('d-none'); }
  else { badge.classList.add('d-none'); }

  const recent = notifs.slice(-10).reverse();
  if (!recent.length) {
    list.innerHTML = '<div class="dropdown-item-text text-center py-2">No new notifications</div>';
  } else {
    list.innerHTML = recent.map(n => `
      <li><a class="dropdown-item small py-2 ${n.Read_Status==='unread'?'fw-600':''}" href="#" onclick="markNotifRead('${n.Notif_ID}',this);return false;">
        <div class="mb-1">${Utils.esc(n.Message)}</div>
        <div style="font-size:.68rem;opacity:.6">${Utils.esc(Utils.formatDate(n.Created_At))}</div>
      </a></li>
    `).join('<li><hr class="dropdown-divider my-1"></li>');
  }
}
function markNotifRead(id, el) {
  DB.updateById('notifications', 'Notif_ID', id, {Read_Status:'read'});
  el?.classList.remove('fw-600');
  loadNotifications();
}

// ── Delete task ───────────────────────────────────────────────
function confirmDelete(taskId) {
  if (!confirm('Delete this task? This cannot be undone.')) return;
  DB.deleteById('tasks', 'Task_ID', taskId);
  DB.getAll('approvals').filter(a => a.Task_ID === taskId).forEach(a => DB.deleteById('approvals', 'Approval_ID', a.Approval_ID));
  setFlash('success', 'Task deleted.');
  location.reload();
}

// ── Export Excel ──────────────────────────────────────────────
function exportTableExcel(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) { alert('Table not found'); return; }
  if (typeof XLSX === 'undefined') { alert('Excel library not loaded'); return; }
  const wb = XLSX.utils.table_to_book(table, {sheet:'Report'});
  XLSX.writeFile(wb, filename + '_' + Utils.today() + '.xlsx');
}

// ── Export PDF ────────────────────────────────────────────────
function exportTablePDF(tableId, title) {
  const table = document.getElementById(tableId);
  if (!table || typeof jspdf === 'undefined') { alert('PDF library not loaded'); return; }
  const {jsPDF} = jspdf;
  const doc = new jsPDF({orientation:'landscape', unit:'mm', format:'a4'});
  doc.setFontSize(14); doc.setTextColor(0,100,180); doc.text('TBI – MCE Hassan', 15, 15);
  doc.setFontSize(11); doc.setTextColor(50,100,150); doc.text(title, 15, 22);
  doc.setFontSize(8); doc.setTextColor(120); doc.text('Generated: ' + new Date().toLocaleString('en-IN'), 15, 28);
  doc.autoTable({html:'#'+tableId, startY:32, styles:{fontSize:8,cellPadding:2}, headStyles:{fillColor:[10,50,100],textColor:255,fontStyle:'bold'}, alternateRowStyles:{fillColor:[235,244,255]}});
  doc.save(title.replace(/\s+/g,'_') + '_' + Utils.today() + '.pdf');
}

// ── URL params helper ─────────────────────────────────────────
function getParams() { return new URLSearchParams(window.location.search); }
