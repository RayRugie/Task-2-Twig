<?php

namespace App\Core;

use Supabase\CreateClient as Supabase;

/**
 * Supabase Client Wrapper
 * 
 * Provides a singleton instance of the Supabase client for the application.
 */
class SupabaseClient
{
    private static $instance = null;
    
    /**
     * Get Supabase client instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            // Validate environment configuration
            $supabaseUrl = trim(SUPABASE_URL);
            $anonKey = trim(SUPABASE_ANON_KEY);

            if ($supabaseUrl === '' || $anonKey === '') {
                throw new \RuntimeException('Supabase is not configured. Please set SUPABASE_URL and SUPABASE_ANON_KEY in your .env');
            }

            // Parse and validate SUPABASE_URL
            $parsed = parse_url($supabaseUrl);
            $host = $parsed['host'] ?? '';

            // Accept only project hosts like *.supabase.co
            if ($host === 'supabase.com' || !str_ends_with($host, '.supabase.co')) {
                throw new \RuntimeException('Invalid SUPABASE_URL. Expected format: https://<project-ref>.supabase.co');
            }

            // Extract project ref from host: <project-ref>.supabase.co
            $parts = explode('.', $host);
            $referenceId = $parts[0] ?? '';
            if ($referenceId === '') {
                throw new \RuntimeException('Could not extract project reference from SUPABASE_URL');
            }

            self::$instance = new Supabase($anonKey, $referenceId);
        }
        
        return self::$instance;
    }
    
    /**
     * Get Supabase auth client
     */
    public static function auth()
    {
        return self::getInstance()->auth;
    }
    
    /**
     * Get Supabase database client (query builder)
     */
    public static function query()
    {
        return self::getInstance()->query;
    }
    
    /**
     * Get current user (from session storage)
     */
    public static function getUser()
    {
        // Get user from PHP session (stored during login)
        $userData = Session::getUser();
        return $userData;
    }
    
    /**
     * Get current session (from storage)
     */
    public static function getSession()
    {
        $user = self::getUser();
        if ($user && isset($user['access_token'])) {
            return [
                'access_token' => $user['access_token'],
                'refresh_token' => $user['refresh_token'] ?? null,
                'user' => $user
            ];
        }
        return null;
    }
    
    /**
     * Check if user is authenticated (based on PHP session)
     */
    public static function isAuthenticated(): bool
    {
        return Session::isLoggedIn();
    }
    
    /**
     * Sign up a new user
     */
    public static function signUp(string $email, string $password)
    {
        $auth = self::auth();
        return $auth->signUp([
            'email' => $email,
            'password' => $password
        ]);
    }
    
    /**
     * Sign in a user
     */
    public static function signIn(string $email, string $password)
    {
        $auth = self::auth();
        return $auth->signInWithPassword([
            'email' => $email,
            'password' => $password
        ]);
    }
    
    /**
     * Sign out the current user
     */
    public static function signOut()
    {
        $auth = self::auth();
        return $auth->signOut();
    }
}

