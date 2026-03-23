<?php
require_once 'includes/header.php';

$search = sanitize($conn, $_GET['q'] ?? '');
$genre  = sanitize($conn, $_GET['genre'] ?? '');
$sort   = sanitize($conn, $_GET['sort'] ?? 'default');

$where = "WHERE 1=1";
if ($search) $where .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%')";
if ($genre)  $where .= " AND c.name = '$genre'";

$orderBy = match($sort) {
    'price-asc'  => "b.price ASC",
    'price-desc' => "b.price DESC",
    'rating'     => "b.rating DESC",
    'title'      => "b.title ASC",
    'sold'       => "b.sold DESC",
    default      => "b.book_id DESC"
};

$sql = "SELECT b.*, c.name AS genre FROM books b LEFT JOIN categories c ON b.c_id=c.c_id $where ORDER BY $orderBy";
$result = $conn->query($sql);
$count  = $result->num_rows;

$cats = $conn->query("SELECT * FROM categories");
?>

<!-- SEARCH BAR -->
<form method="GET" action="shop.php">
  <div class="search-bar">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" id="searchInput" name="q" placeholder="Search books, authors…"
             value="<?= htmlspecialchars($search) ?>">
    </div>
    <select class="filter-sel" name="genre" onchange="this.form.submit()">
      <option value="">All Genres</option>
      <?php $cats->data_seek(0); while($cat=$cats->fetch_assoc()): ?>
        <option value="<?= $cat['name'] ?>" <?= $genre===$cat['name']?'selected':'' ?>>
          <?= $cat['icon'].' '.$cat['name'] ?>
        </option>
      <?php endwhile; ?>
    </select>
    <select class="filter-sel" name="sort" onchange="this.form.submit()">
      <option value="default" <?= $sort==='default'?'selected':'' ?>>Sort By</option>
      <option value="price-asc"  <?= $sort==='price-asc'?'selected':'' ?>>Price: Low → High</option>
      <option value="price-desc" <?= $sort==='price-desc'?'selected':'' ?>>Price: High → Low</option>
      <option value="rating"     <?= $sort==='rating'?'selected':'' ?>>Top Rated</option>
      <option value="sold"       <?= $sort==='sold'?'selected':'' ?>>Bestsellers</option>
      <option value="title"      <?= $sort==='title'?'selected':'' ?>>A – Z</option>
    </select>
    <button type="submit" class="btn-search">Search</button>
  </div>
</form>

<!-- CATEGORY CHIPS -->
<div class="categories">
  <div class="cat-grid">
    <a class="cat-chip <?= !$genre?'active':'' ?>" href="shop.php">📚 All</a>
    <?php $cats->data_seek(0); while($cat=$cats->fetch_assoc()): ?>
      <a class="cat-chip <?= $genre===$cat['name']?'active':'' ?>" href="shop.php?genre=<?= urlencode($cat['name']) ?>">
        <?= $cat['icon'].' '.htmlspecialchars($cat['name']) ?>
      </a>
    <?php endwhile; ?>
  </div>
</div>

<div class="section">
  <div class="section-header">
    <h2 class="section-title">All <span>Books</span></h2>
    <span style="color:var(--muted);font-size:.9rem;"><?= $count ?> book<?= $count!==1?'s':'' ?> found</span>
  </div>
  <?php if($count === 0): ?>
    <div class="no-results">😔 No books found. <a href="shop.php" style="color:var(--amber);">Clear filters</a></div>
  <?php else: ?>
    <div class="books-grid">
      <?php while($book = $result->fetch_assoc()): ?>
        <?php include 'includes/book_card.php'; ?>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
