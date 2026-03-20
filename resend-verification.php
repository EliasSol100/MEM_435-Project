<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$successMessage = '';
$email = trim($_GET['email'] ?? '');

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
            SELECT id, full_name, email, is_verified, profile_complete
            FROM users
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            sync_profile_complete_flag($user['id']);
            $user = fetch_user_by_id($user['id']);
        }

        if (!$user) {
            $successMessage = 'If this email exists, a verification code has been sent.';
        } elseif ((int)$user['is_verified'] === 1) {
            $successMessage = 'This account is already verified. You can log in now.';
        } elseif ((int)$user['profile_complete'] !== 1) {
            set_auth_flow_user_session($user);
            set_flash('error', 'Please finish your profile setup before requesting verification.');
            redirect('complete-profile.php');
        } else {
            [$mailSent, $mailError] = issue_verification_code((int)$user['id'], $user['email'], $user['full_name']);

            if ($mailSent) {
                set_verification_user_session($user);
                redirect('verify.php');
            }

            $errors[] = 'Could not send the verification code right now. Please try again later.';
            error_log('Resend verification email error: ' . (string)$mailError);
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Account setup</span>
        <h1>Resend Verification Code</h1>
        <p>Enter your account email and we will send a fresh verification code.</p>
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

    <form method="POST" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($email); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary full-width">Resend Code</button>
    </form>

    <p class="muted-text"><a class="text-link" href="login.php">Back to login</a></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
