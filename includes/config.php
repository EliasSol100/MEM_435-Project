<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'unitrade_cy');

define('SITE_NAME', 'UniTrade CY');
define('UPLOAD_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024);

define('APP_BASE_URL', 'http://localhost/UniTradeCY');

define('MAIL_FROM_ADDRESS', 'admin@festival-web.com');
define('MAIL_FROM_NAME', 'UniTrade CY');
define('SMTP_HOST', 'premium245.web-hosting.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'admin@festival-web.com');
define('SMTP_PASSWORD', '!g3$~8tYju*D');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_ALLOW_SELF_SIGNED', true);
define('SMTP_DEBUG', 0);

date_default_timezone_set('Asia/Nicosia');
