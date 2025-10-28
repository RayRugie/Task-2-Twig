# Fly.io Deployment Guide for Twig Application

## 🚀 Quick Start

### Prerequisites
- [Fly.io account](https://fly.io)
- Fly CLI installed
- Supabase credentials ready

### Step 1: Install Fly CLI

```bash
# macOS
brew install flyctl

# Linux
curl -L https://fly.io/install.sh | sh

# Windows
iwr https://fly.io/install.ps1 -useb | iex
```

### Step 2: Login to Fly.io

```bash
fly auth login
```

### Step 3: Initialize Your App

```bash
# Navigate to project directory
cd "Task 2-Twig"

# Initialize Fly.io (if not already done)
fly launch

# Or use existing config
fly apps create ticketa
```

### Step 4: Set Environment Variables

```bash
# Set your Supabase credentials
fly secrets set SUPABASE_URL="https://your-project.supabase.co"
fly secrets set SUPABASE_ANON_KEY="your-anon-key"
fly secrets set SUPABASE_SERVICE_ROLE_KEY="your-service-role-key"
fly secrets set APP_NAME="Ticketa"
fly secrets set APP_ENV="production"
fly secrets set APP_DEBUG="false"
```

### Step 5: Deploy

```bash
fly deploy
```

### Step 6: Open Your App

```bash
fly open
```

## 🔧 Configuration

### Update config/database.php

The Dockerfile and deployment expect environment variables. Update your `config/database.php`:

```php
<?php

// Use environment variables (set via fly secrets)
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? 'https://placeholder.supabase.co');
define('SUPABASE_ANON_KEY', $_ENV['SUPABASE_ANON_KEY'] ?? 'placeholder');
define('SUPABASE_SERVICE_ROLE_KEY', $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? 'placeholder');

?>
```

### Update config/app.php

```php
<?php

// Production settings
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Ticketa');
define('APP_VERSION', '1.0.0');
define('SESSION_TIMEOUT', 3600);
define('CSRF_TOKEN_NAME', '_token');

?>
```

## 📊 Database Setup

1. **Create Supabase Tables**
   - Go to Supabase Dashboard
   - SQL Editor
   - Run `sql/002_create_tickets_table_supabase.sql`

2. **Configure Row Level Security**
   - SQL files already include RLS policies
   - Verify in Supabase Dashboard

## 🐛 Troubleshooting

### Issue: "Build failed"

**Solution:**
```bash
# Check build logs
fly logs

# Rebuild locally to test
docker build -t ticketa .
docker run -p 8000:80 ticketa
```

### Issue: "502 Bad Gateway"

**Solution:**
```bash
# Check app logs
fly logs

# SSH into machine
fly ssh console

# Check Apache status
service apache2 status
```

### Issue: "Environment variables not found"

**Solution:**
```bash
# List secrets
fly secrets list

# Add missing secrets
fly secrets set KEY="value"
```

### Issue: "Stylesheets not loading"

**Solution:**
Check file permissions:
```bash
fly ssh console
ls -la public/css/
chmod 755 public/css/
```

## 🔍 Monitoring

### View Logs
```bash
# Real-time logs
fly logs

# Specific app
fly logs -a ticketa

# Tail logs
fly logs --tail
```

### SSH into Machine
```bash
fly ssh console
```

### Check App Status
```bash
fly status
fly info
```

## 🔄 Updates & Maintenance

### Deploy Updates
```bash
# Pull latest changes
git pull origin main

# Deploy
fly deploy

# Or specify region
fly deploy --region iad
```

### Scale Application
```bash
# Scale to 2 machines
fly scale count 2

# Scale memory
fly scale memory 1024
```

### Add New Secrets
```bash
fly secrets set NEW_KEY="new_value"
fly deploy
```

## 💰 Pricing

Fly.io offers generous free tier:
- **Free tier includes:**
  - 3 shared-cpu-1x 256MB VMs
  - 160GB outbound data transfer per month
  - 3GB persistent volume storage

- **Cost to run this app:**
  - ~$5-10/month for production use

## 🌍 Custom Domain

### Add Custom Domain

```bash
# Add domain
fly certs add yourdomain.com

# Check certificate status
fly certs show

# Update DNS
# Add CNAME record: yourdomain.com -> ticketa.fly.dev
```

## 📝 Important Notes

1. **File Permissions**: Docker handles these automatically
2. **Environment Variables**: Use `fly secrets` for sensitive data
3. **Composer**: Runs during build, not at runtime
4. **Apache**: Configured to use `public/` as document root
5. **HTTPS**: Automatically enabled by Fly.io
6. **Logs**: Access via `fly logs`
7. **Backups**: Supabase handles database backups

## 🧪 Testing After Deployment

1. **Test Landing Page**
   ```bash
   fly open
   ```

2. **Test Authentication**
   - Create account
   - Login
   - Verify redirect to dashboard

3. **Test CRUD Operations**
   - Create ticket
   - Edit ticket
   - Delete ticket

4. **Check Logs**
   ```bash
   fly logs --tail
   ```

## 🚨 Common Commands

```bash
# Deploy app
fly deploy

# View logs
fly logs

# SSH into machine
fly ssh console

# Check status
fly status

# List secrets
fly secrets list

# Update secrets
fly secrets set KEY="value"

# Open app
fly open

# Restart app
fly apps restart ticketa

# Scale
fly scale count 2

# View metrics
fly dashboard
```

## 📚 Resources

- [Fly.io Documentation](https://fly.io/docs)
- [PHP on Fly.io](https://fly.io/docs/php/)
- [Docker Documentation](https://docs.docker.com)
- [Supabase Documentation](https://supabase.com/docs)

