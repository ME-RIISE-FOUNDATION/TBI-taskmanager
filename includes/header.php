<?php
// Determine active page for sidebar highlighting
$_curPage = basename($_SERVER['PHP_SELF']);
$_curDir  = basename(dirname($_SERVER['PHP_SELF']));
function _isActive(string $dir, string $file): bool {
    global $_curPage, $_curDir;
    return $_curDir === $dir && $_curPage === $file;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
<meta name="base-url"    content="<?= BASE_URL ?>">
<meta name="csrf-token"  content="<?= csrfToken() ?>">

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<!-- Inter font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Chart.js (loaded here so inline scripts in page body can use it) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- Glassmorphism CSS -->
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="tbi-wrapper">

  <!-- ══ Sidebar ══════════════════════════════════════════════ -->
  <aside class="tbi-sidebar" id="sidebar">

    <!-- Brand -->
    <a class="sidebar-brand"
       href="<?= BASE_URL ?>/<?= isAdmin() ? 'admin' : 'employee' ?>/dashboard.php">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="TBI-MCE"
           onerror="this.style.display='none'">
      <div>
        <div class="sb-title">TBI – MCE Hassan</div>
        <div class="sb-sub">Task Manager</div>
      </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <?php if (isAdmin()): ?>
        <div class="sidebar-section">Main</div>

        <a href="<?= BASE_URL ?>/admin/dashboard.php"
           class="<?= _isActive('admin','dashboard.php') ? 'active' : '' ?>">
          <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <a href="<?= BASE_URL ?>/admin/tasks.php"
           class="<?= (_isActive('admin','tasks.php') || _isActive('admin','create_task.php')) ? 'active' : '' ?>">
          <i class="bi bi-list-task"></i> Task Management
        </a>

        <a href="<?= BASE_URL ?>/admin/employees.php"
           class="<?= _isActive('admin','employees.php') ? 'active' : '' ?>">
          <i class="bi bi-people-fill"></i> Employees
        </a>

        <a href="<?= BASE_URL ?>/admin/approvals.php"
           class="<?= _isActive('admin','approvals.php') ? 'active' : '' ?>">
          <i class="bi bi-check2-circle"></i> Approvals
        </a>

        <div class="sidebar-section">Reports</div>

        <a href="<?= BASE_URL ?>/admin/analytics.php"
           class="<?= _isActive('admin','analytics.php') ? 'active' : '' ?>">
          <i class="bi bi-bar-chart-fill"></i> Analytics
        </a>

        <a href="<?= BASE_URL ?>/admin/reports.php"
           class="<?= _isActive('admin','reports.php') ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-bar-graph-fill"></i> Reports
        </a>

      <?php else: ?>
        <div class="sidebar-section">My Workspace</div>

        <a href="<?= BASE_URL ?>/employee/dashboard.php"
           class="<?= _isActive('employee','dashboard.php') ? 'active' : '' ?>">
          <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <a href="<?= BASE_URL ?>/employee/tasks.php"
           class="<?= _isActive('employee','tasks.php') ? 'active' : '' ?>">
          <i class="bi bi-list-task"></i> My Tasks
        </a>

        <a href="<?= BASE_URL ?>/employee/profile.php"
           class="<?= _isActive('employee','profile.php') ? 'active' : '' ?>">
          <i class="bi bi-person-fill"></i> Profile
        </a>
      <?php endif; ?>
    </nav>

    <!-- User info + logout -->
    <div class="sidebar-user">
      <div class="su-avatar">
        <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
      </div>
      <div class="su-info">
        <div class="su-name"><?= e($_SESSION['name'] ?? 'User') ?></div>
        <div class="su-role"><?= e($_SESSION['designation'] ?? '') ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="su-logout" title="Logout">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>

  </aside>

  <!-- Mobile overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ══ Main Wrapper ══════════════════════════════════════════ -->
  <div class="tbi-main-wrap">

    <!-- Topbar -->
    <header class="tbi-topbar">
      <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
      </button>

      <div class="topbar-title">
        <?= isset($pageTitle) ? e($pageTitle) : APP_NAME ?>
      </div>

      <div class="topbar-actions">
        <!-- Notification bell -->
        <div class="dropdown">
          <a class="notif-btn" href="#" data-bs-toggle="dropdown"
             id="notifBell" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="notif-count d-none" id="notifBadge">0</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end notif-dropdown p-1"
              id="notifDropdown" style="min-width:310px">
            <li><div class="dropdown-header">Notifications</div></li>
            <li id="notifList">
              <div class="dropdown-item-text text-center py-2">
                No new notifications
              </div>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <!-- Flash message -->
    <div class="flash-area">
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show mb-0">
        <?= e($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    </div>

    <!-- Page content -->
    <main class="tbi-content">
