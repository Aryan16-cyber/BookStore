<?php
require_once 'header.php';

$totalBooks     = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$totalOrders    = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) as c FROM users WHERE user_type='customer'")->fetch_assoc()['c'];
$totalRevenue   = $conn->query("SELECT SUM(total_amount) as s FROM orders WHERE status!='Cancelled'")->fetch_assoc()['s'] ?? 0;
$pendingOrders  = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Pending'")->fetch_assoc()['c'];
$lowStock       = $conn->query("SELECT COUNT(*) as c FROM books WHERE stock < 5")->fetch_assoc()['c'];

$recentOrders = $conn->query("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o JOIN users u ON o.user_id=u.user_id
    ORDER BY o.order_date DESC LIMIT 8");

$topBooks = $conn->query("SELECT * FROM books ORDER BY sold DESC LIMIT 5");
?>

<div class="admin-layout">
  <!-- SIDEBAR -->
  <div class="admin-sidebar">
    <h3>⚙️ Admin Panel</h3>
    <a class="si active" href="index.php"><span>📊</span> Dashboard</a>
    <a class="si" href="books.php"><span>📚</span> Manage Books</a>
    <a class="si" href="orders.php"><span>📦</span> Orders</a>
    <a class="si" href="customers.php"><span>👥</span> Customers</a>
    <a class="si" href="categories.php"><span>🏷️</span> Categories</a>
    <a class="si" href="reports.php"><span>📈</span> Reports</a>
    <a class="si" href="../logout.php" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,.1);"><span>🚪</span> Logout</a>
  </div>

  <!-- CONTENT -->
  <div class="admin-content">
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">Dashboard Overview</h2>

    <!-- STAT CARDS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="sc-icon">📚</div>
        <div class="sc-val"><?= $totalBooks ?></div>
        <div class="sc-label">Total Books</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon">📦</div>
        <div class="sc-val"><?= $totalOrders ?></div>
        <div class="sc-label">Total Orders</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon">👥</div>
        <div class="sc-val"><?= $totalCustomers ?></div>
        <div class="sc-label">Customers</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon">💰</div>
        <div class="sc-val">₹<?= number_format($totalRevenue, 0) ?></div>
        <div class="sc-label">Revenue</div>
      </div>
    </div>

    <!-- ALERT BADGES -->
    <div style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
      <?php if($pendingOrders > 0): ?>
        <div style="background:#fff3cd;color:#856404;padding:.6rem 1.2rem;border-radius:10px;font-weight:600;font-size:.88rem;">
          ⏳ <?= $pendingOrders ?> Pending Order<?= $pendingOrders>1?'s':'' ?>
          <a href="orders.php?status=Pending" style="color:#856404;text-decoration:underline;margin-left:.5rem;">View</a>
        </div>
      <?php endif; ?>
      <?php if($lowStock > 0): ?>
        <div style="background:#f8d7da;color:#721c24;padding:.6rem 1.2rem;border-radius:10px;font-weight:600;font-size:.88rem;">
          ⚠️ <?= $lowStock ?> Book<?= $lowStock>1?'s':'' ?> Low on Stock
          <a href="books.php" style="color:#721c24;text-decoration:underline;margin-left:.5rem;">View</a>
        </div>
      <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:2rem;align-items:start;">
      <!-- RECENT ORDERS -->
      <div>
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Recent Orders</h3>
        <table class="data-table">
          <thead>
            <tr><th>#</th><th>Customer</th><th>Amount</th><th>Date</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
            <?php if($recentOrders->num_rows === 0): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">No orders yet</td></tr>
            <?php else: ?>
              <?php while($o = $recentOrders->fetch_assoc()):
                $sc = match($o['status']) { 'Delivered'=>'s-delivered', 'Shipped'=>'s-shipped', 'Cancelled'=>'s-cancelled', default=>'s-pending' };
              ?>
                <tr>
                  <td><strong>#<?= $o['order_id'] ?></strong></td>
                  <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
                  <td>₹<?= number_format($o['total_amount'], 0) ?></td>
                  <td style="font-size:.8rem;color:var(--muted);"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                  <td><span class="status-badge <?= $sc ?>"><?= $o['status'] ?></span></td>
                  <td><a href="orders.php?id=<?= $o['order_id'] ?>" class="admin-btn">View</a></td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <a href="orders.php" style="color:var(--amber);font-size:.85rem;font-weight:600;display:block;margin-top:.75rem;">View all orders →</a>
      </div>

      <!-- TOP BOOKS -->
      <div>
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Top Selling Books</h3>
        <?php while($b = $topBooks->fetch_assoc()): ?>
          <div style="display:flex;align-items:center;gap:.9rem;padding:.8rem 0;border-bottom:1px solid var(--border);">
            <div style="font-size:1.8rem;background:<?= $b['cover_bg'] ?>;padding:.5rem;border-radius:8px;"><?= $b['cover_icon'] ?></div>
            <div style="flex:1;">
              <div style="font-weight:600;font-size:.88rem;"><?= htmlspecialchars($b['title']) ?></div>
              <div style="color:var(--muted);font-size:.78rem;"><?= $b['sold'] ?> sold · Stock: <?= $b['stock'] ?></div>
            </div>
            <div style="font-weight:700;color:var(--amber);">₹<?= number_format($b['price'], 0) ?></div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
