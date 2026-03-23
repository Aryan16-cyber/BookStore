<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = sanitize($conn, $_POST['name'] ?? '');
        $icon = sanitize($conn, $_POST['icon'] ?? '📚');
        if (!$name) { redirect('categories.php', 'Category name is required.', 'error'); }
        $conn->query("INSERT INTO categories (name, icon) VALUES ('$name', '$icon')");
        redirect('categories.php', "Category \"$name\" added! ✅");
    }

    if ($action === 'delete') {
        $id = (int)($_POST['c_id'] ?? 0);
        // Set books in this category to NULL
        $conn->query("UPDATE books SET c_id=NULL WHERE c_id=$id");
        $conn->query("DELETE FROM categories WHERE c_id=$id");
        redirect('categories.php', 'Category deleted.');
    }
}

$cats = $conn->query("
    SELECT c.*, COUNT(b.book_id) AS book_count
    FROM categories c LEFT JOIN books b ON c.c_id=b.c_id
    GROUP BY c.c_id ORDER BY c.c_id");
?>

<div class="admin-layout">
  <div class="admin-sidebar">
    <h3>⚙️ Admin Panel</h3>
    <a class="si" href="index.php"><span>📊</span> Dashboard</a>
    <a class="si" href="books.php"><span>📚</span> Manage Books</a>
    <a class="si" href="orders.php"><span>📦</span> Orders</a>
    <a class="si" href="customers.php"><span>👥</span> Customers</a>
    <a class="si active" href="categories.php"><span>🏷️</span> Categories</a>
    <a class="si" href="reports.php"><span>📈</span> Reports</a>
    <a class="si" href="../logout.php" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,.1);"><span>🚪</span> Logout</a>
  </div>

  <div class="admin-content">
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">🏷️ Categories</h2>

    <!-- ADD CATEGORY -->
    <div class="form-card" style="max-width:480px;">
      <h4>➕ Add New Category</h4>
      <form method="POST" action="categories.php">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
          <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="name" placeholder="e.g. Romance" required>
          </div>
          <div class="form-group">
            <label>Icon (emoji)</label>
            <input type="text" name="icon" placeholder="💕" value="📚">
          </div>
        </div>
        <button type="submit" class="admin-btn" style="padding:.7rem 1.6rem;">➕ Add Category</button>
      </form>
    </div>

    <!-- CATEGORIES LIST -->
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">All Categories</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;">
      <?php while($c = $cats->fetch_assoc()): ?>
        <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.4rem;display:flex;flex-direction:column;gap:.8rem;">
          <div style="font-size:2.5rem;text-align:center;"><?= $c['icon'] ?></div>
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($c['name']) ?></div>
            <div style="color:var(--muted);font-size:.82rem;"><?= $c['book_count'] ?> book<?= $c['book_count']!=1?'s':'' ?></div>
          </div>
          <form method="POST" action="categories.php" onsubmit="return confirm('Delete this category?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="c_id" value="<?= $c['c_id'] ?>">
            <button type="submit" class="admin-btn danger" style="width:100%;">🗑 Delete</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
