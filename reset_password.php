<?php
// ============================================================
//  Reset Password
//  Security fix: compare SHA-256 hash of URL token against DB
// ============================================================
require_once __DIR__ . '/includes/functions.php';
startSession();

require_once __DIR__ . '/api/DataService.php';
$sheets = getDataService();

$rawToken = trim($_GET['token'] ?? '');
$error    = '';
$success  = '';
$user     = null;

if ($rawToken) {
    $hashedToken = hash('sha256', $rawToken);
    foreach ($sheets->getAll(SHEET_USERS) as $u) {
        if (($u['Reset_Token'] ?? '') === $hashedToken) {
            $expiry = $u['Reset_Expiry'] ?? '';
            if ($expiry && strtotime($expiry) > time()) {
                $user = $u;
            }
            break;
        }
    }
}

if (!$rawToken || !$user) {
    $error = 'Invalid or expired reset link. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $pwd1 = $_POST['password']  ?? '';
    $pwd2 = $_POST['password2'] ?? '';

    if (strlen($pwd1) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($pwd1 !== $pwd2) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($pwd1, PASSWORD_BCRYPT);
        $sheets->updateById(SHEET_USERS, 'User_ID', $user['User_ID'], [
            'Password_Hash' => $hash,
            'Reset_Token'   => '',
            'Reset_Expiry'  => '',
        ]);
        $success = 'Password updated successfully! You can now login.';
        $user = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — <?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">

    <div class="auth-header">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="TBI-MCE" class="auth-logo"
           onerror="this.style.display='none'">
      <div class="auth-title">Reset Password</div>
      <div class="auth-sub">Technology Business Incubator – MCE Hassan</div>
    </div>

    <div class="auth-body">
      <?php if ($error):  ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
        </a>
      <?php elseif ($user): ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="password"
                   minlength="8" required placeholder="Min 8 characters">
          </div>
          <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="password2"
                   minlength="8" required placeholder="Repeat password">
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-shield-check me-2"></i>Update Password
          </button>
        </form>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/forgot_password.php" class="btn btn-primary w-100">
          <i class="bi bi-arrow-clockwise me-2"></i>Request New Reset Link
        </a>
      <?php endif; ?>

      <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/index.php" class="small text-muted">
          <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
      </div>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
