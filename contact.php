<?php
require_once __DIR__ . '/includes/header.php';

$channels = [
    [
        'title' => 'Student support',
        'text' => 'Questions about accounts, listings, verification, or using the marketplace.',
        'action' => 'mailto:' . MAIL_FROM_ADDRESS,
        'label' => MAIL_FROM_ADDRESS
    ],
    [
        'title' => 'University or society partnerships',
        'text' => 'For student clubs, campus communities, or university groups interested in helping launch adoption.',
        'action' => 'mailto:' . MAIL_FROM_ADDRESS . '?subject=' . rawurlencode('UniTrade CY Partnership Inquiry'),
        'label' => 'Send partnership inquiry'
    ],
    [
        'title' => 'Pilot launch discussions',
        'text' => 'For feedback, course demonstration support, and early-stage collaboration around the business concept.',
        'action' => 'mailto:' . MAIL_FROM_ADDRESS . '?subject=' . rawurlencode('UniTrade CY Pilot Launch'),
        'label' => 'Discuss pilot launch'
    ],
];

$marketingChannels = [
    'Student societies and campus communities',
    'Instagram, TikTok, and student-friendly digital content',
    'Referral-style growth through active sellers and buyers',
    'Direct outreach to early student-service providers',
    'Partnerships with university-relevant communities',
];

$shortTermObjectives = [
    'Drive awareness in the first universities and student groups targeted for launch.',
    'Fill the marketplace with enough quality listings to make browsing immediately useful.',
    'Strengthen trust through clearer onboarding, profile completion, and better safety communication.',
    'Use feedback from early adopters to prioritize moderation, reporting, and monetization features.',
];
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Contact & Partnerships</span>
        <h1>How UniTrade CY Reaches Users and Builds Early Momentum</h1>
        <p>This page supports the marketing, outreach, and early growth side of the business plan. It shows how the platform can communicate with students, build partnerships, and create an early launch loop around trust and utility.</p>
    </div>
</section>

<section class="results-summary">
    <div>
        <span class="eyebrow">Go-to-market support</span>
        <h2>Simple channels, focused launch, campus-first outreach</h2>
        <p class="section-copy">The early strategy is not to market everywhere. It is to launch where the problem is strongest, build listing density, and grow through relevant communities first.</p>
    </div>
    <div class="results-meta">
        <div class="mini-stat">
            <strong>Marketing</strong>
            <span>digital student channels and communities</span>
        </div>
        <div class="mini-stat">
            <strong>Sales logic</strong>
            <span>grow usage first, monetize after traction</span>
        </div>
        <div class="mini-stat">
            <strong>Partnership angle</strong>
            <span>campus groups, student societies, pilot collaborators</span>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Contact Paths</span>
            <h2>Who should reach out and why</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($channels as $channel): ?>
            <article class="info-card">
                <h3><?= e($channel['title']); ?></h3>
                <p><?= e($channel['text']); ?></p>
                <a class="btn btn-secondary" href="<?= e($channel['action']); ?>"><?= e($channel['label']); ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Marketing Channels</span>
            <h2>How the platform can attract users</h2>
        </div>
    </div>

    <div class="profile-details-grid">
        <article class="info-card">
            <h3>Main channels</h3>
            <div class="tag-list">
                <?php foreach ($marketingChannels as $channel): ?>
                    <span class="tag"><?= e($channel); ?></span>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="info-card">
            <h3>Basic sales strategy</h3>
            <p>The immediate “sale” is adoption: getting enough students to list, browse, and return. The business can later convert traction into revenue through premium visibility and carefully chosen partnership products.</p>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Short-Term Objectives</span>
            <h2>What matters in the first phase</h2>
        </div>
    </div>

    <div class="steps-grid">
        <?php foreach ($shortTermObjectives as $index => $objective): ?>
            <article class="step-card">
                <span class="step-number"><?= $index + 1; ?></span>
                <h3>Priority <?= $index + 1; ?></h3>
                <p><?= e($objective); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="cta-banner">
        <div>
            <span class="eyebrow">Business plan support</span>
            <h2>UniTrade CY now shows the product, the trust layer, and the launch logic</h2>
            <p>That makes the website a much stronger companion to the written business plan you submit.</p>
        </div>
        <div class="card-actions">
            <a class="btn btn-primary" href="about.php">Business Overview</a>
            <a class="btn btn-ghost" href="trust-safety.php">Trust & Safety</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
