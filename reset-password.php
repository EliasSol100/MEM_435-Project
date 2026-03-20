<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$password = '';
$confirmPassword = '';
$resetRow = null;

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $errors[] = 'Invalid or missing reset token.';
} else {
    $tokenHash = hash_token($token);

    $stmt = $mysqli->prepare("
        SELECT pr.id, pr.user_id, pr.expires_at, u.full_name
        FROM password_resets pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.token_hash = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $resetRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$resetRow) {
        $errors[] = 'Reset link is invalid or already used.';
    } elseif (strtotime($resetRow['expires_at']) < time()) {
        $errors[] = 'Reset link has expired. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors && $resetRow) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password)) {
        $errors[] = 'Password must include an uppercase letter, a number, and a symbol.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $updateStmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $updateStmt->bind_param('si', $passwordHash, $resetRow['user_id']);
        $updateStmt->execute();
        $updateStmt->close();

        $deleteStmt = $mysqli->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $deleteStmt->bind_param('i', $resetRow['user_id']);
        $deleteStmt->execute();
        $deleteStmt->close();

        set_flash('success', 'Password updated successfully. You can now log in.');
        redirect('login.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Account recovery</span>
        <h1>Reset Password</h1>
        <p>Choose a new password for your account.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$errors && $resetRow): ?>
        <form method="POST" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="token" value="<?= e($token); ?>">

            <div class="form-group">
                <label for="password">New Password</label>
                <div class="password-row">
                    <input id="password" type="password" name="password" minlength="8" required>
                    <button type="button" class="password-toggle js-toggle-password" data-target="#password" aria-label="Show password">Show</button>
                </div>
                <small class="input-help">At least 8 characters, including an uppercase letter, a number, and a symbol.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-row">
                    <input id="confirm_password" type="password" name="confirm_password" minlength="8" required>
                    <button type="button" class="password-toggle js-toggle-password" data-target="#confirm_password" aria-label="Show password">Show</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary full-width">Update Password</button>
        </form>
    <?php endif; ?>

    <p class="muted-text"><a class="text-link" href="forgot-password.php">Request another reset link</a></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
