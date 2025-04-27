<?php
require_once 'timezone.php';

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        try {
            $this->connect();
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {

        $host = "localhost";
        $user = "root";
        $pass = "123456";
        $name = "projectDB";

        if (!$host || !$user || !$name) {
            throw new Exception("Missing required database configuration");
        }

        $this->conn = new mysqli($host, $user, $pass, $name);

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    public function query($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $this->conn->error);
            }

            if (!empty($params)) {
                $types = '';
                $bindParams = [];
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $bindParams[] = $param;
                }
                $stmt->bind_param($types, ...$bindParams);
            }

            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }

            $result = $stmt->get_result();
            if ($result === false && $stmt->errno) {
                throw new Exception("Failed to get result: " . $stmt->error);
            }

            if ($result === false) {
                return $stmt->affected_rows;
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function beginTransaction()
    {
        $this->conn->begin_transaction();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function rollback()
    {
        $this->conn->rollback();
    }

    public function lastInsertId()
    {
        return $this->conn->insert_id;
    }

    public function escape($string)
    {
        return $this->conn->real_escape_string($string);
    }

    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function prepare(string $query)
    {
        return $this->conn->prepare($query);
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}