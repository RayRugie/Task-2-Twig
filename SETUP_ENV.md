# Environment Setup Guide

## 🔒 Security Note

**NEVER commit `.env` files to Git!** This file contains sensitive credentials.

## 📝 Initial Setup

### Step 1: Copy the example file
```bash
cp .env.example .env
```

### Step 2: Edit `.env` with your credentials
```bash
# Open the file
nano .env

# Or use any text editor
code .env
```

### Step 3: Add your Supabase credentials

Get your Supabase credentials:
1. Go to [supabase.com](https://supabase.com)
2. Select your project
3. Go to **Settings** → **API**
4. Copy the following:
   - Project URL → `SUPABASE_URL`
   - anon public → `SUPABASE_ANON_KEY`
   - service_role → `SUPABASE_SERVICE_ROLE_KEY`

### Step 4: Update `.env` file

```env
# Application
APP_ENV=development
APP_DEBUG=true
APP_NAME=Ticketa
APP_URL=http://localhost:8000

# Supabase (add your actual keys here)
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=eyJhbGci...your-key-here
SUPABASE_SERVICE_ROLE_KEY=eyJhbGci...your-key-here

# Security
CSRF_TOKEN_NAME=_token
SESSION_TIMEOUT=3600
APP_TIMEZONE=UTC
```

## ✅ Verify .env is in .gitignore

Check that `.env` is in `.gitignore`:
```bash
cat .gitignore | grep .env
```

You should see:
```
/.env
/.env.local
/.env.production
```

## 🚀 For Production (Fly.io)

### Using Fly Secrets (Recommended)

When deploying to Fly.io, set secrets using `fly secrets`:

```bash
fly secrets set SUPABASE_URL="https://your-project.supabase.co"
fly secrets set SUPABASE_ANON_KEY="your-anon-key"
fly secrets set SUPABASE_SERVICE_ROLE_KEY="your-service-role-key"
fly secrets set APP_ENV="production"
fly secrets set APP_DEBUG="false"
fly secrets set APP_NAME="Ticketa"
```

The app will read from environment variables in production.

## 🧪 Test Locally

1. Start the development server:
```bash
php -S localhost:8000 -t public
```

2. Visit: http://localhost:8000

3. The app should connect to Supabase using credentials from `.env`

## 🔍 Check Your Setup

Run this to verify environment variables are loaded:
```bash
php -r "require 'vendor/autoload.php'; require 'config/app.php'; var_dump(SUPABASE_URL);"
```

You should see your Supabase URL (not empty).

## 🐛 Troubleshooting

### Error: "Supabase URL is empty"
**Solution:** Make sure `.env` file exists and has valid credentials:
```bash
cat .env
```

### Error: ".env file not found"
**Solution:** Copy from example:
```bash
cp .env.example .env
```

### Error: "Database connection failed"
**Solution:** 
1. Check Supabase credentials in `.env`
2. Verify Supabase project is active
3. Check Supabase dashboard for any errors

## 📋 Checklist

- [ ] `.env.example` exists (with dummy values)
- [ ] `.env` file created locally
- [ ] Supabase credentials added to `.env`
- [ ] `.env` is in `.gitignore`
- [ ] Local development works
- [ ] Production secrets set via `fly secrets`

## 🚨 Important Security Rules

1. ✅ **DO**: Use `.env.example` for template
2. ✅ **DO**: Add real values to `.env` locally
3. ✅ **DO**: Use `fly secrets` for production
4. ❌ **DON'T**: Commit `.env` to Git
5. ❌ **DON'T**: Share your `.env` file
6. ❌ **DON'T**: Hardcode secrets in code

## 🎯 Quick Commands

```bash
# Create .env from example
cp .env.example .env

# Edit .env
nano .env

# Verify .env is ignored
git status | grep .env
# Should show nothing (or show as ignored)

# Check if variables are loaded
php -r "require 'vendor/autoload.php'; require 'config/app.php';"
```

## 📚 Next Steps

After setting up `.env`:

1. **Test locally**: `php -S localhost:8000 -t public`
2. **Commit your code** (without .env)
3. **Deploy to Fly.io**: `fly deploy`
4. **Set secrets on Fly.io**: `fly secrets set KEY="value"`

