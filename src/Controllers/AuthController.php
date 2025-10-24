<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Security;
use App\Core\Session;

/**
 * Authentication Controller
 * 
 * Handles user authentication including login, logout, registration,
 * and profile management with proper security measures.
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
            'title' => 'Login',
            'form_data' => Session::getFormData()
        ]);
    }
    
    /**
     * Process login form
     */
    public function login(): void
    {
        $this->requireCSRF();
        
        $data = $this->sanitizeInput($this->getPostData());
        $errors = $this->validateRequired($data, ['username', 'password']);
        
        if (!empty($errors)) {
            $this->handleValidationErrors($errors, $data);
            $this->redirect('/login');
        }
        
        $username = $data['username'];
        $password = $data['password'];
        
        // Check rate limiting
        if (!Security::checkRateLimit($username)) {
            Session::setFlash('error', 'Too many login attempts. Please try again later.');
            $this->redirect('/login');
        }
        
        // Find user by username or email
        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
            [$username, $username]
        );
        
        if (!$user || !Security::verifyPassword($password, $user['password_hash'])) {
            Security::recordFailedAttempt($username);
            Session::setFlash('error', 'Invalid username or password.');
            $this->redirect('/login');
        }
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            Session::setFlash('error', 'Account is temporarily locked due to too many failed attempts.');
            $this->redirect('/login');
        }
        
        // Clear failed attempts and update last login
        $this->db->execute(
            'UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?',
            [$user['id']]
        );
        
        // Login user
        Session::login($user);
        
        // Redirect to intended page or dashboard
        $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        
        Session::setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
        $this->redirect($redirectTo);
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
            'title' => 'Register',
            'form_data' => Session::getFormData()
        ]);
    }
    
    /**
     * Process registration form
     */
    public function register(): void
    {
        $this->requireCSRF();
        
        $data = $this->sanitizeInput($this->getPostData());
        $errors = $this->validateRequired($data, ['username', 'email', 'password', 'confirm_password', 'first_name', 'last_name']);
        
        // Validate email format
        if (!empty($data['email']) && !Security::validateEmail($data['email'])) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Validate password strength
        if (!empty($data['password'])) {
            $passwordErrors = Security::validatePassword($data['password']);
            if (!empty($passwordErrors)) {
                $errors['password'] = implode('. ', $passwordErrors);
            }
        }
        
        // Check password confirmation
        if (!empty($data['password']) && $data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        // Check if username already exists
        if (!empty($data['username'])) {
            $existingUser = $this->db->fetchOne('SELECT id FROM users WHERE username = ?', [$data['username']]);
            if ($existingUser) {
                $errors['username'] = 'Username is already taken';
            }
        }
        
        // Check if email already exists
        if (!empty($data['email'])) {
            $existingEmail = $this->db->fetchOne('SELECT id FROM users WHERE email = ?', [$data['email']]);
            if ($existingEmail) {
                $errors['email'] = 'Email address is already registered';
            }
        }
        
        if (!empty($errors)) {
            $this->handleValidationErrors($errors, $data);
            $this->redirect('/register');
        }
        
        // Create new user
        $userId = $this->db->insert(
            'INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['username'],
                $data['email'],
                Security::hashPassword($data['password']),
                $data['first_name'],
                $data['last_name'],
                'user'
            ]
        );
        
        // Get the created user
        $user = $this->db->fetchOne('SELECT * FROM users WHERE id = ?', [$userId]);
        
        // Auto-login the new user
        Session::login($user);
        
        Session::setFlash('success', 'Welcome to ' . APP_NAME . ', ' . $user['first_name'] . '!');
        $this->redirect('/dashboard');
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
            'user' => $user,
            'form_data' => Session::getFormData()
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $user = $this->getCurrentUser();
        $data = $this->sanitizeInput($this->getPostData());
        $errors = $this->validateRequired($data, ['first_name', 'last_name', 'email']);
        
        // Validate email format
        if (!empty($data['email']) && !Security::validateEmail($data['email'])) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Check if email is already taken by another user
        if (!empty($data['email'])) {
            $existingEmail = $this->db->fetchOne(
                'SELECT id FROM users WHERE email = ? AND id != ?',
                [$data['email'], $user['id']]
            );
            if ($existingEmail) {
                $errors['email'] = 'Email address is already registered';
            }
        }
        
        // Handle password change if provided
        if (!empty($data['current_password']) || !empty($data['new_password'])) {
            $errors = array_merge($errors, $this->validateRequired($data, ['current_password', 'new_password', 'confirm_password']));
            
            if (empty($errors['current_password'])) {
                // Verify current password
                $currentUser = $this->db->fetchOne('SELECT password_hash FROM users WHERE id = ?', [$user['id']]);
                if (!Security::verifyPassword($data['current_password'], $currentUser['password_hash'])) {
                    $errors['current_password'] = 'Current password is incorrect';
                }
            }
            
            if (empty($errors['new_password'])) {
                $passwordErrors = Security::validatePassword($data['new_password']);
                if (!empty($passwordErrors)) {
                    $errors['new_password'] = implode('. ', $passwordErrors);
                }
            }
            
            if (!empty($data['new_password']) && $data['new_password'] !== $data['confirm_password']) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }
        
        if (!empty($errors)) {
            $this->handleValidationErrors($errors, $data);
            $this->redirect('/profile');
        }
        
        // Update user profile
        $updateFields = ['first_name = ?', 'last_name = ?', 'email = ?'];
        $updateValues = [$data['first_name'], $data['last_name'], $data['email']];
        
        // Update password if provided
        if (!empty($data['new_password'])) {
            $updateFields[] = 'password_hash = ?';
            $updateValues[] = Security::hashPassword($data['new_password']);
        }
        
        $updateValues[] = $user['id'];
        
        $this->db->execute(
            'UPDATE users SET ' . implode(', ', $updateFields) . ' WHERE id = ?',
            $updateValues
        );
        
        // Update session data
        $_SESSION['first_name'] = $data['first_name'];
        $_SESSION['last_name'] = $data['last_name'];
        $_SESSION['email'] = $data['email'];
        
        Session::setFlash('success', 'Profile updated successfully!');
        $this->redirect('/profile');
    }
}
