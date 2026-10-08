<?php

require_once "../config/config.php";

require_once "../classes/User.php";


$error = "";

$success = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? "");

    $email = trim($_POST['email'] ?? "");

    $phone = trim($_POST['phone'] ?? "");

    $password = $_POST['password'] ?? "";

    $confirmPassword =
        $_POST['confirm_password'] ?? "";

    $address = trim($_POST['address'] ?? "");


    if (
        $name === "" ||
        $email === "" ||
        $phone === "" ||
        $password === ""
    ) {

        $error =
            "All required fields are mandatory.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error =
            "Please enter a valid email.";

    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {

        $error =
            "Phone number must contain 10 digits.";

    } elseif (strlen($password) < 6) {

        $error =
            "Password must be at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error =
            "Passwords do not match.";

    } else {

        $database = new Database();

        $db = $database->connect();

        $userObj = new User($db);


        if (
            $userObj->register(
                $name,
                $email,
                $phone,
                $password,
                "customer",
                $address
            )
        ) {

            $success =
                "Registration successful! You can now login.";

        } else {

            $error =
                "Email already registered.";
        }
    }
}


$pageTitle = "Register";

include "../includes/header.php";

?>


<div class="auth-page">

    <div class="auth-card register-card">

        <div class="text-center mb-4">

            <div class="auth-icon">

                <i class="bi bi-person-plus"></i>

            </div>

            <h2 class="fw-bold">
                Create Account
            </h2>

            <p class="text-muted">
                Register as a FixMate customer
            </p>

        </div>


        <?php if ($error): ?>

            <div class="alert alert-danger">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div class="alert alert-success">

                <?php echo htmlspecialchars($success); ?>

                <br>

                <a href="login.php">
                    Login now
                </a>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="row">


                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Full Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        required
                    >

                </div>


                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Phone *
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        maxlength="10"
                        required
                    >

                </div>


                <div class="col-12 mb-3">

                    <label class="form-label">
                        Email *
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                    >

                </div>


                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Password *
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Confirm Password *
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="col-12 mb-3">

                    <label class="form-label">
                        Address
                    </label>

                    <textarea
                        name="address"
                        class="form-control"
                        rows="3"
                    ></textarea>

                </div>


            </div>


            <button
                type="submit"
                class="btn btn-primary w-100"
            >

                <i class="bi bi-person-plus"></i>

                Create Account

            </button>


        </form>


        <div class="text-center mt-4">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>

    </div>

</div>


<?php include "../includes/footer.php"; ?>