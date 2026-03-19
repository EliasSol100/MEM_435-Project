<?php
require_once __DIR__ . '/includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$itemType = isset($_GET['type']) ? trim($_GET['type']) : '';

$sql = "
    SELECT l.*, c.name AS category_name, u.full_name
    FROM listings l
    JOIN categories c ON c.id = l.category_id
    JOIN users u ON u.id = l.user_id
    WHERE l.status = 'Active'
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $searchLike = '%' . $search . '%';
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= 'ss';
}

if ($categoryId > 0) {
    $sql .= " AND l.category_id = ?";
    $params[] = $categoryId;
    $types .= 'i';
}

if (in_array($itemType, ['Item', 'Notes', 'Service'], true)) {
    $sql .= " AND l.item_type = ?";
    $params[] = $itemType;
    $types .= 's';
}

$sql .= " ORDER BY l.created_at DESC";

$stmt = $mysqli->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$listings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = get_categories();
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Marketplace</span>
        <h1>Browse Listings</h1>
        <p>Search all active listings posted by students across Cyprus.</p>
    </div>
</section>

<section class="filter-card">
    <form method="GET" class="filter-grid">
        <div class="form-group">
            <label for="search">Search</label>
            <input id="search" type="text" name="search" value="<?= e($search); ?>" placeholder="Search books, notes, gadgets...">
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="0">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id']; ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : ''; ?>>
                        <?= e($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type">
                <option value="">All</option>
                <option value="Item" <?= $itemType === 'Item' ? 'selected' : ''; ?>>Item</option>
                <option value="Notes" <?= $itemType === 'Notes' ? 'selected' : ''; ?>>Notes</option>
                <option value="Service" <?= $itemType === 'Service' ? 'selected' : ''; ?>>Service</option>
            </select>
        </div>

        <div class="form-group action-group">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
    </form>
</section>

<div class="listing-grid">
    <?php if ($listings): ?>
        <?php foreach ($listings as $listing): ?>
            <article class="listing-card">
                <a class="listing-image-wrap" href="listing.php?id=<?= (int)$listing['id']; ?>">
                    <img src="<?= listing_image($listing); ?>" alt="<?= e($listing['title']); ?>" class="listing-image">
                </a>
                <div class="listing-body">
                    <div class="listing-meta">
                        <span class="pill"><?= e($listing['category_name']); ?></span>
                        <span class="price"><?= format_price($listing['price']); ?></span>
                    </div>
                    <h3><a href="listing.php?id=<?= (int)$listing['id']; ?>"><?= e($listing['title']); ?></a></h3>
                    <p><?= e(excerpt($listing['description'], 100)); ?></p>
                    <div class="card-footer">
                        <span><?= e($listing['full_name']); ?></span>
                        <a class="text-link" href="listing.php?id=<?= (int)$listing['id']; ?>">See details</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3>No matching listings found</h3>
            <p>Try a different search or category filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
