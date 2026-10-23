<?php

declare(strict_types=1);

require_once __DIR__ . '/app.php';

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

function adminLogout(): void
{
    destroySession();
}
