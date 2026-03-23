<?php
require_once 'includes/header.php';

$id   = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id WHERE b.book_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) { redirect('shop.php', 'Book not found.', 'error'); }

$discount = ($book['original_price'] > 0 && $book['original_price'] > $book['price'])
    ? round((1 - $book['price'] / $book['original_price']) * 100) : 0;

// Related books
$cid      = (int)$book['c_id'];
$related  = $conn->query("SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id WHERE b.c_id=$cid AND b.book_id!=$id LIMIT 4");
?>

<div class="book-detail-page">
  <div class="breadcrumb">
    <a href="index.php">Home</a> › <a href="shop.php">Shop</a> › <?= htmlspecialchars($book['title']) ?>
  </div>

  <div class="book-detail-grid">
    <!-- COVER -->
    <div>
      <div class="detail-cover" style="background:<?= $book['cover_bg'] ?>">
        <span><?= $book['cover_icon'] ?></span>
      </div>
      <div style="margin-top:1rem;background:var(--cream);border-radius:12px;padding:1rem;font-size:.85rem;color:var(--muted);">
        <div style="margin-bottom:.4rem;">📦 <strong>Publisher:</strong> <?= htmlspecialchars($book['publisher']) ?></div>
        <div style="margin-bottom:.4rem;">📊 <strong>Stock:</strong> <?= $book['stock'] ?> copies</div>
        <div style="margin-bottom:.4rem;">⭐ <strong>Rating:</strong> <?= $book['rating'] ?>/5</div>
        <div>🏷️ <strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></div>
      </div>
    </div>

    <!-- INFO -->
    <div>
      <h1 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;line-height:1.2;margin-bottom:.4rem;">
        <?= htmlspecialchars($book['title']) ?>
      </h1>
      <p style="color:var(--muted);font-size:1rem;margin-bottom:.75rem;">by <strong><?= htmlspecialchars($book['author']) ?></strong></p>
      <div class="detail-meta">
        <span class="meta-tag">📚 <?= htmlspecialchars($book['genre']) ?></span>
        <span class="meta-tag">⭐ <?= $book['rating'] ?>/5</span>
        <?php if($book['badge']): ?>
          <span class="meta-tag">🏷️ <?= htmlspecialchars($book['badge']) ?></span>
        <?php endif; ?>
        <span class="meta-tag">🛒 <?= $book['sold'] ?> sold</span>
      </div>
      <div class="detail-price">
        ₹<?= number_format($book['price'], 0) ?>
        <?php if($discount > 0): ?>
          <small>₹<?= number_format($book['original_price'], 0) ?></small>
          <span class="discount-pct">Save <?= $discount ?>%</span>
        <?php endif; ?>
      </div>
      <p class="detail-desc"><?= nl2br(htmlspecialchars($book['description'])) ?></p>

      <?php if($book['stock'] > 0): ?>
        <form method="POST" action="cart.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
          <div class="qty-control">
            <button type="button" class="qty-btn" data-action="dec">−</button>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $book['stock'] ?>" style="width:55px;text-align:center;border:2px solid var(--border);border-radius:8px;padding:.35rem;font-size:.95rem;">
            <button type="button" class="qty-btn" data-action="inc">+</button>
            <span style="color:var(--muted);font-size:.85rem;">(<?= $book['stock'] ?> in stock)</span>
          </div>
          <div class="detail-actions">
            <button type="submit" class="btn-cart-big">🛒 Add to Cart</button>
            <a href="cart.php" class="btn-cart-big" style="background:var(--forest);text-align:center;">Buy Now</a>
          </div>
        </form>
      <?php else: ?>
        <div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:10px;font-weight:600;">
          ❌ Out of Stock — Check back later.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RELATED BOOKS -->
  <?php if($related->num_rows > 0): ?>
    <div style="margin-top:3rem;">
      <h2 class="section-title" style="margin-bottom:1.5rem;">You May Also <span>Like</span></h2>
      <div class="books-grid">
        <?php while($book = $related->fetch_assoc()): ?>
          <?php include 'includes/book_card.php'; ?>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
