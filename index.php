<?php
// ============================================================
//  Login Page
// ============================================================
require_once __DIR__ . '/includes/functions.php';
startSession();

if (isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? '/admin/dashboard.php' : '/employee/dashboard.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if (!$username || !$password) {
        $error = 'Please enter username and password.';
    } else {
        try {
            require_once __DIR__ . '/api/DataService.php';
            $sheets = getDataService();
            $user   = $sheets->findOne(SHEET_USERS, 'Username', $username);

            if ($user && password_verify($password, $user['Password_Hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']      = $user['User_ID'];
                $_SESSION['username']     = $user['Username'];
                $_SESSION['name']         = $user['Name'];
                $_SESSION['designation']  = $user['Designation'];
                $_SESSION['employee_id']  = $user['Employee_ID'];
                $_SESSION['email']        = $user['Email'];
                $_SESSION['_last_active'] = time();
                $_SESSION['_regen_at']    = time();

                // Validate redirect param — only allow same-origin paths
                $dest = $_GET['redirect'] ?? '';
                if (!$dest || !str_starts_with($dest, '/') || str_starts_with($dest, '//')) {
                    $dest = in_array($user['Designation'], ADMIN_ROLES)
                        ? BASE_URL . '/admin/dashboard.php'
                        : BASE_URL . '/employee/dashboard.php';
                }
                redirect($dest);
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'System error. Please try again or contact administrator.';
            error_log($e->getMessage());
        }
    }
}

$timeout = isset($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — <?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet"> -->
</head>
<body>
<div class="auth-page">
  <div class="auth-card">

    <!-- Header -->
    <div class="auth-header">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="TBI-MCE" class="auth-logo"
           onerror="this.style.display='none'">
      <div class="auth-title">TBI – MCE Hassan</div>
      <div class="auth-sub">Technology Business Incubator · Malnad College of Engineering</div>
    </div>

    <!-- Body -->
    <div class="auth-body">
      <h5 class="fw-600 mb-4 text-center" style="color:var(--t2)">Sign in to Task Manager</h5>

      <?php if ($timeout): ?>
        <div class="alert alert-warning py-2">
          <i class="bi bi-clock me-1"></i>Session expired. Please sign in again.
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2">
          <i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="username"
                   value="<?= e($_POST['username'] ?? '') ?>"
                   placeholder="Enter your username" required autofocus>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password"
                   placeholder="Enter your password" required id="pwdField">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd()">
              <i class="bi bi-eye" id="pwdEye"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
          <a href="<?= BASE_URL ?>/forgot_password.php" class="small" style="color:var(--accent)">
            Forgot password?
          </a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-600">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>
      </form>

      <hr class="my-4">
      <div class="text-center small text-muted">
        <i class="bi bi-shield-check me-1" style="color:var(--c-success)"></i>
        Secure login · Session-encrypted · CSRF-protected
      </div>

      <!-- Dev credentials hint — REMOVE IN PRODUCTION -->
      <!-- <div class="alert alert-info mt-3 py-2 small">
        <strong>Demo Credentials:</strong><br>
        CEO: <code>geetha</code> / <code>Admin@123</code> &nbsp;|&nbsp;
        COO: <code>mohana</code> / <code>Admin@123</code><br>
        Software: <code>darshan</code> / <code>Employee@123</code>
      </div> -->
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd() {
  const f = document.getElementById('pwdField');
  const e = document.getElementById('pwdEye');
  if (f.type === 'password') { f.type = 'text';     e.className = 'bi bi-eye-slash'; }
  else                       { f.type = 'password'; e.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
