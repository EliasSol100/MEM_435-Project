<?php
require_once __DIR__ . '/includes/header.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $userRecord = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($userRecord && password_verify($password, $userRecord['password_hash'])) {
            $_SESSION['user_id'] = (int)$userRecord['id'];
            set_flash('success', 'Welcome back!');
            redirect('profile.php');
        }

        $errors[] = 'Invalid email or password.';
    }
}
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Welcome back</span>
        <h1>Log in to UniTrade CY</h1>
        <p>Access your listings, wishlist, and student profile.</p>
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
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($email); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary full-width">Log In</button>
    </form>

    <p class="muted-text">No account yet? <a class="text-link" href="register.php">Register here</a>.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
