<?php
require_once __DIR__ . '/includes/header.php';

$viewedUserId = isset($_GET['id']) ? (int)$_GET['id'] : (is_logged_in() ? (int)$_SESSION['user_id'] : 0);

if ($viewedUserId <= 0) {
    set_flash('error', 'Please log in to view your profile.');
    redirect('login.php');
}

$errors = [];
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in() && (int)$_SESSION['user_id'] !== $viewedUserId) {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Please choose a rating between 1 and 5.';
    }

    if ($comment === '') {
        $errors[] = 'Please write a short review comment.';
    }

    if (!$errors) {
        $reviewerId = (int)$_SESSION['user_id'];
        $stmt = $mysqli->prepare("
            INSERT INTO reviews (reviewer_id, reviewed_user_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
        ");
        $stmt->bind_param('iiis', $reviewerId, $viewedUserId, $rating, $comment);

        if ($stmt->execute()) {
            $successMessage = 'Your review has been saved.';
        } else {
            $errors[] = 'Could not save your review.';
        }

        $stmt->close();
    }
}

$stmt = $mysqli->prepare("
    SELECT id, full_name, email, university, bio, created_at
    FROM users
    WHERE id = ?
");
$stmt->bind_param('i', $viewedUserId);
$stmt->execute();
$profileUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profileUser) {
    set_flash('error', 'User not found.');
    redirect('index.php');
}

$stmt = $mysqli->prepare("
    SELECT l.*, c.name AS category_name
    FROM listings l
    JOIN categories c ON c.id = l.category_id
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
");
$stmt->bind_param('i', $viewedUserId);
$stmt->execute();
$userListings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT r.*, u.full_name AS reviewer_name
    FROM reviews r
    JOIN users u ON u.id = r.reviewer_id
    WHERE r.reviewed_user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param('i', $viewedUserId);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE reviewed_user_id = ?");
$stmt->bind_param('i', $viewedUserId);
$stmt->execute();
$reviewStats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$isOwnProfile = is_logged_in() && (int)$viewedUserId === (int)$_SESSION['user_id'];
?>

<section class="profile-hero">
    <div class="profile-main-card">
        <span class="eyebrow"><?= $isOwnProfile ? 'Your account' : 'Seller profile'; ?></span>
        <h1><?= e($profileUser['full_name']); ?></h1>
        <div class="profile-meta">
            <span><?= e($profileUser['university']); ?></span>
            <span>Joined <?= date('M Y', strtotime($profileUser['created_at'])); ?></span>
        </div>
        <p><?= e($profileUser['bio'] ?: 'This user has not added a bio yet.'); ?></p>

        <?php if ($isOwnProfile): ?>
            <div class="profile-actions">
                <a class="btn btn-primary" href="create-listing.php">Create Listing</a>
                <a class="btn btn-secondary" href="browse.php">Browse Marketplace</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-side-card">
        <h3>Seller Rating</h3>
        <div class="rating-number">
            <?= $reviewStats['avg_rating'] ? number_format((float)$reviewStats['avg_rating'], 1) : '0.0'; ?>/5
        </div>
        <p><?= (int)$reviewStats['total_reviews']; ?> review(s)</p>
        <?php if (!$isOwnProfile && is_logged_in()): ?>
            <p class="muted-text">Bought something from this seller? Leave a rating below.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($successMessage): ?>
    <div class="alert alert-success"><?= e($successMessage); ?></div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Listings</span>
            <h2><?= $isOwnProfile ? 'Your Listings' : 'Posted Listings'; ?></h2>
        </div>
    </div>

    <div class="listing-grid">
        <?php if ($userListings): ?>
            <?php foreach ($userListings as $listing): ?>
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
                            <span><?= e($listing['status']); ?></span>
                            <div class="inline-actions">
                                <a class="text-link" href="listing.php?id=<?= (int)$listing['id']; ?>">View</a>
                                <?php if ($isOwnProfile): ?>
                                    <a class="text-link" href="edit-listing.php?id=<?= (int)$listing['id']; ?>">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No listings yet</h3>
                <p><?= $isOwnProfile ? 'Start by posting your first item or service.' : 'This user has not posted any listings yet.'; ?></p>
                <?php if ($isOwnProfile): ?>
                    <a class="btn btn-primary" href="create-listing.php">Create Listing</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Community trust</span>
            <h2>Reviews</h2>
        </div>
    </div>

    <?php if (!$isOwnProfile && is_logged_in()): ?>
        <form method="POST" class="form-card review-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating" required>
                        <option value="">Choose</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="comment">Comment</label>
                <textarea id="comment" name="comment" rows="4" placeholder="Describe your experience with the seller" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    <?php endif; ?>

    <div class="reviews-list">
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $review): ?>
                <article class="review-item">
                    <div class="review-top">
                        <strong><?= e($review['reviewer_name']); ?></strong>
                        <span><?= str_repeat('★', (int)$review['rating']); ?><?= str_repeat('☆', 5 - (int)$review['rating']); ?></span>
                    </div>
                    <p><?= e($review['comment']); ?></p>
                    <small><?= date('d M Y', strtotime($review['created_at'])); ?></small>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No reviews yet</h3>
                <p>Once users complete an exchange, they can leave feedback here.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
