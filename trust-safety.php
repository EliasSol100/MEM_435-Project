<?php
require_once __DIR__ . '/includes/header.php';

$currentControls = [
    [
        'title' => 'Email verification',
        'text' => 'New accounts go through email-based verification before they fully access the platform.'
    ],
    [
        'title' => 'Structured user profiles',
        'text' => 'Users complete a fuller profile with identity and contact details, making marketplace activity feel more accountable.'
    ],
    [
        'title' => 'Listing ownership rules',
        'text' => 'Only the listing owner can edit or delete their content, and sensitive actions are protected with CSRF checks.'
    ],
    [
        'title' => 'Password recovery flow',
        'text' => 'Secure reset links support account recovery without exposing user passwords.'
    ],
    [
        'title' => 'Seller reviews',
        'text' => 'Review history helps create a lightweight reputation layer for repeat trust inside the community.'
    ],
    [
        'title' => 'Listing reporting workflow',
        'text' => 'Users can now report suspicious or misleading listings so concerns enter a moderation review queue.'
    ],
    [
        'title' => 'Safer session handling',
        'text' => 'User sessions are refreshed at login and cleaned up properly on logout.'
    ],
];

$riskPlan = [
    [
        'title' => 'Fake or low-trust accounts',
        'current' => 'Current control: email verification, fuller profiles, seller reviews.',
        'next' => 'Next step: add stronger university-only verification options and reporting tools.'
    ],
    [
        'title' => 'Misleading listings',
        'current' => 'Current control: structured listing fields, visible seller profile, category filtering, and live listing reporting.',
        'next' => 'Next step: add admin moderation workflows and clearer report-resolution handling.'
    ],
    [
        'title' => 'Low early adoption',
        'current' => 'Current control: narrow focus on real student needs and categories with repeated demand.',
        'next' => 'Next step: launch with student groups, partnerships, and ambassador-style promotion.'
    ],
    [
        'title' => 'Trust during exchanges',
        'current' => 'Current control: profile visibility and safer public-meeting guidance.',
        'next' => 'Next step: expand trust guidance, moderation, and optional safety support features.'
    ],
];
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Trust & Safety</span>
        <h1>Building a Marketplace Students Can Trust</h1>
        <p>For a student marketplace, trust is not optional. UniTrade CY already includes a base level of account and listing protection, and the roadmap continues to strengthen moderation, reporting, and university-focused verification.</p>
    </div>
</section>

<section class="results-summary">
    <div>
        <span class="eyebrow">Risk management</span>
        <h2>Current safeguards plus a realistic trust roadmap</h2>
        <p class="section-copy">This page supports the business plan by showing how UniTrade CY thinks about platform risk, student confidence, and practical controls at the MVP stage.</p>
    </div>
    <div class="results-meta">
        <div class="mini-stat">
            <strong>Now</strong>
            <span>verification, profile structure, ownership protection</span>
        </div>
        <div class="mini-stat">
            <strong>Next</strong>
            <span>reporting, moderation, stronger student verification</span>
        </div>
        <div class="mini-stat">
            <strong>Goal</strong>
            <span>a marketplace that feels safe enough to return to</span>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Current Controls</span>
            <h2>What already supports trust today</h2>
        </div>
    </div>

    <div class="feature-grid">
        <?php foreach ($currentControls as $control): ?>
            <article class="info-card">
                <h3><?= e($control['title']); ?></h3>
                <p><?= e($control['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="section-head">
        <div>
            <span class="eyebrow">Safe Trading Guide</span>
            <h2>Good practices for student exchanges</h2>
        </div>
    </div>

    <div class="steps-grid">
        <article class="step-card">
            <span class="step-number">1</span>
            <h3>Use verified accounts</h3>
            <p>Complete registration properly and keep your contact details accurate so other users know they are dealing with a real student seller or buyer.</p>
        </article>
        <article class="step-card">
            <span class="step-number">2</span>
            <h3>Meet in public places</h3>
            <p>Arrange exchanges in public campus areas or other safe, visible locations, especially for higher-value items.</p>
        </article>
        <article class="step-card">
            <span class="step-number">3</span>
            <h3>Inspect before completing</h3>
            <p>Check item condition, accessories, and expectations together before finalizing the exchange or service agreement.</p>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="section-head">
        <div>
            <span class="eyebrow">Main Risks & Challenges</span>
            <h2>How the business plans to manage them</h2>
        </div>
    </div>

    <div class="profile-details-grid">
        <?php foreach ($riskPlan as $risk): ?>
            <article class="info-card">
                <h3><?= e($risk['title']); ?></h3>
                <p><strong>Today:</strong> <?= e($risk['current']); ?></p>
                <p><strong>Roadmap:</strong> <?= e($risk['next']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block section-alt">
    <div class="cta-banner">
        <div>
            <span class="eyebrow">Need support?</span>
            <h2>Safety, support, and partnership communication should stay easy to find</h2>
            <p>UniTrade CY is designed to make the product idea feel more credible inside the business plan, not just more attractive visually.</p>
        </div>
        <div class="card-actions">
            <a class="btn btn-primary" href="contact.php">Contact & Partnerships</a>
            <a class="btn btn-ghost" href="about.php">Business Overview</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
