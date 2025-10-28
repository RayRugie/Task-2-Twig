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
            // Extract project reference ID from SUPABASE_URL
            // Format: https://xyzabc.supabase.co -> xyzabc
            $url = SUPABASE_URL;
            $url = str_replace(['https://', 'http://'], '', $url);
            $parts = explode('.', $url);
            $referenceId = $parts[0];
            
            self::$instance = new Supabase(
                SUPABASE_ANON_KEY,
                $referenceId
            );
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

