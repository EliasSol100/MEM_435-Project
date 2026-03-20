<?php
require_once __DIR__ . '/includes/header.php';

$segments = [
    [
        'title' => 'Student buyers',
        'text' => 'Students looking for affordable textbooks, gadgets, notes, and trusted campus-to-campus exchanges.'
    ],
    [
        'title' => 'Student sellers',
        'text' => 'Students who want a cleaner way to sell unused items instead of relying on scattered chats and social posts.'
    ],
    [
        'title' => 'Student service providers',
        'text' => 'Tutors, designers, coders, and other students who want to promote useful academic services.'
    ],
];

$businessModel = [
    [
        'title' => 'Launch pricing',
        'text' => 'UniTrade CY launches as a free marketplace to reduce friction and build a strong student user base first.'
    ],
    [
        'title' => 'Planned revenue streams',
        'text' => 'Future monetization can include promoted listings, premium seller tools, partnership packages, and selected student-service visibility boosts.'
    ],
    [
        'title' => 'Positioning',
        'text' => 'The platform is positioned as a Cyprus-focused student marketplace that is simpler, more trustworthy, and more relevant than broad second-hand platforms.'
    ],
];

$marketInsights = [
    [
        'title' => 'Launch scope',
        'text' => 'The first addressable market is university students across Cyprus, with early focus on campus communities where books, notes, and student services move frequently.'
    ],
    [
        'title' => 'Customer behavior',
        'text' => 'Students are price-sensitive, mobile-first, and likely to exchange through peers when the process feels simple, relevant, and trustworthy.'
    ],
    [
        'title' => 'Immediate need',
        'text' => 'Academic materials, second-hand gadgets, and project-related services create recurring demand throughout the semester.'
    ],
];

$operations = [
    [
        'title' => 'Key platform activities',
        'text' => 'User onboarding, email verification, listing creation, browsing, wishlist management, seller profiles, and community reviews.'
    ],
    [
        'title' => 'Resources and infrastructure',
        'text' => 'PHP, MySQL, front-end templates, email delivery through PHPMailer, shared hosting, and a structured student marketplace database.'
    ],
    [
        'title' => 'Target partners',
        'text' => 'University communities, student societies, campus ambassadors, and project-related communities that can help drive trusted early adoption.'
    ],
];

$competitiveContext = [
    [
        'title' => 'Main alternatives today',
        'text' => 'Students currently rely on informal channels like chat groups, social posts, and broad classified platforms that are not built around campus needs.'
    ],
    [
        'title' => 'Competitive advantage',
        'text' => 'UniTrade CY focuses only on student-relevant categories, clearer profiles, cleaner listing flow, and a trust-first experience tailored to Cyprus.'
    ],
    [
        'title' => 'Why this positioning matters',
        'text' => 'A more focused platform can feel easier to trust, easier to search, and more useful for repeat student exchanges than generic alternatives.'
    ],
];

$financialSnapshot = [
    [
        'title' => 'Revenue model',
        'text' => 'Launch free to grow usage first, then test promoted listings, premium tools, and partnership-based revenue once traction is proven.'
    ],
    [
        'title' => 'Key costs',
        'text' => 'Core costs include hosting, maintenance, email infrastructure, continued product development, moderation support, and launch marketing.'
    ],
    [
        'title' => 'Funding need',
        'text' => 'The MVP can operate lean, but growth-stage funding would help with outreach, moderation, stronger verification, and feature expansion.'
    ],
    [
        'title' => '1-2 year outlook',
        'text' => 'Year 1 focuses on adoption and trust. Year 2 focuses on denser marketplace activity, stronger partnerships, and careful monetization tests.'
    ],
];

$roadmap = [
    [
        'step' => '1',
        'title' => 'Strengthen the Cyprus launch',
        'text' => 'Focus on books, notes, gadgets, and student services while building awareness across core Cyprus universities.'
    ],
    [
        'step' => '2',
        'title' => 'Improve trust and moderation',
        'text' => 'Add stronger reporting, moderation workflows, and more student-specific verification options.'
    ],
    [
        'step' => '3',
        'title' => 'Scale partnerships and monetization',
        'text' => 'Expand through student communities, referral loops, and premium marketplace features once usage is established.'
    ],
];
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Business Overview</span>
        <h1>Why UniTrade CY Exists</h1>
        <p>UniTrade CY is a student marketplace built for Cyprus. It turns a scattered reselling problem into a cleaner, more trustworthy digital marketplace with clearer profiles, better listing structure, and student-focused discovery.</p>
    </div>
</section>

<section class="results-summary">
    <div>
        <span class="eyebrow">Executive summary</span>
        <h2>A focused marketplace for student essentials and services</h2>
        <p class="section-copy">The idea responds to a practical student problem: useful items and academic services are available, but discovery and trust are fragmented across informal chats and social channels. UniTrade CY offers a more structured, campus-relevant solution.</p>
    </div>
    <div class="results-meta">
        <div class="mini-stat">
            <strong>Problem</strong>
            <span>student reselling is scattered and inconsistent</span>
        </div>
        <div class="mini-stat">
            <strong>Solution</strong>
            <span>a student-focused marketplace with cleaner flows</span>
        </div>
        <div class="mini-stat">
            <strong>Stage</strong>
            <span>working MVP / prototype ready for demonstration</span>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Problem & Opportunity</span>
            <h2>The market gap UniTrade CY addresses</h2>
        </div>
    </div>

    <div class="feature-grid">
        <article class="info-card">
            <h3>Fragmented student selling</h3>
            <p>Students often buy and sell through social media posts, private messages, or small group chats. Those channels are noisy, temporary, and hard to search.</p>
        </article>
        <article class="info-card">
            <h3>Trust is hard to assess</h3>
            <p>Without proper profiles, verification, and listing structure, buyers struggle to know who they are dealing with and whether a listing is reliable.</p>
        </article>
        <article class="info-card">
            <h3>Cyprus-specific focus is missing</h3>
            <p>Broad marketplaces are not designed around university life in Cyprus. UniTrade CY creates a more relevant experience around student needs and local exchange behavior.</p>
        </article>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Value Proposition</span>
            <h2>How UniTrade CY creates value</h2>
        </div>
    </div>

    <div class="profile-details-grid">
        <article class="info-card">
            <h3>For buyers</h3>
            <p>Browse organized listings by category, see seller profiles, compare prices more easily, and find student-relevant items faster.</p>
        </article>
        <article class="info-card">
            <h3>For sellers</h3>
            <p>Create a structured listing, manage availability, build trust through a profile, and reach students looking for exactly these categories.</p>
        </article>
        <article class="info-card">
            <h3>For service providers</h3>
            <p>Offer tutoring, design support, coding help, and other project-related services inside a marketplace that feels connected to student demand.</p>
        </article>
        <article class="info-card">
            <h3>For the business</h3>
            <p>Build a campus-centered platform with room to scale through partnerships, premium listing options, and stronger university trust signals over time.</p>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Customer Segments</span>
            <h2>Who the platform is built for</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($segments as $segment): ?>
            <article class="feature-card">
                <h3><?= e($segment['title']); ?></h3>
                <p><?= e($segment['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Market Insight</span>
            <h2>How the early market behaves</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($marketInsights as $insight): ?>
            <article class="info-card">
                <h3><?= e($insight['title']); ?></h3>
                <p><?= e($insight['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Mission & Objectives</span>
            <h2>What UniTrade CY is trying to achieve first</h2>
        </div>
    </div>

    <div class="steps-grid">
        <article class="step-card">
            <span class="step-number">1</span>
            <h3>Make student exchange easier</h3>
            <p>Reduce friction for buying, selling, and offering small student services across Cyprus.</p>
        </article>
        <article class="step-card">
            <span class="step-number">2</span>
            <h3>Build trust early</h3>
            <p>Support stronger student confidence through cleaner onboarding, email verification, clearer listing ownership, and better profile structure.</p>
        </article>
        <article class="step-card">
            <span class="step-number">3</span>
            <h3>Prove demand on campus</h3>
            <p>Validate adoption with an MVP before scaling features, partnerships, and future monetization options.</p>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Marketing, Sales & Pricing</span>
            <h2>How the business can enter the market</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($businessModel as $item): ?>
            <article class="info-card">
                <h3><?= e($item['title']); ?></h3>
                <p><?= e($item['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Operations & Organization</span>
            <h2>How the platform works operationally</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($operations as $item): ?>
            <article class="info-card">
                <h3><?= e($item['title']); ?></h3>
                <p><?= e($item['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Competition</span>
            <h2>Where UniTrade CY stands in the market</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($competitiveContext as $item): ?>
            <article class="info-card">
                <h3><?= e($item['title']); ?></h3>
                <p><?= e($item['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Financial Snapshot</span>
            <h2>The business model in simple terms</h2>
        </div>
    </div>

    <div class="profile-details-grid">
        <?php foreach ($financialSnapshot as $item): ?>
            <article class="info-card">
                <h3><?= e($item['title']); ?></h3>
                <p><?= e($item['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Innovation & Growth</span>
            <h2>How UniTrade CY can grow beyond the prototype</h2>
        </div>
    </div>

    <div class="steps-grid">
        <?php foreach ($roadmap as $item): ?>
            <article class="step-card">
                <span class="step-number"><?= e($item['step']); ?></span>
                <h3><?= e($item['title']); ?></h3>
                <p><?= e($item['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="cta-banner">
        <div>
            <span class="eyebrow">See the platform</span>
            <h2>The business idea is supported by a working student marketplace flow</h2>
            <p>Browse the live prototype, create listings, and review the trust and support pages to see how the business concept turns into a usable product.</p>
        </div>
        <div class="card-actions">
            <a class="btn btn-primary" href="browse.php">Browse Listings</a>
            <a class="btn btn-ghost" href="trust-safety.php">Trust & Safety</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
