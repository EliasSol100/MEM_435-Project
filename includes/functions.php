<?php
require_once __DIR__ . '/db.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in first.');
        redirect('login.php');
    }
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf()
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function current_user()
{
    global $mysqli;

    if (!is_logged_in()) {
        return null;
    }

    static $user = null;

    if ($user !== null) {
        return $user;
    }

    $stmt = $mysqli->prepare("SELECT id, full_name, email, university, bio, created_at FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

function get_categories()
{
    global $mysqli;

    $categories = [];
    $result = $mysqli->query("SELECT id, name FROM categories ORDER BY name ASC");

    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    return $categories;
}

function format_price($price)
{
    return '€' . number_format((float)$price, 2);
}

function excerpt($text, $limit = 100)
{
    $text = trim((string)$text);

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...');
    }

    return strlen($text) > $limit ? substr($text, 0, $limit - 3) . '...' : $text;
}


function listing_image($listing)
{
    if (!empty($listing['image_path']) && file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . $listing['image_path'])) {
        return e($listing['image_path']);
    }

    if (!empty($listing['image_url'])) {
        return e($listing['image_url']);
    }

    return 'assets/images/placeholder.svg';
}

function upload_listing_image($file)
{
    if (empty($file['name'])) {
        return [true, null];
    }

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Image upload failed.'];
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        return [false, 'Image must be smaller than 2MB.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return [false, 'Only JPG, PNG, and WEBP images are allowed.'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }

    $filename = uniqid('listing_', true) . '.' . $allowed[$mime];
    $targetPath = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, 'Could not save uploaded image.'];
    }

    return [true, 'uploads/' . $filename];
}

function is_wishlisted($userId, $listingId)
{
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT id FROM wishlists WHERE user_id = ? AND listing_id = ?");
    $stmt->bind_param('ii', $userId, $listingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = (bool) $result->fetch_assoc();
    $stmt->close();

    return $exists;
}

function listing_owner_name($listing)
{
    return !empty($listing['full_name']) ? $listing['full_name'] : 'Unknown Seller';
}

function fetch_listing_by_id($id)
{
    global $mysqli;

    $stmt = $mysqli->prepare("
        SELECT l.*, c.name AS category_name, u.full_name, u.university, u.email
        FROM listings l
        JOIN categories c ON c.id = l.category_id
        JOIN users u ON u.id = l.user_id
        WHERE l.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();
    $stmt->close();

    return $listing;
}
