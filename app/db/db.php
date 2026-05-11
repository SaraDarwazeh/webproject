<?php
/**
 * Database Connection - Singleton Pattern
 */

require_once __DIR__ . '/../config/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            die('Database Connection Error: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * Execute a prepared statement and return the result
     */
    public function query($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return false;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            // For INSERT/UPDATE/DELETE
            return [
                'affected_rows' => $stmt->affected_rows,
                'insert_id' => $stmt->insert_id
            ];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Shorthand: fetch all rows
     */
    public function fetchAll($sql, $params = [], $types = '') {
        return $this->query($sql, $params, $types);
    }

    /**
     * Shorthand: fetch single row
     */
    public function fetchOne($sql, $params = [], $types = '') {
        $result = $this->query($sql, $params, $types);
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    /**
     * Shorthand: execute (INSERT/UPDATE/DELETE)
     */
    public function execute($sql, $params = [], $types = '') {
        return $this->query($sql, $params, $types);
    }
}
?>
