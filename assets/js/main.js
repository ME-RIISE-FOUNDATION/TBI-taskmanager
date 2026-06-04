/* ============================================================
   TBI-MCE Task Manager — Main JavaScript
   ============================================================ */

'use strict';

// ── Chart.js global defaults (dark glass theme) ───────────────
(function configureCharts() {
  if (typeof Chart === 'undefined') return;
  Chart.defaults.color             = 'rgba(180, 220, 255, 0.80)';
  Chart.defaults.borderColor       = 'rgba(255, 255, 255, 0.08)';
  Chart.defaults.font.family       = "'Inter', -apple-system, sans-serif";
  Chart.defaults.font.size         = 12;
  if (Chart.defaults.plugins) {
    if (Chart.defaults.plugins.legend)
      Chart.defaults.plugins.legend.labels = {
        ...(Chart.defaults.plugins.legend.labels || {}),
        color: 'rgba(180, 220, 255, 0.80)',
        boxWidth: 12,
        padding: 10,
      };
    if (Chart.defaults.plugins.tooltip) {
      Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(5, 16, 40, 0.95)';
      Chart.defaults.plugins.tooltip.titleColor      = '#e8f4ff';
      Chart.defaults.plugins.tooltip.bodyColor       = 'rgba(180, 220, 255, 0.80)';
      Chart.defaults.plugins.tooltip.borderColor     = 'rgba(255, 255, 255, 0.10)';
      Chart.defaults.plugins.tooltip.borderWidth     = 1;
      Chart.defaults.plugins.tooltip.padding         = 10;
      Chart.defaults.plugins.tooltip.cornerRadius    = 10;
    }
  }
  if (Chart.defaults.scale) {
    Chart.defaults.scale.grid  = { color: 'rgba(255, 255, 255, 0.06)' };
    Chart.defaults.scale.ticks = { color: 'rgba(180, 220, 255, 0.65)' };
  }
})();

// ── Sidebar toggle ────────────────────────────────────────────
(function initSidebar() {
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (!toggle || !sidebar || !overlay) return;

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
  });
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
  });
})();

// ── Notifications (poll every 60 s) ──────────────────────────
(function initNotifications() {
  const bell  = document.getElementById('notifBell');
  const list  = document.getElementById('notifList');
  const badge = document.getElementById('notifBadge');
  if (!bell || !list || !badge) return;

  function loadNotifs() {
    fetch(getBaseUrl() + '/api/notifications_api.php?action=unread')
      .then(r => r.json())
      .then(data => {
        const count = data.count || 0;
        if (count > 0) {
          badge.textContent = count > 9 ? '9+' : count;
          badge.classList.remove('d-none');
        } else {
          badge.classList.add('d-none');
        }
        if (data.notifications && data.notifications.length > 0) {
          list.innerHTML = data.notifications.map(n => `
            <li>
              <a class="dropdown-item small py-2 ${n.read_status === 'unread' ? 'fw-600' : ''}"
                 href="#" onclick="return markNotifRead('${n.id}', this)">
                <div class="mb-1">${escHtml(n.message)}</div>
                <div style="font-size:.68rem;opacity:.6">${escHtml(n.created_at)}</div>
              </a>
            </li>
          `).join('<li><hr class="dropdown-divider my-1"></li>');
        } else {
          list.innerHTML = '<li><div class="dropdown-item-text text-center py-2">No new notifications</div></li>';
        }
      })
      .catch(() => {});
  }

  loadNotifs();
  setInterval(loadNotifs, 60000);
})();

function markNotifRead(id, el) {
  const fd = new FormData();
  fd.append('action',     'mark_read');
  fd.append('id',         id);
  fd.append('csrf_token', getCsrfToken());

  fetch(getBaseUrl() + '/api/notifications_api.php', { method: 'POST', body: fd })
    .then(() => {
      el.classList.remove('fw-600');
    })
    .catch(() => {});
  return false;
}

// ── Delete task via POST (fixes GET-delete CSRF vulnerability) ─
function confirmDelete(taskId, empFilter) {
  if (!confirm('Delete this task? This cannot be undone.')) return;

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '';
  form.style.display = 'none';

  [
    ['csrf_token', getCsrfToken()],
    ['action',     'delete_task'],
    ['task_id',    taskId],
    ['employee',   empFilter || ''],
  ].forEach(([name, value]) => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = name; inp.value = value;
    form.appendChild(inp);
  });

  document.body.appendChild(form);
  form.submit();
}

// ── Export Table to Excel (SheetJS) ──────────────────────────
function exportTableExcel(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) { alert('Table not found'); return; }
  const wb   = XLSX.utils.table_to_book(table, { sheet: 'Report' });
  const date = new Date().toISOString().split('T')[0];
  XLSX.writeFile(wb, filename + '_' + date + '.xlsx');
}

// ── Export Table to PDF (jsPDF) ───────────────────────────────
function exportTablePDF(tableId, title) {
  const table = document.getElementById(tableId);
  if (!table || typeof jspdf === 'undefined') { alert('PDF library not loaded'); return; }
  const { jsPDF } = jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
  doc.setFontSize(14); doc.setTextColor(0, 100, 180);
  doc.text('TBI – MCE Hassan', 15, 15);
  doc.setFontSize(11); doc.setTextColor(50, 100, 150);
  doc.text(title, 15, 22);
  doc.setFontSize(8); doc.setTextColor(120);
  doc.text('Generated: ' + new Date().toLocaleString('en-IN'), 15, 28);
  doc.autoTable({
    html: '#' + tableId, startY: 32,
    styles:     { fontSize: 8, cellPadding: 2 },
    headStyles: { fillColor: [10, 50, 100], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [235, 244, 255] },
  });
  doc.save(title.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
}

// ── Auto-dismiss alerts after 5 s ────────────────────────────
document.querySelectorAll('.alert-dismissible').forEach(el => {
  setTimeout(() => { el.querySelector('.btn-close')?.click(); }, 5000);
});

// ── Tooltip init ──────────────────────────────────────────────
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
  new bootstrap.Tooltip(el);
});

// ── Helpers ───────────────────────────────────────────────────
function getBaseUrl() {
  return document.querySelector('meta[name="base-url"]')?.content ?? '';
}
function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}
function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
