<?php
// ============================================================
//  Employee — Profile
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../api/DataService.php';

$sheets  = getDataService();
$myEmpId = $_SESSION['employee_id'];
$emp     = $sheets->findOne(SHEET_EMPLOYEES, 'Employee_ID', $myEmpId);
$user    = $sheets->findOne(SHEET_USERS,     'Employee_ID', $myEmpId);
$tasks   = $sheets->findMany(SHEET_TASKS,    'Employee_ID', $myEmpId);
$stats   = employeeStats($tasks, $myEmpId);

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new1    = $_POST['new_password']     ?? '';
        $new2    = $_POST['confirm_password'] ?? '';

        if (!$user || !password_verify($current, $user['Password_Hash'] ?? '')) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new1) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new1 !== $new2) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($new1, PASSWORD_BCRYPT);
            $sheets->updateById(SHEET_USERS, 'User_ID', $user['User_ID'], ['Password_Hash' => $hash]);
            $success = 'Password changed successfully.';
        }
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';

$initials = substr(implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', $emp['Name'] ?? 'U'))), 0, 2);
?>

<h4 class="fw-700 mb-4">
  <i class="bi bi-person-circle me-2 text-primary"></i>My Profile
</h4>

<div class="row g-4">

  <!-- Profile Card -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center">
      <div class="card-body py-4">

        <?php if (!empty($emp['Photo_URL'])): ?>
          <img src="<?= e($emp['Photo_URL']) ?>" alt="Profile"
               class="rounded-circle mb-3" width="100" height="100"
               style="object-fit:cover;border:3px solid var(--glass-bd2)">
        <?php else: ?>
          <div class="emp-avatar-initials mx-auto mb-3"
               style="width:100px;height:100px;font-size:1.8rem">
            <?= e($initials) ?>
          </div>
        <?php endif; ?>

        <h5 class="fw-700 mb-1"><?= e($emp['Name'] ?? '') ?></h5>
        <div class="text-muted mb-1"><?= e($emp['Designation'] ?? '') ?></div>
        <div class="text-muted small"><?= e($emp['Email'] ?? '') ?></div>
        <?php if (!empty($emp['Phone'])): ?>
        <div class="text-muted small"><?= e($emp['Phone']) ?></div>
        <?php endif; ?>

        <hr class="my-3">

        <div class="row g-2 text-center mb-3">
          <div class="col-4">
            <div class="fw-700 text-primary"><?= $stats['total'] ?></div>
            <div class="text-muted" style="font-size:.7rem">Total</div>
          </div>
          <div class="col-4">
            <div class="fw-700 text-success"><?= $stats['approved'] ?></div>
            <div class="text-muted" style="font-size:.7rem">Approved</div>
          </div>
          <div class="col-4">
            <div class="fw-700 text-purple"><?= $stats['approvalPct'] ?>%</div>
            <div class="text-muted" style="font-size:.7rem">Rate</div>
          </div>
        </div>

        <div class="progress" style="height:7px">
          <div class="progress-bar bg-<?= $stats['approvalPct'] >= 70 ? 'success' : ($stats['approvalPct'] >= 40 ? 'warning' : 'danger') ?>"
               style="width:<?= $stats['approvalPct'] ?>%"></div>
        </div>
        <div class="text-muted small mt-1">Approval Rate</div>

      </div>
    </div>
  </div>

  <!-- Details -->
  <div class="col-md-8">

    <!-- Employee Info -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header fw-600">
        <i class="bi bi-info-circle me-2"></i>Employee Information
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="text-muted small">Employee ID</div>
            <div class="fw-500"><?= e($emp['Employee_ID'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted small">Designation</div>
            <div class="fw-500"><?= e($emp['Designation'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted small">Email</div>
            <div class="fw-500"><?= e($emp['Email'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted small">Phone</div>
            <div class="fw-500"><?= e($emp['Phone'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted small">Username</div>
            <div class="fw-500"><code><?= e($user['Username'] ?? '—') ?></code></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Password -->
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-600">
        <i class="bi bi-shield-lock me-2"></i>Change Password
      </div>
      <div class="card-body">
        <?php if ($error):   ?><div class="alert alert-danger  py-2 small"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 small"><?= e($success) ?></div><?php endif; ?>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action"     value="change_password">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small">Current Password</label>
              <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label small">New Password</label>
              <input type="password" class="form-control" name="new_password"
                     minlength="8" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label small">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password"
                     minlength="8" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary mt-3">Update Password</button>
        </form>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
