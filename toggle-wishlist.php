<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    set_flash('error', 'Invalid request.');
    redirect('browse.php');
}

$listingId = (int)($_POST['listing_id'] ?? 0);

if ($listingId <= 0) {
    set_flash('error', 'Invalid listing selected.');
    redirect('browse.php');
}

$userId = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT id FROM wishlists WHERE user_id = ? AND listing_id = ?");
$stmt->bind_param('ii', $userId, $listingId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    $deleteStmt = $mysqli->prepare("DELETE FROM wishlists WHERE user_id = ? AND listing_id = ?");
    $deleteStmt->bind_param('ii', $userId, $listingId);
    $deleteStmt->execute();
    $deleteStmt->close();
    set_flash('success', 'Listing removed from wishlist.');
} else {
    $insertStmt = $mysqli->prepare("INSERT INTO wishlists (user_id, listing_id) VALUES (?, ?)");
    $insertStmt->bind_param('ii', $userId, $listingId);
    $insertStmt->execute();
    $insertStmt->close();
    set_flash('success', 'Listing added to wishlist.');
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$appBase = rtrim(APP_BASE_URL, '/');

if ($referer !== '' && str_starts_with($referer, $appBase)) {
    redirect($referer);
}

redirect('browse.php');
