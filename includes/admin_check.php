<?php

require_once __DIR__ . "/auth_check.php";


if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {

    header(
        "Location: " .
        BASE_URL .
        "auth/login.php"
    );

    exit;
}