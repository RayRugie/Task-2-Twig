# Supabase Integration for Twig Application

## Overview

The Twig application has been updated to use Supabase as the backend instead of MySQL/PDO. This matches the architecture of the React version.

## Changes Made

### 1. Dependencies Added
- **Supabase PHP SDK**: Added to `composer.json` - `supabase/supabase-php`

### 2. Configuration Files Updated

#### `config/app.php`
- Added Supabase URL configuration
- Added Supabase anon key configuration
- Added Supabase service role key configuration
- Changed app name from "Ticket Manager" to "Ticketa"

### 3. New Core Classes

#### `src/Core/SupabaseClient.php`
- Wrapper for Supabase client
- Provides singleton instance
- Handles authentication (sign up, sign in, sign out)
- Gets current user and session

#### `src/Core/SupabaseDatabase.php`
- Database wrapper for Supabase operations
- Methods: fetchOne, fetchAll, insert, update, delete, count
- Provides easy-to-use interface for database operations

### 4. Updated Classes

#### `src/Core/BaseController.php`
- Changed from `Database $db` to `SupabaseDatabase $db`
- Now uses Supabase for all database operations

#### `src/Core/Session.php`
- Integrated with Supabase authentication
- Stores Supabase session in PHP session
- Uses SupabaseClient for auth state management

#### `src/Controllers/AuthController.php`
- Complete rewrite to use Supabase authentication
- Sign up/sign in using Supabase auth
- Profile management works with Supabase database

### 5. Frontend (No Changes)
- All Twig templates remain the same
- CSS styling matches React version
- UI/UX is identical to React version

## Setup Instructions

### 1. Install Dependencies

```bash
cd "Task 2-Twig"
composer install
```

### 2. Configure Supabase

Update `config/app.php` with your Supabase credentials:

```php
define('SUPABASE_URL', 'https://your-project.supabase.co');
define('SUPABASE_ANON_KEY', 'your-anon-key-here');
define('SUPABASE_SERVICE_ROLE_KEY', 'your-service-role-key-here');
```

Or use environment variables:

```bash
export SUPABASE_URL="https://your-project.supabase.co"
export SUPABASE_ANON_KEY="your-anon-key-here"
export SUPABASE_SERVICE_ROLE_KEY="your-service-role-key-here"
```

### 3. Create Supabase Tables

In your Supabase project, create these tables:

#### profiles table
```sql
CREATE TABLE profiles (
  id UUID PRIMARY KEY,
  email TEXT UNIQUE NOT NULL,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP
);
```

#### tickets table
```sql
CREATE TABLE tickets (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  title TEXT NOT NULL,
  description TEXT,
  status TEXT DEFAULT 'open',
  priority TEXT DEFAULT 'medium',
  category TEXT,
  assigned_to UUID REFERENCES profiles(id),
  created_by UUID REFERENCES profiles(id),
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP
);
```

### 4. Enable Row Level Security (RLS)

Enable RLS on your tables and create policies:

```sql
-- Enable RLS
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE tickets ENABLE ROW LEVEL SECURITY;

-- Policy for profiles (users can only read their own profile)
CREATE POLICY "Users can read own profile" ON profiles
  FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update own profile" ON profiles
  FOR UPDATE USING (auth.uid() = id);

-- Policy for tickets
CREATE POLICY "Users can read all tickets" ON tickets
  FOR SELECT USING (true);

CREATE POLICY "Users can insert tickets" ON tickets
  FOR INSERT WITH CHECK (auth.uid() = created_by);

CREATE POLICY "Users can update own tickets" ON tickets
  FOR UPDATE USING (auth.uid() = created_by);
```

## Architecture Comparison

### React Version (Supabase)
```
Frontend (React) 
  ↓ API Calls
Supabase Auth + Database
```

### Twig Version (NOW - Supabase)
```
PHP Controllers
  ↓
Supabase Client
  ↓ API Calls
Supabase Auth + Database
```

## Key Differences from Original

### Before (MySQL/PDO)
- Local MySQL database
- PHP sessions for authentication
- SQL queries via PDO
- Server-side only

### After (Supabase)
- Supabase cloud database
- Supabase authentication
- Supabase REST API calls
- Can access from any client (matching React app)

## Testing

1. Start a PHP development server:
```bash
php -S localhost:8000 -t public
```

2. Visit `http://localhost:8000`
3. Register a new account
4. Login and create tickets

## Environment Variables

Create a `.env` file in the project root:

```
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key
```

## Notes

- The Twig version now uses the same Supabase backend as the React version
- Both can share the same database and authentication
- Users created in one version can log in to the other
- This enables seamless integration between React and Twig versions

