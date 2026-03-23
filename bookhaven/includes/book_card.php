<?php
// Partial: book card — expects $book array
$badgeClass = match(strtolower($book['badge'] ?? '')) {
    'bestseller' => 'badge-bestseller',
    'new'        => 'badge-new new-badge',
    'hot'        => 'badge-hot hot-badge',
    'classic'    => 'badge-classic',
    default      => ''
};
$discount = ($book['original_price'] > 0 && $book['original_price'] > $book['price'])
    ? round((1 - $book['price'] / $book['original_price']) * 100) : 0;
?>
<div class="book-card">
  <a href="book.php?id=<?= $book['book_id'] ?>">
    <div class="book-cover" style="background:<?= htmlspecialchars($book['cover_bg']) ?>;">
      <span><?= $book['cover_icon'] ?></span>
      <?php if($book['badge']): ?>
        <span class="book-badge <?= $badgeClass ?>"><?= htmlspecialchars($book['badge']) ?></span>
      <?php endif; ?>
    </div>
  </a>
  <div class="book-info">
    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
    <div style="margin-bottom:.5rem;">
      <span class="stars"><?= str_repeat('★', (int)$book['rating']) ?></span>
      <span style="color:var(--muted);font-size:.76rem;"> <?= $book['rating'] ?></span>
    </div>
    <div class="book-footer">
      <div class="book-price">
        ₹<?= number_format($book['price'], 0) ?>
        <?php if($discount > 0): ?>
          <small>₹<?= number_format($book['original_price'], 0) ?>
            <span class="discount-pct">-<?= $discount ?>%</span>
          </small>
        <?php endif; ?>
      </div>
      <form method="POST" action="cart.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
        <input type="hidden" name="quantity" value="1">
        <button type="submit" class="btn-add">+ Cart</button>
      </form>
    </div>
    <a class="btn-view" href="book.php?id=<?= $book['book_id'] ?>">View Details →</a>
  </div>
</div>
