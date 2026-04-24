<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$errors = [];
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if ($loginInput === '') {
        $errors[] = 'Email or username is required.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("
            SELECT
                id,
                full_name,
                username,
                email,
                password_hash,
                is_verified,
                profile_complete,
                university,
                phone,
                country,
                city,
                address,
                postcode,
                dob,
                last_login
            FROM users
            WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?)
            LIMIT 1
        ");
        $stmt->bind_param('ss', $loginInput, $loginInput);
        $stmt->execute();
        $userRecord = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($userRecord && password_verify($password, $userRecord['password_hash'])) {
            $previousLogin = $userRecord['last_login'];

            $updateLogin = $mysqli->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateLogin->bind_param('i', $userRecord['id']);
            $updateLogin->execute();
            $updateLogin->close();

            sync_profile_complete_flag($userRecord['id']);
            $userRecord = fetch_user_by_id($userRecord['id']);

            if (!$userRecord) {
                $errors[] = 'Could not load your account. Please try again.';
            } else {
                if (!user_profile_is_complete($userRecord)) {
                    set_auth_flow_user_session($userRecord);
                    set_flash('error', 'Please complete your profile before continuing.');
                    redirect('complete-profile.php');
                }

                if ((int)$userRecord['is_verified'] !== 1) {
                    set_verification_user_session($userRecord);
                    set_flash('error', 'Please verify your account before logging in.');
                    redirect('verify.php');
                }

                set_authenticated_user_session($userRecord);
                set_flash(
                    'success',
                    $previousLogin
                        ? 'Welcome back! Last login: ' . date('d M Y, H:i', strtotime($previousLogin))
                        : 'Welcome to UniTrade CY. Your account is now active.'
                );
                redirect('profile.php');
            }
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Welcome back</span>
        <h1>Log in to UniTrade CY</h1>
        <p>Sign in with your username or email. New accounts complete profile setup and email verification during registration before marketplace access.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

        <div class="form-group">
            <label for="login_input">Username or Email</label>
            <input id="login_input" type="text" name="login_input" value="<?= e($loginInput); ?>" placeholder="e.g. eliastrades or elias@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-row">
                <input id="password" type="password" name="password" required>
                <button type="button" class="password-toggle js-toggle-password" data-target="#password" aria-label="Show password">Show</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary full-width">Log In</button>
    </form>

    <p class="muted-text">No account yet? <a class="text-link" href="register.php">Register here</a>.</p>
    <p class="muted-text"><a class="text-link" href="forgot-password.php">Forgot password?</a></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
