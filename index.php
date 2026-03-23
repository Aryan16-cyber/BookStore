<?php
require_once 'includes/header.php';

// Stats
$totalBooks = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$totalAuthors = $conn->query("SELECT COUNT(DISTINCT author) as c FROM books")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];

// Featured books (8)
$featured = $conn->query("SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id ORDER BY b.sold DESC LIMIT 8");

// Bestsellers
$bestsellers = $conn->query("SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id ORDER BY b.sold DESC LIMIT 4");

// Categories
$cats = $conn->query("SELECT * FROM categories");
?>

<!-- HERO -->
<div class="hero">
  <div class="hero-text">
    <span class="hero-tag">📚 <?= $totalBooks ?>+ Books Available</span>
    <h1>Discover Your Next <em>Favourite</em> Story</h1>
    <p>From timeless classics to contemporary bestsellers — every page a new adventure. Browse, buy, and get books delivered to your doorstep.</p>
    <div class="hero-btns">
      <a class="btn-primary" href="shop.php">Browse Books</a>
      <a class="btn-outline" href="register.php">Join for Free</a>
    </div>
    <div class="hero-stats">
      <div><span class="stat-num"><?= $totalBooks ?>+</span><span class="stat-label">Books</span></div>
      <div><span class="stat-num"><?= $totalAuthors ?>+</span><span class="stat-label">Authors</span></div>
      <div><span class="stat-num"><?= $totalOrders ?>+</span><span class="stat-label">Orders</span></div>
      <div><span class="stat-num">4.8★</span><span class="stat-label">Rating</span></div>
    </div>
  </div>
  <div class="hero-bg-letter">B</div>
</div>

<!-- CATEGORIES -->
<div class="categories">
  <div class="cat-grid">
    <a class="cat-chip active" href="shop.php">📚 All Genres</a>
    <?php while($cat = $cats->fetch_assoc()): ?>
      <a class="cat-chip" href="shop.php?genre=<?= urlencode($cat['name']) ?>">
        <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
      </a>
    <?php endwhile; ?>
  </div>
</div>

<!-- FEATURED BOOKS -->
<div class="section">
  <div class="section-header">
    <h2 class="section-title">Featured <span>Books</span></h2>
    <a class="see-all" href="shop.php">See all →</a>
  </div>
  <div class="books-grid">
    <?php while($book = $featured->fetch_assoc()): ?>
      <?php include 'includes/book_card.php'; ?>
    <?php endwhile; ?>
  </div>
</div>

<!-- BESTSELLERS -->
<div class="page-section">
  <div class="section">
    <div class="section-header">
      <h2 class="section-title">🔥 Best<span>sellers</span></h2>
      <a class="see-all" href="shop.php?sort=sold">See all →</a>
    </div>
    <div class="books-grid">
      <?php while($book = $bestsellers->fetch_assoc()): ?>
        <?php include 'includes/book_card.php'; ?>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
