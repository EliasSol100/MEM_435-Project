<?php
require_once __DIR__ . '/includes/functions.php';

set_flash('success', 'Email verification is the only active verification method. We redirected you to the code screen.');
redirect('verify.php');
