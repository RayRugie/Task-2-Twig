<?php

namespace App\Core;

use Twig\Environment;

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers including
 * Twig integration, request handling, and response methods.
 */
abstract class BaseController
{
    protected $twig;
    protected SupabaseDatabase $db;
    
    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->db = new SupabaseDatabase();
    }
    
    /**
     * Render a template
     */
    protected function render(string $template, array $data = []): void
    {
        echo $this->twig->render($template, $data);
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get POST data
     */
    protected function getPostData(): array
    {
        return $_POST;
    }
    
    /**
     * Get GET data
     */
    protected function getGetData(): array
    {
        return $_GET;
    }
    
    /**
     * Get request data (POST or GET)
     */
    protected function getRequestData(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF(): bool
    {
        $token = $this->getPostData()[CSRF_TOKEN_NAME] ?? '';
        return Security::verifyCSRFToken($token);
    }
    
    /**
     * Require CSRF token validation
     */
    protected function requireCSRF(): void
    {
        if (!$this->validateCSRF()) {
            Session::setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeInput(array $data): array
    {
        return Security::sanitizeInput($data);
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        return $errors;
    }
    
    /**
     * Handle validation errors
     */
    protected function handleValidationErrors(array $errors, array $formData = []): void
    {
        Session::setFlash('error', 'Please correct the following errors:');
        Session::setFormData($formData);
        
        foreach ($errors as $field => $message) {
            Session::setFlash("error_{$field}", $message);
        }
    }
    
    /**
     * Get pagination parameters
     */
    protected function getPaginationParams(int $defaultLimit = 10): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? $defaultLimit)));
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    /**
     * Calculate pagination info
     */
    protected function calculatePagination(?int $total, int $page, int $limit): array
    {
        $total = $total ?? 0;
        $totalPages = $total > 0 ? ceil($total / $limit) : 0;
        
        return [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $total,
            'items_per_page' => $limit,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null,
        ];
    }
    
    /**
     * Get current user
     */
    protected function getCurrentUser(): ?array
    {
        return Session::getUser();
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        Session::requireAuth();
    }
    
    /**
     * Require admin role
     */
    protected function requireAdmin(): void
    {
        Session::requireAdmin();
    }
}
