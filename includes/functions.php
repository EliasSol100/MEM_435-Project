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
        $_SESSION['csrf_token'] = generate_secure_token(32);
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf()
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function app_url($path = '')
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim((string)$path, '/');

    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function generate_secure_token($bytes = 32)
{
    try {
        return bin2hex(random_bytes((int)$bytes));
    } catch (\Throwable $e) {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes((int)$bytes));
        }

        throw $e;
    }
}

function hash_token($token)
{
    return hash('sha256', (string)$token);
}

function fetch_user_by_id($userId)
{
    global $mysqli;

    $userId = (int)$userId;
    if ($userId <= 0) {
        return null;
    }

    $stmt = $mysqli->prepare("
        SELECT
            id,
            full_name,
            first_name,
            middle_name,
            last_name,
            username,
            email,
            password_hash,
            is_verified,
            verification_code,
            verification_expires_at,
            university,
            bio,
            phone,
            country,
            city,
            address,
            postcode,
            dob,
            profile_complete,
            created_at,
            last_login
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

function clear_auth_flow_session()
{
    unset(
        $_SESSION['manual_email'],
        $_SESSION['auth_flow_user_id'],
        $_SESSION['auth_flow_email'],
        $_SESSION['auth_flow_full_name'],
        $_SESSION['verify_user_id']
    );
}

function set_auth_flow_user_session(array $user)
{
    $_SESSION['auth_flow_user_id'] = (int)($user['id'] ?? 0);
    $_SESSION['auth_flow_email'] = (string)($user['email'] ?? '');
    $_SESSION['auth_flow_full_name'] = (string)($user['full_name'] ?? '');
}

function set_verification_user_session(array $user)
{
    set_auth_flow_user_session($user);
    $_SESSION['verify_user_id'] = (int)($user['id'] ?? 0);
}

function auth_flow_user_id()
{
    return !empty($_SESSION['auth_flow_user_id']) ? (int)$_SESSION['auth_flow_user_id'] : 0;
}

function verification_user_id()
{
    if (!empty($_SESSION['verify_user_id'])) {
        return (int)$_SESSION['verify_user_id'];
    }

    return auth_flow_user_id();
}

function set_authenticated_user_session(array $user)
{
    clear_auth_flow_session();

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['email'] = (string)($user['email'] ?? '');
    $_SESSION['full_name'] = (string)($user['full_name'] ?? '');
}

function current_user()
{
    if (!is_logged_in()) {
        return null;
    }

    static $cachedUser = null;
    static $cachedUserId = 0;
    $userId = (int)$_SESSION['user_id'];

    if ($cachedUser !== null && $cachedUserId === $userId) {
        return $cachedUser;
    }

    $cachedUser = fetch_user_by_id($userId);
    $cachedUserId = $userId;

    return $cachedUser;
}

function split_full_name($fullName)
{
    $parts = preg_split('/\s+/', trim((string)$fullName), -1, PREG_SPLIT_NO_EMPTY);

    return [
        'parts' => $parts,
        'first_name' => $parts[0] ?? '',
        'middle_name' => count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : null,
        'last_name' => $parts ? $parts[count($parts) - 1] : ''
    ];
}

function user_profile_is_complete(array $user)
{
    $requiredFields = [
        'full_name',
        'username',
        'university',
        'country',
        'city',
        'address',
        'postcode',
        'dob',
        'phone'
    ];

    foreach ($requiredFields as $field) {
        if (empty($user[$field])) {
            return false;
        }
    }

    return true;
}

function sync_profile_complete_flag($userId)
{
    global $mysqli;

    $user = fetch_user_by_id($userId);
    if (!$user) {
        return false;
    }

    $isComplete = user_profile_is_complete($user) ? 1 : 0;
    $stmt = $mysqli->prepare("UPDATE users SET profile_complete = ? WHERE id = ?");
    $stmt->bind_param('ii', $isComplete, $userId);
    $stmt->execute();
    $stmt->close();

    return (bool)$isComplete;
}

function send_app_email($toEmail, $toName, $subject, $plainBody, $htmlBody = null)
{
    $projectRoot = dirname(__DIR__);
    $phpMailerPath = '';
    $folderCandidates = ['PHPMailer-master', 'phpmailer-master'];

    foreach ($folderCandidates as $folderName) {
        $candidatePath = $projectRoot . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        if (file_exists($candidatePath . 'PHPMailer.php')) {
            $phpMailerPath = $candidatePath;
            break;
        }
    }

    if ($phpMailerPath === '') {
        return [false, 'PHPMailer is missing.'];
    }

    require_once $phpMailerPath . 'Exception.php';
    require_once $phpMailerPath . 'PHPMailer.php';
    require_once $phpMailerPath . 'SMTP.php';

    if (SMTP_HOST === '' || SMTP_USERNAME === '' || SMTP_PASSWORD === '') {
        return [false, 'SMTP settings are incomplete in includes/config.php.'];
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->SMTPDebug = (int)SMTP_DEBUG;
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->Port = (int)SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 20;

        if (SMTP_ENCRYPTION === 'tls') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (SMTP_ENCRYPTION === 'ssl') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        }

        if (defined('SMTP_ALLOW_SELF_SIGNED') && SMTP_ALLOW_SELF_SIGNED) {
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress((string)$toEmail, (string)$toName);
        $mail->isHTML($htmlBody !== null);
        $mail->Subject = (string)$subject;
        $mail->Body = $htmlBody !== null ? (string)$htmlBody : (string)$plainBody;
        $mail->AltBody = (string)$plainBody;

        $mail->send();
        return [true, null];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}

function issue_verification_code($userId, $email, $fullName)
{
    global $mysqli;

    $code = (string)random_int(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', time() + (20 * 60));

    $stmt = $mysqli->prepare("
        UPDATE users
        SET verification_code = ?, verification_expires_at = ?, is_verified = 0
        WHERE id = ?
    ");
    $stmt->bind_param('ssi', $code, $expiresAt, $userId);
    $stmt->execute();
    $stmt->close();

    $subject = 'Your UniTrade CY verification code';
    $plainBody = "Hi {$fullName},\n\n"
        . "Your UniTrade CY verification code is: {$code}\n\n"
        . "This code is valid for 20 minutes.\n\n"
        . "If you did not request this, you can ignore this email.\n\n"
        . "UniTrade CY";

    return send_app_email($email, $fullName, $subject, $plainBody);
}

function issue_password_reset_email($userId, $email, $fullName)
{
    global $mysqli;

    $token = generate_secure_token(32);
    $tokenHash = hash_token($token);
    $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60));

    $deleteStmt = $mysqli->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $deleteStmt->bind_param('i', $userId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $mysqli->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
    $insertStmt->bind_param('iss', $userId, $tokenHash, $expiresAt);
    $insertStmt->execute();
    $insertStmt->close();

    $resetLink = app_url('reset-password.php?token=' . urlencode($token));
    $subject = 'Reset your UniTrade CY password';
    $plainBody = "Hi {$fullName},\n\n"
        . "We received a password reset request for your UniTrade CY account.\n"
        . "Use this link to set a new password:\n"
        . $resetLink . "\n\n"
        . "This reset link expires in 30 minutes.\n\n"
        . "If you did not request this, you can ignore this email.\n\n"
        . "UniTrade CY";

    return send_app_email($email, $fullName, $subject, $plainBody);
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
    return '&euro;' . number_format((float)$price, 2);
}

function render_rating_stars($rating, $max = 5)
{
    $max = max(1, (int)$max);
    $rating = max(0, min($max, (int)$rating));

    return str_repeat('&#9733;', $rating) . str_repeat('&#9734;', $max - $rating);
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
        'image/png' => 'png',
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
    $exists = (bool)$result->fetch_assoc();
    $stmt->close();

    return $exists;
}

function listing_report_reasons()
{
    return [
        'misleading' => 'Misleading title, photos, or description',
        'prohibited' => 'Prohibited, unsafe, or inappropriate item',
        'spam' => 'Spam, duplicate, or clearly low-quality listing',
        'fraud' => 'Suspicious or potentially fraudulent behaviour',
        'other' => 'Other concern',
    ];
}

function fetch_open_listing_report_for_user($userId, $listingId)
{
    global $mysqli;

    $userId = (int)$userId;
    $listingId = (int)$listingId;

    if ($userId <= 0 || $listingId <= 0) {
        return null;
    }

    $stmt = $mysqli->prepare("
        SELECT id, listing_id, reporter_user_id, reason, details, status, created_at
        FROM listing_reports
        WHERE reporter_user_id = ? AND listing_id = ? AND status = 'Open'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param('ii', $userId, $listingId);
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $report ?: null;
}

function listing_owner_name($listing)
{
    return !empty($listing['full_name']) ? $listing['full_name'] : 'Unknown Seller';
}

function fetch_listing_by_id($id)
{
    global $mysqli;

    $stmt = $mysqli->prepare("
        SELECT
            l.*,
            c.name AS category_name,
            u.full_name,
            u.username,
            u.university,
            u.email,
            u.phone,
            u.city,
            u.country,
            u.is_verified,
            u.last_login
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
