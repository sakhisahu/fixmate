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

$error = "";

/* =====================================================
   GET ACTIVE SERVICES
===================================================== */

$services = [];

$serviceQuery = $conn->query(
    "SELECT * FROM services WHERE status = 'Active' ORDER BY id ASC"
);

if ($serviceQuery) {

    while ($row = $serviceQuery->fetch_assoc()) {
        $services[] = $row;
    }

}


/* =====================================================
   FORM SUBMISSION
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $serviceId = isset($_POST['service_id'])
        ? (int)$_POST['service_id']
        : 0;

    $deviceType = trim($_POST['device_type'] ?? "");
    $deviceBrand = trim($_POST['device_brand'] ?? "");
    $deviceModel = trim($_POST['device_model'] ?? "");
    $problemDescription = trim($_POST['problem_description'] ?? "");
    $priority = trim($_POST['priority'] ?? "Normal");


    /* -----------------------------------------------
       VALIDATION
    ------------------------------------------------ */

    if ($serviceId <= 0) {

        $error = "Please select a repair service.";

    } elseif ($deviceType === "") {

        $error = "Please select your device type.";

    } elseif ($deviceBrand === "") {

        $error = "Please enter device brand.";

    } elseif ($deviceModel === "") {

        $error = "Please enter device model.";

    } elseif ($problemDescription === "") {

        $error = "Please describe the problem.";

    } else {


        /* -------------------------------------------
           GET SELECTED SERVICE
        -------------------------------------------- */

        $selectedService = null;

        $serviceStmt = $conn->prepare(
            "SELECT * FROM services WHERE id = ? AND status = 'Active' LIMIT 1"
        );

        if ($serviceStmt) {

            $serviceStmt->bind_param(
                "i",
                $serviceId
            );

            $serviceStmt->execute();

            $serviceResult = $serviceStmt->get_result();

            if ($serviceResult->num_rows > 0) {

                $selectedService = $serviceResult->fetch_assoc();

            }

            $serviceStmt->close();

        }


        if (!$selectedService) {

            $error = "Selected repair service is not available.";

        } else {

            /* ---------------------------------------
               SERVICE PRICE
            ---------------------------------------- */

            $estimatedCost = (float)$selectedService['price'];

            $status = "Pending";


            /* ---------------------------------------
               INSERT REQUEST
            ---------------------------------------- */

            $insertSql = "
                INSERT INTO repair_requests
                (
                    customer_id,
                    service_id,
                    technician_id,
                    device_type,
                    device_brand,
                    device_model,
                    problem_description,
                    estimated_cost,
                    priority,
                    status
                )
                VALUES
                (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)
            ";

            $insertStmt = $conn->prepare($insertSql);

            if (!$insertStmt) {

                $error = "Database Error: " . $conn->error;

            } else {

                $insertStmt->bind_param(
                    "iissssdss",
                    $customerId,
                    $serviceId,
                    $deviceType,
                    $deviceBrand,
                    $deviceModel,
                    $problemDescription,
                    $estimatedCost,
                    $priority,
                    $status
                );


                if ($insertStmt->execute()) {

                    $_SESSION['success_message'] =
                        "Repair service booked successfully! Your request has been submitted.";

                    $insertStmt->close();
                    $conn->close();

                    header("Location: my_requests.php");
                    exit;

                } else {

                    $error =
                        "Unable to book repair service: "
                        . $insertStmt->error;

                }

                $insertStmt->close();

            }

        }

    }

}

?>

<?php require_once "../includes/header.php"; ?>


<style>

.book-page {
    padding: 35px 0 60px;
}

.book-header {
    margin-bottom: 30px;
}

.book-header h1 {
    font-weight: 800;
    margin-bottom: 8px;
}

.book-header p {
    color: #6b7280;
}


/* Main Card */

.booking-card {
    background: #ffffff;
    border-radius: 22px;
    padding: 30px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.06);
    border: 1px solid #eef0f5;
}


/* Section */

.form-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 18px;
}

.section-title i {
    color: #635bff;
    margin-right: 7px;
}


/* Service Cards */

.service-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.service-option {
    position: relative;
}

.service-option input {
    position: absolute;
    opacity: 0;
}

.service-label {
    display: block;
    cursor: pointer;
    border: 2px solid #e5e7eb;
    border-radius: 17px;
    padding: 20px;
    transition: 0.2s;
    height: 100%;
}

.service-label:hover {
    border-color: #a5b4fc;
    transform: translateY(-2px);
}

.service-option input:checked + .service-label {
    border-color: #635bff;
    background: #f5f3ff;
    box-shadow: 0 8px 20px rgba(99,91,255,0.12);
}

.service-name {
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 6px;
}

.service-description {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 12px;
}

.service-price {
    font-size: 21px;
    font-weight: 800;
    color: #635bff;
}

.service-days {
    color: #6b7280;
    font-size: 13px;
}


/* Forms */

.form-label {
    font-weight: 700;
    margin-bottom: 7px;
}

.form-control,
.form-select {
    border-radius: 12px;
    padding: 12px 14px;
    border: 1px solid #dfe3ea;
}

.form-control:focus,
.form-select:focus {
    border-color: #635bff;
    box-shadow: 0 0 0 3px rgba(99,91,255,0.10);
}


/* Priority */

.priority-box {
    display: flex;
    gap: 12px;
}

.priority-option {
    flex: 1;
}

.priority-option input {
    display: none;
}

.priority-label {
    display: block;
    text-align: center;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
}

.priority-option input:checked + .priority-label {
    border-color: #635bff;
    background: #f5f3ff;
    color: #4f46e5;
}


/* Info */

.info-box {
    background: #f5f3ff;
    border-radius: 15px;
    padding: 18px;
    margin-bottom: 25px;
    color: #4338ca;
}

.info-box i {
    margin-right: 7px;
}


/* Button */

.book-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 13px;
    background: linear-gradient(135deg, #635bff, #8b5cf6);
    color: white;
    font-size: 16px;
    font-weight: 800;
    transition: 0.2s;
}

.book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(99,91,255,0.25);
}


/* Error */

.error-box {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
    border-radius: 13px;
    padding: 14px 16px;
    margin-bottom: 22px;
    font-weight: 600;
}


/* Responsive */

@media(max-width: 768px) {

    .service-grid {
        grid-template-columns: 1fr;
    }

    .priority-box {
        flex-direction: column;
    }

    .booking-card {
        padding: 20px;
    }

}

</style>


<div class="book-page">

    <div class="container">

        <!-- HEADER -->

        <div class="book-header">

            <h1>
                <i class="bi bi-tools"></i>
                Book Repair Service
            </h1>

            <p>
                Tell us about your device and we'll take care of the rest.
            </p>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="error-box">

                <i class="bi bi-exclamation-circle-fill"></i>

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <div class="booking-card">

            <form
                method="POST"
                action=""
            >


                <!-- =====================================
                     SERVICE
                ====================================== -->

                <div class="form-section">

                    <div class="section-title">

                        <i class="bi bi-wrench-adjustable"></i>

                        Select Repair Service

                    </div>


                    <div class="service-grid">


                        <?php if (empty($services)): ?>

                            <div class="alert alert-warning">

                                No repair services are currently available.

                            </div>

                        <?php else: ?>


                            <?php foreach ($services as $service): ?>

                                <?php

                                /*
                                 * Service name compatibility
                                 */

                                if (isset($service['name'])) {

                                    $serviceName = $service['name'];

                                } elseif (isset($service['service_name'])) {

                                    $serviceName = $service['service_name'];

                                } elseif (isset($service['title'])) {

                                    $serviceName = $service['title'];

                                } else {

                                    $serviceName = "Repair Service";

                                }

                                ?>


                                <div class="service-option">

                                    <input
                                        type="radio"
                                        name="service_id"
                                        id="service_<?php echo $service['id']; ?>"
                                        value="<?php echo $service['id']; ?>"
                                        <?php
                                        if (
                                            isset($_POST['service_id']) &&
                                            $_POST['service_id'] == $service['id']
                                        ) {
                                            echo "checked";
                                        }
                                        ?>
                                    >


                                    <label
                                        class="service-label"
                                        for="service_<?php echo $service['id']; ?>"
                                    >

                                        <div class="service-name">

                                            <?php
                                            echo htmlspecialchars(
                                                $serviceName
                                            );
                                            ?>

                                        </div>


                                        <div class="service-description">

                                            <?php

                                            echo htmlspecialchars(
                                                $service['description'] ?? 
                                                "Professional repair service for your device."
                                            );

                                            ?>

                                        </div>


                                        <div>

                                            <span class="service-price">

                                                ₹<?php
                                                echo number_format(
                                                    (float)$service['price'],
                                                    0
                                                );
                                                ?>

                                            </span>

                                            <span class="service-days">

                                                &nbsp; • &nbsp;

                                                <?php
                                                echo htmlspecialchars(
                                                    $service['days'] ?? "1"
                                                );
                                                ?>

                                                day service

                                            </span>

                                        </div>

                                    </label>

                                </div>


                            <?php endforeach; ?>


                        <?php endif; ?>

                    </div>

                </div>


                <!-- =====================================
                     DEVICE INFORMATION
                ====================================== -->

                <div class="form-section">

                    <div class="section-title">

                        <i class="bi bi-phone"></i>

                        Device Information

                    </div>


                    <div class="row g-3">


                        <div class="col-md-4">

                            <label class="form-label">
                                Device Type
                            </label>

                            <select
                                name="device_type"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Device
                                </option>

                                <option
                                    value="Mobile"
                                    <?php
                                    echo (
                                        ($_POST['device_type'] ?? '') === 'Mobile'
                                    )
                                    ? 'selected'
                                    : '';
                                    ?>
                                >
                                    Mobile
                                </option>

                                <option
                                    value="Tablet"
                                    <?php
                                    echo (
                                        ($_POST['device_type'] ?? '') === 'Tablet'
                                    )
                                    ? 'selected'
                                    : '';
                                    ?>
                                >
                                    Tablet
                                </option>

                                <option
                                    value="Laptop"
                                    <?php
                                    echo (
                                        ($_POST['device_type'] ?? '') === 'Laptop'
                                    )
                                    ? 'selected'
                                    : '';
                                    ?>
                                >
                                    Laptop
                                </option>

                                <option
                                    value="Other"
                                    <?php
                                    echo (
                                        ($_POST['device_type'] ?? '') === 'Other'
                                    )
                                    ? 'selected'
                                    : '';
                                    ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Brand
                            </label>

                            <input
                                type="text"
                                name="device_brand"
                                class="form-control"
                                placeholder="e.g. Samsung"
                                value="<?php
                                echo htmlspecialchars(
                                    $_POST['device_brand'] ?? ''
                                );
                                ?>"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Model
                            </label>

                            <input
                                type="text"
                                name="device_model"
                                class="form-control"
                                placeholder="e.g. Galaxy S23"
                                value="<?php
                                echo htmlspecialchars(
                                    $_POST['device_model'] ?? ''
                                );
                                ?>"
                                required
                            >

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     PROBLEM
                ====================================== -->

                <div class="form-section">

                    <div class="section-title">

                        <i class="bi bi-chat-left-text"></i>

                        Problem Details

                    </div>


                    <label class="form-label">
                        Describe the Problem
                    </label>

                    <textarea
                        name="problem_description"
                        class="form-control"
                        rows="5"
                        placeholder="Example: Phone display is broken and touch is not working..."
                        required
                    ><?php
                    echo htmlspecialchars(
                        $_POST['problem_description'] ?? ''
                    );
                    ?></textarea>

                </div>


                <!-- =====================================
                     PRIORITY
                ====================================== -->

                <div class="form-section">

                    <div class="section-title">

                        <i class="bi bi-flag"></i>

                        Priority

                    </div>


                    <div class="priority-box">


                        <div class="priority-option">

                            <input
                                type="radio"
                                name="priority"
                                id="priority_normal"
                                value="Normal"
                                <?php
                                echo (
                                    ($_POST['priority'] ?? 'Normal') === 'Normal'
                                )
                                ? 'checked'
                                : '';
                                ?>
                            >

                            <label
                                for="priority_normal"
                                class="priority-label"
                            >
                                Normal
                            </label>

                        </div>


                        <div class="priority-option">

                            <input
                                type="radio"
                                name="priority"
                                id="priority_high"
                                value="High"
                                <?php
                                echo (
                                    ($_POST['priority'] ?? '') === 'High'
                                )
                                ? 'checked'
                                : '';
                                ?>
                            >

                            <label
                                for="priority_high"
                                class="priority-label"
                            >
                                High
                            </label>

                        </div>


                        <div class="priority-option">

                            <input
                                type="radio"
                                name="priority"
                                id="priority_urgent"
                                value="Urgent"
                                <?php
                                echo (
                                    ($_POST['priority'] ?? '') === 'Urgent'
                                )
                                ? 'checked'
                                : '';
                                ?>
                            >

                            <label
                                for="priority_urgent"
                                class="priority-label"
                            >
                                Urgent
                            </label>

                        </div>


                    </div>

                </div>


                <!-- INFO -->

                <div class="info-box">

                    <i class="bi bi-info-circle-fill"></i>

                    The displayed price is an estimated cost.
                    Final charges may vary after technician inspection.

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    name="book_service"
                    class="book-btn"
                >

                    <i class="bi bi-calendar-check"></i>

                    Book Repair Service

                </button>


            </form>

        </div>

    </div>

</div>


<?php require_once "../includes/footer.php"; ?>