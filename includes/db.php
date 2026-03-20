<?php
require_once __DIR__ . '/config.php';

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_errno) {
    die('Database connection failed. Please check includes/config.php and import unitrade_web.sql first.');
}

$mysqli->set_charset('utf8mb4');

// Lightweight schema sync for auth upgrades (safe to run repeatedly).
$usernameColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'username'");
if ($usernameColumn && $usernameColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN username VARCHAR(80) NULL AFTER full_name");
}
if ($usernameColumn instanceof mysqli_result) {
    $usernameColumn->free();
}

$usernameIndex = $mysqli->query("SHOW INDEX FROM users WHERE Key_name = 'unique_users_username'");
if ($usernameIndex && $usernameIndex->num_rows === 0) {
    $mysqli->query("CREATE UNIQUE INDEX unique_users_username ON users (username)");
}
if ($usernameIndex instanceof mysqli_result) {
    $usernameIndex->free();
}

$lastLoginColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'last_login'");
if ($lastLoginColumn && $lastLoginColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER created_at");
}
if ($lastLoginColumn instanceof mysqli_result) {
    $lastLoginColumn->free();
}

$firstNameColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'first_name'");
if ($firstNameColumn && $firstNameColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NULL AFTER full_name");
}
if ($firstNameColumn instanceof mysqli_result) {
    $firstNameColumn->free();
}

$middleNameColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'middle_name'");
if ($middleNameColumn && $middleNameColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN middle_name VARCHAR(100) NULL AFTER first_name");
}
if ($middleNameColumn instanceof mysqli_result) {
    $middleNameColumn->free();
}

$lastNameColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'last_name'");
if ($lastNameColumn && $lastNameColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NULL AFTER middle_name");
}
if ($lastNameColumn instanceof mysqli_result) {
    $lastNameColumn->free();
}

$isVerifiedColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
if ($isVerifiedColumn && $isVerifiedColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash");
    // Existing accounts should remain usable after migration.
    $mysqli->query("UPDATE users SET is_verified = 1");
}
if ($isVerifiedColumn instanceof mysqli_result) {
    $isVerifiedColumn->free();
}

$verificationExpiryColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'verification_expires_at'");
if ($verificationExpiryColumn && $verificationExpiryColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN verification_expires_at DATETIME NULL AFTER is_verified");
}
if ($verificationExpiryColumn instanceof mysqli_result) {
    $verificationExpiryColumn->free();
}

$verificationCodeColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'verification_code'");
if ($verificationCodeColumn && $verificationCodeColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN verification_code VARCHAR(10) NULL AFTER verification_expires_at");
}
if ($verificationCodeColumn instanceof mysqli_result) {
    $verificationCodeColumn->free();
}

$phoneColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'phone'");
if ($phoneColumn && $phoneColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL AFTER university");
}
if ($phoneColumn instanceof mysqli_result) {
    $phoneColumn->free();
}

$countryColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'country'");
if ($countryColumn && $countryColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN country VARCHAR(100) NULL AFTER phone");
}
if ($countryColumn instanceof mysqli_result) {
    $countryColumn->free();
}

$cityColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'city'");
if ($cityColumn && $cityColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN city VARCHAR(100) NULL AFTER country");
}
if ($cityColumn instanceof mysqli_result) {
    $cityColumn->free();
}

$addressColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'address'");
if ($addressColumn && $addressColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL AFTER city");
}
if ($addressColumn instanceof mysqli_result) {
    $addressColumn->free();
}

$postcodeColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'postcode'");
if ($postcodeColumn && $postcodeColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN postcode VARCHAR(20) NULL AFTER address");
}
if ($postcodeColumn instanceof mysqli_result) {
    $postcodeColumn->free();
}

$dobColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'dob'");
if ($dobColumn && $dobColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN dob DATE NULL AFTER postcode");
}
if ($dobColumn instanceof mysqli_result) {
    $dobColumn->free();
}

$profileCompleteColumn = $mysqli->query("SHOW COLUMNS FROM users LIKE 'profile_complete'");
if ($profileCompleteColumn && $profileCompleteColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN profile_complete TINYINT(1) NOT NULL DEFAULT 0 AFTER dob");
}
if ($profileCompleteColumn instanceof mysqli_result) {
    $profileCompleteColumn->free();
}

$mysqli->query("
    UPDATE users
    SET profile_complete = CASE
        WHEN username IS NOT NULL AND username <> ''
            AND university IS NOT NULL AND university <> ''
            AND phone IS NOT NULL AND phone <> ''
            AND country IS NOT NULL AND country <> ''
            AND city IS NOT NULL AND city <> ''
            AND address IS NOT NULL AND address <> ''
            AND postcode IS NOT NULL AND postcode <> ''
            AND dob IS NOT NULL
        THEN 1
        ELSE 0
    END
");

$passwordResetTableSql = "
    CREATE TABLE IF NOT EXISTS password_resets (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_password_resets_user_id (user_id),
        CONSTRAINT fk_password_resets_user
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
";

$mysqli->query($passwordResetTableSql);

$listingReportsTableSql = "
    CREATE TABLE IF NOT EXISTS listing_reports (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        listing_id INT UNSIGNED NOT NULL,
        reporter_user_id INT UNSIGNED NOT NULL,
        reason VARCHAR(50) NOT NULL,
        details TEXT NULL,
        status ENUM('Open', 'Reviewed', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_listing_reports_listing_id (listing_id),
        INDEX idx_listing_reports_reporter_user_id (reporter_user_id),
        INDEX idx_listing_reports_status (status),
        CONSTRAINT fk_listing_reports_listing
            FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
        CONSTRAINT fk_listing_reports_reporter
            FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
";

$mysqli->query($listingReportsTableSql);
