<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;

/**
 * Home Controller
 * 
 * Handles the landing page and public-facing content.
 */
class HomeController extends BaseController
{
    /**
     * Show landing page
     */
    public function index(): void
    {
        // If user is logged in, redirect to dashboard
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->render('home/index.twig', [
            'title' => 'Welcome to ' . APP_NAME
        ]);
    }
}
