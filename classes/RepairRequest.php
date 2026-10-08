<?php

class RepairRequest
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    // =====================================
    // CREATE REQUEST
    // =====================================

    public function create(
        $customer_id,
        $service_id,
        $device_type,
        $device_brand,
        $device_model,
        $problem_description,
        $priority
    ) {

        $serviceStmt = $this->conn->prepare(
            "SELECT price FROM services WHERE id=?"
        );

        $serviceStmt->bind_param(
            "i",
            $service_id
        );

        $serviceStmt->execute();

        $service = $serviceStmt
            ->get_result()
            ->fetch_assoc();

        $price = $service
            ? $service['price']
            : 0;


        $stmt = $this->conn->prepare(
            "INSERT INTO repair_requests
            (
                customer_id,
                service_id,
                device_type,
                device_brand,
                device_model,
                problem_description,
                estimated_cost,
                priority
            )
            VALUES (?,?,?,?,?,?,?,?)"
        );

        $stmt->bind_param(
            "iissssds",
            $customer_id,
            $service_id,
            $device_type,
            $device_brand,
            $device_model,
            $problem_description,
            $price,
            $priority
        );

        return $stmt->execute();
    }


    // =====================================
    // ALL REQUESTS
    // =====================================

    public function getAll()
    {

        $sql = "
        SELECT
            r.*,
            c.name AS customer_name,
            c.phone AS customer_phone,
            s.service_name,
            t.name AS technician_name

        FROM repair_requests r

        INNER JOIN users c
            ON r.customer_id = c.id

        INNER JOIN services s
            ON r.service_id = s.id

        LEFT JOIN users t
            ON r.technician_id = t.id

        ORDER BY r.id DESC
        ";

        return $this->conn->query($sql);
    }


    // =====================================
    // CUSTOMER REQUESTS
    // =====================================

    public function getByCustomer($customer_id)
    {

        $stmt = $this->conn->prepare(
            "SELECT
                r.*,
                s.service_name,
                t.name AS technician_name

             FROM repair_requests r

             INNER JOIN services s
                ON r.service_id = s.id

             LEFT JOIN users t
                ON r.technician_id = t.id

             WHERE r.customer_id = ?

             ORDER BY r.id DESC"
        );

        $stmt->bind_param(
            "i",
            $customer_id
        );

        $stmt->execute();

        return $stmt->get_result();
    }


    // =====================================
    // TECHNICIAN REQUESTS
    // =====================================

    public function getByTechnician($technician_id)
    {

        $stmt = $this->conn->prepare(
            "SELECT
                r.*,
                c.name AS customer_name,
                c.phone AS customer_phone,
                s.service_name

             FROM repair_requests r

             INNER JOIN users c
                ON r.customer_id = c.id

             INNER JOIN services s
                ON r.service_id = s.id

             WHERE r.technician_id = ?

             ORDER BY r.id DESC"
        );

        $stmt->bind_param(
            "i",
            $technician_id
        );

        $stmt->execute();

        return $stmt->get_result();
    }


    // =====================================
    // SINGLE REQUEST
    // =====================================

    public function getById($id)
    {

        $stmt = $this->conn->prepare(
            "SELECT
                r.*,
                c.name AS customer_name,
                c.email AS customer_email,
                c.phone AS customer_phone,
                c.address AS customer_address,
                s.service_name,
                s.description AS service_description,
                t.name AS technician_name,
                t.phone AS technician_phone

             FROM repair_requests r

             INNER JOIN users c
                ON r.customer_id = c.id

             INNER JOIN services s
                ON r.service_id = s.id

             LEFT JOIN users t
                ON r.technician_id = t.id

             WHERE r.id = ?"
        );

        $stmt->bind_param(
            "i",
            $id
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }


    // =====================================
    // ASSIGN TECHNICIAN
    // =====================================

    public function assignTechnician(
        $request_id,
        $technician_id
    ) {

        $stmt = $this->conn->prepare(
            "UPDATE repair_requests
             SET technician_id=?,
                 status='Assigned'
             WHERE id=?"
        );

        $stmt->bind_param(
            "ii",
            $technician_id,
            $request_id
        );

        return $stmt->execute();
    }


    // =====================================
    // UPDATE STATUS
    // =====================================

    public function updateStatus(
        $request_id,
        $status
    ) {

        if ($status === "Completed") {

            $stmt = $this->conn->prepare(
                "UPDATE repair_requests
                 SET status=?,
                     completed_date=NOW()
                 WHERE id=?"
            );

        } else {

            $stmt = $this->conn->prepare(
                "UPDATE repair_requests
                 SET status=?
                 WHERE id=?"
            );
        }

        $stmt->bind_param(
            "si",
            $status,
            $request_id
        );

        return $stmt->execute();
    }


    // =====================================
    // DELETE REQUEST
    // =====================================

    public function delete($id)
    {

        $stmt = $this->conn->prepare(
            "DELETE FROM repair_requests
             WHERE id=?"
        );

        $stmt->bind_param(
            "i",
            $id
        );

        return $stmt->execute();
    }
}