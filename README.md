# Ticket Management System

A production-ready ticket management web application built with PHP and Twig templating engine. This system provides a complete solution for managing support tickets, user authentication, and team collaboration.

## 🚀 Features

### Core Functionality
- **User Authentication**: Secure login/register system with password hashing
- **Ticket Management**: Full CRUD operations for tickets
- **Dashboard**: Interactive charts and statistics using Chart.js
- **Comments System**: Add comments and track ticket progress
- **User Roles**: Admin and regular user roles with different permissions
- **Search & Filtering**: Advanced filtering and search capabilities
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5

### Security Features
- **CSRF Protection**: All forms protected with CSRF tokens
- **Rate Limiting**: Login attempt limiting with account lockout
- **Password Security**: Argon2ID password hashing
- **Session Management**: Secure session handling with regeneration
- **Input Sanitization**: All user inputs sanitized and validated
- **SQL Injection Protection**: Prepared statements throughout

### Technical Features
- **Front Controller Pattern**: Clean URL routing
- **Twig Templating**: Secure template engine with inheritance
- **PDO Database Layer**: Secure database interactions
- **Client-side Validation**: Real-time form validation
- **localStorage Integration**: Persistent user preferences and filters
- **RESTful Design**: Clean API-like structure

## 📋 Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache with mod_rewrite enabled
- **Extensions**: PDO, PDO_MySQL, JSON, Session

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd ticket-management-system
```

### 2. Install Dependencies

**Option A: Using Composer (Recommended)**
```bash
composer install
```

**Option B: Manual Setup (If Composer fails)**
```bash
# Run the setup script
php setup.php
```

The application includes a fallback autoloader and simple template system for development when Composer installation fails.

### 3. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE ticket_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Configure database connection:
```bash
cp config/database.example.php config/database.php
```

3. Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ticket_manager');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

4. Run the SQL migrations:

**Option A: Using the setup script (Recommended)**
```bash
php setup-database.php
```

**Option B: Manual import**
```bash
# Import the database schema
mysql -u your_username -p ticket_manager < sql/001_create_users_table.sql
mysql -u your_username -p ticket_manager < sql/002_create_tickets_table.sql
mysql -u your_username -p ticket_manager < sql/003_sample_data.sql
```

### 4. Web Server Configuration

#### Apache Configuration

Ensure your document root points to the `public` directory:

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/ticket-management-system/public
    ServerName ticketmanager.local
    
    <Directory /path/to/ticket-management-system/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name ticketmanager.local;
    root /path/to/ticket-management-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Set Permissions

```bash
# Set proper permissions
chmod -R 755 public/
chmod -R 644 config/
chmod -R 644 templates/
chmod -R 644 views/

# Create cache directory if needed
mkdir -p cache/twig
chmod 755 cache/twig
```

### 6. Environment Configuration

Update `config/app.php` for your environment:

```php
// Set to false in production
define('APP_DEBUG', false);

// Update with your domain
define('APP_URL', 'https://yourdomain.com');
```

## 🚀 Quick Start

### Using PHP Built-in Server (Development)

```bash
# Start the development server
php -S localhost:8000 -t public/

# Visit http://localhost:8000
```

### Default Login Credentials

After running the sample data migration, you can login with:

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Regular User:**
- Username: `john.doe`
- Password: `password123`

## 📁 Project Structure

```
ticket-management-system/
├── public/                 # Web-accessible files
│   ├── index.php          # Front controller
│   ├── .htaccess          # Apache configuration
│   ├── css/               # Static CSS files
│   └── js/                # Static JavaScript files
├── src/                   # Application source code
│   ├── Controllers/       # MVC Controllers
│   ├── Models/           # Data models
│   └── Core/             # Core application classes
├── templates/            # Twig templates
│   ├── auth/             # Authentication templates
│   ├── tickets/          # Ticket management templates
│   ├── dashboard/        # Dashboard templates
│   └── errors/           # Error pages
├── views/                # Static assets
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── config/               # Configuration files
├── sql/                  # Database migrations
├── vendor/               # Composer dependencies
├── composer.json         # Composer configuration
└── README.md            # This file
```

## 🔧 Configuration

### Application Settings

Edit `config/app.php` to customize:

- Application name and version
- Security settings (CSRF, rate limiting)
- File upload limits
- Email configuration
- Session timeout

### Database Settings

Edit `config/database.php` to configure:

- Database connection parameters
- PDO options
- Character set

## 🎨 Customization

### Themes and Styling

- Modify `views/css/app.css` for custom styling
- Update Bootstrap variables in the CSS file
- Add custom JavaScript in `views/js/app.js`

### Templates

- All templates are in the `templates/` directory
- Use Twig inheritance for consistent layouts
- Templates support internationalization

### Adding New Features

1. Create controller in `src/Controllers/`
2. Add routes in `src/Core/Application.php`
3. Create templates in `templates/`
4. Update navigation in `templates/base.twig`

## 🔒 Security Considerations

### Production Deployment

1. **Set APP_DEBUG to false** in `config/app.php`
2. **Use HTTPS** for all communications
3. **Secure database credentials** - never commit `config/database.php`
4. **Regular updates** - keep dependencies updated
5. **Backup strategy** - implement regular database backups
6. **Monitor logs** - set up error logging and monitoring

### Security Features Implemented

- ✅ CSRF token protection on all forms
- ✅ SQL injection prevention with prepared statements
- ✅ XSS protection with input sanitization
- ✅ Rate limiting on login attempts
- ✅ Secure password hashing (Argon2ID)
- ✅ Session security with regeneration
- ✅ File upload validation
- ✅ Security headers in .htaccess

## 📊 Database Schema

### Users Table
- User authentication and profile information
- Role-based access control
- Login attempt tracking

### Tickets Table
- Ticket information and metadata
- Status and priority tracking
- Assignment and creation tracking

### Ticket Comments Table
- Comments and internal notes
- User attribution and timestamps

### Ticket Attachments Table
- File upload tracking
- Security validation

## 🧪 Testing

### Manual Testing Checklist

- [ ] User registration and login
- [ ] Ticket creation and editing
- [ ] Comment system functionality
- [ ] Dashboard charts and statistics
- [ ] Search and filtering
- [ ] User role permissions
- [ ] Form validation (client and server-side)
- [ ] Responsive design on mobile devices

### Browser Compatibility

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

## 🚀 Deployment

### Production Checklist

1. **Environment Setup**
   - [ ] Set `APP_DEBUG = false`
   - [ ] Configure production database
   - [ ] Set up SSL certificate
   - [ ] Configure web server properly

2. **Security**
   - [ ] Change default passwords
   - [ ] Review file permissions
   - [ ] Set up firewall rules
   - [ ] Enable security headers

3. **Performance**
   - [ ] Enable Twig caching
   - [ ] Set up database indexing
   - [ ] Configure web server caching
   - [ ] Optimize images and assets

4. **Monitoring**
   - [ ] Set up error logging
   - [ ] Configure backup system
   - [ ] Monitor server resources
   - [ ] Set up uptime monitoring

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

### Common Issues

**Q: Getting "Database connection failed" error**
A: Check your database credentials in `config/database.php` and ensure MySQL is running.

**Q: Pages showing 404 errors**
A: Ensure mod_rewrite is enabled and your web server is configured to point to the `public` directory.

**Q: CSS/JS files not loading**
A: Check file permissions and ensure your web server can serve static files from the `public` directory.

**Q: Login not working**
A: Verify the database has been imported correctly and check the user table for the default admin account.

### Getting Help

- Check the troubleshooting section above
- Review the configuration files
- Check web server error logs
- Verify database connectivity

## 🔄 Updates and Maintenance

### Regular Maintenance Tasks

1. **Weekly**: Review error logs and user activity
2. **Monthly**: Update dependencies and security patches
3. **Quarterly**: Review and update documentation
4. **Annually**: Security audit and performance review

### Backup Strategy

```bash
# Database backup
mysqldump -u username -p ticket_manager > backup_$(date +%Y%m%d).sql

# File backup
tar -czf files_backup_$(date +%Y%m%d).tar.gz public/ config/ templates/ views/
```

---

**Built with ❤️ using PHP, Twig, and modern web technologies.**
