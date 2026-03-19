<?php
require_once __DIR__ . '/includes/header.php';

$errors = [];
$fullName = '';
$email = '';
$university = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!verify_csrf()) {
        $errors[] = 'Invalid request token. Please try again.';
    }

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($university === '') {
        $errors[] = 'University is required.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (full_name, email, password_hash, university) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $fullName, $email, $passwordHash, $university);

        if ($stmt->execute()) {
            set_flash('success', 'Account created successfully. You can now log in.');
            $stmt->close();
            redirect('login.php');
        }

        $stmt->close();
        $errors[] = 'Could not create account. Please try again.';
    }
}
?>

<section class="auth-card">
    <div class="auth-heading">
        <span class="eyebrow">Create account</span>
        <h1>Join UniTrade CY</h1>
        <p>Create your student profile and start posting listings.</p>
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
            <label for="full_name">Full Name</label>
            <input id="full_name" type="text" name="full_name" value="<?= e($fullName); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($email); ?>" required>
        </div>

        <div class="form-group">
            <label for="university">University</label>
            <input id="university" type="text" name="university" value="<?= e($university); ?>" placeholder="e.g. Cyprus University of Technology" required>
        </div>

        <div class="form-grid two-cols">
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input id="confirm_password" type="password" name="confirm_password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary full-width">Create Account</button>
    </form>

    <p class="muted-text">Already have an account? <a class="text-link" href="login.php">Log in</a>.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
