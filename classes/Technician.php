<?php

class Technician
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    // GET ALL TECHNICIANS

    public function getAll()
    {

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM users
             WHERE role='technician'
             ORDER BY id DESC"
        );

        $stmt->execute();

        return $stmt->get_result();
    }


    // GET TECHNICIAN

    public function getById($id)
    {

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM users
             WHERE id=?
             AND role='technician'"
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


    // ADD TECHNICIAN

    public function create(
        $name,
        $email,
        $phone,
        $password,
        $address
    ) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $this->conn->prepare(
            "INSERT INTO users
            (name,email,phone,password,role,address)
            VALUES (?,?,?,?, 'technician', ?)"
        );

        $stmt->bind_param(
            "sssss",
            $name,
            $email,
            $phone,
            $hashedPassword,
            $address
        );

        return $stmt->execute();
    }


    // DELETE TECHNICIAN

    public function delete($id)
    {

        $stmt = $this->conn->prepare(
            "DELETE FROM users
             WHERE id=?
             AND role='technician'"
        );

        $stmt->bind_param(
            "i",
            $id
        );

        return $stmt->execute();
    }
}