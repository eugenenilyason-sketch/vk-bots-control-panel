# 🚀 Deployment Checklist

Before deploying VK Neuro-Agents to production, ensure all items are checked:

## 🔐 Security

- [ ] All `.env` files removed from repository
- [ ] `JWT_SECRET` changed from default (min 64 chars)
- [ ] `POSTGRES_PASSWORD` is strong (min 32 chars)
- [ ] `REDIS_PASSWORD` is strong (min 32 chars)
- [ ] VK OAuth credentials are for production app
- [ ] SSL certificates obtained and configured
- [ ] No hardcoded secrets in source code
- [ ] `.env.example` files are up to date

## 📦 Dependencies

- [ ] Backend: `npm install` completed
- [ ] Frontend: `composer install` completed
- [ ] All Docker images built successfully
- [ ] No npm/composer security warnings

## 🗄️ Database

- [ ] PostgreSQL container is running
- [ ] Migrations executed: `npx prisma migrate deploy`
- [ ] Admin user created (via `make-admin.sh`)
- [ ] Database backups configured

## 🌐 Domain & SSL

- [ ] Domain DNS configured
- [ ] SSL certificates obtained (Let's Encrypt)
- [ ] Nginx configuration updated
- [ ] HTTP → HTTPS redirect working

## 🧪 Testing

- [ ] Email/password login works
- [ ] VK ID login works
- [ ] Dashboard loads correctly
- [ ] API endpoints respond
- [ ] Logout works properly
- [ ] Admin panel accessible

## 📊 Monitoring

- [ ] Docker logs accessible
- [ ] Error monitoring configured
- [ ] Backup script tested
- [ ] Health check endpoint working (`/up`)

## 📝 Documentation

- [ ] `README.md` is up to date
- [ ] `SECURITY.md` reviewed
- [ ] Environment variables documented
- [ ] Deployment guide followed

---

## Quick Deploy Command

```bash
# 1. Pull latest code
git pull origin main

# 2. Initialize (if first time)
./scripts/init.sh

# 3. Configure .env
nano .env

# 4. Start services
docker compose up -d

# 5. Run migrations
docker compose exec backend npx prisma migrate deploy

# 6. Create admin
./scripts/make-admin.sh admin@yourdomain.com superadmin

# 7. Verify
docker compose ps
curl -k https://yourdomain.com/up
```

---

*Generated: 3 April 2026*
