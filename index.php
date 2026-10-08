<?php

require_once "config/config.php";

$pageTitle = "Home";

include "includes/header.php";

?>


<section class="hero">

    <div class="container">

        <div class="row align-items-center">


            <div class="col-lg-7">

                <span class="badge bg-primary mb-3 px-3 py-2">

                    <i class="bi bi-stars"></i>

                    Smart Repair Management

                </span>


                <h1>

                    Repair Management

                    <span class="text-primary">
                        Made Simple
                    </span>

                </h1>


                <p class="hero-text mt-4">

                    FixMate is a smart repair service
                    management system that helps customers
                    book repair services, technicians manage
                    repair requests and administrators manage
                    the complete repair business.

                </p>


                <div class="mt-4">


                    <?php if (!isset($_SESSION['user_id'])): ?>

                        <a
                            href="auth/register.php"
                            class="btn btn-primary btn-lg me-2"
                        >

                            <i class="bi bi-person-plus"></i>

                            Get Started

                        </a>


                        <a
                            href="auth/login.php"
                            class="btn btn-outline-dark btn-lg"
                        >

                            Login

                        </a>


                    <?php else: ?>

                        <?php

                        $dashboard = "customer/dashboard.php";

                        if ($_SESSION['role'] === "admin") {
                            $dashboard =
                                "admin/dashboard.php";
                        }

                        if ($_SESSION['role'] === "technician") {
                            $dashboard =
                                "technician/dashboard.php";
                        }

                        ?>

                        <a
                            href="<?php echo $dashboard; ?>"
                            class="btn btn-primary btn-lg"
                        >

                            Go to Dashboard

                            <i class="bi bi-arrow-right"></i>

                        </a>

                    <?php endif; ?>


                </div>

            </div>


            <div class="col-lg-5 mt-5 mt-lg-0">

                <div class="dashboard-card">

                    <div class="d-flex align-items-center mb-4">

                        <div class="stat-icon me-3">

                            <i class="bi bi-tools"></i>

                        </div>

                        <div>

                            <h5 class="mb-1">
                                Repair Management
                            </h5>

                            <small class="text-muted">
                                Everything in one place
                            </small>

                        </div>

                    </div>


                    <div class="mb-3">

                        <div class="d-flex justify-content-between">

                            <span>
                                Repair Requests
                            </span>

                            <strong>
                                Easy
                            </strong>

                        </div>

                        <div class="progress mt-2">

                            <div
                                class="progress-bar"
                                style="width: 90%"
                            ></div>

                        </div>

                    </div>


                    <div class="mb-3">

                        <div class="d-flex justify-content-between">

                            <span>
                                Technician Assignment
                            </span>

                            <strong>
                                Fast
                            </strong>

                        </div>

                        <div class="progress mt-2">

                            <div
                                class="progress-bar bg-success"
                                style="width: 85%"
                            ></div>

                        </div>

                    </div>


                    <div>

                        <div class="d-flex justify-content-between">

                            <span>
                                Payment Management
                            </span>

                            <strong>
                                Secure
                            </strong>

                        </div>

                        <div class="progress mt-2">

                            <div
                                class="progress-bar bg-warning"
                                style="width: 80%"
                            ></div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">
                Why Choose FixMate?
            </h2>

            <p class="text-muted">
                Complete repair service management
                solution.
            </p>

        </div>


        <div class="row g-4">


            <div class="col-md-4">

                <div class="dashboard-card h-100">

                    <div class="stat-icon mb-3">

                        <i class="bi bi-calendar-check"></i>

                    </div>

                    <h5>
                        Easy Service Booking
                    </h5>

                    <p class="text-muted">

                        Customers can easily submit
                        repair requests and track
                        their service status.

                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="dashboard-card h-100">

                    <div class="stat-icon mb-3">

                        <i class="bi bi-person-gear"></i>

                    </div>

                    <h5>
                        Technician Management
                    </h5>

                    <p class="text-muted">

                        Admin can assign repair requests
                        to available technicians.

                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="dashboard-card h-100">

                    <div class="stat-icon mb-3">

                        <i class="bi bi-credit-card"></i>

                    </div>

                    <h5>
                        Payment Tracking
                    </h5>

                    <p class="text-muted">

                        Manage repair payments and
                        transaction information easily.

                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<?php

include "includes/footer.php";

?>