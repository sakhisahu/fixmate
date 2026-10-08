<?php

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "fixmate";

    protected $conn;

    public function connect()
    {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database
        );

        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }
}