<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = $id > 0 ? fetch_listing_by_id($id) : null;

if (!$listing || (int)$listing['user_id'] !== (int)$_SESSION['user_id']) {
    set_flash('error', 'You are not allowed to delete this listing.');
    redirect('profile.php');
}

$stmt = $mysqli->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $_SESSION['user_id']);

if ($stmt->execute()) {
    set_flash('success', 'Listing deleted successfully.');
} else {
    set_flash('error', 'Could not delete the listing.');
}

$stmt->close();
redirect('profile.php');
