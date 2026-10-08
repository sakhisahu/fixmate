<?php

require_once "../includes/auth_check.php";

require_once "../config/config.php";
require_once "../classes/RepairRequest.php";
require_once "../classes/Service.php";


// Only customer can access
if ($_SESSION['role'] !== 'customer') {

    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'technician') {
        header("Location: ../technician/dashboard.php");
    } else {
        header("Location: ../auth/login.php");
    }

    exit;
}


$database = new Database();
$db = $database->connect();

$repairObj = new RepairRequest($db);
$serviceObj = new Service($db);


// Customer ID
$customerId = $_SESSION['user_id'];


// Get customer requests
$requests = $repairObj->getByCustomer($customerId);


// Convert result into array
$requestData = [];

while ($row = $requests->fetch_assoc()) {
    $requestData[] = $row;
}


// Statistics
$totalRequests = count($requestData);

$pendingRequests = 0;
$assignedRequests = 0;
$progressRequests = 0;
$completedRequests = 0;
$cancelledRequests = 0;

foreach ($requestData as $request) {

    switch ($request['status']) {

        case 'Pending':
            $pendingRequests++;
            break;

        case 'Assigned':
            $assignedRequests++;
            break;

        case 'In Progress':
            $progressRequests++;
            break;

        case 'Completed':
            $completedRequests++;
            break;

        case 'Cancelled':
            $cancelledRequests++;
            break;
    }
}


// Active services
$services = $serviceObj->getActiveServices();

$serviceData = [];

while ($service = $services->fetch_assoc()) {
    $serviceData[] = $service;
}


$pageTitle = "Customer Dashboard";

include "../includes/header.php";

?>


<div class="container-fluid px-4">


    <!-- ================================
         WELCOME SECTION
    ================================= -->

    <div class="dashboard-welcome mb-4">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <span class="welcome-badge">

                    <i class="bi bi-stars"></i>

                    Customer Portal

                </span>

                <h1 class="mt-3">

                    Welcome,

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['name']
                        );
                        ?>
                    </span>

                    👋

                </h1>

                <p>

                    Manage your repair requests,
                    book services and track your
                    device repairs from one place.

                </p>

            </div>


            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

                <a
                    href="book_service.php"
                    class="btn btn-light btn-lg"
                >

                    <i class="bi bi-plus-circle"></i>

                    Book New Repair

                </a>

            </div>

        </div>

    </div>


    <!-- ================================
         STATISTICS
    ================================= -->

    <div class="row g-4 mb-4">


        <!-- Total -->

        <div class="col-xl-3 col-md-6">

            <div class="customer-stat-card">

                <div class="stat-icon-box blue">

                    <i class="bi bi-clipboard2-check"></i>

                </div>

                <div>

                    <p>Total Requests</p>

                    <h3>
                        <?php echo $totalRequests; ?>
                    </h3>

                    <small>
                        All repair requests
                    </small>

                </div>

            </div>

        </div>


        <!-- Pending -->

        <div class="col-xl-3 col-md-6">

            <div class="customer-stat-card">

                <div class="stat-icon-box orange">

                    <i class="bi bi-clock-history"></i>

                </div>

                <div>

                    <p>Pending</p>

                    <h3>
                        <?php echo $pendingRequests; ?>
                    </h3>

                    <small>
                        Waiting for assignment
                    </small>

                </div>

            </div>

        </div>


        <!-- Progress -->

        <div class="col-xl-3 col-md-6">

            <div class="customer-stat-card">

                <div class="stat-icon-box purple">

                    <i class="bi bi-tools"></i>

                </div>

                <div>

                    <p>In Progress</p>

                    <h3>
                        <?php
                        echo
                            $assignedRequests
                            + $progressRequests;
                        ?>
                    </h3>

                    <small>
                        Currently being repaired
                    </small>

                </div>

            </div>

        </div>


        <!-- Completed -->

        <div class="col-xl-3 col-md-6">

            <div class="customer-stat-card">

                <div class="stat-icon-box green">

                    <i class="bi bi-check-circle"></i>

                </div>

                <div>

                    <p>Completed</p>

                    <h3>
                        <?php echo $completedRequests; ?>
                    </h3>

                    <small>
                        Successfully repaired
                    </small>

                </div>

            </div>

        </div>


    </div>


    <!-- ================================
         MAIN CONTENT
    ================================= -->

    <div class="row g-4">


        <!-- LEFT -->

        <div class="col-lg-8">


            <!-- Recent Requests -->

            <div class="customer-panel mb-4">

                <div class="panel-header">

                    <div>

                        <h4>
                            Recent Repair Requests
                        </h4>

                        <p>
                            Track your latest repair requests
                        </p>

                    </div>

                    <a
                        href="my_requests.php"
                        class="btn btn-outline-primary btn-sm"
                    >

                        View All

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>


                <?php if (count($requestData) > 0): ?>


                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>

                                <tr>

                                    <th>
                                        Request
                                    </th>

                                    <th>
                                        Device
                                    </th>

                                    <th>
                                        Service
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php

                            $recentRequests =
                                array_slice(
                                    $requestData,
                                    0,
                                    5
                                );

                            ?>


                            <?php foreach (
                                $recentRequests
                                as $request
                            ): ?>


                                <tr>


                                    <td>

                                        <strong>
                                            #FM-<?php
                                            echo
                                                str_pad(
                                                    $request['id'],
                                                    4,
                                                    '0',
                                                    STR_PAD_LEFT
                                                );
                                            ?>
                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $request[
                                                        'request_date'
                                                    ]
                                                )
                                            );
                                            ?>

                                        </small>

                                    </td>


                                    <td>

                                        <div class="device-info">

                                            <div class="device-icon">

                                                <i class="bi bi-phone"></i>

                                            </div>

                                            <div>

                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $request[
                                                            'device_type'
                                                        ]
                                                    );
                                                    ?>
                                                </strong>

                                                <small>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $request[
                                                            'device_brand'
                                                        ]
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $request[
                                                'service_name'
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        $status =
                                            $request['status'];

                                        $statusClass =
                                            "status-pending";

                                        if (
                                            $status ===
                                            "Assigned"
                                        ) {

                                            $statusClass =
                                                "status-assigned";

                                        } elseif (
                                            $status ===
                                            "In Progress"
                                        ) {

                                            $statusClass =
                                                "status-progress";

                                        } elseif (
                                            $status ===
                                            "Completed"
                                        ) {

                                            $statusClass =
                                                "status-completed";

                                        } elseif (
                                            $status ===
                                            "Cancelled"
                                        ) {

                                            $statusClass =
                                                "status-cancelled";
                                        }

                                        ?>


                                        <span
                                            class="status-badge <?php echo $statusClass; ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $status
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <a
                                            href="request_details.php?id=<?php echo $request['id']; ?>"
                                            class="btn btn-sm btn-light"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">

                            <i class="bi bi-clipboard-x"></i>

                        </div>

                        <h5>
                            No Repair Requests Yet
                        </h5>

                        <p>
                            You haven't booked any repair
                            service yet.
                        </p>

                        <a
                            href="book_service.php"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-plus-circle"></i>

                            Book Your First Repair

                        </a>

                    </div>


                <?php endif; ?>


            </div>


            <!-- Repair Journey -->

            <div class="customer-panel">

                <div class="panel-header">

                    <div>

                        <h4>
                            How FixMate Works
                        </h4>

                        <p>
                            Simple repair process
                        </p>

                    </div>

                </div>


                <div class="repair-steps">


                    <div class="repair-step">

                        <div class="step-number">
                            1
                        </div>

                        <div>

                            <h6>
                                Book Service
                            </h6>

                            <p>
                                Submit your device
                                repair request.
                            </p>

                        </div>

                    </div>


                    <div class="step-line"></div>


                    <div class="repair-step">

                        <div class="step-number">
                            2
                        </div>

                        <div>

                            <h6>
                                Technician Assigned
                            </h6>

                            <p>
                                Our technician will
                                handle your device.
                            </p>

                        </div>

                    </div>


                    <div class="step-line"></div>


                    <div class="repair-step">

                        <div class="step-number">
                            3
                        </div>

                        <div>

                            <h6>
                                Repair In Progress
                            </h6>

                            <p>
                                Track your repair
                                status online.
                            </p>

                        </div>

                    </div>


                    <div class="step-line"></div>


                    <div class="repair-step">

                        <div class="step-number">
                            4
                        </div>

                        <div>

                            <h6>
                                Get Device Back
                            </h6>

                            <p>
                                Repair completed
                                successfully.
                            </p>

                        </div>

                    </div>


                </div>

            </div>


        </div>


        <!-- RIGHT -->

        <div class="col-lg-4">


            <!-- Quick Actions -->

            <div class="customer-panel mb-4">

                <div class="panel-header">

                    <div>

                        <h4>
                            Quick Actions
                        </h4>

                        <p>
                            Manage your account
                        </p>

                    </div>

                </div>


                <div class="quick-actions">


                    <a
                        href="book_service.php"
                        class="quick-action"
                    >

                        <div class="quick-icon blue">

                            <i class="bi bi-plus-circle"></i>

                        </div>

                        <div>

                            <strong>
                                Book New Repair
                            </strong>

                            <small>
                                Submit a repair request
                            </small>

                        </div>

                        <i class="bi bi-chevron-right"></i>

                    </a>


                    <a
                        href="my_requests.php"
                        class="quick-action"
                    >

                        <div class="quick-icon purple">

                            <i class="bi bi-list-check"></i>

                        </div>

                        <div>

                            <strong>
                                My Requests
                            </strong>

                            <small>
                                Track all repairs
                            </small>

                        </div>

                        <i class="bi bi-chevron-right"></i>

                    </a>


                    <a
                        href="book_service.php"
                        class="quick-action"
                    >

                        <div class="quick-icon green">

                            <i class="bi bi-grid"></i>

                        </div>

                        <div>

                            <strong>
                                Browse Services
                            </strong>

                            <small>
                                Explore repair services
                            </small>

                        </div>

                        <i class="bi bi-chevron-right"></i>

                    </a>


                </div>

            </div>


            <!-- Popular Services -->

            <div class="customer-panel">

                <div class="panel-header">

                    <div>

                        <h4>
                            Repair Services
                        </h4>

                        <p>
                            Available services
                        </p>

                    </div>

                </div>


                <?php

                $displayServices =
                    array_slice(
                        $serviceData,
                        0,
                        5
                    );

                ?>


                <?php if (
                    count($displayServices) > 0
                ): ?>


                    <div class="service-list">


                    <?php foreach (
                        $displayServices
                        as $service
                    ): ?>


                        <div class="service-item">

                            <div class="service-item-icon">

                                <i class="bi bi-phone"></i>

                            </div>

                            <div class="service-item-info">

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $service[
                                            'service_name'
                                        ]
                                    );
                                    ?>

                                </strong>

                                <small>

                                    Starting ₹<?php
                                    echo number_format(
                                        $service['price'],
                                        0
                                    );
                                    ?>

                                </small>

                            </div>


                            <a
                                href="book_service.php"
                                class="service-arrow"
                            >

                                <i class="bi bi-arrow-up-right"></i>

                            </a>

                        </div>


                    <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <p class="text-muted">
                        No services available.
                    </p>


                <?php endif; ?>


            </div>


        </div>


    </div>


</div>


<?php

include "../includes/footer.php";

?>