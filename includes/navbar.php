<nav class="navbar navbar-expand-lg navbar-dark fixed-top">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="<?php echo BASE_URL; ?>"
        >

            <i class="bi bi-tools"></i>

            FixMate

        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav ms-auto">


                <?php if (!isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="<?php echo BASE_URL; ?>"
                        >
                            Home
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="<?php echo BASE_URL; ?>auth/login.php"
                        >
                            Login
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="btn btn-light ms-lg-2"
                            href="<?php echo BASE_URL; ?>auth/register.php"
                        >
                            Register
                        </a>

                    </li>


                <?php else: ?>


                    <?php if ($_SESSION['role'] === 'admin'): ?>

                        <li class="nav-item">

                            <a
                                class="nav-link"
                                href="<?php echo BASE_URL; ?>admin/dashboard.php"
                            >
                                Dashboard
                            </a>

                        </li>

                    <?php elseif ($_SESSION['role'] === 'technician'): ?>

                        <li class="nav-item">

                            <a
                                class="nav-link"
                                href="<?php echo BASE_URL; ?>technician/dashboard.php"
                            >
                                Dashboard
                            </a>

                        </li>

                    <?php else: ?>

                        <li class="nav-item">

                            <a
                                class="nav-link"
                                href="<?php echo BASE_URL; ?>customer/dashboard.php"
                            >
                                Dashboard
                            </a>

                        </li>

                    <?php endif; ?>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="<?php echo BASE_URL; ?>auth/logout.php"
                        >

                            <i class="bi bi-box-arrow-right"></i>

                            Logout

                        </a>

                    </li>

                <?php endif; ?>


            </ul>

        </div>

    </div>

</nav>