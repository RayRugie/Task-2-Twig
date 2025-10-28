<?php

namespace App\Core;

/**
 * Session Management Class
 * 
 * Handles user session management with Supabase authentication.
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
     * Login user (store Supabase session info)
     */
    public static function login(array $userData): void
    {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['email'] = $userData['email'] ?? '';
        $_SESSION['access_token'] = $userData['access_token'] ?? '';
        $_SESSION['refresh_token'] = $userData['refresh_token'] ?? '';
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Logout user
     */
    public static function logout(): void
    {
        // Sign out from Supabase
        try {
            SupabaseClient::signOut();
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Logout error: ' . $e->getMessage());
            }
        }
        
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
        // Check PHP session
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if session is still valid (not expired)
        if (!isset($_SESSION['last_activity']) || 
            (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Update last activity timestamp
     */
    public static function updateActivity(): void
    {
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId(): ?string
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user data from session
     */
    public static function getUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        // Return data from session (set during login)
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['email'] ?? '',
            'access_token' => $_SESSION['access_token'] ?? '',
            'refresh_token' => $_SESSION['refresh_token'] ?? '',
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool
    {
        // Since Supabase doesn't have built-in roles like MySQL,
        // we'll check if user is authenticated
        return self::isLoggedIn();
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        // For now, all authenticated users have same permissions
        // You can extend this to check a user metadata table
        return self::isLoggedIn();
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
