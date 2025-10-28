# Quick Deploy to Fly.io - Step by Step

## ✅ Pre-flight Checklist

- [ ] You have a Fly.io account
- [ ] Fly CLI installed on your machine
- [ ] You're in the `Task 2-Twig` directory
- [ ] All files are committed to Git

## 🚀 Deploy in 5 Steps

### Step 1: Login to Fly.io
```bash
fly auth login
```

### Step 2: Launch Your App
```bash
fly launch
```
- When asked for app name: `ticketa` (or your preferred name)
- When asked for region: choose closest to you (e.g., `iad`, `ord`, `lhr`)
- Don't deploy yet: **Say 'No' when asked to deploy**

### Step 3: Set Secrets (Supabase Credentials)

```bash
# Replace with your actual Supabase credentials
fly secrets set SUPABASE_URL="https://your-project.supabase.co"
fly secrets set SUPABASE_ANON_KEY="your-anon-key-here"
fly secrets set SUPABASE_SERVICE_ROLE_KEY="your-service-role-key-here"
fly secrets set APP_NAME="Ticketa"
fly secrets set APP_ENV="production"
fly secrets set APP_DEBUG="false"
```

**To get your Supabase credentials:**
1. Go to https://supabase.com
2. Select your project
3. Go to Settings → API
4. Copy the values from there

### Step 4: Deploy
```bash
fly deploy
```
This will build the Docker image and deploy your app. Takes 2-3 minutes.

### Step 5: Open Your App
```bash
fly open
```

That's it! Your app is live. 🎉

## 🔍 Verify It's Working

1. Landing page should load
2. Try creating an account
3. Login and check dashboard
4. Create a ticket

## 🐛 Troubleshooting

### Build Failed?
```bash
# Check logs
fly logs

# Try deploying with verbose output
fly deploy --verbose
```

### App Shows 502 Error?
```bash
# Check what's happening
fly logs

# SSH into machine
fly ssh console

# Once inside, check Apache
service apache2 status
```

### Can't Connect to Supabase?
- Verify secrets are set correctly
- Check Supabase project is active
- Verify API keys are correct
- Check Supabase logs in dashboard

## 📊 Common Commands

```bash
# View logs
fly logs

# SSH into machine
fly ssh console

# Check app status
fly status

# Update secrets
fly secrets set KEY="value"
fly deploy

# Redeploy
fly deploy
```

## 🔄 Update Your App

When you make changes:

```bash
# Commit changes
git add .
git commit -m "Your changes"

# Deploy
fly deploy
```

## 💰 Costs

- **Free tier**: 3 shared-cpu-1x (256MB) VMs
- **Production**: ~$5-10/month for stable hosting
- This app uses 1 VM, so fits in free tier for testing

## 📝 Next Steps

1. Set up custom domain (optional)
2. Enable monitoring
3. Set up backups (Supabase handles this)
4. Scale if needed (unlikely for now)

## 🆘 Need Help?

- Check logs: `fly logs --tail`
- Fly.io docs: https://fly.io/docs
- Your deployment guide: `FLY_DEPLOYMENT.md`

