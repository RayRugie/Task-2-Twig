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
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($user);
        
        // Get recent tickets
        $recentTickets = $this->getRecentTickets($user);
        
        // Get chart data
        $chartData = $this->getChartData($user);
        
        $this->render('dashboard/index.twig', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
            'chart_data' => $chartData
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(array $user): array
    {
        $stats = [];
        
        // Base query conditions for user permissions
        $userCondition = '';
        $params = [];
        
        if ($user['role'] !== 'admin') {
            $userCondition = 'WHERE (created_by = ? OR assigned_to = ?)';
            $params = [$user['id'], $user['id']];
        }
        
        // Total tickets
        $stats['total_tickets'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tickets {$userCondition}",
            $params
        )['count'];
        
        // Open tickets
        $openParams = array_merge($params, ['open']);
        $stats['open_tickets'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tickets {$userCondition} " . 
            ($userCondition ? 'AND' : 'WHERE') . " status = ?",
            $openParams
        )['count'];
        
        // In progress tickets
        $inProgressParams = array_merge($params, ['in_progress']);
        $stats['in_progress_tickets'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tickets {$userCondition} " . 
            ($userCondition ? 'AND' : 'WHERE') . " status = ?",
            $inProgressParams
        )['count'];
        
        // Resolved tickets
        $resolvedParams = array_merge($params, ['resolved']);
        $stats['resolved_tickets'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tickets {$userCondition} " . 
            ($userCondition ? 'AND' : 'WHERE') . " status = ?",
            $resolvedParams
        )['count'];
        
        // Closed tickets
        $closedParams = array_merge($params, ['closed']);
        $stats['closed_tickets'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tickets {$userCondition} " . 
            ($userCondition ? 'AND' : 'WHERE') . " status = ?",
            $closedParams
        )['count'];
        
        // High priority tickets
        $highPriorityParams = array_merge($params, ['high', 'urgent']);
        $stats['high_priority_tickets'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tickets {$userCondition} " . 
            ($userCondition ? 'AND' : 'WHERE') . " priority IN (?, ?)",
            $highPriorityParams
        )['count'];
        
        // My assigned tickets (if not admin)
        if ($user['role'] !== 'admin') {
            $stats['my_assigned_tickets'] = $this->db->fetchOne(
                'SELECT COUNT(*) as count FROM tickets WHERE assigned_to = ?',
                [$user['id']]
            )['count'];
        }
        
        // Tickets created by me
        $stats['my_created_tickets'] = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM tickets WHERE created_by = ?',
            [$user['id']]
        )['count'];
        
        return $stats;
    }
    
    /**
     * Get recent tickets
     */
    private function getRecentTickets(array $user): array
    {
        $userCondition = '';
        $params = [];
        
        if ($user['role'] !== 'admin') {
            $userCondition = 'WHERE (t.created_by = ? OR t.assigned_to = ?)';
            $params = [$user['id'], $user['id']];
        }
        
        $sql = "
            SELECT 
                t.*,
                creator.first_name as creator_first_name,
                creator.last_name as creator_last_name,
                assignee.first_name as assignee_first_name,
                assignee.last_name as assignee_last_name
            FROM tickets t
            LEFT JOIN users creator ON t.created_by = creator.id
            LEFT JOIN users assignee ON t.assigned_to = assignee.id
            {$userCondition}
            ORDER BY t.created_at DESC
            LIMIT 10
        ";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get chart data for dashboard
     */
    private function getChartData(array $user): array
    {
        $chartData = [];
        
        // Status distribution
        $userCondition = '';
        $params = [];
        
        if ($user['role'] !== 'admin') {
            $userCondition = 'WHERE (created_by = ? OR assigned_to = ?)';
            $params = [$user['id'], $user['id']];
        }
        
        $statusData = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM tickets {$userCondition} GROUP BY status ORDER BY status",
            $params
        );
        
        $chartData['status_distribution'] = [
            'labels' => array_column($statusData, 'status'),
            'data' => array_column($statusData, 'count')
        ];
        
        // Priority distribution
        $priorityData = $this->db->fetchAll(
            "SELECT priority, COUNT(*) as count FROM tickets {$userCondition} GROUP BY priority ORDER BY priority",
            $params
        );
        
        $chartData['priority_distribution'] = [
            'labels' => array_column($priorityData, 'priority'),
            'data' => array_column($priorityData, 'count')
        ];
        
        // Category distribution
        $categoryData = $this->db->fetchAll(
            "SELECT category, COUNT(*) as count FROM tickets {$userCondition} GROUP BY category ORDER BY count DESC LIMIT 10",
            $params
        );
        
        $chartData['category_distribution'] = [
            'labels' => array_column($categoryData, 'category'),
            'data' => array_column($categoryData, 'count')
        ];
        
        // Tickets created over time (last 30 days)
        $timeData = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM tickets 
            {$userCondition} " . 
            ($userCondition ? 'AND' : 'WHERE') . " 
            created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC",
            $params
        );
        
        $chartData['tickets_over_time'] = [
            'labels' => array_column($timeData, 'date'),
            'data' => array_column($timeData, 'count')
        ];
        
        // Average resolution time by category (for admin only)
        if ($user['role'] === 'admin') {
            $resolutionData = $this->db->fetchAll(
                "SELECT 
                    category,
                    AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours
                FROM tickets 
                WHERE status IN ('resolved', 'closed') 
                AND resolved_at IS NOT NULL
                GROUP BY category
                ORDER BY avg_hours DESC
                LIMIT 10"
            );
            
            $chartData['resolution_time'] = [
                'labels' => array_column($resolutionData, 'category'),
                'data' => array_map(function($hours) {
                    return round($hours, 1);
                }, array_column($resolutionData, 'avg_hours'))
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
                if ($user['role'] === 'admin') {
                    $response = $chartData['resolution_time'] ?? [];
                }
                break;
        }
        
        $this->json($response);
    }
}
