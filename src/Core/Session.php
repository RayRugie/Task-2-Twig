<?php

namespace App\Core;

/**
 * Session Management Class
 * 
 * Handles user session management with security best practices.
 * Provides methods for login, logout, and session validation.
 */
class Session
{
    /**
     * Start secure session
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session security
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
        }
    }
    
    /**
     * Set session data
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session data
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session data
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Login user
     */
    public static function login(array $user): void
    {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set user data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['is_active'] = $user['is_active'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Clear any rate limiting
        Security::clearRateLimit($user['username']);
        Security::clearRateLimit($user['email']);
    }
    
    /**
     * Logout user
     */
    public static function logout(): void
    {
        // Clear all session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['login_time']) && 
               self::isSessionValid();
    }
    
    /**
     * Check if session is valid (not expired)
     */
    public static function isSessionValid(): bool
    {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }
        
        // Check session timeout
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user data
     */
    public static function getUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'role' => $_SESSION['role'],
            'is_active' => $_SESSION['is_active']
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool
    {
        return self::isLoggedIn() && $_SESSION['role'] === $role;
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }
    
    /**
     * Set flash message
     */
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get and clear flash message
     */
    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    
    /**
     * Get all flash messages
     */
    public static function getAllFlashes(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
    
    /**
     * Set form data for repopulation after validation errors
     */
    public static function setFormData(array $data): void
    {
        $_SESSION['form_data'] = $data;
    }
    
    /**
     * Get and clear form data
     */
    public static function getFormData(): array
    {
        $data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        return $data;
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        
        if (!self::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
    }
}
