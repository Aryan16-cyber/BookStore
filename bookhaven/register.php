<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = sanitize($conn, $_POST['first_name'] ?? '');
    $last  = sanitize($conn, $_POST['last_name']  ?? '');
    $email = sanitize($conn, $_POST['email']       ?? '');
    $phone = sanitize($conn, $_POST['phone']       ?? '');
    $addr  = sanitize($conn, $_POST['address']     ?? '');
    $pass  = $_POST['password'] ?? '';
    $cpass = $_POST['confirm_password'] ?? '';

    if (!$first || !$email || !$pass) {
        $error = 'Please fill in all required fields.';
    } elseif ($pass !== $cpass) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $exists = $conn->query("SELECT user_id FROM users WHERE email='$email'")->num_rows;
        if ($exists) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name,last_name,email,phone,address,password) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $first, $last, $email, $phone, $addr, $hash);
            $stmt->execute();
            $uid = $conn->insert_id;
            $_SESSION['user_id']    = $uid;
            $_SESSION['first_name'] = $first;
            $_SESSION['email']      = $email;
            $_SESSION['user_type']  = 'customer';
            redirect('index.php', "Welcome to BookHaven, $first! 🎉");
        }
    }
}
require_once 'includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box" style="max-width:520px;">
    <h2 class="auth-title">Create Account</h2>
    <p class="auth-subtitle">Join thousands of book lovers on BookHaven</p>

    <?php if($error): ?>
      <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="form-row">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" placeholder="Aryan" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" placeholder="Kumar" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Shipping Address</label>
        <input type="text" name="address" placeholder="123, Street, City, State" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Password * (min 6 chars)</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Confirm Password *</label>
          <input type="password" name="confirm_password" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="auth-submit">Create Account →</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
