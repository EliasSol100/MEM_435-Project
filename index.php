<?php
require_once __DIR__ . '/includes/header.php';

$featuredListings = [];
$marketStats = [
    'active_listings' => 0,
    'student_sellers' => 0,
    'categories' => 0,
];

$statsResult = $mysqli->query("
    SELECT
        (SELECT COUNT(*) FROM listings WHERE status = 'Active') AS active_listings,
        (SELECT COUNT(DISTINCT user_id) FROM listings) AS student_sellers,
        (SELECT COUNT(*) FROM categories) AS categories
");

if ($statsResult) {
    $marketStats = $statsResult->fetch_assoc() ?: $marketStats;
    $statsResult->free();
}

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
        <div class="hero-stats">
            <div class="stat-chip">
                <strong><?= (int)$marketStats['active_listings']; ?></strong>
                <span>active listings ready to browse</span>
            </div>
            <div class="stat-chip">
                <strong><?= (int)$marketStats['student_sellers']; ?></strong>
                <span>student sellers already participating</span>
            </div>
            <div class="stat-chip">
                <strong><?= (int)$marketStats['categories']; ?></strong>
                <span>categories covering books, tech, and services</span>
            </div>
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
            <span class="eyebrow">Customer Focus</span>
            <h2>Built around the students who actually use it</h2>
        </div>
        <a class="text-link" href="about.php">See business overview</a>
    </div>

    <div class="feature-grid">
        <article class="feature-card">
            <h3>Student buyers</h3>
            <p>Affordable access to textbooks, notes, gadgets, and project help without searching across scattered social posts.</p>
        </article>
        <article class="feature-card">
            <h3>Student sellers</h3>
            <p>A cleaner place to turn unused items into cash with better structure, visibility, and seller identity.</p>
        </article>
        <article class="feature-card">
            <h3>Student service providers</h3>
            <p>Tutors, designers, and technical helpers can reach students looking for practical academic support.</p>
        </article>
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

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Business Plan Support</span>
            <h2>The website now supports the business case too</h2>
        </div>
    </div>

    <div class="profile-details-grid">
        <article class="info-card">
            <h3>Business overview</h3>
            <p>See the problem, opportunity, value proposition, customer segments, positioning, operations, and growth roadmap behind UniTrade CY.</p>
            <a class="btn btn-secondary" href="about.php">Open About Page</a>
        </article>
        <article class="info-card">
            <h3>Trust and risk management</h3>
            <p>Review current safety controls, platform risks, and the next trust-building steps planned for stronger rollout.</p>
            <a class="btn btn-secondary" href="trust-safety.php">Open Safety Page</a>
        </article>
        <article class="info-card">
            <h3>Go-to-market and outreach</h3>
            <p>Explore how UniTrade CY can communicate with students, build partnerships, and support early market adoption.</p>
            <a class="btn btn-secondary" href="contact.php">Open Contact Page</a>
        </article>
        <article class="info-card">
            <h3>Prototype status</h3>
            <p>The product side is already tangible: registration, verification, listing management, browsing, profiles, reviews, and wishlist flows.</p>
            <a class="btn btn-secondary" href="register.php">Try the Product</a>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="cta-banner">
        <div>
            <span class="eyebrow">Ready to get started?</span>
            <h2><?= is_logged_in() ? 'Turn your unused student essentials into your next sale.' : 'Create your student marketplace account in minutes.'; ?></h2>
            <p><?= is_logged_in() ? 'Post a listing, save what you like, and keep your marketplace profile polished.' : 'Build your profile, verify your email, and start browsing or selling with a cleaner flow.'; ?></p>
        </div>
        <div class="card-actions">
            <a class="btn btn-primary" href="<?= is_logged_in() ? 'create-listing.php' : 'register.php'; ?>">
                <?= is_logged_in() ? 'Create Listing' : 'Register Now'; ?>
            </a>
            <a class="btn btn-ghost" href="browse.php">Browse Marketplace</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
