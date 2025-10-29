<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\SupabaseClient;
use App\Core\Session;
use App\Core\Security;

/**
 * Authentication Controller
 * 
 * Handles user authentication using Supabase auth.
 */
class AuthController extends BaseController
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->render('auth/login.twig', [
            'title' => 'Login'
        ]);
    }
    
    /**
     * Process login form
     */
    public function login(): void
    {
        // Validate CSRF token
        $this->requireCSRF();
        
        $data = $this->sanitizeInput($this->getPostData());
        
        // Validate required fields
        if (empty($data['email']) || empty($data['password'])) {
            Session::setFlash('error', 'Email and password are required.');
            $this->redirect('/login');
            return;
        }
        
        try {
            // Sign in with Supabase
            $response = SupabaseClient::signIn($data['email'], $data['password']);
            
            // Log response for debugging (remove in production)
            if (APP_DEBUG) {
                error_log('Supabase login response: ' . print_r($response, true));
            }
            
            // Check if there's an error in the response
            if (isset($response['error']) && $response['error'] !== null) {
                $error = $response['error'];
                $errorMsg = ($error instanceof \Exception) ? $error->getMessage() : 'Invalid email or password.';
                
                if (APP_DEBUG) {
                    error_log('Login error from Supabase: ' . $errorMsg);
                }
                
                Session::setFlash('error', $errorMsg);
                $this->redirect('/login');
                return;
            }
            
            // Check if we have data - Supabase returns ['data' => [...auth data...], 'error' => null]
            if (isset($response['data']) && is_array($response['data'])) {
                $authData = $response['data'];
                
                // Supabase signInWithPassword returns data in this structure:
                // - access_token, refresh_token, expires_in, token_type at top level
                // - user object separately
                // - session object containing user and tokens
                $accessToken = $authData['access_token'] ?? null;
                $user = $authData['user'] ?? null;
                
                // If user not at top level, check session object
                if (!$user && isset($authData['session']['user'])) {
                    $user = $authData['session']['user'];
                }
                
                // Fallback: if we have token but no user object, log warning
                if ($accessToken && !$user) {
                    if (APP_DEBUG) {
                        error_log('Warning: Access token received but no user object in response. Full response: ' . json_encode($authData));
                    }
                    // Without user ID, we can't fully authenticate, but we'll proceed with email
                    // This might happen if Supabase API response structure is different
                }
                
                if ($accessToken) {
                    // Ensure we have at least email
                    $userEmail = $user['email'] ?? $data['email'];
                    $userId = $user['id'] ?? null;
                    
                    if (!$userId && APP_DEBUG) {
                        error_log('Warning: No user ID in response, login may have limited functionality');
                    }
                    
                    $userData = [
                        'id' => $userId,
                        'email' => $userEmail,
                        'access_token' => $accessToken,
                        'refresh_token' => $authData['refresh_token'] ?? '',
                    ];
                    
                    // Store user session
                    Session::login($userData);
                    
                    // Clear any rate limiting
                    Security::clearRateLimit($data['email']);
                    
                    Session::setFlash('success', 'Welcome back!');
                    $this->redirect('/dashboard');
                    return;
                }
            }
            
            // If we get here, login failed (no access token)
            if (APP_DEBUG) {
                error_log('Login failed: No access token in response. Response: ' . json_encode($response));
            }
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            error_log('Login exception: ' . $e->getMessage());
            if (APP_DEBUG) {
                error_log('Login exception trace: ' . $e->getTraceAsString());
            }
            
            $errorMsg = $e->getMessage();
            // Make error message user-friendly
            if (strpos($errorMsg, 'Invalid login credentials') !== false || 
                strpos($errorMsg, 'Email not confirmed') !== false ||
                strpos($errorMsg, 'invalid') !== false) {
                Session::setFlash('error', $errorMsg);
            } else {
                Session::setFlash('error', 'An error occurred during login. Please check your credentials and try again.');
            }
            $this->redirect('/login');
        }
    }
    
    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        // Redirect if already logged in
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->render('auth/register.twig', [
            'title' => 'Register'
        ]);
    }
    
    /**
     * Process registration form
     */
    public function register(): void
    {
        $data = $this->sanitizeInput($this->getPostData());
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['email', 'password']);
        
        // Validate email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Validate password strength
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            Session::setFormData($data);
            $this->redirect('/register');
        }
        
        try {
            // Sign up with Supabase
            $response = SupabaseClient::signUp($data['email'], $data['password']);
            
            if (isset($response['data']['user']) && !isset($response['error'])) {
                $user = $response['data']['user'];
                $userId = $user['id'] ?? null;
                
                if ($userId) {
                    // Create user profile in database
                    try {
                        $this->db->insert('profiles', [
                            'id' => $userId,
                            'email' => $data['email'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    } catch (\Exception $e) {
                        // Profile might already exist
                        error_log('Profile creation error: ' . $e->getMessage());
                    }
                    
                    // Auto sign in after registration
                    $signInResponse = SupabaseClient::signIn($data['email'], $data['password']);
                    
                    if (isset($signInResponse['data']) && !isset($signInResponse['error'])) {
                        $authData = $signInResponse['data'];
                        $accessToken = $authData['access_token'] ?? null;
                        $signInUser = $authData['user'] ?? null;
                        
                        if ($accessToken && $signInUser) {
                            $userData = [
                                'id' => $userId,
                                'email' => $data['email'],
                                'access_token' => $accessToken,
                                'refresh_token' => $authData['refresh_token'] ?? '',
                            ];
                            
                            Session::login($userData);
                            
                            Session::setFlash('success', 'Welcome! Your account has been created.');
                            $this->redirect('/dashboard');
                        }
                    }
                }
            }
            
            Session::setFlash('error', 'Failed to create account. Please try again.');
            $this->redirect('/register');
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during registration. Please try again.');
            $this->redirect('/register');
        }
    }
    
    /**
     * Logout user
     */
    public function logout(): void
    {
        Session::logout();
        Session::setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/');
    }
    
    /**
     * Show user profile
     */
    public function showProfile(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        $this->render('auth/profile.twig', [
            'title' => 'My Profile',
            'user' => $user
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $data = $this->sanitizeInput($this->getPostData());
        
        try {
            // Update profile in database
            $this->db->update('profiles', ['id' => $user['id']], [
                'email' => $data['email'] ?? $user['email'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            Session::setFlash('success', 'Profile updated successfully!');
            $this->redirect('/profile');
        } catch (\Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update profile. Please try again.');
            $this->redirect('/profile');
        }
    }
}
