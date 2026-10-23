<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/admin.php';
adminLogout();
redirect('login.php?logged_out=1');
