<?php
/**
 * Authentication Controller
 * Handles login, register, logout with PHP sessions + MySQL
 */

require_once __DIR__ . '/../db/db.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Login user
     * @return array ['success' => bool, 'message' => string]
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$email],
            's'
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];

        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Register new user
     * @return array ['success' => bool, 'message' => string]
     */
    public function register($username, $email, $password, $confirmPassword) {
        // Validate
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        // Check if email exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email],
            's'
        );
        if ($existing) {
            return ['success' => false, 'message' => 'Email is already registered'];
        }

        // Check if username exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = ?",
            [$username],
            's'
        );
        if ($existing) {
            return ['success' => false, 'message' => 'Username is already taken'];
        }

        // Hash password and insert
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $result = $this->db->execute(
            "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)",
            [$username, $email, $hash],
            'sss'
        );

        if ($result && isset($result['insert_id']) && $result['insert_id'] > 0) {
            // Auto-login after registration
            $_SESSION['user_id'] = $result['insert_id'];
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['is_admin'] = false;

            return ['success' => true, 'message' => 'Account created successfully'];
        }

        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'is_admin' => $_SESSION['is_admin']
        ];
    }
}
?>
