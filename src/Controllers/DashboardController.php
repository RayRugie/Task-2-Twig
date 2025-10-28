<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;

/**
 * Dashboard Controller
 * 
 * Handles dashboard display with statistics and charts using Chart.js.
 */
class DashboardController extends BaseController
{
    /**
     * Show dashboard with statistics and charts
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        // Get dashboard statistics filtered by user email
        $stats = [
            'total_tickets' => 0,
            'open_tickets' => 0,
            'closed_tickets' => 0,
        ];
        
        try {
            // Fetch tickets for this user only
            $tickets = $this->db->fetchAll(
                'SELECT * FROM tickets WHERE email = ?',
                [$user['email']]
            );
            
            // Calculate stats
            $stats['total_tickets'] = count($tickets);
            $stats['open_tickets'] = count(array_filter($tickets, fn($t) => $t['status'] === 'open'));
            $stats['closed_tickets'] = count(array_filter($tickets, fn($t) => $t['status'] === 'closed'));
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
     * Get dashboard statistics
     */
    private function getDashboardStats(array $user): array
    {
        $userRole = $user['role'] ?? 'user';
        
        // Initialize all stats to 0
        $stats = [
            'total_tickets' => 0,
            'open_tickets' => 0,
            'in_progress_tickets' => 0,
            'resolved_tickets' => 0,
            'closed_tickets' => 0,
            'high_priority_tickets' => 0,
            'my_assigned_tickets' => 0,
            'my_created_tickets' => 0,
        ];
        
        try {
            // Use Supabase count method
            $stats['total_tickets'] = $this->db->count('tickets', []);
            $stats['open_tickets'] = $this->db->count('tickets', ['status' => 'open']);
            $stats['in_progress_tickets'] = $this->db->count('tickets', ['status' => 'in_progress']);
            $stats['resolved_tickets'] = $this->db->count('tickets', ['status' => 'resolved']);
            $stats['closed_tickets'] = $this->db->count('tickets', ['status' => 'closed']);
            $stats['high_priority_tickets'] = 0; // TODO: Implement count with IN filter
            
            // My assigned tickets
            if ($userRole !== 'admin') {
                $stats['my_assigned_tickets'] = $this->db->count('tickets', ['assigned_to' => $user['id']]);
            } else {
                $stats['my_assigned_tickets'] = 0;
            }
            
            // Tickets created by me
            $stats['my_created_tickets'] = $this->db->count('tickets', ['created_by' => $user['id']]);
        } catch (\Exception $e) {
            // If query fails, return zeros (already set above)
            if (APP_DEBUG) {
                error_log('Dashboard stats error: ' . $e->getMessage());
            }
        }
        
        return $stats;
    }
    
    /**
     * Get recent tickets
     */
    private function getRecentTickets(array $user): array
    {
        try {
            // Get all tickets (Supabase will handle permissions via RLS)
            $allTickets = $this->db->fetchAll('tickets', [], 'created_at', 10);
            
            // Return the most recent tickets
            return array_slice($allTickets, 0, 10);
        } catch (\Exception $e) {
            error_log('Error fetching recent tickets: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get chart data for dashboard
     */
    private function getChartData(array $user): array
    {
        $chartData = [];
        
        try {
            // Get all tickets to calculate chart data
            $allTickets = $this->db->fetchAll('tickets', []);
            
            if (empty($allTickets)) {
                // Return empty chart data
                $chartData['status_distribution'] = ['labels' => [], 'data' => []];
                $chartData['priority_distribution'] = ['labels' => [], 'data' => []];
                $chartData['category_distribution'] = ['labels' => [], 'data' => []];
                $chartData['tickets_over_time'] = ['labels' => [], 'data' => []];
                return $chartData;
            }
            
            // Status distribution
            $statusCount = [];
            $priorityCount = [];
            $categoryCount = [];
            
            foreach ($allTickets as $ticket) {
                // Count by status
                $status = $ticket['status'] ?? 'unknown';
                $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
                
                // Count by priority
                $priority = $ticket['priority'] ?? 'unknown';
                $priorityCount[$priority] = ($priorityCount[$priority] ?? 0) + 1;
                
                // Count by category
                $category = $ticket['category'] ?? 'uncategorized';
                $categoryCount[$category] = ($categoryCount[$category] ?? 0) + 1;
            }
            
            $chartData['status_distribution'] = [
                'labels' => array_keys($statusCount),
                'data' => array_values($statusCount)
            ];
            
            $chartData['priority_distribution'] = [
                'labels' => array_keys($priorityCount),
                'data' => array_values($priorityCount)
            ];
            
            // Sort categories by count
            arsort($categoryCount);
            $chartData['category_distribution'] = [
                'labels' => array_slice(array_keys($categoryCount), 0, 10),
                'data' => array_slice(array_values($categoryCount), 0, 10)
            ];
            
            // Tickets over time (simple version)
            $chartData['tickets_over_time'] = [
                'labels' => ['Today'],
                'data' => [count($allTickets)]
            ];
            
        } catch (\Exception $e) {
            error_log('Error getting chart data: ' . $e->getMessage());
            $chartData = [
                'status_distribution' => ['labels' => [], 'data' => []],
                'priority_distribution' => ['labels' => [], 'data' => []],
                'category_distribution' => ['labels' => [], 'data' => []],
                'tickets_over_time' => ['labels' => [], 'data' => []],
            ];
        }
        
        return $chartData;
    }
    
    /**
     * API endpoint for chart data (AJAX)
     */
    public function chartData(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $chartType = $_GET['type'] ?? 'status';
        
        $chartData = $this->getChartData($user);
        
        $response = [];
        
        switch ($chartType) {
            case 'status':
                $response = $chartData['status_distribution'] ?? [];
                break;
            case 'priority':
                $response = $chartData['priority_distribution'] ?? [];
                break;
            case 'category':
                $response = $chartData['category_distribution'] ?? [];
                break;
            case 'time':
                $response = $chartData['tickets_over_time'] ?? [];
                break;
            case 'resolution':
                $userRole = $user['role'] ?? 'user';
                if ($userRole === 'admin') {
                    $response = $chartData['resolution_time'] ?? [];
                }
                break;
        }
        
        $this->json($response);
    }
}
