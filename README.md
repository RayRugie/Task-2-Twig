# Ticketa - Twig/PHP Implementation

A modern ticket management system built with PHP (Twig templating), using Supabase for authentication and database management.

## 🚀 Frameworks and Libraries Used

- **PHP 8.1+** - Backend language
- **Twig 3.x** - Templating engine
- **Supabase PHP SDK** - Authentication and database
- **Composer** - Dependency management
- **Apache/Nginx** - Web server

## 📦 Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer installed
- Supabase account and project
- Web server (Apache/Nginx) or PHP built-in server

### Setup Steps

1. **Clone the repository**
```bash
cd "Task 2-Twig"
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure environment**
```bash
cp config/database.example.php config/database.php
```

Edit `config/database.php` with your Supabase credentials:
```php
define('SUPABASE_URL', 'https://your-project.supabase.co');
define('SUPABASE_ANON_KEY', 'your-anon-key');
define('SUPABASE_SERVICE_ROLE_KEY', 'your-service-role-key');
```

4. **Set up the database**
Run the SQL migrations in your Supabase SQL editor:
- `sql/001_create_users_table.sql`
- `sql/002_create_tickets_table_supabase.sql`

5. **Configure the application**
Edit `config/app.php`:
```php
define('APP_DEBUG', true);
define('APP_NAME', 'Ticketa');
define('SESSION_TIMEOUT', 3600);
```

6. **Start the development server**
```bash
php -S localhost:8000 -t public
```

Visit `http://localhost:8000` in your browser.

login details
email: admin@gmail.com
password: admin001

## 🎯 Features

### 1. Landing Page ✅
- App name and description
- Call-to-action buttons (Login, Get Started)
- Wavy SVG background
- Decorative circles
- Feature boxes with shadows and rounded corners
- Max-width: 1440px, centered
- Fully responsive (mobile, tablet, desktop)
- Consistent footer

### 2. Authentication ✅
- Login and Signup pages
- Form validation with inline errors
- Toast notifications for failed logins
- Redirects to Dashboard on success
- Session management via PHP sessions

### 3. Dashboard ✅
- Statistics display:
  - Total tickets
  - Open tickets
  - Closed tickets
- Navigation links to Ticket Management
- Logout button
- Max-width: 1440px, centered layout
- Responsive design

### 4. Ticket Management (CRUD) ✅
- **Create**: Form for new tickets
- **Read**: Card-based ticket list
- **Update**: Edit ticket details with validation
- **Delete**: Remove tickets with confirmation
- Real-time validation
- Success/error feedback (flash messages)

## 🔒 Data Validation

### Required Fields
- **title**: Mandatory (string)
- **status**: Mandatory (must be: `open`, `in_progress`, `closed`)
- **description**: Mandatory (string)

### Status Values
- `open` - Green tone (#10b981)
- `in_progress` - Amber tone (#f59e0b)
- `closed` - Gray tone (#9ca3af)

### Validation Rules
- All fields required
- Status must be one of the three allowed values
- Error messages displayed inline or via flash messages

## 🛡️ Security & Authorization

### Protected Routes
- `/dashboard` - Requires authentication
- `/tickets` - Requires authentication
- `/tickets/{id}/edit` - Requires authentication + ownership
- `/tickets/{id}/delete` - Requires authentication + ownership

### Security Features
- Email-based filtering: Users can only see/modify their own tickets
- CSRF token protection on all POST requests
- Session-based authentication
- SQL injection prevention via parameterized queries
- XSS prevention via Twig auto-escaping

### Session Management
- PHP native sessions
- Session timeout: 3600 seconds (1 hour)
- Automatic session regeneration on login
- Secure cookie settings

## 🎨 Design & Layout

### Layout Rules
- **Max-width**: 1440px on large screens
- **Centered**: Content horizontally centered
- **Responsive**: Mobile-first approach

### Visual Elements
- **Hero Section**: Wavy SVG background at bottom
- **Decorative Circles**: At least 2 circular elements
- **Card Boxes**: Feature sections with shadows and rounded corners
- **Status Tags**: Color-coded (green, amber, gray)

### Responsive Breakpoints
- **Mobile**: < 640px (stacked layout)
- **Tablet**: 640px - 900px (adjusted grid)
- **Desktop**: > 900px (multi-column layout)

## 📱 Accessibility

- Semantic HTML5 elements
- ARIA labels and roles
- Visible focus states
- Sufficient color contrast (WCAG AA)
- Keyboard navigation support
- Alt text for images

## 🧪 Test Credentials

For testing purposes, create a user account via the Supabase authentication system or use your own credentials.

**Example test user:**
```
Email: test@example.com
Password: Test1234!
```

Note: Replace with actual Supabase authentication credentials.

## 📁 Project Structure

```
Task 2-Twig/
├── config/
│   ├── app.php              # Application configuration
│   └── database.php          # Supabase credentials
├── public/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── index.php             # Entry point
├── src/
│   ├── Controllers/          # MVC controllers
│   ├── Core/                 # Core classes
│   └── Models/               # Data models
├── templates/
│   ├── auth/                 # Auth pages
│   ├── dashboard/            # Dashboard
│   ├── tickets/              # Ticket management
│   └── base.twig             # Base template
├── sql/                      # Database migrations
└── vendor/                   # Composer dependencies
```

## 🔄 Switching Between React, Vue, and Twig Versions

The project includes three separate implementations:

1. **Task2-React/** - React + TypeScript + Vite
2. **Task2-Vue/** - Vue 3 + TypeScript + Vite  
3. **Task 2-Twig/** - PHP + Twig

Each is a standalone application using the same Supabase backend.

To switch between versions:
1. **React**: `cd Task2-React/ticket-manager && npm run dev`
2. **Vue**: `cd Task2-Vue && npm run dev`
3. **Twig**: `cd "Task 2-Twig" && php -S localhost:8000 -t public`

All versions share:
- Same Supabase database
- Same authentication system
- Same ticket data
- Same business logic

## 🐛 Known Issues

- PHP version compatibility (requires PHP 8.1+)
- Supabase connection timeout handling
- CSRF token regeneration on page refresh
- Mobile responsiveness on very small screens (< 320px)

## 📝 Notes

### UI Components
- **Header**: App logo and navigation
- **Hero Section**: Landing page introduction
- **Feature Cards**: Grid-based feature showcase
- **Stat Cards**: Dashboard statistics display
- **Ticket Cards**: List of tickets with status tags
- **Forms**: Ticket creation/editing with validation

### State Structure
- **Sessions**: User authentication state (PHP sessions)
- **Flash Messages**: Temporary success/error messages
- **Form Data**: Pre-filled data on validation errors
- **Database**: Supabase tables (users, tickets)

### Accessibility Features
- Form labels and aria-describedby attributes
- Error message associations
- Keyboard shortcuts
- Screen reader compatible
- High contrast mode support

## 🚀 Deployment

### Production Deployment

1. **Update environment variables**
```php
define('APP_DEBUG', false);
define('SUPABASE_URL', 'production-url');
```

2. **Optimize assets**
```bash
# Minify CSS/JS if needed
```

3. **Configure web server**
- Apache: Configure `.htaccess` (included)
- Nginx: Configure `public` as document root

4. **Set proper permissions**
```bash
chmod 755 public
```

## 📄 License

Built for HNG Internship Task 2

## 👥 Contributors

HNG Internship Program
