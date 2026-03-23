<?php
require_once 'header.php';

$totalBooks     = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) as c FROM users WHERE user_type='customer'")->fetch_assoc()['c'];
$totalOrders    = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalRevenue   = $conn->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE status!='Cancelled'")->fetch_assoc()['s'];
$deliveredOrders= $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Delivered'")->fetch_assoc()['c'];
$pendingOrders  = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Pending'")->fetch_assoc()['c'];
$cancelledOrders= $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Cancelled'")->fetch_assoc()['c'];

$topBooks = $conn->query("SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id ORDER BY b.sold DESC LIMIT 10");
$topCustomers = $conn->query("
    SELECT u.first_name, u.last_name, u.email, COUNT(o.order_id) AS orders, COALESCE(SUM(o.total_amount),0) AS spent
    FROM users u LEFT JOIN orders o ON u.user_id=o.user_id
    WHERE u.user_type='customer' GROUP BY u.user_id ORDER BY spent DESC LIMIT 10");

$salesByGenre = $conn->query("
    SELECT c.name AS genre, c.icon, COUNT(oi.order_item_id) AS items_sold, COALESCE(SUM(oi.price*oi.quantity),0) AS revenue
    FROM categories c
    LEFT JOIN books b ON c.c_id=b.c_id
    LEFT JOIN order_items oi ON b.book_id=oi.book_id
    GROUP BY c.c_id ORDER BY revenue DESC");

$monthlyRevenue = $conn->query("
    SELECT DATE_FORMAT(order_date,'%b %Y') AS month, COUNT(*) AS orders, SUM(total_amount) AS revenue
    FROM orders WHERE status!='Cancelled'
    GROUP BY YEAR(order_date), MONTH(order_date)
    ORDER BY order_date DESC LIMIT 6");
?>

<div class="admin-layout">
  <div class="admin-sidebar">
    <h3>⚙️ Admin Panel</h3>
    <a class="si" href="index.php"><span>📊</span> Dashboard</a>
    <a class="si" href="books.php"><span>📚</span> Manage Books</a>
    <a class="si" href="orders.php"><span>📦</span> Orders</a>
    <a class="si" href="customers.php"><span>👥</span> Customers</a>
    <a class="si" href="categories.php"><span>🏷️</span> Categories</a>
    <a class="si active" href="reports.php"><span>📈</span> Reports</a>
    <a class="si" href="../logout.php" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,.1);"><span>🚪</span> Logout</a>
  </div>

  <div class="admin-content">
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">📈 Reports & Analytics</h2>

    <!-- SUMMARY STATS -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:2rem;">
      <div class="stat-card"><div class="sc-icon">💰</div><div class="sc-val">₹<?= number_format($totalRevenue,0) ?></div><div class="sc-label">Total Revenue</div></div>
      <div class="stat-card"><div class="sc-icon">📦</div><div class="sc-val"><?= $totalOrders ?></div><div class="sc-label">Total Orders</div></div>
      <div class="stat-card"><div class="sc-icon">👥</div><div class="sc-val"><?= $totalCustomers ?></div><div class="sc-label">Customers</div></div>
      <div class="stat-card"><div class="sc-icon">📚</div><div class="sc-val"><?= $totalBooks ?></div><div class="sc-label">Books Listed</div></div>
    </div>

    <!-- ORDER STATUS BREAKDOWN -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem;">
      <div>
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Order Status Breakdown</h3>
        <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.5rem;">
          <?php
          $total = max(1, $totalOrders);
          foreach([['Delivered',$deliveredOrders,'#155724','#d4edda'],['Pending',$pendingOrders,'#856404','#fff3cd'],['Cancelled',$cancelledOrders,'#721c24','#f8d7da']] as [$label,$count,$color,$bg]):
            $pct = round($count/$total*100);
          ?>
            <div style="margin-bottom:1rem;">
              <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                <span style="font-weight:600;font-size:.88rem;"><?= $label ?></span>
                <span style="font-size:.85rem;color:var(--muted);"><?= $count ?> (<?= $pct ?>%)</span>
              </div>
              <div style="background:#e9ecef;border-radius:10px;height:10px;">
                <div style="background:<?= $color ?>;width:<?= $pct ?>%;height:10px;border-radius:10px;transition:width .5s;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- MONTHLY REVENUE -->
      <div>
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Monthly Revenue</h3>
        <table class="data-table">
          <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
          <tbody>
            <?php if($monthlyRevenue->num_rows === 0): ?>
              <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:1.5rem;">No data yet</td></tr>
            <?php else: ?>
              <?php while($m=$monthlyRevenue->fetch_assoc()): ?>
                <tr>
                  <td><?= $m['month'] ?></td>
                  <td><?= $m['orders'] ?></td>
                  <td><strong>₹<?= number_format($m['revenue'],0) ?></strong></td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SALES BY GENRE -->
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Sales by Genre</h3>
    <table class="data-table" style="margin-bottom:2rem;">
      <thead><tr><th>Genre</th><th>Items Sold</th><th>Revenue</th></tr></thead>
      <tbody>
        <?php while($g=$salesByGenre->fetch_assoc()): ?>
          <tr>
            <td><?= $g['icon'] ?> <?= htmlspecialchars($g['genre']) ?></td>
            <td><?= $g['items_sold'] ?></td>
            <td><strong>₹<?= number_format($g['revenue'],0) ?></strong></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- TOP BOOKS -->
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Top 10 Selling Books</h3>
    <table class="data-table" style="margin-bottom:2rem;">
      <thead><tr><th>#</th><th>Cover</th><th>Title</th><th>Author</th><th>Genre</th><th>Price</th><th>Stock</th><th>Sold</th></tr></thead>
      <tbody>
        <?php $r=1; while($b=$topBooks->fetch_assoc()): ?>
          <tr>
            <td><?= $r++ ?></td>
            <td style="text-align:center;font-size:1.4rem;background:<?= $b['cover_bg'] ?>"><?= $b['cover_icon'] ?></td>
            <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td><?= htmlspecialchars($b['genre'] ?? '-') ?></td>
            <td>₹<?= number_format($b['price'],0) ?></td>
            <td><?= $b['stock'] ?></td>
            <td><strong style="color:var(--amber);"><?= $b['sold'] ?></strong></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- TOP CUSTOMERS -->
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Top Customers</h3>
    <table class="data-table">
      <thead><tr><th>#</th><th>Customer</th><th>Email</th><th>Orders</th><th>Total Spent</th></tr></thead>
      <tbody>
        <?php if($topCustomers->num_rows===0): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:1.5rem;">No customer data yet.</td></tr>
        <?php else: ?>
          <?php $r=1; while($c=$topCustomers->fetch_assoc()): ?>
            <tr>
              <td><?= $r++ ?></td>
              <td><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($c['email']) ?></td>
              <td><?= $c['orders'] ?></td>
              <td><strong style="color:var(--amber);">₹<?= number_format($c['spent'],0) ?></strong></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
