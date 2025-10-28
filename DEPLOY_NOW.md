# Deploy to Fly.io - Complete Fix

## ✅ Files Fixed

1. **Dockerfile** - Now uses port 8080 and proper Apache configuration
2. **fly.toml** - Clean configuration with correct port mapping
3. Removed conflicting launch.json

## 🚀 Deploy Steps (Run These Commands)

### Step 1: Make sure you're in the right directory
```bash
cd "Task 2-Twig"
```

### Step 2: Clean any old Fly.io config (if exists)
```bash
rm -rf .fly
fly apps destroy ticketa --yes 2>/dev/null || true
```

### Step 3: Launch the app
```bash
fly launch --dockerfile Dockerfile --name ticketa --region iad
```

When prompted:
- Overwrite fly.toml? **No** (we already have the correct one)
- Deploy now? **No** (we'll do it after setting secrets)

### Step 4: Set your Supabase secrets
**⚠️ IMPORTANT: Replace with YOUR actual Supabase credentials!**

```bash
# Get your Supabase credentials from: https://supabase.com → Settings → API
fly secrets set SUPABASE_URL="https://YOUR-PROJECT.supabase.co"
fly secrets set SUPABASE_ANON_KEY="YOUR-ANON-KEY-HERE"
fly secrets set SUPABASE_SERVICE_ROLE_KEY="YOUR-SERVICE-ROLE-KEY-HERE"
fly secrets set APP_NAME="Ticketa"
fly secrets set APP_ENV="production"
fly secrets set APP_DEBUG="false"
```

**Note:** Never share or commit these keys!

### Step 5: Deploy
```bash
fly deploy
```

This will:
- Build Docker image (~2-3 minutes)
- Install Composer dependencies
- Deploy to Fly.io
- Expose on ports 80 (HTTP) and 443 (HTTPS)

### Step 6: Open your app
```bash
fly open
```

Your app will be live at: `https://ticketa.fly.dev`

## 🔍 Verify It's Working

After deployment, test:
```bash
# Check status
fly status

# View logs
fly logs

# Test the app
fly open
```

## 🐛 Troubleshooting

### Error: "App ticketa already exists"
```bash
fly apps destroy ticketa --yes
fly launch --dockerfile Dockerfile --name ticketa --region iad
```

### Error: "Cannot find vendor/"
```bash
# Files are copied correctly in Dockerfile
# If this happens, check Dockerfile COPY commands
```

### Error: "Stylesheets not loading"
```bash
# Check logs
fly logs

# SSH and check permissions
fly ssh console
ls -la /var/www/html/public/css/
```

### Error: "Connection refused"
```bash
# Check if Apache is running
fly ssh console
ps aux | grep apache
service apache2 status
```

### Build is slow?
```bash
# First build includes Composer install
# Subsequent builds use cache
fly deploy
```

## 📊 What Changed

### Dockerfile Changes:
- ✅ Port changed from 80 → 8080
- ✅ Proper Apache configuration
- ✅ Composer install before copying code (for caching)
- ✅ Proper file permissions
- ✅ Document root set to /public

### fly.toml Changes:
- ✅ Uses proper Fly.io services structure
- ✅ Port 8080 internal → 80/443 external
- ✅ Includes health checks
- ✅ No conflicting sections

## ✅ Success Indicators

When deployment succeeds you'll see:
```
✓ Building image
✓ Creating VM
✓ Deploying image
✓ App is live!
Visit: https://ticketa.fly.dev
```

## 🎯 Quick Reference

```bash
# View logs (stream)
fly logs --tail

# View logs (last 100)
fly logs

# SSH into machine
fly ssh console

# Restart app
fly apps restart ticketa

# Scale (if needed)
fly scale count 2

# Update secrets
fly secrets set KEY="value"
fly deploy
```

## 💡 Next Steps After Deployment

1. ✅ Test landing page loads
2. ✅ Test login/signup
3. ✅ Create a ticket
4. ✅ Edit a ticket
5. ✅ Delete a ticket

Your Twig app is now deployed! 🎉

