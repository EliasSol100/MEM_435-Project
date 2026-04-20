<?php
require_once __DIR__ . '/functions.php';
$flash = get_flash();
$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF']);
$displayName = $user['first_name'] ?? '';

if ($displayName === '' && !empty($user['full_name'])) {
    $nameParts = preg_split('/\s+/', trim((string)$user['full_name']));
    $displayName = $nameParts[0] ?? $user['full_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(SITE_NAME); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/main.js"></script>
</head>
<body>
<header class="site-header">
    <div class="container navbar">
        <a class="logo" href="index.php" aria-label="<?= e(SITE_NAME); ?>">
            <img class="logo-image" src="assets/images/unitrade-logo.svg" alt="<?= e(SITE_NAME); ?>">
        </a>

        <div class="nav-cluster">
            <?php if ($user): ?>
                <div class="nav-user-chip">
                    <span class="nav-user-label"><?= e($displayName ?: 'Student'); ?></span>
                    <span class="nav-user-status"><?= !empty($user['is_verified']) ? 'Verified account' : 'Setup in progress'; ?></span>
                </div>
            <?php endif; ?>

            <nav class="nav-links">
                <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="browse.php" class="<?= $currentPage === 'browse.php' ? 'active' : ''; ?>">Browse</a>
                <a href="about.php" class="<?= $currentPage === 'about.php' ? 'active' : ''; ?>">About</a>
                <a href="trust-safety.php" class="<?= $currentPage === 'trust-safety.php' ? 'active' : ''; ?>">Safety</a>
                <a href="contact.php" class="<?= $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact</a>
                <?php if (is_logged_in()): ?>
                    <a href="create-listing.php" class="<?= $currentPage === 'create-listing.php' ? 'active' : ''; ?>">Create Listing</a>
                    <a href="wishlist.php" class="<?= $currentPage === 'wishlist.php' ? 'active' : ''; ?>">Wishlist</a>
                    <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : ''; ?>">Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="<?= $currentPage === 'login.php' ? 'active' : ''; ?>">Login</a>
                    <a href="register.php" class="<?= $currentPage === 'register.php' ? 'active' : ''; ?>">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<main class="page-shell">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']); ?>"><?= e($flash['message']); ?></div>
        <?php endif; ?>
