<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\SupabaseClient;
use App\Core\Session;

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
        $data = $this->sanitizeInput($this->getPostData());
        
        // Validate required fields
        if (empty($data['email']) || empty($data['password'])) {
            Session::setFlash('error', 'Email and password are required.');
            $this->redirect('/login');
        }
        
        try {
            // Sign in with Supabase
            $response = SupabaseClient::signIn($data['email'], $data['password']);
            
            if (isset($response['data']) && !isset($response['error'])) {
                $authData = $response['data'];
                
                // Extract user info
                $accessToken = $authData['access_token'] ?? null;
                $user = $authData['user'] ?? null;
                
                if ($accessToken && $user) {
                    $userData = [
                        'id' => $user['id'] ?? null,
                        'email' => $user['email'] ?? null,
                        'access_token' => $accessToken,
                        'refresh_token' => $authData['refresh_token'] ?? '',
                    ];
                    
                    // Store user session
                    Session::login($userData);
                    
                    Session::setFlash('success', 'Welcome back!');
                    $this->redirect('/dashboard');
                } else {
                    Session::setFlash('error', 'Invalid email or password.');
                    $this->redirect('/login');
                }
            } else {
                $errorMsg = isset($response['error']) ? $response['error']->getMessage() : 'Invalid email or password.';
                Session::setFlash('error', $errorMsg);
                $this->redirect('/login');
            }
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during login. Please try again.');
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
