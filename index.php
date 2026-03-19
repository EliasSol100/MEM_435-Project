<?php
require_once __DIR__ . '/includes/header.php';

$featuredListings = [];
$result = $mysqli->query("
    SELECT l.*, c.name AS category_name, u.full_name
    FROM listings l
    JOIN categories c ON c.id = l.category_id
    JOIN users u ON u.id = l.user_id
    WHERE l.status = 'Active'
    ORDER BY l.created_at DESC
    LIMIT 6
");

while ($row = $result->fetch_assoc()) {
    $featuredListings[] = $row;
}
?>

<section class="hero">
    <div class="hero-copy">
        <span class="badge">Student Marketplace • Cyprus</span>
        <h1>Buy, sell, and share student essentials with ease.</h1>
        <p>UniTrade CY connects university students across Cyprus for books, notes, gadgets, and services in one trusted platform.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="browse.php">Explore Listings</a>
            <a class="btn btn-secondary" href="<?= is_logged_in() ? 'create-listing.php' : 'register.php'; ?>">
                <?= is_logged_in() ? 'Post a Listing' : 'Join Now'; ?>
            </a>
        </div>
    </div>
    <div class="hero-card">
        <div class="metric-card">
            <strong>Secure community</strong>
            <span>Student-focused buying and selling</span>
        </div>
        <div class="metric-card">
            <strong>Affordable choices</strong>
            <span>Find second-hand items and useful resources</span>
        </div>
        <div class="metric-card">
            <strong>Simple workflow</strong>
            <span>Register, post, browse, and connect</span>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Categories</span>
            <h2>Everything students actually need</h2>
        </div>
    </div>

    <div class="feature-grid">
        <article class="feature-card">
            <h3>Books & Notes</h3>
            <p>Sell textbooks, lab notes, summaries, and exam prep materials.</p>
        </article>
        <article class="feature-card">
            <h3>Tech & Gadgets</h3>
            <p>Trade laptops, calculators, tablets, and accessories at student-friendly prices.</p>
        </article>
        <article class="feature-card">
            <h3>Student Services</h3>
            <p>Offer tutoring, design help, coding support, and project-related services.</p>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Latest listings</span>
            <h2>Fresh posts from the community</h2>
        </div>
        <a class="text-link" href="browse.php">View all</a>
    </div>

    <div class="listing-grid">
        <?php if ($featuredListings): ?>
            <?php foreach ($featuredListings as $listing): ?>
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
                <h3>No listings yet</h3>
                <p>Create the first listing and start building the marketplace.</p>
                <a class="btn btn-primary" href="<?= is_logged_in() ? 'create-listing.php' : 'register.php'; ?>">Get Started</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">How it works</span>
            <h2>Simple student-to-student trading</h2>
        </div>
    </div>

    <div class="steps-grid">
        <div class="step-card">
            <span class="step-number">1</span>
            <h3>Create an account</h3>
            <p>Register with your university and build your student profile.</p>
        </div>
        <div class="step-card">
            <span class="step-number">2</span>
            <h3>Post or browse</h3>
            <p>List your items in minutes or search for what you need.</p>
        </div>
        <div class="step-card">
            <span class="step-number">3</span>
            <h3>Connect safely</h3>
            <p>Contact the seller, arrange the exchange, and leave a review.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
