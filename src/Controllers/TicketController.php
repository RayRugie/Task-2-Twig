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
        
        try {
            // Fetch tickets for this user with explicit SQL (like React: .eq("email", userEmail))
            $tickets = $this->db->fetchAll(
                'SELECT * FROM tickets WHERE email = ? ORDER BY created_at DESC',
                [$user['email']]
            );
            
            if (APP_DEBUG && empty($tickets)) {
                error_log('No tickets found for user: ' . $user['email']);
            }
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('TicketController index error: ' . $e->getMessage());
            }
            $tickets = [];
            Session::setFlash('error', 'Failed to load tickets.');
        }
        
        $this->render('tickets/index.twig', [
            'title' => 'Ticket Management',
            'tickets' => $tickets
        ]);
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
            // Create ticket with email (exactly like React)
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
            // Get the ticket (with email check for security)
            $ticket = $this->db->fetchOne(
                'SELECT * FROM tickets WHERE id = ? AND email = ?',
                [$ticketId, $user['email']]
            );
            
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
            // Get the ticket first to ensure it belongs to this user (security check)
            $ticket = $this->db->fetchOne(
                'SELECT * FROM tickets WHERE id = ? AND email = ?',
                [$ticketId, $user['email']]
            );
            
            if (!$ticket) {
                Session::setFlash('error', 'Ticket not found.');
                $this->redirect('/tickets');
            }
            
            // Update ticket (matches React: .update().eq("id", id).eq("email", userEmail))
            $this->db->executeUpdate('tickets', 
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
            // Security: First check if the ticket belongs to this user
            $ticket = $this->db->fetchOne(
                'SELECT * FROM tickets WHERE id = ? AND email = ?',
                [$ticketId, $user['email']]
            );
            
            if (!$ticket) {
                Session::setFlash('error', 'Ticket not found or you do not have permission to delete it.');
                $this->redirect('/tickets');
            }
            
            // Only delete if the ticket belongs to this user (extra security - email check works here)
            $this->db->executeDelete('tickets', [
                'id' => $ticketId,
                'email' => $user['email']
            ]);
            
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

