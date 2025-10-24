<?php

namespace App\Core;

/**
 * Security Helper Class
 * 
 * Provides security utilities including CSRF protection, input sanitization,
 * rate limiting, and other security measures.
 */
class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email address
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        return $errors;
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check rate limiting for login attempts
     */
    public static function checkRateLimit(string $identifier, int $maxAttempts = LOGIN_ATTEMPTS_LIMIT, int $lockoutTime = LOGIN_LOCKOUT_TIME): bool
    {
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'last_attempt' => 0,
                'locked_until' => 0
            ];
        }
        
        $rateLimit = $_SESSION[$key];
        $now = time();
        
        // Check if still locked out
        if ($rateLimit['locked_until'] > $now) {
            return false;
        }
        
        // Reset attempts if lockout period has passed
        if ($rateLimit['last_attempt'] + $lockoutTime < $now) {
            $_SESSION[$key]['attempts'] = 0;
            $_SESSION[$key]['locked_until'] = 0;
        }
        
        return $_SESSION[$key]['attempts'] < $maxAttempts;
    }
    
    /**
     * Record failed login attempt
     */
    public static function recordFailedAttempt(string $identifier, int $lockoutTime = LOGIN_LOCKOUT_TIME): void
    {
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'last_attempt' => 0,
                'locked_until' => 0
            ];
        }
        
        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = time();
        
        // Lock account if max attempts reached
        if ($_SESSION[$key]['attempts'] >= LOGIN_ATTEMPTS_LIMIT) {
            $_SESSION[$key]['locked_until'] = time() + $lockoutTime;
        }
    }
    
    /**
     * Clear rate limiting for successful login
     */
    public static function clearRateLimit(string $identifier): void
    {
        $key = 'rate_limit_' . md5($identifier);
        unset($_SESSION[$key]);
    }
    
    /**
     * Generate secure random string
     */
    public static function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload(array $file): array
    {
        $errors = [];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, UPLOAD_ALLOWED_TYPES)) {
            $errors[] = 'File type not allowed';
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Invalid file type';
        }
        
        return $errors;
    }
    
    /**
     * Sanitize filename for safe storage
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path information
        $filename = basename($filename);
        
        // Replace spaces and special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Ensure filename isn't empty
        if (empty($filename)) {
            $filename = 'file_' . time();
        }
        
        return $filename;
    }
}
