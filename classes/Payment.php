<?php

class Payment
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    // CREATE PAYMENT

    public function create(
        $request_id,
        $customer_id,
        $amount,
        $payment_method,
        $transaction_id = ""
    ) {

        $stmt = $this->conn->prepare(
            "INSERT INTO payments
            (
                request_id,
                customer_id,
                amount,
                payment_method,
                payment_status,
                transaction_id
            )
            VALUES (?,?,?,?, 'Paid', ?)"
        );

        $stmt->bind_param(
            "iidss",
            $request_id,
            $customer_id,
            $amount,
            $payment_method,
            $transaction_id
        );

        return $stmt->execute();
    }


    // ALL PAYMENTS

    public function getAll()
    {

        $sql = "
        SELECT
            p.*,
            c.name AS customer_name,
            c.phone AS customer_phone,
            r.device_type

        FROM payments p

        INNER JOIN users c
            ON p.customer_id = c.id

        INNER JOIN repair_requests r
            ON p.request_id = r.id

        ORDER BY p.id DESC
        ";

        return $this->conn->query($sql);
    }


    // CUSTOMER PAYMENTS

    public function getByCustomer($customer_id)
    {

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM payments
             WHERE customer_id=?
             ORDER BY id DESC"
        );

        $stmt->bind_param(
            "i",
            $customer_id
        );

        $stmt->execute();

        return $stmt->get_result();
    }


    // PAYMENT BY REQUEST

    public function getByRequest($request_id)
    {

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM payments
             WHERE request_id=?
             ORDER BY id DESC"
        );

        $stmt->bind_param(
            "i",
            $request_id
        );

        $stmt->execute();

        return $stmt->get_result();
    }
}