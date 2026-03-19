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
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2MB

date_default_timezone_set('Asia/Nicosia');
