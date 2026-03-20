<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$errors = [];
$fieldErrors = [];
$email = trim($_SESSION['manual_email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['manual_email'] ?? '');

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['manual_email'] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $fieldErrors['manual_email'] = 'An account with this email already exists.';
        }
    }

    if (!$errors && !$fieldErrors) {
        clear_auth_flow_session();
        $_SESSION['manual_email'] = $email;
        redirect('complete-profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Create account</span>
        <h1>Start Your UniTrade CY Profile</h1>
        <p>Enter your email first, then we will guide you through the full student profile setup.</p>
    </div>

    <?php if ($errors || $fieldErrors): ?>
        <div class="alert alert-error">
            <?php if ($fieldErrors): ?>
                <div>Please review the highlighted field before continuing.</div>
            <?php endif; ?>
            <?php foreach ($errors as $error): ?>
                <div><?= e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

        <div class="form-group">
            <label for="manual_email">Email Address</label>
            <input id="manual_email" type="email" name="manual_email" value="<?= e($email); ?>" placeholder="e.g. student@cut.ac.cy" autocomplete="email" required class="<?= isset($fieldErrors['manual_email']) ? 'is-invalid' : ''; ?>">
            <small class="input-help">Use the email address where you want to receive your verification code.</small>
            <small class="input-help is-error field-error"<?= isset($fieldErrors['manual_email']) ? '' : ' hidden'; ?>><?= isset($fieldErrors['manual_email']) ? e($fieldErrors['manual_email']) : ''; ?></small>
        </div>

        <button type="submit" class="btn btn-primary full-width">Continue</button>
    </form>

    <p class="muted-text">Already have an account? <a class="text-link" href="login.php">Log in</a>.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
