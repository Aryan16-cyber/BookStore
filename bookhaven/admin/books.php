<?php
require_once 'header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title    = sanitize($conn, $_POST['title'] ?? '');
        $author   = sanitize($conn, $_POST['author'] ?? '');
        $c_id     = (int)($_POST['c_id'] ?? 0);
        $price    = (float)($_POST['price'] ?? 0);
        $origprice= (float)($_POST['original_price'] ?? 0);
        $stock    = (int)($_POST['stock'] ?? 0);
        $rating   = (float)($_POST['rating'] ?? 4.0);
        $desc     = sanitize($conn, $_POST['description'] ?? '');
        $pub      = sanitize($conn, $_POST['publisher'] ?? '');
        $icon     = sanitize($conn, $_POST['cover_icon'] ?? '📚');
        $bg       = sanitize($conn, $_POST['cover_bg'] ?? '#f5f5f5');
        $badge    = sanitize($conn, $_POST['badge'] ?? '');

        if (!$title || !$author || $price <= 0) {
            redirect('books.php', 'Title, Author and Price are required.', 'error');
        }
        $stmt = $conn->prepare("INSERT INTO books (title,author,c_id,price,original_price,stock,rating,description,publisher,cover_icon,cover_bg,badge) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssiidddsssss", $title,$author,$c_id,$price,$origprice,$stock,$rating,$desc,$pub,$icon,$bg,$badge);
        $stmt->execute();
        redirect('books.php', "Book \"$title\" added successfully! ✅");
    }

    if ($action === 'delete') {
        $id = (int)($_POST['book_id'] ?? 0);
        $conn->query("DELETE FROM books WHERE book_id=$id");
        redirect('books.php', 'Book deleted.');
    }

    if ($action === 'update') {
        $id    = (int)($_POST['book_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $badge = sanitize($conn, $_POST['badge'] ?? '');
        $conn->query("UPDATE books SET price=$price, stock=$stock, badge='$badge' WHERE book_id=$id");
        redirect('books.php', 'Book updated. ✅');
    }
}

$cats  = $conn->query("SELECT * FROM categories");
$books = $conn->query("SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id ORDER BY b.book_id DESC");
?>

<div class="admin-layout">
  <div class="admin-sidebar">
    <h3>⚙️ Admin Panel</h3>
    <a class="si" href="index.php"><span>📊</span> Dashboard</a>
    <a class="si active" href="books.php"><span>📚</span> Manage Books</a>
    <a class="si" href="orders.php"><span>📦</span> Orders</a>
    <a class="si" href="customers.php"><span>👥</span> Customers</a>
    <a class="si" href="categories.php"><span>🏷️</span> Categories</a>
    <a class="si" href="reports.php"><span>📈</span> Reports</a>
    <a class="si" href="../logout.php" style="margin-top:2rem;border-top:1px solid rgba(255,255,255,.1);"><span>🚪</span> Logout</a>
  </div>

  <div class="admin-content">
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">📚 Manage Books</h2>

    <!-- ADD BOOK FORM -->
    <div class="form-card">
      <h4>➕ Add New Book</h4>
      <form method="POST" action="books.php">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
          <div class="form-group"><label>Title *</label><input type="text" name="title" placeholder="Book title" required></div>
          <div class="form-group"><label>Author *</label><input type="text" name="author" placeholder="Author name" required></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Category</label>
            <select name="c_id" class="filter-sel" style="width:100%;">
              <?php $cats->data_seek(0); while($c=$cats->fetch_assoc()): ?>
                <option value="<?= $c['c_id'] ?>"><?= $c['icon'].' '.$c['name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group"><label>Publisher</label><input type="text" name="publisher" placeholder="Publisher name"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" placeholder="299" step="0.01" required></div>
          <div class="form-group"><label>Original Price (₹)</label><input type="number" name="original_price" placeholder="399" step="0.01"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Stock</label><input type="number" name="stock" placeholder="50" value="10"></div>
          <div class="form-group"><label>Rating (0-5)</label><input type="number" name="rating" placeholder="4.5" step="0.1" min="0" max="5" value="4.0"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Cover Icon (emoji)</label><input type="text" name="cover_icon" placeholder="📚" value="📚"></div>
          <div class="form-group"><label>Cover Background</label><input type="color" name="cover_bg" value="#f5f5f5"></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Badge</label>
            <select name="badge" class="filter-sel" style="width:100%;">
              <option value="">None</option>
              <option value="Bestseller">Bestseller</option>
              <option value="New">New</option>
              <option value="Hot">Hot</option>
              <option value="Classic">Classic</option>
            </select>
          </div>
          <div class="form-group"><label>Description</label><input type="text" name="description" placeholder="Brief description of the book"></div>
        </div>
        <button type="submit" class="admin-btn" style="padding:.7rem 1.8rem;font-size:.95rem;">➕ Add Book</button>
      </form>
    </div>

    <!-- BOOKS TABLE -->
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">All Books (<?= $books->num_rows ?>)</h3>
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Cover</th><th>Title</th><th>Author</th><th>Genre</th><th>Price</th><th>Stock</th><th>Sold</th><th>Badge</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php while($b = $books->fetch_assoc()): ?>
          <tr>
            <td><?= $b['book_id'] ?></td>
            <td style="font-size:1.5rem;background:<?= $b['cover_bg'] ?>;text-align:center;"><?= $b['cover_icon'] ?></td>
            <td><strong><?= htmlspecialchars($b['title']) ?></strong><br><small style="color:var(--muted);">⭐ <?= $b['rating'] ?></small></td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td><?= htmlspecialchars($b['genre'] ?? '-') ?></td>
            <td>₹<?= number_format($b['price'], 0) ?></td>
            <td>
              <span style="<?= $b['stock'] < 5 ? 'color:var(--rust);font-weight:700;' : '' ?>">
                <?= $b['stock'] ?><?= $b['stock'] < 5 ? ' ⚠️' : '' ?>
              </span>
            </td>
            <td><?= $b['sold'] ?></td>
            <td>
              <?php if($b['badge']): ?>
                <span class="badge-tag badge-<?= strtolower($b['badge']) ?>"><?= $b['badge'] ?></span>
              <?php else: echo '-'; endif; ?>
            </td>
            <td>
              <!-- Quick update inline form -->
              <form method="POST" action="books.php" style="display:flex;gap:.3rem;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                <input type="number" name="price" value="<?= $b['price'] ?>" style="width:65px;padding:.3rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;">
                <input type="number" name="stock" value="<?= $b['stock'] ?>" style="width:55px;padding:.3rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;">
                <select name="badge" style="padding:.3rem;border:1px solid var(--border);border-radius:6px;font-size:.78rem;">
                  <option value="" <?= !$b['badge']?'selected':'' ?>>-</option>
                  <option value="Bestseller" <?= $b['badge']==='Bestseller'?'selected':'' ?>>Bestseller</option>
                  <option value="New" <?= $b['badge']==='New'?'selected':'' ?>>New</option>
                  <option value="Hot" <?= $b['badge']==='Hot'?'selected':'' ?>>Hot</option>
                  <option value="Classic" <?= $b['badge']==='Classic'?'selected':'' ?>>Classic</option>
                </select>
                <button type="submit" class="admin-btn">Save</button>
              </form>
              <form method="POST" action="books.php" style="margin-top:.3rem;" onsubmit="return confirm('Delete this book?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                <button type="submit" class="admin-btn danger">🗑 Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
