<?php
require_once 'header.php';

// Delete customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $uid = (int)($_POST['user_id'] ?? 0);
    $conn->query("DELETE FROM users WHERE user_id=$uid AND user_type='customer'");
    redirect('customers.php', 'Customer removed.');
}

$customers = $conn->query("
    SELECT u.*, COUNT(o.order_id) AS order_count, COALESCE(SUM(o.total_amount),0) AS total_spent
    FROM users u
    LEFT JOIN orders o ON u.user_id=o.user_id
    WHERE u.user_type='customer'
    GROUP BY u.user_id
    ORDER BY u.created_at DESC");
?>

<div class="admin-layout">
  <div class="admin-sidebar">
    <h3>⚙️ Admin Panel</h3>
    <a class="si" href="index.php"><span>📊</span> Dashboard</a>
    <a class="si" href="books.php"><span>📚</span> Manage Books</a>
    <a class="si" href="orders.php"><span>📦</span> Orders</a>
    <a class="si active" href="customers.php"><span>👥</span> Customers</a>
    <a class="si" href="categories.php"><span>🏷️</span> Categories</a>
    <a class="si" href="reports.php"><span>📈</span> Reports</a>
    <a class="si" href="../logout.php" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,.1);"><span>🚪</span> Logout</a>
  </div>

  <div class="admin-content">
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">👥 Customers (<?= $customers->num_rows ?>)</h2>

    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Orders</th><th>Spent</th><th>Joined</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php if($customers->num_rows === 0): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:2.5rem;">No customers registered yet.</td></tr>
        <?php else: ?>
          <?php $i=1; while($c = $customers->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:.6rem;">
                  <div style="width:32px;height:32px;background:var(--amber);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:var(--ink);flex-shrink:0;">
                    <?= strtoupper(substr($c['first_name'],0,1)) ?>
                  </div>
                  <span><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></span>
                </div>
              </td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($c['email']) ?></td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
              <td style="font-size:.8rem;color:var(--muted);max-width:160px;"><?= htmlspecialchars($c['address'] ?? '-') ?></td>
              <td style="text-align:center;font-weight:700;"><?= $c['order_count'] ?></td>
              <td style="font-weight:700;color:var(--amber);">₹<?= number_format($c['total_spent'], 0) ?></td>
              <td style="font-size:.78rem;color:var(--muted);"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
              <td>
                <form method="POST" action="customers.php" onsubmit="return confirm('Remove this customer?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="user_id" value="<?= $c['user_id'] ?>">
                  <button type="submit" class="admin-btn danger">Remove</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
