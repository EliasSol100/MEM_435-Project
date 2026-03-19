<?php
require_once __DIR__ . '/config.php';

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_errno) {
    die('Database connection failed. Please check includes/config.php and import schema.sql first.');
}

$mysqli->set_charset('utf8mb4');
