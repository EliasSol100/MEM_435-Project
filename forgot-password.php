<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$successMessage = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("
            SELECT id, full_name, email
            FROM users
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($userRow) {
            [$mailSent, $mailError] = issue_password_reset_email((int)$userRow['id'], $userRow['email'], $userRow['full_name']);

            if (!$mailSent) {
                $errors[] = 'Could not send reset email right now. Please try again later.';
                error_log('Forgot password email error: ' . (string)$mailError);
            }
        }

        if (!$errors) {
            $successMessage = 'If this email belongs to an account, a password reset link has been sent.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Account recovery</span>
        <h1>Forgot Password</h1>
        <p>Enter your email and we will send a secure reset link.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= e($successMessage); ?></div>
    <?php endif; ?>

    <?php if (!$successMessage): ?>
        <form method="POST" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e($email); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary full-width">Send Reset Link</button>
        </form>
    <?php endif; ?>

    <p class="muted-text"><a class="text-link" href="login.php">Back to login</a></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
