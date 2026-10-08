<?php

require_once "../config/config.php";

require_once "../classes/User.php";


if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'admin') {

        header("Location: ../admin/dashboard.php");

    } elseif ($_SESSION['role'] === 'technician') {

        header("Location: ../technician/dashboard.php");

    } else {

        header("Location: ../customer/dashboard.php");
    }

    exit;
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? "");

    $password = $_POST['password'] ?? "";


    if ($email === "" || $password === "") {

        $error = "Please enter email and password.";

    } else {

        $database = new Database();

        $db = $database->connect();

        $userObj = new User($db);

        $user = $userObj->login(
            $email,
            $password
        );


        if ($user) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];

            $_SESSION['name'] = $user['name'];

            $_SESSION['email'] = $user['email'];

            $_SESSION['role'] = $user['role'];


            if ($user['role'] === 'admin') {

                header(
                    "Location: ../admin/dashboard.php"
                );

            } elseif ($user['role'] === 'technician') {

                header(
                    "Location: ../technician/dashboard.php"
                );

            } else {

                header(
                    "Location: ../customer/dashboard.php"
                );
            }

            exit;

        } else {

            $error = "Invalid email or password.";
        }
    }
}


$pageTitle = "Login";

include "../includes/header.php";

?>


<div class="auth-page">

    <div class="auth-card">

        <div class="text-center mb-4">

            <div class="auth-icon">

                <i class="bi bi-tools"></i>

            </div>

            <h2 class="fw-bold">
                Welcome Back
            </h2>

            <p class="text-muted">
                Login to your FixMate account
            </p>

        </div>


        <?php if ($error): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle"></i>

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="mb-3">

                <label class="form-label">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary w-100"
            >

                <i class="bi bi-box-arrow-in-right"></i>

                Login

            </button>


        </form>


        <div class="text-center mt-4">

            <p>
                Don't have an account?

                <a href="register.php">
                    Create Account
                </a>
            </p>

        </div>

    </div>

</div>


<?php include "../includes/footer.php"; ?>