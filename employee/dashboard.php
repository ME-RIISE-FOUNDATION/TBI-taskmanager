<?php
// ============================================================
//  Employee Dashboard
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../api/DataService.php';

$sheets  = getDataService();
$myEmpId = $_SESSION['employee_id'];

$allTasks  = $sheets->findMany(SHEET_TASKS,         'Employee_ID', $myEmpId);
$approvals = $sheets->findMany(SHEET_APPROVALS,     'Employee_ID', $myEmpId);
$notifs    = $sheets->findMany(SHEET_NOTIFICATIONS, 'User_ID',     $myEmpId);
$unread    = array_filter($notifs, fn($n) => ($n['Read_Status'] ?? '') === 'unread');

$pending  = array_values(array_filter($allTasks, fn($t) => in_array($t['Status'] ?? '', ['Pending','In Progress'])));
$overdue  = array_values(array_filter($allTasks, fn($t) => isOverdue($t['Deadline'] ?? '', $t['Status'] ?? '')));
$approved = array_values(array_filter($allTasks, fn($t) => ($t['Status'] ?? '') === 'Approved'));
$rejected = array_values(array_filter($allTasks, fn($t) => ($t['Status'] ?? '') === 'Rejected'));

$stats = employeeStats($allTasks, $myEmpId);

// Build urgent list — deduplicate by Task_ID (reliable)
$urgentMap = [];
foreach ($overdue as $t)  $urgentMap[$t['Task_ID']] = $t;
foreach ($pending as $t) {
    if (($t['Priority'] ?? '') === 'High' && !isset($urgentMap[$t['Task_ID']])) {
        $urgentMap[$t['Task_ID']] = $t;
    }
}
usort($urgentMap, fn($a,$b) => strcmp($a['Deadline'] ?? '', $b['Deadline'] ?? ''));
$urgent = array_slice(array_values($urgentMap), 0, 6);

$pageTitle = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<!-- Page heading -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-700 mb-0">
      <i class="bi bi-grid-fill me-2 text-primary"></i>My Dashboard
    </h4>
    <small class="text-muted">
      <?= e($_SESSION['name']) ?> &bull; <?= e($_SESSION['designation']) ?> &bull; <?= date('l, d F Y') ?>
    </small>
  </div>
  <a href="<?= BASE_URL ?>/employee/tasks.php" class="btn btn-primary btn-sm">
    <i class="bi bi-list-task me-1"></i>My Tasks
  </a>
</div>

<!-- ── Stat Cards ────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

  <div class="col-6 col-sm-4 col-lg-2">
    <div class="stat-card border-start border-primary border-4 text-center">
      <div class="stat-icon text-primary"><i class="bi bi-list-task"></i></div>
      <div class="stat-val text-primary"><?= $stats['total'] ?></div>
      <div class="stat-lbl">Total</div>
    </div>
  </div>

  <div class="col-6 col-sm-4 col-lg-2">
    <div class="stat-card border-start border-success border-4 text-center">
      <div class="stat-icon text-success"><i class="bi bi-check2-all"></i></div>
      <div class="stat-val text-success"><?= $stats['approved'] ?></div>
      <div class="stat-lbl">Approved</div>
    </div>
  </div>

  <div class="col-6 col-sm-4 col-lg-2">
    <div class="stat-card border-start border-info border-4 text-center">
      <div class="stat-icon text-info"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-val text-info"><?= $stats['completed'] ?></div>
      <div class="stat-lbl">Completed</div>
    </div>
  </div>

  <div class="col-6 col-sm-4 col-lg-2">
    <div class="stat-card border-start border-warning border-4 text-center">
      <div class="stat-icon text-warning"><i class="bi bi-clock"></i></div>
      <div class="stat-val text-warning"><?= $stats['pending'] ?></div>
      <div class="stat-lbl">Pending</div>
    </div>
  </div>

  <div class="col-6 col-sm-4 col-lg-2">
    <div class="stat-card border-start border-danger border-4 text-center">
      <div class="stat-icon text-danger"><i class="bi bi-exclamation-triangle"></i></div>
      <div class="stat-val text-danger"><?= $stats['overdue'] ?></div>
      <div class="stat-lbl">Overdue</div>
    </div>
  </div>

  <div class="col-6 col-sm-4 col-lg-2">
    <div class="stat-card border-start border-4 text-center"
         style="border-left-color:var(--c-purple)!important">
      <div class="stat-icon text-purple"><i class="bi bi-graph-up"></i></div>
      <div class="stat-val text-purple"><?= $stats['approvalPct'] ?>%</div>
      <div class="stat-lbl">Approval %</div>
    </div>
  </div>

</div>

<!-- ── Alert Banners ─────────────────────────────────────────── -->
<?php if (count($unread) > 0): ?>
<div class="alert alert-info d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-bell-fill fs-5 flex-shrink-0"></i>
  <div>
    You have <strong><?= count($unread) ?></strong>
    unread notification<?= count($unread) > 1 ? 's' : '' ?>.
    <a href="#notifSection" class="alert-link">View below</a>
  </div>
</div>
<?php endif; ?>

<?php if (count($overdue) > 0): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
  <div>
    <strong><?= count($overdue) ?> overdue task<?= count($overdue) > 1 ? 's' : '' ?></strong>
    need immediate attention!
    <a href="<?= BASE_URL ?>/employee/tasks.php?tab=overdue" class="alert-link">View overdue</a>
  </div>
</div>
<?php endif; ?>

<!-- ── Chart + Urgent Tasks ──────────────────────────────────── -->
<div class="row g-3 mb-4">

  <!-- Doughnut chart -->
  <div class="col-md-4 mb-3 mb-md-0">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-600">
        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Task Overview
      </div>
      <div class="card-body d-flex align-items-center justify-content-center"
           style="height:240px; position:relative">
        <?php if ($stats['total'] > 0): ?>
          <canvas id="myTaskChart"></canvas>
        <?php else: ?>
          <div class="text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <span class="small">No tasks yet</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Priority / Overdue list -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-600">
          <i class="bi bi-exclamation-circle text-danger me-2"></i>Priority &amp; Overdue Tasks
        </span>
        <a href="<?= BASE_URL ?>/employee/tasks.php" class="btn btn-sm btn-outline-primary">
          View All
        </a>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if (empty($urgent)): ?>
          <div class="list-group-item text-center text-muted py-4">
            <i class="bi bi-check2-circle text-success fs-4 d-block mb-1"></i>
            All clear — no urgent tasks!
          </div>
          <?php else: ?>
          <?php foreach ($urgent as $t):
            $od = isOverdue($t['Deadline'] ?? '', $t['Status'] ?? '');
          ?>
          <div class="list-group-item d-flex align-items-start gap-3 py-2 px-3">
            <div class="flex-fill min-w-0">
              <div class="fw-500 small text-truncate"><?= e($t['Task_Title'] ?? '') ?></div>
              <div class="d-flex flex-wrap gap-1 mt-1">
                <?= priorityBadge($t['Priority'] ?? '') ?>
                <?= statusBadge($t['Status'] ?? '') ?>
                <?php if ($od): ?>
                <span class="badge bg-danger">
                  <?= daysPending($t['Deadline'] ?? '') ?>d overdue
                </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-end small text-muted flex-shrink-0 pt-1">
              <?= formatDate($t['Deadline'] ?? '') ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ── Recent Assignments ─────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="fw-600">
      <i class="bi bi-clock-history me-2"></i>Recent Assignments
    </span>
    <a href="<?= BASE_URL ?>/employee/tasks.php" class="btn btn-sm btn-outline-primary">
      All Tasks
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Task</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $recent = array_slice(array_reverse($allTasks), 0, 8);
          foreach ($recent as $t):
            $od = isOverdue($t['Deadline'] ?? '', $t['Status'] ?? '');
          ?>
          <tr class="<?= $od ? 'table-danger' : '' ?>">
            <td>
              <div class="fw-500 small"><?= e($t['Task_Title'] ?? '') ?></div>
              <?php if (!empty($t['Description'])): ?>
              <div class="text-muted" style="font-size:.7rem">
                <?php
                  $desc = $t['Description'];
                  echo e(strlen($desc) > 60 ? substr($desc, 0, 60) . '…' : $desc);
                ?>
              </div>
              <?php endif; ?>
            </td>
            <td><?= priorityBadge($t['Priority'] ?? '') ?></td>
            <td class="small <?= $od ? 'text-danger fw-600' : '' ?>">
              <?= formatDate($t['Deadline'] ?? '') ?>
            </td>
            <td><?= statusBadge($t['Status'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent)): ?>
          <tr>
            <td colspan="4" class="text-center text-muted py-4">
              <i class="bi bi-inbox d-block fs-4 mb-1"></i>No tasks assigned yet
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Notifications ─────────────────────────────────────────── -->
<div class="card border-0 shadow-sm" id="notifSection">
  <div class="card-header fw-600">
    <i class="bi bi-bell me-2"></i>Notifications
    <?php if (count($unread) > 0): ?>
    <span class="badge bg-danger ms-1"><?= count($unread) ?></span>
    <?php endif; ?>
  </div>
  <div class="list-group list-group-flush">
    <?php
    $recentNotifs = array_slice(array_reverse($notifs), 0, 10);
    foreach ($recentNotifs as $n):
      $isNew = ($n['Read_Status'] ?? '') === 'unread';
      $icon  = match($n['Type'] ?? '') {
          'task_assigned'   => 'bi-list-task text-primary',
          'approval_result' => 'bi-check2-circle text-success',
          default           => 'bi-bell text-muted',
      };
    ?>
    <div class="list-group-item d-flex align-items-center gap-3 py-2 notif-item
         <?= $isNew ? 'notif-unread' : '' ?>">
      <i class="bi <?= $icon ?> fs-5 flex-shrink-0"></i>
      <div class="flex-fill">
        <div class="small <?= $isNew ? 'fw-600' : '' ?>"><?= e($n['Message'] ?? '') ?></div>
        <div class="text-muted" style="font-size:.7rem">
          <?= formatDate($n['Created_At'] ?? '') ?>
        </div>
      </div>
      <?php if ($isNew): ?>
      <span class="badge bg-primary rounded-pill flex-shrink-0">New</span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (empty($notifs)): ?>
    <div class="list-group-item text-center text-muted py-4">
      <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>No notifications yet
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($stats['total'] > 0): ?>
<script>
new Chart(document.getElementById('myTaskChart'), {
  type: 'doughnut',
  data: {
    labels: ['Approved', 'In Progress', 'Pending', 'Rejected'],
    datasets: [{
      data: [
        <?= $stats['approved'] ?>,
        <?= count(array_filter($allTasks, fn($t) => ($t['Status'] ?? '') === 'In Progress')) ?>,
        <?= count(array_filter($allTasks, fn($t) => ($t['Status'] ?? '') === 'Pending')) ?>,
        <?= $stats['rejected'] ?>
      ],
      backgroundColor: ['#4ade80', '#60a5fa', '#facc15', '#f87171'],
      borderColor: 'rgba(255,255,255,0.08)',
      borderWidth: 2,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { boxWidth: 10, padding: 10 }
      }
    }
  }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
