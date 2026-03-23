<?php
require_once 'header.php';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id  = (int)($_POST['order_id'] ?? 0);
    $newStatus = sanitize($conn, $_POST['status'] ?? '');
    $allowed   = ['Pending','Shipped','Delivered','Cancelled'];
    if ($order_id && in_array($newStatus, $allowed)) {
        $conn->query("UPDATE orders SET status='$newStatus' WHERE order_id=$order_id");
        redirect('orders.php', "Order #$order_id status updated to $newStatus. ✅");
    }
}

// Filter
$filterStatus = sanitize($conn, $_GET['status'] ?? '');
$where = $filterStatus ? "WHERE o.status='$filterStatus'" : "";

$orders = $conn->query("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone,
           COUNT(oi.order_item_id) AS item_count
    FROM orders o
    JOIN users u ON o.user_id=u.user_id
    LEFT JOIN order_items oi ON o.order_id=oi.order_id
    $where
    GROUP BY o.order_id
    ORDER BY o.order_date DESC");
?>

<div class="admin-layout">
  <div class="admin-sidebar">
    <h3>⚙️ Admin Panel</h3>
    <a class="si" href="index.php"><span>📊</span> Dashboard</a>
    <a class="si" href="books.php"><span>📚</span> Manage Books</a>
    <a class="si active" href="orders.php"><span>📦</span> Orders</a>
    <a class="si" href="customers.php"><span>👥</span> Customers</a>
    <a class="si" href="categories.php"><span>🏷️</span> Categories</a>
    <a class="si" href="reports.php"><span>📈</span> Reports</a>
    <a class="si" href="../logout.php" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,.1);"><span>🚪</span> Logout</a>
  </div>

  <div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
      <h2 style="font-family:'Playfair Display',serif;">📦 All Orders</h2>
      <!-- Status Filter -->
      <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a href="orders.php" class="admin-btn" style="<?= !$filterStatus?'background:var(--ink);color:#fff;':'' ?>">All</a>
        <a href="orders.php?status=Pending"   class="admin-btn" style="<?= $filterStatus==='Pending'  ?'background:var(--rust);color:#fff;':'' ?>">Pending</a>
        <a href="orders.php?status=Shipped"   class="admin-btn" style="<?= $filterStatus==='Shipped'  ?'background:var(--forest);color:#fff;':'' ?>">Shipped</a>
        <a href="orders.php?status=Delivered" class="admin-btn" style="<?= $filterStatus==='Delivered'?'background:#155724;color:#fff;':'' ?>">Delivered</a>
        <a href="orders.php?status=Cancelled" class="admin-btn" style="<?= $filterStatus==='Cancelled'?'background:#6c757d;color:#fff;':'' ?>">Cancelled</a>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Customer</th><th>Items</th><th>Amount</th><th>Payment</th><th>Address</th><th>Date</th><th>Status</th><th>Update</th></tr>
      </thead>
      <tbody>
        <?php if($orders->num_rows === 0): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:2.5rem;">No orders found.</td></tr>
        <?php else: ?>
          <?php while($o = $orders->fetch_assoc()):
            $sc = match($o['status']) { 'Delivered'=>'s-delivered','Shipped'=>'s-shipped','Cancelled'=>'s-cancelled',default=>'s-pending' };

            // Get order items detail
            $items = $conn->query("SELECT oi.quantity, oi.price, b.title FROM order_items oi JOIN books b ON oi.book_id=b.book_id WHERE oi.order_id={$o['order_id']}");
          ?>
            <tr>
              <td><strong>#<?= $o['order_id'] ?></strong></td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></div>
                <div style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($o['email']) ?></div>
              </td>
              <td>
                <?php while($it=$items->fetch_assoc()): ?>
                  <div style="font-size:.8rem;">• <?= htmlspecialchars($it['title']) ?> ×<?= $it['quantity'] ?></div>
                <?php endwhile; ?>
              </td>
              <td><strong>₹<?= number_format($o['total_amount'], 0) ?></strong></td>
              <td style="font-size:.82rem;"><?= htmlspecialchars($o['payment_method']) ?></td>
              <td style="font-size:.78rem;color:var(--muted);max-width:150px;"><?= htmlspecialchars($o['shipping_addr'] ?? '-') ?></td>
              <td style="font-size:.78rem;color:var(--muted);"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
              <td><span class="status-badge <?= $sc ?>"><?= $o['status'] ?></span></td>
              <td>
                <form method="POST" action="orders.php">
                  <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                  <select name="status" class="filter-sel" style="font-size:.8rem;padding:.3rem .5rem;margin-bottom:.3rem;">
                    <option value="Pending"   <?= $o['status']==='Pending'  ?'selected':'' ?>>Pending</option>
                    <option value="Shipped"   <?= $o['status']==='Shipped'  ?'selected':'' ?>>Shipped</option>
                    <option value="Delivered" <?= $o['status']==='Delivered'?'selected':'' ?>>Delivered</option>
                    <option value="Cancelled" <?= $o['status']==='Cancelled'?'selected':'' ?>>Cancelled</option>
                  </select><br>
                  <button type="submit" class="admin-btn">Update</button>
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
