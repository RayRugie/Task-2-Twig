<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;

/**
 * Ticket Controller
 * 
 * Handles ticket CRUD operations, comments, and file attachments.
 */
class TicketController extends BaseController
{
    /**
     * List all tickets with filtering and pagination
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $filters = $this->getRequestData();
        $pagination = $this->getPaginationParams(15);
        
        // Build query conditions
        $conditions = ['1=1'];
        $params = [];
        
        // Filter by status
        if (!empty($filters['status'])) {
            $conditions[] = 't.status = ?';
            $params[] = $filters['status'];
        }
        
        // Filter by priority
        if (!empty($filters['priority'])) {
            $conditions[] = 't.priority = ?';
            $params[] = $filters['priority'];
        }
        
        // Filter by category
        if (!empty($filters['category'])) {
            $conditions[] = 't.category = ?';
            $params[] = $filters['category'];
        }
        
        // Filter by assigned user (for non-admin users, only show their tickets)
        if (!$user['role'] === 'admin') {
            $conditions[] = '(t.created_by = ? OR t.assigned_to = ?)';
            $params[] = $user['id'];
            $params[] = $user['id'];
        } elseif (!empty($filters['assigned_to'])) {
            $conditions[] = 't.assigned_to = ?';
            $params[] = $filters['assigned_to'];
        }
        
        // Search functionality
        if (!empty($filters['search'])) {
            $conditions[] = '(t.title LIKE ? OR t.description LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM tickets t WHERE {$whereClause}";
        $total = $this->db->fetchOne($countSql, $params)['total'];
        
        // Get tickets with pagination
        $sql = "
            SELECT 
                t.*,
                creator.first_name as creator_first_name,
                creator.last_name as creator_last_name,
                creator.username as creator_username,
                assignee.first_name as assignee_first_name,
                assignee.last_name as assignee_last_name,
                assignee.username as assignee_username
            FROM tickets t
            LEFT JOIN users creator ON t.created_by = creator.id
            LEFT JOIN users assignee ON t.assigned_to = assignee.id
            WHERE {$whereClause}
            ORDER BY t.created_at DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}
        ";
        
        $tickets = $this->db->fetchAll($sql, $params);
        
        // Get filter options
        $statuses = $this->db->fetchAll('SELECT DISTINCT status FROM tickets ORDER BY status');
        $priorities = $this->db->fetchAll('SELECT DISTINCT priority FROM tickets ORDER BY priority');
        $categories = $this->db->fetchAll('SELECT DISTINCT category FROM tickets ORDER BY category');
        $users = $this->db->fetchAll('SELECT id, first_name, last_name, username FROM users WHERE is_active = 1 ORDER BY first_name');
        
        $this->render('tickets/index.twig', [
            'title' => 'Tickets',
            'tickets' => $tickets,
            'filters' => $filters,
            'pagination' => $this->calculatePagination($total, $pagination['page'], $pagination['limit']),
            'statuses' => array_column($statuses, 'status'),
            'priorities' => array_column($priorities, 'priority'),
            'categories' => array_column($categories, 'category'),
            'users' => $users
        ]);
    }
    
    /**
     * Show create ticket form
     */
    public function create(): void
    {
        $this->requireAuth();
        
        $users = $this->db->fetchAll('SELECT id, first_name, last_name, username FROM users WHERE is_active = 1 ORDER BY first_name');
        
        $this->render('tickets/create.twig', [
            'title' => 'Create New Ticket',
            'users' => $users,
            'form_data' => Session::getFormData()
        ]);
    }
    
    /**
     * Store new ticket
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $data = $this->sanitizeInput($this->getPostData());
        $errors = $this->validateRequired($data, ['title', 'description', 'priority', 'category']);
        
        // Validate priority
        if (!empty($data['priority']) && !in_array($data['priority'], ['low', 'medium', 'high', 'urgent'])) {
            $errors['priority'] = 'Invalid priority level';
        }
        
        // Validate assigned user if provided
        if (!empty($data['assigned_to'])) {
            $user = $this->db->fetchOne('SELECT id FROM users WHERE id = ? AND is_active = 1', [$data['assigned_to']]);
            if (!$user) {
                $errors['assigned_to'] = 'Invalid user selected';
            }
        }
        
        if (!empty($errors)) {
            $this->handleValidationErrors($errors, $data);
            $this->redirect('/tickets/create');
        }
        
        $user = $this->getCurrentUser();
        
        // Create ticket
        $ticketId = $this->db->insert(
            'INSERT INTO tickets (title, description, priority, category, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['title'],
                $data['description'],
                $data['priority'],
                $data['category'],
                !empty($data['assigned_to']) ? $data['assigned_to'] : null,
                $user['id']
            ]
        );
        
        Session::setFlash('success', 'Ticket created successfully!');
        $this->redirect("/tickets/{$ticketId}");
    }
    
    /**
     * Show ticket details
     */
    public function show(array $params): void
    {
        $this->requireAuth();
        
        $ticketId = (int)$params['id'];
        $user = $this->getCurrentUser();
        
        // Get ticket with creator and assignee info
        $ticket = $this->db->fetchOne(
            'SELECT 
                t.*,
                creator.first_name as creator_first_name,
                creator.last_name as creator_last_name,
                creator.username as creator_username,
                assignee.first_name as assignee_first_name,
                assignee.last_name as assignee_last_name,
                assignee.username as assignee_username
            FROM tickets t
            LEFT JOIN users creator ON t.created_by = creator.id
            LEFT JOIN users assignee ON t.assigned_to = assignee.id
            WHERE t.id = ?',
            [$ticketId]
        );
        
        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('/tickets');
        }
        
        // Check if user can view this ticket
        if ($user['role'] !== 'admin' && $ticket['created_by'] !== $user['id'] && $ticket['assigned_to'] !== $user['id']) {
            Session::setFlash('error', 'You do not have permission to view this ticket.');
            $this->redirect('/tickets');
        }
        
        // Get ticket comments
        $comments = $this->db->fetchAll(
            'SELECT 
                tc.*,
                u.first_name,
                u.last_name,
                u.username
            FROM ticket_comments tc
            JOIN users u ON tc.user_id = u.id
            WHERE tc.ticket_id = ?
            ORDER BY tc.created_at ASC',
            [$ticketId]
        );
        
        // Get ticket attachments
        $attachments = $this->db->fetchAll(
            'SELECT 
                ta.*,
                u.first_name,
                u.last_name
            FROM ticket_attachments ta
            JOIN users u ON ta.user_id = u.id
            WHERE ta.ticket_id = ?
            ORDER BY ta.created_at ASC',
            [$ticketId]
        );
        
        // Get users for assignment dropdown
        $users = $this->db->fetchAll('SELECT id, first_name, last_name, username FROM users WHERE is_active = 1 ORDER BY first_name');
        
        $this->render('tickets/show.twig', [
            'title' => 'Ticket #' . $ticketId,
            'ticket' => $ticket,
            'comments' => $comments,
            'attachments' => $attachments,
            'users' => $users,
            'form_data' => Session::getFormData()
        ]);
    }
    
    /**
     * Show edit ticket form
     */
    public function edit(array $params): void
    {
        $this->requireAuth();
        
        $ticketId = (int)$params['id'];
        $user = $this->getCurrentUser();
        
        $ticket = $this->db->fetchOne('SELECT * FROM tickets WHERE id = ?', [$ticketId]);
        
        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('/tickets');
        }
        
        // Check permissions
        if ($user['role'] !== 'admin' && $ticket['created_by'] !== $user['id']) {
            Session::setFlash('error', 'You do not have permission to edit this ticket.');
            $this->redirect('/tickets');
        }
        
        $users = $this->db->fetchAll('SELECT id, first_name, last_name, username FROM users WHERE is_active = 1 ORDER BY first_name');
        
        $this->render('tickets/edit.twig', [
            'title' => 'Edit Ticket #' . $ticketId,
            'ticket' => $ticket,
            'users' => $users,
            'form_data' => Session::getFormData()
        ]);
    }
    
    /**
     * Update ticket
     */
    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $ticketId = (int)$params['id'];
        $user = $this->getCurrentUser();
        
        $ticket = $this->db->fetchOne('SELECT * FROM tickets WHERE id = ?', [$ticketId]);
        
        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('/tickets');
        }
        
        // Check permissions
        if ($user['role'] !== 'admin' && $ticket['created_by'] !== $user['id']) {
            Session::setFlash('error', 'You do not have permission to edit this ticket.');
            $this->redirect('/tickets');
        }
        
        $data = $this->sanitizeInput($this->getPostData());
        $errors = $this->validateRequired($data, ['title', 'description', 'priority', 'category', 'status']);
        
        // Validate status
        if (!empty($data['status']) && !in_array($data['status'], ['open', 'in_progress', 'resolved', 'closed'])) {
            $errors['status'] = 'Invalid status';
        }
        
        // Validate priority
        if (!empty($data['priority']) && !in_array($data['priority'], ['low', 'medium', 'high', 'urgent'])) {
            $errors['priority'] = 'Invalid priority level';
        }
        
        // Validate assigned user if provided
        if (!empty($data['assigned_to'])) {
            $assignedUser = $this->db->fetchOne('SELECT id FROM users WHERE id = ? AND is_active = 1', [$data['assigned_to']]);
            if (!$assignedUser) {
                $errors['assigned_to'] = 'Invalid user selected';
            }
        }
        
        if (!empty($errors)) {
            $this->handleValidationErrors($errors, $data);
            $this->redirect("/tickets/{$ticketId}/edit");
        }
        
        // Update ticket
        $updateData = [
            $data['title'],
            $data['description'],
            $data['status'],
            $data['priority'],
            $data['category'],
            !empty($data['assigned_to']) ? $data['assigned_to'] : null,
            $ticketId
        ];
        
        $this->db->execute(
            'UPDATE tickets SET title = ?, description = ?, status = ?, priority = ?, category = ?, assigned_to = ? WHERE id = ?',
            $updateData
        );
        
        // Add comment if status changed
        if ($ticket['status'] !== $data['status']) {
            $this->db->insert(
                'INSERT INTO ticket_comments (ticket_id, user_id, comment, is_internal) VALUES (?, ?, ?, ?)',
                [
                    $ticketId,
                    $user['id'],
                    "Status changed from {$ticket['status']} to {$data['status']}",
                    false
                ]
            );
        }
        
        Session::setFlash('success', 'Ticket updated successfully!');
        $this->redirect("/tickets/{$ticketId}");
    }
    
    /**
     * Delete ticket
     */
    public function delete(array $params): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $ticketId = (int)$params['id'];
        $user = $this->getCurrentUser();
        
        $ticket = $this->db->fetchOne('SELECT * FROM tickets WHERE id = ?', [$ticketId]);
        
        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('/tickets');
        }
        
        // Only admin or ticket creator can delete
        if ($user['role'] !== 'admin' && $ticket['created_by'] !== $user['id']) {
            Session::setFlash('error', 'You do not have permission to delete this ticket.');
            $this->redirect('/tickets');
        }
        
        $this->db->execute('DELETE FROM tickets WHERE id = ?', [$ticketId]);
        
        Session::setFlash('success', 'Ticket deleted successfully!');
        $this->redirect('/tickets');
    }
    
    /**
     * Add comment to ticket
     */
    public function addComment(array $params): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $ticketId = (int)$params['id'];
        $user = $this->getCurrentUser();
        
        $ticket = $this->db->fetchOne('SELECT * FROM tickets WHERE id = ?', [$ticketId]);
        
        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('/tickets');
        }
        
        // Check if user can comment on this ticket
        if ($user['role'] !== 'admin' && $ticket['created_by'] !== $user['id'] && $ticket['assigned_to'] !== $user['id']) {
            Session::setFlash('error', 'You do not have permission to comment on this ticket.');
            $this->redirect('/tickets');
        }
        
        $data = $this->sanitizeInput($this->getPostData());
        $errors = $this->validateRequired($data, ['comment']);
        
        if (!empty($errors)) {
            $this->handleValidationErrors($errors, $data);
            $this->redirect("/tickets/{$ticketId}");
        }
        
        $this->db->insert(
            'INSERT INTO ticket_comments (ticket_id, user_id, comment, is_internal) VALUES (?, ?, ?, ?)',
            [
                $ticketId,
                $user['id'],
                $data['comment'],
                $data['is_internal'] ?? false
            ]
        );
        
        Session::setFlash('success', 'Comment added successfully!');
        $this->redirect("/tickets/{$ticketId}");
    }
}
