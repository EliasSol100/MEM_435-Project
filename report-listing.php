<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$listingId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['listing_id'] ?? 0);
$listing = $listingId > 0 ? fetch_listing_by_id($listingId) : null;

if (!$listing) {
    set_flash('error', 'Listing not found.');
    redirect('browse.php');
}

if ((int)$listing['user_id'] === (int)$_SESSION['user_id']) {
    set_flash('error', 'You cannot report your own listing.');
    redirect('listing.php?id=' . $listingId);
}

$reasonOptions = listing_report_reasons();
$selectedReason = trim($_POST['reason'] ?? '');
$details = trim($_POST['details'] ?? '');
$errors = [];
$existingReport = fetch_open_listing_report_for_user((int)$_SESSION['user_id'], $listingId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if ($existingReport) {
        $errors[] = 'You already submitted a report for this listing. Our moderation queue has it recorded.';
    }

    if (!isset($reasonOptions[$selectedReason])) {
        $errors[] = 'Please choose a valid report reason.';
    }

    if (mb_strlen($details) > 1200) {
        $errors[] = 'Please keep the additional details under 1200 characters.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("
            INSERT INTO listing_reports (listing_id, reporter_user_id, reason, details, status)
            VALUES (?, ?, ?, ?, 'Open')
        ");
        $reporterUserId = (int)$_SESSION['user_id'];
        $stmt->bind_param('iiss', $listingId, $reporterUserId, $selectedReason, $details);

        if ($stmt->execute()) {
            $stmt->close();
            set_flash('success', 'Thank you. Your report has been submitted for moderation review.');
            redirect('listing.php?id=' . $listingId);
        }

        $stmt->close();
        $errors[] = 'We could not submit your report right now. Please try again.';
    }

    $existingReport = fetch_open_listing_report_for_user((int)$_SESSION['user_id'], $listingId);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div>
        <span class="eyebrow">Safety Reporting</span>
        <h1>Report Listing</h1>
        <p>Use this form if something about the listing feels misleading, inappropriate, unsafe, or suspicious. Reports are stored for moderation review.</p>
    </div>
</section>

<div class="form-layout">
    <div>
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($existingReport): ?>
            <div class="form-card">
                <div class="detail-top">
                    <span class="eyebrow">Report already submitted</span>
                    <span class="status-pill pending"><?= e($existingReport['status']); ?></span>
                </div>
                <h2><?= e($listing['title']); ?></h2>
                <p class="section-copy">You already sent a report for this listing on <?= date('d M Y, H:i', strtotime($existingReport['created_at'])); ?>. If the issue is urgent, use the contact page as well.</p>
                <div class="info-list">
                    <div class="info-row">
                        <strong>Reason</strong>
                        <span><?= e($reasonOptions[$existingReport['reason']] ?? ucfirst($existingReport['reason'])); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Additional details</strong>
                        <span><?= e($existingReport['details'] !== '' ? $existingReport['details'] : 'No extra details provided'); ?></span>
                    </div>
                </div>
                <div class="card-actions">
                    <a class="btn btn-secondary" href="listing.php?id=<?= (int)$listingId; ?>">Back to Listing</a>
                    <a class="btn btn-ghost" href="contact.php">Contact Support</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" class="form-card">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                <input type="hidden" name="listing_id" value="<?= (int)$listingId; ?>">

                <div class="form-group">
                    <label for="reason">Reason</label>
                    <select id="reason" name="reason" required>
                        <option value="">Choose a reason</option>
                        <?php foreach ($reasonOptions as $value => $label): ?>
                            <option value="<?= e($value); ?>" <?= $selectedReason === $value ? 'selected' : ''; ?>>
                                <?= e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="details">Additional Details (optional)</label>
                    <textarea id="details" name="details" rows="6" placeholder="Explain what looks wrong or why this listing should be reviewed."><?= e($details); ?></textarea>
                    <small class="input-help">Helpful reports make moderation faster. Include only relevant details.</small>
                </div>

                <div class="card-actions">
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                    <a class="btn btn-secondary" href="listing.php?id=<?= (int)$listingId; ?>">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <aside class="support-card">
        <div>
            <span class="eyebrow">Listing under review</span>
            <h3><?= e($listing['title']); ?></h3>
        </div>

        <div class="info-list">
            <div class="info-row">
                <strong>Seller</strong>
                <span><?= e($listing['full_name']); ?></span>
            </div>
            <div class="info-row">
                <strong>Category</strong>
                <span><?= e($listing['category_name']); ?></span>
            </div>
            <div class="info-row">
                <strong>Type</strong>
                <span><?= e($listing['item_type']); ?></span>
            </div>
            <div class="info-row">
                <strong>Status</strong>
                <span><?= e($listing['status']); ?></span>
            </div>
        </div>

        <div class="support-tip">
            Reports help UniTrade CY support safer marketplace activity. They should be used for real safety, spam, or trust concerns, not ordinary price disagreements.
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
