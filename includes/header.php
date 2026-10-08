<?php

require_once __DIR__ . "/../config/config.php";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php
        echo isset($pageTitle)
            ? $pageTitle . " | " . SITE_NAME
            : SITE_NAME;
        ?>
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- Custom CSS -->

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>assets/css/style.css"
    >

</head>


<body>

<?php include __DIR__ . "/navbar.php"; ?>

<main class="main-content">