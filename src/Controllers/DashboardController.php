<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;

/**
 * Dashboard Controller
 * 
 * Handles dashboard display with statistics filtered by user email.
 */
class DashboardController extends BaseController
{
    /**
     * Show dashboard with statistics
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $userEmail = $user['email'] ?? '';
        
        // Initialize statistics
        $stats = [
            'total_tickets' => 0,
            'open_tickets' => 0,
            'resolved_tickets' => 0,
            'closed_tickets' => 0,
        ];
        
        try {
            // Use REST-based counts directly from Supabase (server-side) for reliability
            $stats['total_tickets'] = $this->db->countRest('tickets', ['email' => $userEmail]);
            $stats['open_tickets'] = $this->db->countRest('tickets', ['email' => $userEmail, 'status' => 'open']);
            $stats['resolved_tickets'] = $this->db->countRest('tickets', ['email' => $userEmail, 'status' => 'resolved']);
            $stats['closed_tickets'] = $this->db->countRest('tickets', ['email' => $userEmail, 'status' => 'closed']);
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Dashboard stats error: ' . $e->getMessage());
            }
        }
        
        $this->render('dashboard/index.twig', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'user' => $user
        ]);
    }
    
    /**
     * API endpoint for real-time stats (AJAX)
     */
    public function getStats(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $userEmail = $user['email'] ?? '';
        
        $stats = [
            'total_tickets' => 0,
            'open_tickets' => 0,
            'resolved_tickets' => 0,
            'closed_tickets' => 0,
        ];
        
        try {
            // Fetch tickets filtered by user email
            $tickets = $this->db->fetchAll('tickets', ['email' => $userEmail]);
            
            if (!empty($tickets)) {
                $stats['total_tickets'] = count($tickets);
                
                foreach ($tickets as $ticket) {
                    $status = strtolower($ticket['status'] ?? '');
                    
                    switch ($status) {
                        case 'open':
                            $stats['open_tickets']++;
                            break;
                        case 'resolved':
                            $stats['resolved_tickets']++;
                            break;
                        case 'closed':
                            $stats['closed_tickets']++;
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Dashboard stats error: ' . $e->getMessage());
            }
        }
        
        $this->json($stats);
    }
    
    /**
     * Get chart data for dashboard
     */
    public function chartData(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $userEmail = $user['email'] ?? '';
        $chartType = $_GET['type'] ?? 'status';
        
        try {
            // Fetch tickets filtered by user email
            $tickets = $this->db->fetchAll('tickets', ['email' => $userEmail]);
            
            $response = [];
            
            switch ($chartType) {
                case 'status':
                    $statusCount = [];
                    foreach ($tickets as $ticket) {
                        $status = $ticket['status'] ?? 'unknown';
                        $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
                    }
                    $response = [
                        'labels' => array_keys($statusCount),
                        'data' => array_values($statusCount)
                    ];
                    break;
                    
                case 'priority':
                    $priorityCount = [];
                    foreach ($tickets as $ticket) {
                        $priority = $ticket['priority'] ?? 'unknown';
                        $priorityCount[$priority] = ($priorityCount[$priority] ?? 0) + 1;
                    }
                    $response = [
                        'labels' => array_keys($priorityCount),
                        'data' => array_values($priorityCount)
                    ];
                    break;
                    
                case 'category':
                    $categoryCount = [];
                    foreach ($tickets as $ticket) {
                        $category = $ticket['category'] ?? 'uncategorized';
                        $categoryCount[$category] = ($categoryCount[$category] ?? 0) + 1;
                    }
                    arsort($categoryCount);
                    $response = [
                        'labels' => array_slice(array_keys($categoryCount), 0, 10),
                        'data' => array_slice(array_values($categoryCount), 0, 10)
                    ];
                    break;
            }
            
            $this->json($response);
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Chart data error: ' . $e->getMessage());
            }
            $this->json(['labels' => [], 'data' => []]);
        }
    }
}