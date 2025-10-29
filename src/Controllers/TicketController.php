<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;

/**
 * Ticket Controller - Simplified to match React version
 * 
 * Handles basic ticket CRUD operations filtered by user email.
 * Mirrors React Ticket management: fetch, create, update, delete.
 */
class TicketController extends BaseController
{
    /**
     * List all tickets for the logged-in user
     * Matches React: .select("*").eq("email", userEmail).order("created_at", { ascending: false })
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();

        // Query params: status filter, pagination, sort
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(20, (int)($_GET['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;

        // Build filters
        $filters = ['email' => $user['email']];
        if (in_array($status, ['open', 'in_progress', 'closed', 'resolved'], true)) {
            $filters['status'] = $status;
        }

        try {
            // Total for pagination
            $total = $this->db->countRest('tickets', $filters);

            // Fetch page of tickets ordered by created_at desc
            $tickets = $this->db->fetchRest('tickets', $filters, 'created_at', false, $perPage, $offset, '*');

            $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

            $this->render('tickets/index.twig', [
                'title' => 'Ticket Management',
                'tickets' => $tickets,
                'filter_status' => $status,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages,
                ],
            ]);
        } catch (\Throwable $e) {
            if (APP_DEBUG) {
                error_log('TicketController index error: ' . $e->getMessage());
            }
            Session::setFlash('error', 'Failed to load tickets.');
            $this->render('tickets/index.twig', [
                'title' => 'Ticket Management',
                'tickets' => [],
                'filter_status' => $status,
                'pagination' => [
                    'page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 1,
                    'has_prev' => false,
                    'has_next' => false,
                ],
            ]);
        }
    }
    
    /**
     * Create new ticket
     * Matches React: .insert([{ title, description, status, email }])
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $data = $this->sanitizeInput($this->getPostData());
        
        // Validate required fields (matches React validation)
        if (empty($data['title']) || empty($data['description']) || empty($data['status'])) {
            Session::setFlash('error', 'All fields are required.');
            Session::setFormData($data); // Keep form data for retry
            $this->redirect('/tickets/create');
        }
        
        // Validate status (requirements: "open", "in_progress", "closed")
        $validStatuses = ['open', 'in_progress', 'closed'];
        if (!in_array($data['status'], $validStatuses)) {
            Session::setFlash('error', 'Invalid status.');
            Session::setFormData($data); // Keep form data for retry
            $this->redirect('/tickets/create');
        }
        
        $user = $this->getCurrentUser();
        
        try {
            // Create ticket with email (REST, like Vue)
            $this->db->insert('tickets', [
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'],
                'email' => $user['email'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            Session::setFlash('success', '🎟️ Ticket created successfully!');
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Store ticket error: ' . $e->getMessage());
            }
            Session::setFlash('error', 'Something went wrong while creating the ticket.');
        }
        
        $this->redirect('/tickets');
    }
    
    /**
     * Show edit ticket form
     * Loads a single ticket for editing
     */
    public function edit(array $params): void
    {
        $this->requireAuth();
        
        $ticketId = $params['id'] ?? null;
        $user = $this->getCurrentUser();
        
        if (!$ticketId) {
            Session::setFlash('error', 'Invalid ticket ID.');
            $this->redirect('/tickets');
        }
        
        try {
            // Get the ticket (with email check for security) via REST to ensure auth headers apply
            $tickets = $this->db->fetchRest('tickets', ['id' => $ticketId, 'email' => $user['email']], null, false, 1, 0, '*');
            $ticket = is_array($tickets) && count($tickets) > 0 ? $tickets[0] : null;
            
            if (!$ticket) {
                Session::setFlash('error', 'Ticket not found.');
                $this->redirect('/tickets');
            }
            
            $this->render('tickets/edit.twig', [
                'title' => 'Edit Ticket',
                'ticket' => $ticket,
                'form_data' => Session::getFormData()
            ]);
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Edit ticket error: ' . $e->getMessage());
            }
            Session::setFlash('error', 'Failed to load ticket.');
            $this->redirect('/tickets');
        }
    }
    
    /**
     * Update ticket
     * Matches React: .update({ title, description, status, updated_at }).eq("id", id).eq("email", userEmail)
     */
    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $ticketId = $params['id'] ?? null;
        $user = $this->getCurrentUser();
        
        if (!$ticketId) {
            Session::setFlash('error', 'Invalid ticket ID.');
            $this->redirect('/tickets');
        }
        
        $data = $this->sanitizeInput($this->getPostData());
        
        // Validate required fields
        if (empty($data['title']) || empty($data['description']) || empty($data['status'])) {
            Session::setFlash('error', 'All fields are required.');
            Session::setFormData($data); // Keep form data for retry
            $this->redirect("/tickets/{$ticketId}/edit");
        }
        
        // Validate status (requirements: "open", "in_progress", "closed")
        $validStatuses = ['open', 'in_progress', 'closed'];
        if (!in_array($data['status'], $validStatuses)) {
            Session::setFlash('error', 'Invalid status.');
            Session::setFormData($data); // Keep form data for retry
            $this->redirect("/tickets/{$ticketId}/edit");
        }
        
        try {
            // Update ticket (REST, mirrors Vue eq filters)
            $this->db->update('tickets', 
                ['id' => $ticketId, 'email' => $user['email']], 
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
            
            Session::setFlash('success', '✅ Ticket updated successfully!');
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Update ticket error: ' . $e->getMessage());
            }
            Session::setFlash('error', 'Something went wrong while updating the ticket.');
        }
        
        $this->redirect('/tickets');
    }
    
    /**
     * Delete ticket
     * Security: Checks both ID and email to ensure user can only delete their own tickets
     */
    public function delete(array $params): void
    {
        $this->requireAuth();
        $this->requireCSRF();
        
        $ticketId = $params['id'] ?? null;
        $user = $this->getCurrentUser();
        
        if (!$ticketId) {
            Session::setFlash('error', 'Invalid ticket ID.');
            $this->redirect('/tickets');
        }
        
        try {
            // Mirror Vue logic: attempt delete with id + email; admins can delete by id
            $isAdmin = \App\Core\Session::isAdmin();
            $filters = ['id' => $ticketId];
            if (!$isAdmin && !empty($user['email'] ?? '')) {
                $filters['email'] = $user['email'];
            }

            // Execute delete; Supabase returns success even if 0 rows match, which matches the Vue UX
            $this->db->executeDelete('tickets', $filters);
            
            Session::setFlash('success', '🗑️ Ticket deleted successfully!');
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Delete ticket error: ' . $e->getMessage());
            }
            Session::setFlash('error', 'Failed to delete ticket.');
        }
        
        $this->redirect('/tickets');
    }
}

