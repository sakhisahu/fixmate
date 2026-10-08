<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "customer") {
    header("Location: ../index.php");
    exit;
}

$customerId = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "fixmate");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

/* ----------------------------------------------------
   GET CUSTOMER REPAIR REQUESTS
---------------------------------------------------- */

$sql = "
    SELECT
        rr.id,
        rr.service_id,
        rr.device_type,
        rr.device_brand,
        rr.device_model,
        rr.problem_description,
        rr.estimated_cost,
        rr.priority,
        rr.status,
        rr.created_at,
        rr.technician_id
    FROM repair_requests rr
    WHERE rr.customer_id = ?
    ORDER BY rr.id DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $customerId);
$stmt->execute();

$result = $stmt->get_result();

$requests = [];

while ($row = $result->fetch_assoc()) {

    /* ---------------------------------------------
       GET SERVICE DETAILS
    --------------------------------------------- */

    $serviceName = "Repair Service";

    if (!empty($row['service_id'])) {

        $serviceStmt = $conn->prepare(
            "SELECT * FROM services WHERE id = ? LIMIT 1"
        );

        if ($serviceStmt) {

            $serviceStmt->bind_param(
                "i",
                $row['service_id']
            );

            $serviceStmt->execute();

            $serviceResult = $serviceStmt->get_result();

            if ($serviceRow = $serviceResult->fetch_assoc()) {

                /*
                 * Different databases may have different
                 * service-name column names.
                 */

                if (isset($serviceRow['name'])) {
                    $serviceName = $serviceRow['name'];
                } elseif (isset($serviceRow['service_name'])) {
                    $serviceName = $serviceRow['service_name'];
                } elseif (isset($serviceRow['title'])) {
                    $serviceName = $serviceRow['title'];
                } elseif (isset($serviceRow['service'])) {
                    $serviceName = $serviceRow['service'];
                }
            }

            $serviceStmt->close();
        }
    }

    /* ---------------------------------------------
       GET TECHNICIAN DETAILS
    --------------------------------------------- */

    $technicianName = "Not Assigned";
    $technicianPhone = "";

    if (!empty($row['technician_id'])) {

        $techStmt = $conn->prepare(
            "SELECT name, phone FROM users WHERE id = ? LIMIT 1"
        );

        if ($techStmt) {

            $techStmt->bind_param(
                "i",
                $row['technician_id']
            );

            $techStmt->execute();

            $techResult = $techStmt->get_result();

            if ($techRow = $techResult->fetch_assoc()) {
                $technicianName = $techRow['name'];
                $technicianPhone = $techRow['phone'];
            }

            $techStmt->close();
        }
    }

    $row['service_name'] = $serviceName;
    $row['technician_name'] = $technicianName;
    $row['technician_phone'] = $technicianPhone;

    $requests[] = $row;
}

$stmt->close();


/* ----------------------------------------------------
   STATISTICS
---------------------------------------------------- */

$totalRequests = count($requests);
$pendingRequests = 0;
$activeRequests = 0;
$completedRequests = 0;

foreach ($requests as $request) {

    $status = strtolower($request['status']);

    if ($status === "pending") {
        $pendingRequests++;
    }

    if (
        $status === "assigned" ||
        $status === "in progress" ||
        $status === "in_progress" ||
        $status === "repairing"
    ) {
        $activeRequests++;
    }

    if (
        $status === "completed" ||
        $status === "complete"
    ) {
        $completedRequests++;
    }
}

require_once "../includes/header.php";

?>

<style>

.requests-page {
    padding: 30px 0 60px;
}

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    font-weight: 800;
    margin-bottom: 5px;
}

.page-heading p {
    color: #6b7280;
}

/* Stats */

.stat-card {
    border: 0;
    border-radius: 18px;
    padding: 22px;
    background: #ffffff;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    height: 100%;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    margin-bottom: 15px;
    background: #eef2ff;
    color: #4f46e5;
}

.stat-card h3 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 3px;
}

.stat-card p {
    margin: 0;
    color: #6b7280;
}

/* Request Card */

.request-card {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    border: 1px solid #eef0f5;
}

.request-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
}

.service-title {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 5px;
}

.request-id {
    font-size: 13px;
    color: #6b7280;
}

.status-badge {
    padding: 8px 14px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.status-pending {
    background: #fff7ed;
    color: #c2410c;
}

.status-assigned {
    background: #eff6ff;
    color: #1d4ed8;
}

.status-progress {
    background: #eef2ff;
    color: #4338ca;
}

.status-completed {
    background: #ecfdf5;
    color: #047857;
}

.status-cancelled {
    background: #fef2f2;
    color: #b91c1c;
}

.status-default {
    background: #f3f4f6;
    color: #374151;
}

/* Info */

.info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.info-box {
    background: #f8fafc;
    border-radius: 14px;
    padding: 15px;
}

.info-box small {
    display: block;
    color: #6b7280;
    margin-bottom: 5px;
}

.info-box strong {
    font-size: 15px;
}

/* Problem */

.problem-box {
    background: #f8fafc;
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 18px;
}

.problem-box strong {
    display: block;
    margin-bottom: 5px;
}

.problem-box p {
    margin: 0;
    color: #4b5563;
}

/* Bottom */

.request-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #eee;
    padding-top: 18px;
}

.technician {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tech-icon {
    width: 40px;
    height: 40px;
    background: #eef2ff;
    color: #4f46e5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cost {
    font-size: 20px;
    font-weight: 800;
    color: #4f46e5;
}

.empty-state {
    background: white;
    border-radius: 20px;
    padding: 60px 25px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.05);
}

.empty-icon {
    font-size: 55px;
    color: #c7d2fe;
    margin-bottom: 15px;
}

@media (max-width: 900px) {

    .info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {

    .request-top,
    .request-bottom {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

}

</style>

<div class="requests-page">

    <div class="container">

        <!-- PAGE HEADER -->

        <div class="page-heading">

            <h1>
                <i class="bi bi-tools"></i>
                My Repair Requests
            </h1>

            <p>
                Track all your repair service requests in one place.
            </p>

        </div>


        <!-- SUCCESS MESSAGE -->

        <?php if (isset($_SESSION['success_message'])): ?>

            <div class="alert alert-success alert-dismissible fade show">

                <i class="bi bi-check-circle-fill"></i>

                <?php
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']);
                ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>

            </div>

        <?php endif; ?>


        <!-- STATISTICS -->

        <div class="row g-4 mb-4">

            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>

                    <h3><?php echo $totalRequests; ?></h3>

                    <p>Total Requests</p>

                </div>

            </div>


            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>

                    <h3><?php echo $pendingRequests; ?></h3>

                    <p>Pending</p>

                </div>

            </div>


            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-wrench-adjustable"></i>
                    </div>

                    <h3><?php echo $activeRequests; ?></h3>

                    <p>In Progress</p>

                </div>

            </div>


            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>

                    <h3><?php echo $completedRequests; ?></h3>

                    <p>Completed</p>

                </div>

            </div>

        </div>


        <!-- REQUESTS -->

        <?php if (empty($requests)): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    <i class="bi bi-clipboard-x"></i>
                </div>

                <h3>No Repair Requests Yet</h3>

                <p class="text-muted">
                    You haven't booked any repair service yet.
                </p>

                <a
                    href="book_service.php"
                    class="btn btn-primary px-4"
                >
                    <i class="bi bi-plus-circle"></i>
                    Book Repair Service
                </a>

            </div>

        <?php else: ?>


            <?php foreach ($requests as $request): ?>

                <?php

                $status = strtolower($request['status']);

                $statusClass = "status-default";

                if ($status === "pending") {
                    $statusClass = "status-pending";
                } elseif ($status === "assigned") {
                    $statusClass = "status-assigned";
                } elseif (
                    $status === "in progress" ||
                    $status === "in_progress" ||
                    $status === "repairing"
                ) {
                    $statusClass = "status-progress";
                } elseif (
                    $status === "completed" ||
                    $status === "complete"
                ) {
                    $statusClass = "status-completed";
                } elseif (
                    $status === "cancelled" ||
                    $status === "canceled"
                ) {
                    $statusClass = "status-cancelled";
                }

                ?>

                <div class="request-card">

                    <!-- TOP -->

                    <div class="request-top">

                        <div>

                            <div class="service-title">

                                <?php
                                echo htmlspecialchars(
                                    $request['service_name']
                                );
                                ?>

                            </div>

                            <div class="request-id">

                                Request #FM-<?php
                                echo str_pad(
                                    $request['id'],
                                    4,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                ?>

                                &nbsp; • &nbsp;

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($request['created_at'])
                                );
                                ?>

                            </div>

                        </div>


                        <span class="status-badge <?php echo $statusClass; ?>">

                            <?php
                            echo htmlspecialchars(
                                $request['status']
                            );
                            ?>

                        </span>

                    </div>


                    <!-- INFORMATION -->

                    <div class="info-grid">

                        <div class="info-box">

                            <small>
                                <i class="bi bi-phone"></i>
                                Device
                            </small>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $request['device_type']
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="info-box">

                            <small>
                                <i class="bi bi-phone-fill"></i>
                                Brand
                            </small>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $request['device_brand']
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="info-box">

                            <small>
                                <i class="bi bi-box"></i>
                                Model
                            </small>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $request['device_model']
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="info-box">

                            <small>
                                <i class="bi bi-exclamation-circle"></i>
                                Priority
                            </small>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $request['priority']
                                );
                                ?>
                            </strong>

                        </div>

                    </div>


                    <!-- PROBLEM -->

                    <div class="problem-box">

                        <strong>
                            <i class="bi bi-chat-left-text"></i>
                            Problem Description
                        </strong>

                        <p>
                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $request['problem_description']
                                )
                            );
                            ?>
                        </p>

                    </div>


                    <!-- BOTTOM -->

                    <div class="request-bottom">

                        <div class="technician">

                            <div class="tech-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>

                            <div>

                                <small class="text-muted d-block">
                                    Technician
                                </small>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $request['technician_name']
                                    );
                                    ?>

                                </strong>

                                <?php if (!empty($request['technician_phone'])): ?>

                                    <div class="small text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $request['technician_phone']
                                        );
                                        ?>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>


                        <div class="text-end">

                            <small class="text-muted d-block">
                                Estimated Cost
                            </small>

                            <div class="cost">

                                ₹<?php
                                echo number_format(
                                    (float)$request['estimated_cost'],
                                    2
                                );
                                ?>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>