<?php
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = $id > 0 ? fetch_listing_by_id($id) : null;

if (!$listing) {
    set_flash('error', 'Listing not found.');
    redirect('browse.php');
}

$isOwner = is_logged_in() && (int)$listing['user_id'] === (int)$_SESSION['user_id'];
$wishlisted = is_logged_in() ? is_wishlisted((int)$_SESSION['user_id'], (int)$listing['id']) : false;
$existingReport = is_logged_in() && !$isOwner
    ? fetch_open_listing_report_for_user((int)$_SESSION['user_id'], (int)$listing['id'])
    : null;
$sellerLocation = trim(implode(', ', array_filter([$listing['city'] ?? '', $listing['country'] ?? ''])));

require_once __DIR__ . '/includes/header.php';
?>

<section class="detail-layout detail-layout--listing">
    <div class="detail-image-card">
        <img src="<?= listing_image($listing); ?>" alt="<?= e($listing['title']); ?>" class="detail-image">
    </div>

    <div class="detail-info-stack detail-info-stack--listing">
        <div class="detail-meta-strip">
            <div class="detail-meta-card">
                <strong>Category</strong>
                <span class="pill"><?= e($listing['category_name']); ?></span>
            </div>
            <div class="detail-meta-card">
                <strong>Type</strong>
                <span class="pill secondary"><?= e($listing['item_type']); ?></span>
            </div>
            <div class="detail-meta-card detail-meta-card--status">
                <strong>Status</strong>
                <span class="status-pill <?= $listing['status'] === 'Sold' ? 'sold' : 'active'; ?>">
                    <?= e($listing['status']); ?>
                </span>
            </div>
        </div>

        <div class="detail-main-card">
            <div>
                <span class="eyebrow">Listing overview</span>
                <h1><?= e($listing['title']); ?></h1>
                <p class="detail-price"><?= format_price($listing['price']); ?></p>
            </div>

            <div class="detail-spec-grid">
                <div class="detail-spec">
                    <strong>Condition</strong>
                    <span><?= e($listing['condition_label']); ?></span>
                </div>
                <div class="detail-spec">
                    <strong>Target university</strong>
                    <span><?= e($listing['university_target'] ?: $listing['university']); ?></span>
                </div>
                <div class="detail-spec">
                    <strong>Posted on</strong>
                    <span><?= date('d M Y', strtotime($listing['created_at'])); ?></span>
                </div>
                <div class="detail-spec">
                    <strong>Seller location</strong>
                    <span><?= e($sellerLocation !== '' ? $sellerLocation : 'Cyprus'); ?></span>
                </div>
            </div>

            <div>
                <h2>Description</h2>
                <p><?= nl2br(e($listing['description'])); ?></p>
            </div>

            <div class="detail-note">
                Meet on campus or another safe public spot, confirm the item condition together, and use verified profiles to build trust.
            </div>
        </div>

        <div class="detail-actions">
            <?php if ($isOwner): ?>
                <a class="btn btn-secondary" href="edit-listing.php?id=<?= (int)$listing['id']; ?>">Edit Listing</a>
                <form action="delete-listing.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="listing_id" value="<?= (int)$listing['id']; ?>">
                    <button type="submit" class="btn btn-danger js-confirm-delete">Delete Listing</button>
                </form>
            <?php elseif (is_logged_in()): ?>
                <form action="toggle-wishlist.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="listing_id" value="<?= (int)$listing['id']; ?>">
                    <button type="submit" class="btn <?= $wishlisted ? 'btn-secondary' : 'btn-primary'; ?>">
                        <?= $wishlisted ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                    </button>
                </form>
                <?php if ($existingReport): ?>
                    <span class="status-pill pending">Report submitted</span>
                    <a class="btn btn-ghost" href="report-listing.php?id=<?= (int)$listing['id']; ?>">View Report</a>
                <?php else: ?>
                    <a class="btn btn-ghost" href="report-listing.php?id=<?= (int)$listing['id']; ?>">Report Listing</a>
                <?php endif; ?>
            <?php else: ?>
                <a class="btn btn-primary" href="login.php">Log in to save listing</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-main-card detail-seller-card">
        <div class="detail-top">
            <h2 class="seller-card-title">Seller Information</h2>
            <?php if (!empty($listing['is_verified'])): ?>
                <span class="pill accent">Verified account</span>
            <?php endif; ?>
        </div>

        <div class="seller-highlights">
            <div class="seller-highlight">
                <strong><?= e($listing['full_name']); ?></strong>
                <span>@<?= e($listing['username'] ?: 'student-seller'); ?></span>
            </div>
            <div class="seller-highlight">
                <strong>University</strong>
                <span><?= e($listing['university']); ?></span>
            </div>
            <div class="seller-highlight">
                <strong>Location</strong>
                <span><?= e($sellerLocation !== '' ? $sellerLocation : 'Cyprus'); ?></span>
            </div>
            <div class="seller-highlight">
                <strong>Contact</strong>
                <span>
                    <?php if (is_logged_in()): ?>
                        <a class="text-link" href="mailto:<?= e($listing['email']); ?>"><?= e($listing['email']); ?></a>
                        <?php if (!empty($listing['phone'])): ?>
                            <br><?= e($listing['phone']); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <a class="text-link" href="login.php">Log in to view contact details</a>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="card-actions">
            <a class="btn btn-secondary" href="profile.php?id=<?= (int)$listing['user_id']; ?>">View Seller Profile</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
