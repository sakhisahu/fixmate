<?php

class Service
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    // GET ALL ACTIVE SERVICES

    public function getActiveServices()
    {

        $result = $this->conn->query(
            "SELECT * FROM services
             WHERE status = 'active'
             ORDER BY id DESC"
        );

        return $result;
    }


    // GET ALL SERVICES

    public function getAll()
    {

        $result = $this->conn->query(
            "SELECT * FROM services
             ORDER BY id DESC"
        );

        return $result;
    }


    // GET SERVICE

    public function getById($id)
    {

        $stmt = $this->conn->prepare(
            "SELECT * FROM services
             WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    // CREATE SERVICE

    public function create(
        $name,
        $description,
        $price,
        $days
    ) {

        $stmt = $this->conn->prepare(
            "INSERT INTO services
            (service_name,description,price,estimated_days)
            VALUES (?,?,?,?)"
        );

        $stmt->bind_param(
            "ssdi",
            $name,
            $description,
            $price,
            $days
        );

        return $stmt->execute();
    }


    // UPDATE SERVICE

    public function update(
        $id,
        $name,
        $description,
        $price,
        $days,
        $status
    ) {

        $stmt = $this->conn->prepare(
            "UPDATE services
             SET service_name=?,
                 description=?,
                 price=?,
                 estimated_days=?,
                 status=?
             WHERE id=?"
        );

        $stmt->bind_param(
            "ssd isi",
            $name,
            $description,
            $price,
            $days,
            $status,
            $id
        );

        return $stmt->execute();
    }


    // DELETE

    public function delete($id)
    {

        $stmt = $this->conn->prepare(
            "DELETE FROM services
             WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}