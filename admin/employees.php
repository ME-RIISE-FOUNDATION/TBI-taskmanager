<?php
// ============================================================
//  Admin — Employee Management
//  Bug fixes: usort ?:0 → ===false, broken HTML, input validation
// ============================================================
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../api/DataService.php';

$sheets    = getDataService();
$employees = $sheets->getAll(SHEET_EMPLOYEES);
$tasks     = $sheets->getAll(SHEET_TASKS);
$users     = $sheets->getAll(SHEET_USERS);

// ── Handle Add Employee ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_employee') {
        $empId       = generateId('EMP');
        $name        = trim($_POST['name']        ?? '');
        $designation = $_POST['designation']      ?? '';
        $email       = trim($_POST['email']       ?? '');
        $phone       = trim($_POST['phone']       ?? '');
        $photoUrl    = trim($_POST['photo_url']   ?? '');
        $username    = trim($_POST['username']    ?? '');
        $password    = $_POST['password']         ?? '';

        // Whitelist designation
        if (!in_array($designation, DESIGNATIONS, true)) $designation = '';

        if (!$name || !$designation || !$email || !$username || !$password) {
            setFlash('danger', 'All required fields must be filled.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('danger', 'Invalid email address.');
        } elseif (strlen($password) < 8) {
            setFlash('danger', 'Password must be at least 8 characters.');
        } elseif ($sheets->findOne(SHEET_USERS, 'Username', $username)) {
            setFlash('danger', 'Username already exists.');
        } else {
            $sheets->appendRecord(SHEET_EMPLOYEES, [
                'Employee_ID' => $empId,
                'Name'        => $name,
                'Designation' => $designation,
                'Email'       => $email,
                'Phone'       => $phone,
                'Photo_URL'   => $photoUrl,
                'Status'      => 'Active',
            ]);
            $userId  = generateId('USR');
            $pwdHash = password_hash($password, PASSWORD_BCRYPT);
            $sheets->appendRecord(SHEET_USERS, [
                'User_ID'      => $userId,
                'Username'     => $username,
                'Password_Hash'=> $pwdHash,
                'Designation'  => $designation,
                'Employee_ID'  => $empId,
                'Email'        => $email,
                'Name'         => $name,
                'Reset_Token'  => '',
                'Reset_Expiry' => '',
            ]);
            setFlash('success', "Employee $name added successfully.");
        }
        redirect(BASE_URL . '/admin/employees.php');
    }

    if ($action === 'reset_password') {
        $userId = $_POST['user_id']     ?? '';
        $newPwd = $_POST['new_password']?? '';
        if ($userId && strlen($newPwd) >= 8) {
            $hash = password_hash($newPwd, PASSWORD_BCRYPT);
            $sheets->updateById(SHEET_USERS, 'User_ID', $userId, ['Password_Hash' => $hash]);
            setFlash('success', 'Password reset successfully.');
        } else {
            setFlash('danger', 'Password must be at least 8 characters.');
        }
        redirect(BASE_URL . '/admin/employees.php');
    }
}

// Build user lookup by employee_id
$userByEmp = [];
foreach ($users as $u) $userByEmp[$u['Employee_ID'] ?? ''] = $u;

// Fixed usort: use strict === false check so index 0 ("CEO") sorts correctly
$order = ['CEO','COO','Software Associate','Finance Associate','Innovation Associate','Supporting Staff'];
usort($employees, function($a, $b) use ($order) {
    $ai = array_search($a['Designation'] ?? '', $order);
    $bi = array_search($b['Designation'] ?? '', $order);
    $ai = $ai !== false ? $ai : 99;
    $bi = $bi !== false ? $bi : 99;
    return $ai <=> $bi;
});

$pageTitle = 'Employee Management';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0">
    <i class="bi bi-people-fill me-2 text-primary"></i>Employee Management
  </h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmpModal">
    <i class="bi bi-person-plus me-1"></i>Add Employee
  </button>
</div>

<div class="row g-3">
  <?php foreach ($employees as $emp):
    $stats    = employeeStats($tasks, $emp['Employee_ID'] ?? '');
    $user     = $userByEmp[$emp['Employee_ID'] ?? ''] ?? null;
    $initials = substr(implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', $emp['Name'] ?? 'U'))), 0, 2);
  ?>
  <div class="col-12 col-md-6 col-xl-4">
    <?php $empTasksUrl = BASE_URL . '/admin/tasks.php?employee=' . urlencode($emp['Employee_ID'] ?? ''); ?>
    <!-- div instead of <a> — prevents invalid nested <a> which breaks layout -->
    <div class="emp-card"
         onclick="window.location='<?= $empTasksUrl ?>'"
         role="link" tabindex="0"
         onkeydown="if(event.key==='Enter')window.location='<?= $empTasksUrl ?>'">
      <div class="emp-card-header">
        <?php if (!empty($emp['Photo_URL'])): ?>
          <img src="<?= e($emp['Photo_URL']) ?>" class="emp-avatar" alt="<?= e($emp['Name'] ?? '') ?>">
        <?php else: ?>
          <div class="emp-avatar-initials"><?= e($initials) ?></div>
        <?php endif; ?>
        <div>
          <div class="emp-name"><?= e($emp['Name'] ?? '') ?></div>
          <div class="emp-desig"><?= e($emp['Designation'] ?? '') ?></div>
          <div style="font-size:.68rem" class="text-muted mt-1"><?= e($emp['Email'] ?? '') ?></div>
        </div>
      </div>

      <div class="row g-2 text-center mb-3">
        <div class="col-3">
          <div class="emp-stat-val text-primary"><?= $stats['total'] ?></div>
          <div class="emp-stat-lbl">Total</div>
        </div>
        <div class="col-3">
          <div class="emp-stat-val text-success"><?= $stats['completed'] ?></div>
          <div class="emp-stat-lbl">Done</div>
        </div>
        <div class="col-3">
          <div class="emp-stat-val text-warning"><?= $stats['pending'] ?></div>
          <div class="emp-stat-lbl">Pending</div>
        </div>
        <div class="col-3">
          <div class="emp-stat-val text-danger"><?= $stats['overdue'] ?></div>
          <div class="emp-stat-lbl">Overdue</div>
        </div>
      </div>

      <div class="mb-3">
        <div class="d-flex justify-content-between mb-1">
          <small class="text-muted">Approval Rate</small>
          <small class="fw-600"><?= $stats['approvalPct'] ?>%</small>
        </div>
        <div class="progress" style="height:5px">
          <div class="progress-bar bg-<?= $stats['approvalPct'] >= 70 ? 'success' : ($stats['approvalPct'] >= 40 ? 'warning' : 'danger') ?>"
               style="width:<?= $stats['approvalPct'] ?>%"></div>
        </div>
      </div>

      <div class="d-flex gap-2" onclick="event.stopPropagation()">
        <a href="<?= $empTasksUrl ?>"
           class="btn btn-sm btn-outline-primary flex-fill">
          <i class="bi bi-list-task me-1"></i>Tasks
        </a>
        <?php if ($user): ?>
        <button class="btn btn-sm btn-outline-warning"
                onclick="event.stopPropagation(); openResetPwd('<?= e($user['User_ID']) ?>','<?= e($emp['Name'] ?? '') ?>')"
                title="Reset Password">
          <i class="bi bi-key"></i>
        </button>
        <?php endif; ?>
      </div>

      <?php if ($user): ?>
      <div class="emp-card-footer">
        <i class="bi bi-person-circle me-1"></i>Login: <code><?= e($user['Username'] ?? '') ?></code>
      </div>
      <?php endif; ?>

    </div><!-- /emp-card -->
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Add Employee Modal ─────────────────────────────────────── -->
<div class="modal fade" id="addEmpModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-700"><i class="bi bi-person-plus me-2"></i>Add New Employee</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="action"     value="add_employee">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-500">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Designation <span class="text-danger">*</span></label>
              <select class="form-select" name="designation" required>
                <option value="">Select…</option>
                <?php foreach (DESIGNATIONS as $d): ?>
                <option value="<?= $d ?>"><?= $d ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Phone</label>
              <input type="tel" class="form-control" name="phone" placeholder="+91 XXXXX XXXXX">
            </div>
            <div class="col-12">
              <label class="form-label fw-500">Photo URL</label>
              <input type="url" class="form-control" name="photo_url" placeholder="https://…">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Username <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="username" required autocomplete="off">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="password" required
                     minlength="8" placeholder="Min 8 characters" autocomplete="new-password">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Reset Password Modal ───────────────────────────────────── -->
<div class="modal fade" id="resetPwdModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content border-0">
      <div class="modal-header">
        <h6 class="modal-title fw-700">Reset Password</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
        <input type="hidden" name="action"      value="reset_password">
        <input type="hidden" name="user_id"     id="resetUserId">
        <div class="modal-body">
          <p class="small text-muted mb-2">
            Reset password for <strong id="resetEmpName"></strong>
          </p>
          <input type="password" class="form-control" name="new_password"
                 minlength="8" required placeholder="New password (min 8 chars)">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-warning">Reset</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openResetPwd(userId, empName) {
  document.getElementById('resetUserId').value      = userId;
  document.getElementById('resetEmpName').textContent = empName;
  new bootstrap.Modal(document.getElementById('resetPwdModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
