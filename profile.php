<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$uid  = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $first = sanitize($conn, $_POST['first_name'] ?? '');
        $last  = sanitize($conn, $_POST['last_name']  ?? '');
        $phone = sanitize($conn, $_POST['phone']       ?? '');
        $addr  = sanitize($conn, $_POST['address']     ?? '');
        $conn->query("UPDATE users SET first_name='$first',last_name='$last',phone='$phone',address='$addr' WHERE user_id=$uid");
        $_SESSION['first_name'] = $first;
        redirect('profile.php', 'Profile updated successfully! ✅');
    }

    if ($action === 'change_password') {
        $old  = $_POST['old_password'] ?? '';
        $new  = $_POST['new_password'] ?? '';
        $conf = $_POST['confirm_password'] ?? '';
        if (!password_verify($old, $user['password'])) {
            redirect('profile.php', 'Current password is incorrect.', 'error');
        } elseif ($new !== $conf) {
            redirect('profile.php', 'Passwords do not match.', 'error');
        } elseif (strlen($new) < 6) {
            redirect('profile.php', 'Password must be at least 6 characters.', 'error');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hash' WHERE user_id=$uid");
            redirect('profile.php', 'Password changed successfully! 🔒');
        }
    }
}

// Orders
$orders = $conn->query("SELECT o.*, GROUP_CONCAT(b.title SEPARATOR ', ') as titles FROM orders o LEFT JOIN order_items oi ON o.order_id=oi.order_id LEFT JOIN books b ON oi.book_id=b.book_id WHERE o.user_id=$uid GROUP BY o.order_id ORDER BY o.order_date DESC");

require_once 'includes/header.php';
?>

<div class="profile-page">
  <!-- Profile Header -->
  <div class="profile-header">
    <div class="p-avatar"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></div>
    <div>
      <div class="p-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
      <div class="p-email">📧 <?= htmlspecialchars($user['email']) ?></div>
      <?php if($user['phone']): ?>
        <div style="color:#9a8a72;margin-top:.25rem;">📱 <?= htmlspecialchars($user['phone']) ?></div>
      <?php endif; ?>
      <div style="margin-top:.75rem;"><span style="background:var(--amber);color:var(--ink);padding:.2rem .8rem;border-radius:20px;font-size:.78rem;font-weight:700;">Customer</span></div>
    </div>
  </div>

  <!-- Update Profile -->
  <div class="p-section">
    <h3>✏️ Update Profile</h3>
    <form method="POST" action="profile.php">
      <input type="hidden" name="action" value="update_profile">
      <div class="form-row">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
      </div>
      <div class="form-group">
        <label>Shipping Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Your address">
      </div>
      <button type="submit" class="btn-primary">Save Changes</button>
    </form>
  </div>

  <!-- Change Password -->
  <div class="p-section">
    <h3>🔒 Change Password</h3>
    <form method="POST" action="profile.php">
      <input type="hidden" name="action" value="change_password">
      <div class="form-group">
        <label>Current Password</label>
        <input type="password" name="old_password" placeholder="••••••••" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>New Password</label>
          <input type="password" name="new_password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn-primary">Update Password</button>
    </form>
  </div>

  <!-- Order History -->
  <div class="p-section">
    <h3>📦 Order History</h3>
    <?php if($orders->num_rows === 0): ?>
      <p style="color:var(--muted);">No orders yet. <a href="shop.php" style="color:var(--amber);">Start shopping!</a></p>
    <?php else: ?>
      <?php while($ord = $orders->fetch_assoc()):
        $sc = match($ord['status']) { 'Delivered'=>'s-delivered', 'Shipped'=>'s-shipped', 'Cancelled'=>'s-cancelled', default=>'s-pending' };
      ?>
        <div class="order-row">
          <div>
            <div style="font-weight:700;">#<?= $ord['order_id'] ?></div>
            <div style="font-size:.82rem;color:var(--muted);max-width:380px;"><?= htmlspecialchars($ord['titles']) ?></div>
            <div style="font-size:.78rem;color:var(--muted);"><?= $ord['order_date'] ?> · <?= $ord['payment_method'] ?></div>
          </div>
          <div style="text-align:right;">
            <div style="font-weight:700;color:var(--amber);">₹<?= number_format($ord['total_amount'], 0) ?></div>
            <span class="status-badge <?= $sc ?>"><?= $ord['status'] ?></span>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <a class="btn-outline" href="logout.php" style="border-color:var(--rust);color:var(--rust);display:inline-block;margin-top:.5rem;">Sign Out</a>
</div>

<?php require_once 'includes/footer.php'; ?>
