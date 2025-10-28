# Ticket Controller - Refactored to Match React Logic

## Summary of Changes

The TicketController has been completely refactored to match the React version's logic exactly.

### Key Changes:

1. **Simplified to Basic CRUD Only**
   - Removed: Categories, priorities, assigned_to, created_by, comments, attachments
   - Kept: title, description, status, email, created_at, updated_at

2. **Email-Based Filtering (Matches React)**
   - All queries now filter by `email` field (not by id or assigned_to)
   - React: `.eq("email", userEmail)` → PHP: `['email' => $user['email']]`

3. **Status Values (Matches React)**
   - Changed from: 'open', 'in_progress', 'resolved', 'closed'  
   - To: 'open', 'in-progress', 'resolved' (matches React exactly)

4. **Removed Complex Features**
   - No more ticket comments
   - No more ticket attachments  
   - No more user assignment
   - No more complex filters
   - No more pagination (React version doesn't paginate)

## Database Schema Required in Supabase

Your Supabase `tickets` table should have:

```sql
CREATE TABLE tickets (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  status TEXT NOT NULL CHECK (status IN ('open', 'in-progress', 'resolved')),
  email TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Enable Row Level Security
ALTER TABLE tickets ENABLE ROW LEVEL SECURITY;

-- Policy: Users can only see their own tickets
CREATE POLICY "Users can view own tickets" ON tickets
  FOR SELECT USING (auth.jwt() ->> 'email' = email);

-- Policy: Users can insert their own tickets
CREATE POLICY "Users can insert own tickets" ON tickets
  FOR INSERT WITH CHECK (auth.jwt() ->> 'email' = email);

-- Policy: Users can update their own tickets
CREATE POLICY "Users can update own tickets" ON tickets
  FOR UPDATE USING (auth.jwt() ->> 'email' = email);

-- Policy: Users can delete their own tickets
CREATE POLICY "Users can delete own tickets" ON tickets
  FOR DELETE USING (auth.jwt() ->> 'email' = email);
```

## Twig Template Recommendations

### tickets/index.twig

```twig
{% extends "base.twig" %}

{% block title %}Ticket Management - Ticketa{% endblock %}

{% block stylesheets %}
<link href="/css/tickets.css" rel="stylesheet">
{% endblock %}

{% block content %}
<div class="tickets-page">
    <button class="back-button" onclick="window.location.href='/dashboard'">
        ← Back
    </button>
    
    <div class="tickets-container">
        <h1>🎫 Ticket Management</h1>
        <p>Manage your tickets — create, edit, and resolve issues easily.</p>

        {# Ticket Form - Inline like React #}
        <form method="POST" action="/tickets" class="ticket-form">
            {{ csrf_token | raw }}
            
            <input type="text" name="title" placeholder="Ticket Title" required>
            
            <textarea name="description" placeholder="Description" required></textarea>
            
            <select name="status" required>
                <option value="open">Open</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>
            
            <button type="submit">Create Ticket</button>
        </form>

        {# Tickets List #}
        <div class="tickets-list">
            {% if tickets is empty %}
                <p class="no-tickets">No tickets found. Create one above!</p>
            {% else %}
                {% for ticket in tickets %}
                    <div class="ticket-card">
                        <h3>{{ ticket.title }}</h3>
                        <p>{{ ticket.description }}</p>
                        <span class="status status-{{ ticket.status }}">{{ ticket.status }}</span>
                        <small>{{ ticket.created_at }}</small>
                        
                        <div class="ticket-actions">
                            <button onclick="editTicket('{{ ticket.id }}')">Edit</button>
                            <button onclick="deleteTicket('{{ ticket.id }}')">Delete</button>
                        </div>
                    </div>
                {% endfor %}
            {% endif %}
        </div>
    </div>
</div>

{# Add edit form modal or inline editing #}
<script>
function editTicket(id) {
    // Fetch ticket and populate form
    // Similar to React's handleEdit
}

function deleteTicket(id) {
    if(confirm("Are you sure you want to delete this ticket?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/tickets/' + id + '/delete';
        form.innerHTML = '{{ csrf_token | raw }}';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
{% endblock %}
```

## Controller Methods Summary

| Method | Route | Matches React | Logic |
|--------|-------|--------------|-------|
| `index()` | GET /tickets | `fetchTickets()` | Fetches all tickets where email = user email |
| `store()` | POST /tickets | `handleSaveTicket()` (create) | Inserts new ticket with title, description, status, email |
| `update()` | POST /tickets/{id}/update | `handleSaveTicket()` (update) | Updates ticket where id and email match |
| `delete()` | POST /tickets/{id}/delete | `handleDelete()` | Deletes ticket by ID |

## Security Notes

1. **CSRF Protection**: All POST routes include `requireCSRF()`
2. **Authentication**: All routes require `requireAuth()`
3. **Email Scoping**: All queries automatically filter by user's email
4. **RLS**: Supabase Row Level Security ensures users can only access their own tickets

## Testing Checklist

- [ ] Can create new tickets
- [ ] Can view only your own tickets
- [ ] Can update your own tickets
- [ ] Can delete your own tickets
- [ ] Cannot see other users' tickets
- [ ] Cannot modify other users' tickets
- [ ] Flash messages display correctly
- [ ] Form validation works
- [ ] Redirects work properly

