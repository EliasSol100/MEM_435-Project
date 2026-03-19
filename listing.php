<?php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = $id > 0 ? fetch_listing_by_id($id) : null;

if (!$listing) {
    set_flash('error', 'Listing not found.');
    redirect('browse.php');
}

$isOwner = is_logged_in() && (int)$listing['user_id'] === (int)$_SESSION['user_id'];
$wishlisted = is_logged_in() ? is_wishlisted((int)$_SESSION['user_id'], (int)$listing['id']) : false;
?>

<section class="detail-layout">
    <div class="detail-image-card">
        <img src="<?= listing_image($listing); ?>" alt="<?= e($listing['title']); ?>" class="detail-image">
    </div>

    <div class="detail-content">
        <div class="detail-top">
            <span class="pill"><?= e($listing['category_name']); ?></span>
            <span class="pill secondary"><?= e($listing['item_type']); ?></span>
            <span class="status-pill <?= $listing['status'] === 'Sold' ? 'sold' : 'active'; ?>">
                <?= e($listing['status']); ?>
            </span>
        </div>

        <h1><?= e($listing['title']); ?></h1>
        <p class="detail-price"><?= format_price($listing['price']); ?></p>

        <div class="detail-specs">
            <div><strong>Condition:</strong> <?= e($listing['condition_label']); ?></div>
            <div><strong>University:</strong> <?= e($listing['university_target'] ?: $listing['university']); ?></div>
            <div><strong>Posted on:</strong> <?= date('d M Y', strtotime($listing['created_at'])); ?></div>
        </div>

        <div class="detail-description">
            <h3>Description</h3>
            <p><?= nl2br(e($listing['description'])); ?></p>
        </div>

        <div class="seller-card">
            <h3>Seller Information</h3>
            <p><strong>Name:</strong> <?= e($listing['full_name']); ?></p>
            <p><strong>University:</strong> <?= e($listing['university']); ?></p>
            <p><strong>Contact:</strong>
                <?php if (is_logged_in()): ?>
                    <a class="text-link" href="mailto:<?= e($listing['email']); ?>"><?= e($listing['email']); ?></a>
                <?php else: ?>
                    <a class="text-link" href="login.php">Log in to view contact details</a>
                <?php endif; ?>
            </p>
            <p><a class="text-link" href="profile.php?id=<?= (int)$listing['user_id']; ?>">View seller profile</a></p>
        </div>

        <div class="detail-actions">
            <?php if ($isOwner): ?>
                <a class="btn btn-secondary" href="edit-listing.php?id=<?= (int)$listing['id']; ?>">Edit Listing</a>
                <a class="btn btn-danger js-confirm-delete" href="delete-listing.php?id=<?= (int)$listing['id']; ?>">Delete</a>
            <?php elseif (is_logged_in()): ?>
                <form action="toggle-wishlist.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="listing_id" value="<?= (int)$listing['id']; ?>">
                    <button type="submit" class="btn <?= $wishlisted ? 'btn-secondary' : 'btn-primary'; ?>">
                        <?= $wishlisted ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                    </button>
                </form>
            <?php else: ?>
                <a class="btn btn-primary" href="login.php">Log in to save listing</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
