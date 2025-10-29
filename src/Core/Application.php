<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

/**
 * Main Application Class
 * 
 * Handles routing, request processing, and response generation.
 * Integrates Twig templating and manages the application lifecycle.
 */
class Application
{
    private $twig;
    private array $routes = [];
    
    public function __construct()
    {
        // Initialize session
        Session::start();
        
        // Initialize Supabase (will auto-initialize)
        try {
            SupabaseClient::getInstance();
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase initialization error: ' . $e->getMessage());
            }
        }
        
        $this->initializeTwig();
        $this->registerRoutes();
    }
    
    /**
     * Initialize Twig templating engine
     */
    private function initializeTwig(): void
    {
        try {
            // Check if Twig classes exist
            if (class_exists('Twig\Loader\FilesystemLoader') && class_exists('Twig\Environment')) {
                $loader = new \Twig\Loader\FilesystemLoader(TEMPLATES_PATH);
                $this->twig = new \Twig\Environment($loader, [
                    'cache' => APP_DEBUG ? false : APP_ROOT . '/cache/twig',
                    'debug' => APP_DEBUG,
                    'auto_reload' => APP_DEBUG,
                ]);
                
                if (APP_DEBUG && class_exists('Twig\Extension\DebugExtension')) {
                    $this->twig->addExtension(new \Twig\Extension\DebugExtension());
                }
            } else {
                throw new \Exception('Twig classes not available');
            }
        } catch (\Exception $e) {
            // Fallback to simple template system
            $this->twig = new SimpleTemplateEngine();
        }
        
        // Add global variables
        $this->twig->addGlobal('app_name', APP_NAME);
        $this->twig->addGlobal('app_version', APP_VERSION);
        $this->twig->addGlobal('user', Session::getUser());
        $this->twig->addGlobal('csrf_token', Security::generateCSRFToken());
        $this->twig->addGlobal('csrf_token_name', CSRF_TOKEN_NAME);
        $this->twig->addGlobal('flash_messages', Session::getAllFlashes());
    }
    
    /**
     * Register application routes
     */
    private function registerRoutes(): void
    {
        // Public routes
        $this->routes = [
            'GET' => [
                '/' => ['App\Controllers\HomeController', 'index'],
                '/login' => ['App\Controllers\AuthController', 'showLogin'],
                '/register' => ['App\Controllers\AuthController', 'showRegister'],
                '/logout' => ['App\Controllers\AuthController', 'logout'],
            ],
            'POST' => [
                '/login' => ['App\Controllers\AuthController', 'login'],
                '/register' => ['App\Controllers\AuthController', 'register'],
            ]
        ];
        
        // Protected routes (require authentication)
        $protectedRoutes = [
            'GET' => [
                '/dashboard' => ['App\Controllers\DashboardController', 'index'],
                '/tickets' => ['App\Controllers\TicketController', 'index'],
                '/tickets/create' => ['App\Controllers\TicketController', 'create'],
                '/tickets/{id}' => ['App\Controllers\TicketController', 'show'],
                '/tickets/{id}/edit' => ['App\Controllers\TicketController', 'edit'],
                '/profile' => ['App\Controllers\AuthController', 'showProfile'],
            ],
            'POST' => [
                '/tickets' => ['App\Controllers\TicketController', 'store'],
                '/tickets/{id}/update' => ['App\Controllers\TicketController', 'update'],
                '/tickets/{id}/delete' => ['App\Controllers\TicketController', 'delete'],
                '/tickets/{id}/comments' => ['App\Controllers\TicketController', 'addComment'],
                '/profile/update' => ['App\Controllers\AuthController', 'updateProfile'],
            ]
        ];
        
        // Merge protected routes
        foreach ($protectedRoutes as $method => $routes) {
            foreach ($routes as $path => $handler) {
                $this->routes[$method][$path] = $handler;
            }
        }
    }
    
    /**
     * Run the application
     */
    public function run(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = substr($requestUri, 0, -1);
        }
        
        // Check for exact route match first
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $this->handleRoute($this->routes[$requestMethod][$requestUri]);
            return;
        }
        
        // Check for parameterized routes
        $params = [];
        foreach ($this->routes[$requestMethod] ?? [] as $route => $handler) {
            if ($this->matchRoute($route, $requestUri, $params)) {
                $this->handleRoute($handler, $params);
                return;
            }
        }
        
        // No route found
        $this->handle404();
    }
    
    /**
     * Match parameterized route
     */
    private function matchRoute(string $route, string $uri, array &$params = []): bool
    {
        $params = [];
        
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameter names from route
            preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
            
            // Map parameter values to names
            for ($i = 1; $i < count($matches); $i++) {
                $params[$paramNames[1][$i - 1]] = $matches[$i];
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle route execution
     */
    private function handleRoute(array $handler, array $params = []): void
    {
        [$controllerClass, $method] = $handler;
        
        // Check if route requires authentication
        if (!$this->isPublicRoute($handler)) {
            Session::requireAuth();
        }
        
        // Instantiate controller
        $controller = new $controllerClass($this->twig);
        
        // Call controller method
        if (method_exists($controller, $method)) {
            $controller->$method($params);
        } else {
            throw new \Exception("Method {$method} not found in {$controllerClass}");
        }
    }
    
    /**
     * Check if route is public (doesn't require authentication)
     */
    private function isPublicRoute(array $handler): bool
    {
        [$controllerClass, $method] = $handler;
        
        $publicRoutes = [
            'App\Controllers\HomeController::index',
            'App\Controllers\AuthController::showLogin',
            'App\Controllers\AuthController::showRegister',
            'App\Controllers\AuthController::login',
            'App\Controllers\AuthController::register',
            'App\Controllers\AuthController::logout',
        ];
        
        return in_array($controllerClass . '::' . $method, $publicRoutes);
    }
    
    /**
     * Handle 404 Not Found
     */
    private function handle404(): void
    {
        http_response_code(404);
        echo $this->twig->render('errors/404.twig', [
            'title' => 'Page Not Found'
        ]);
    }
    
    /**
     * Get Twig environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
