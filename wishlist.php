<?php
require_once __DIR__ . '/includes/header.php';
require_login();

$userId = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("
    SELECT l.*, c.name AS category_name, u.full_name
    FROM wishlists w
    JOIN listings l ON l.id = w.listing_id
    JOIN categories c ON c.id = l.category_id
    JOIN users u ON u.id = l.user_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Saved items</span>
        <h1>Your Wishlist</h1>
        <p>Keep track of listings you want to revisit later.</p>
    </div>
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
            <h3>Your wishlist is empty</h3>
            <p>Browse listings and save the ones you like.</p>
            <a class="btn btn-primary" href="browse.php">Browse Listings</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
