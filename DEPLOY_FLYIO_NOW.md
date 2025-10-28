# Deploy to Fly.io Now - Fixed!

## 🐛 The Problem
The `fly.toml` file had an extra `[[vm]]` section that was causing conflicts.

## ✅ The Solution

I've simplified the `fly.toml`. Now follow these steps:

## 🚀 Deploy Steps

### Step 1: Delete old Fly config (if exists)
```bash
# In the Task 2-Twig directory
rm -f .fly/launch.json
```

### Step 2: Launch your app
```bash
fly launch
```

**When prompted:**
- Use existing fly.toml? **Yes**
- App name: `ticketa` (or whatever you prefer)
- Region: Choose closest to you
- Postgres database? **No**
- Redis? **No**
- Deploy now? **No** (we'll do it in next step)

### Step 3: Set your Supabase secrets
```bash
fly secrets set SUPABASE_URL="https://zarjztnhyohmtqsxwtxx.supabase.co"
fly secrets set SUPABASE_ANON_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inphcmp6dG5oeW9obXRxc3h3dHh4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjEyODU1MTksImV4cCI6MjA3Njg2MTUxOX0.axhIv5N0ZhvIH8NpPvX49BSym_CLLhlETo7ZMEz9ypE"
fly secrets set SUPABASE_SERVICE_ROLE_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inphcmp6dG5oeW9obXRxc3h3dHh4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MTI4NTUxOSwiZXhwIjoyMDc2ODYxNTE5fQ.e3A5HrDrHV9M-DA2Vb_RJa8DxSKmuRAWf6glrLhtt5o"
fly secrets set APP_NAME="Ticketa"
fly secrets set APP_ENV="production"
fly secrets set APP_DEBUG="false"
```

### Step 4: Deploy!
```bash
fly deploy
```

This will take 2-3 minutes to build and deploy.

### Step 5: Open your app
```bash
fly open
```

## ✅ Success!

Your app is now live at: `https://ticketa.fly.dev`

## 🐛 If You Still Get Errors

### Error: "fly.toml already exists"

**Solution:**
```bash
# Backup the file
cp fly.toml fly.toml.backup

# Delete it temporarily
rm fly.toml

# Run fly launch (it will create new one)
fly launch

# Or use the simplified one I provided
```

### Error: "Cannot find Dockerfile"

**Solution:**
Make sure you're in the correct directory:
```bash
pwd
# Should show: .../Task 2-Twig
```

### Error: "Composer install failed"

**Solution:**
```bash
# Test Docker build locally first
docker build -t ticketa .
docker run -p 8000:80 ticketa
```

## 📝 Quick Commands Reference

```bash
# View logs
fly logs

# SSH into machine
fly ssh console

# Restart app
fly apps restart ticketa

# Check status
fly status

# Update after code changes
fly deploy
```

## 💡 Pro Tip

After deployment, you can view your app's URL:
```bash
fly info
```

Look for "Hostname" in the output.

