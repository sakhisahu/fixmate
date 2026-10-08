<?php

class User
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }


    // ==============================
    // REGISTER USER
    // ==============================

    public function register(
        $name,
        $email,
        $phone,
        $password,
        $role = "customer",
        $address = ""
    ) {

        $check = $this->conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {
            return false;
        }

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $this->conn->prepare(
            "INSERT INTO users
            (name,email,phone,password,role,address)
            VALUES (?,?,?,?,?,?)"
        );

        $stmt->bind_param(
            "ssssss",
            $name,
            $email,
            $phone,
            $hashedPassword,
            $role,
            $address
        );

        return $stmt->execute();
    }


    // ==============================
    // LOGIN
    // ==============================

    public function login($email, $password)
    {

        $stmt = $this->conn->prepare(
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (
                password_verify(
                    $password,
                    $user['password']
                )
            ) {

                return $user;
            }
        }

        return false;
    }


    // ==============================
    // GET USER BY ID
    // ==============================

    public function getById($id)
    {

        $stmt = $this->conn->prepare(
            "SELECT * FROM users WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    // ==============================
    // GET ALL USERS
    // ==============================

    public function getAll()
    {

        $result = $this->conn->query(
            "SELECT * FROM users ORDER BY id DESC"
        );

        return $result;
    }


    // ==============================
    // GET CUSTOMERS
    // ==============================

    public function getCustomers()
    {

        $stmt = $this->conn->prepare(
            "SELECT * FROM users
             WHERE role = 'customer'
             ORDER BY id DESC"
        );

        $stmt->execute();

        return $stmt->get_result();
    }


    // ==============================
    // GET TECHNICIANS
    // ==============================

    public function getTechnicians()
    {

        $stmt = $this->conn->prepare(
            "SELECT * FROM users
             WHERE role = 'technician'
             ORDER BY id DESC"
        );

        $stmt->execute();

        return $stmt->get_result();
    }


    // ==============================
    // DELETE USER
    // ==============================

    public function delete($id)
    {

        $stmt = $this->conn->prepare(
            "DELETE FROM users WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}