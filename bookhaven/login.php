<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['user_type']  = $user['user_type'];
            $dest = ($user['user_type'] === 'admin') ? 'admin/index.php' : 'index.php';
            redirect($dest, 'Welcome back, ' . $user['first_name'] . '! 👋');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
require_once 'includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <h2 class="auth-title">Welcome Back</h2>
    <p class="auth-subtitle">Sign in to your BookHaven account</p>

    <?php if($error): ?>
      <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
        <div class="forgot-link"><a href="#">Forgot password?</a></div>
      </div>
      <button type="submit" class="auth-submit">Sign In →</button>
    </form>

    <div style="margin-top:1.5rem;padding:1rem;background:var(--cream);border-radius:10px;font-size:.83rem;color:var(--muted);">
      <small>Register a new account to shop as a customer.</small>
    </div>

    <p class="auth-switch">Don't have an account? <a href="register.php">Create one free</a></p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
