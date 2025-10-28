# 🚀 Deployment Ready - Twig Application

## ✅ Files Created for Fly.io Deployment

1. **`Dockerfile`** - Docker configuration for PHP 8.1 + Apache
2. **`fly.toml`** - Fly.io application configuration
3. **`.dockerignore`** - Files to exclude from Docker build
4. **`.fly/launch.json`** - Launch configuration
5. **`QUICK_DEPLOY_FLYIO.md`** - Step-by-step deployment guide
6. **`FLY_DEPLOYMENT.md`** - Comprehensive deployment documentation

## ✅ Files Updated for Production

1. **`config/app.php`** - Now supports environment variables for Fly.io secrets
2. **`public/index.php`** - Proper error handling based on APP_DEBUG setting

## 🎯 Ready to Deploy!

### Quick Deploy Commands:

```bash
# 1. Install Fly CLI (if not already)
brew install flyctl  # macOS
# or visit https://fly.io/install

# 2. Login
fly auth login

# 3. Launch
fly launch

# 4. Set secrets
fly secrets set SUPABASE_URL="your-url"
fly secrets set SUPABASE_ANON_KEY="your-key"
fly secrets set SUPABASE_SERVICE_ROLE_KEY="your-service-key"
fly secrets set APP_ENV="production"
fly secrets set APP_DEBUG="false"

# 5. Deploy
fly deploy
```

## 📝 What Changed for Production

### Environment Variables
The app now properly reads from environment variables which are set via `fly secrets`:

```bash
SUPABASE_URL
SUPABASE_ANON_KEY
SUPABASE_SERVICE_ROLE_KEY
APP_NAME
APP_ENV
APP_DEBUG
```

### Error Handling
- Production mode: Hides detailed errors from users
- Development mode: Shows full error details
- Controlled by `APP_DEBUG` environment variable

### Docker Configuration
- PHP 8.1 with Apache
- Composer dependencies installed during build
- Optimized for production (`--no-dev` flag)
- Proper permissions set

## 🔍 Pre-Deployment Checklist

- [ ] Supabase project created
- [ ] Database tables created (run SQL scripts)
- [ ] Supabase credentials ready
- [ ] Git repository pushed to remote
- [ ] All local testing complete

## 🌐 After Deployment

### Your app will be available at:
```
https://ticketa.fly.dev
```

### Custom domain (optional):
```bash
fly certs add yourdomain.com
```

## 📊 Monitoring

```bash
# View logs
fly logs

# Check status
fly status

# SSH into machine
fly ssh console
```

## 🐛 Common Issues & Solutions

### Issue: "Cannot find composer"
**Fix:** Composer is installed in Dockerfile

### Issue: "Environment variables not working"
**Fix:** Run `fly secrets list` to verify they're set

### Issue: "Stylesheets not loading"
**Fix:** File permissions are set automatically in Dockerfile

### Issue: "500 Internal Server Error"
**Fix:** Check `fly logs` for detailed error messages

## 💡 Deployment Summary

✅ **Docker configured** - Multi-stage build for production
✅ **Environment variables** - Secure secret management
✅ **Error handling** - User-friendly production mode
✅ **Composer** - Dependencies installed during build
✅ **Apache** - Configured with proper document root
✅ **Permissions** - Set automatically
✅ **HTTPS** - Enabled by Fly.io automatically
✅ **Logs** - Access via `fly logs`

## 📚 Documentation Files

1. **`QUICK_DEPLOY_FLYIO.md`** - 5-step deployment guide
2. **`FLY_DEPLOYMENT.md`** - Comprehensive deployment docs
3. **`README.md`** - Full application documentation
4. **`REQUIREMENTS_CHECKLIST.md`** - All requirements verified

## 🎉 You're Ready!

Your Twig application is now configured for production deployment on Fly.io. 

Just run:
```bash
fly launch
fly secrets set SUPABASE_URL="..."
fly deploy
```

That's it! Your app will be live in minutes.

