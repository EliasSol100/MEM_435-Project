<?php
require_once __DIR__ . '/includes/functions.php';

$verificationUserId = verification_user_id();
if ($verificationUserId <= 0) {
    set_flash('error', 'Please restart the verification flow.');
    redirect('login.php');
}

$user = fetch_user_by_id($verificationUserId);
if (!$user) {
    clear_auth_flow_session();
    set_flash('error', 'Your verification session expired. Please log in again.');
    redirect('login.php');
}

$displayEmail = (string)($user['email'] ?? ($_SESSION['auth_flow_email'] ?? 'your email'));

if ((int)$user['is_verified'] === 1) {
    set_authenticated_user_session($user);
    redirect('profile.php');
}

$feedbackMessage = '';
$feedbackType = 'error';

$hasActiveCode = !empty($user['verification_code'])
    && !empty($user['verification_expires_at'])
    && strtotime($user['verification_expires_at']) >= time();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$hasActiveCode) {
    [$mailSent, $mailError] = issue_verification_code((int)$user['id'], $user['email'], $user['full_name']);

    if ($mailSent) {
        $feedbackMessage = 'We sent a fresh verification code to your email.';
        $feedbackType = 'success';
        $refreshedUser = fetch_user_by_id((int)$user['id']);
        if ($refreshedUser) {
            $user = $refreshedUser;
            $displayEmail = (string)($user['email'] ?? $displayEmail);
        }
    } else {
        $feedbackMessage = 'We could not send the verification code right now. Please check your SMTP settings and try again.';
        error_log('Automatic verification code email error: ' . (string)$mailError);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $feedbackMessage = 'Invalid request token. Please try again.';
    } elseif (isset($_POST['resend_code'])) {
        [$mailSent, $mailError] = issue_verification_code((int)$user['id'], $user['email'], $user['full_name']);

        if ($mailSent) {
            $feedbackMessage = 'A new verification code has been sent to your email.';
            $feedbackType = 'success';
            $refreshedUser = fetch_user_by_id((int)$user['id']);
            if ($refreshedUser) {
                $user = $refreshedUser;
                $displayEmail = (string)($user['email'] ?? $displayEmail);
            }
        } else {
            $feedbackMessage = 'We could not resend the code right now. Please try again later.';
            error_log('Resend verification code error: ' . (string)$mailError);
        }
    } elseif (isset($_POST['verify_code'])) {
        $inputCode = trim($_POST['verification_code'] ?? '');
        $refreshedUser = fetch_user_by_id((int)$user['id']);
        if (!$refreshedUser) {
            clear_auth_flow_session();
            set_flash('error', 'Your verification session expired. Please log in again.');
            redirect('login.php');
        }

        $user = $refreshedUser;
        $displayEmail = (string)($user['email'] ?? $displayEmail);

        if ($inputCode === '') {
            $feedbackMessage = 'Please enter the 6-digit verification code.';
        } elseif (empty($user['verification_code'])) {
            $feedbackMessage = 'There is no active verification code. Please resend the code.';
        } elseif (!empty($user['verification_expires_at']) && strtotime($user['verification_expires_at']) < time()) {
            $feedbackMessage = 'Your verification code has expired. Please resend the code.';
        } elseif ($inputCode !== (string)$user['verification_code']) {
            $feedbackMessage = 'The verification code is incorrect. Please try again.';
        } else {
            $stmt = $mysqli->prepare("
                UPDATE users
                SET is_verified = 1, verification_code = NULL, verification_expires_at = NULL
                WHERE id = ?
            ");
            $stmt->bind_param('i', $user['id']);
            $stmt->execute();
            $stmt->close();

            $updatedUser = fetch_user_by_id($user['id']);
            if (!$updatedUser) {
                clear_auth_flow_session();
                set_flash('success', 'Your account has been verified successfully. Please log in to continue.');
                redirect('login.php');
            }

            set_authenticated_user_session($updatedUser);
            set_flash('success', 'Your account has been verified successfully.');
            redirect('profile.php');
        }
    }
}

$remainingSeconds = 0;
if (!empty($user['verification_expires_at'])) {
    $expiresAt = strtotime($user['verification_expires_at']);
    if ($expiresAt !== false) {
        $remainingSeconds = max(0, $expiresAt - time());
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Email verification</span>
        <h1>Verify Your Account</h1>
        <p>Enter the 6-digit code we sent to <strong><?= e($displayEmail); ?></strong>.</p>
    </div>

    <?php if ($feedbackMessage !== ''): ?>
        <div class="alert alert-<?= $feedbackType === 'success' ? 'success' : 'error'; ?>">
            <?= e($feedbackMessage); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

        <p class="muted-text" id="verification-timer" data-remaining="<?= (int)$remainingSeconds; ?>"></p>

        <div class="form-group">
            <label for="verification_code">Verification Code</label>
            <input id="verification_code" type="text" name="verification_code" maxlength="6" inputmode="numeric" placeholder="6-digit code" required>
        </div>

        <div class="wizard-actions-row verification-actions">
            <button type="submit" class="btn btn-primary full-width" name="verify_code">Verify Email</button>
            <button type="submit" class="btn btn-secondary full-width" name="resend_code" formnovalidate>Resend Code</button>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var timer = document.getElementById('verification-timer');
    if (!timer) {
        return;
    }

    var remaining = Number(timer.getAttribute('data-remaining') || 0);

    function formatTime(totalSeconds) {
        var minutes = Math.floor(totalSeconds / 60);
        var seconds = totalSeconds % 60;
        return minutes + ':' + String(seconds).padStart(2, '0');
    }

    function render() {
        if (remaining <= 0) {
            timer.textContent = 'Verification code expired. Click "Resend Code" to receive a new one.';
            return;
        }

        timer.textContent = 'Code expires in ' + formatTime(remaining);
        remaining -= 1;
        window.setTimeout(render, 1000);
    }

    render();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
