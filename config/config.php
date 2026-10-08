<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("SITE_NAME", "FixMate");

define(
    "BASE_URL",
    "http://localhost/fixmate/"
);

require_once __DIR__ . "/Database.php";