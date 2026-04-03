# 🔒 Security Policy & Guidelines

## 📋 Reporting a Vulnerability

If you discover a security vulnerability in VK Neuro-Agents, please report it responsibly:

1. **DO NOT** create a public GitHub issue
2. Email the maintainer with details
3. Include steps to reproduce the issue
4. Allow time for a fix before public disclosure

---

## 🛡️ Security Measures

### Authentication
- ✅ JWT tokens with HS256 signature
- ✅ Password hashing with bcrypt
- ✅ CSRF protection on all forms
- ✅ Session invalidation on logout
- ✅ PKCE support for OAuth flows

### Data Protection
- ✅ Environment variables for all secrets
- ✅ No hardcoded credentials in source code
- ✅ SSL/TLS for HTTPS connections
- ✅ PostgreSQL SSL connections
- ✅ Encrypted payment API keys in database

### Infrastructure
- ✅ Docker container isolation
- ✅ Nginx security headers
- ✅ Rate limiting on API endpoints
- ✅ Input validation with Laravel & Zod

---

## ⚠️ Important Security Notes

### Before Deployment

1. **Change all default secrets:**
   ```bash
   # Run initialization script
   ./scripts/init.sh
   ```

2. **Set strong passwords:**
   - `POSTGRES_PASSWORD` — min 32 characters
   - `JWT_SECRET` — min 64 characters
   - `REDIS_PASSWORD` — min 32 characters

3. **VK OAuth credentials:**
   - Never commit `.env` files
   - Use separate apps for dev/production
   - Restrict redirect URIs in VK dashboard

4. **SSL Certificates:**
   - Use Let's Encrypt for production
   - Never commit private keys
   - Rotate certificates regularly

### After Deployment

1. **Monitor logs:**
   ```bash
   docker compose logs -f backend
   docker compose logs -f frontend
   ```

2. **Keep updated:**
   ```bash
   # Update containers
   docker compose pull
   docker compose up -d
   
   # Update dependencies
   cd backend && npm update
   cd ../frontend/php-app && composer update
   ```

3. **Backup database:**
   ```bash
   ./scripts/backup.sh
   ```

---

## 🚫 What NOT to Commit

The following files are **strictly prohibited** in the repository:

### Critical (Will be rejected)
- ❌ `.env` files with real secrets
- ❌ SSL private keys (`.key`, `.pem`)
- ❌ Database dumps with real data
- ❌ API keys or tokens

### Technical (Auto-excluded)
- ❌ `vendor/` directory
- ❌ `node_modules/` directory
- ❌ `storage/` and `bootstrap/cache/`
- ❌ `.log` files
- ❌ IDE configuration (`.idea/`, `.vscode/`)

### Allowed
- ✅ `.env.example` (with placeholder values)
- ✅ SSL certificates (`.crt` only, not keys)
- ✅ Configuration files (without secrets)
- ✅ Documentation

---

## 🔍 Pre-commit Checklist

Before committing code, verify:

- [ ] No `.env` files are included
- [ ] No real passwords or API keys
- [ ] No SSL private keys
- [ ] No database dumps
- [ ] `.env.example` is updated if config changed
- [ ] `README.md` documents new environment variables
- [ ] No hardcoded credentials in source code

---

## 📝 Environment Variables Security

### Root `.env` (Docker Compose)

| Variable | Sensitivity | Description |
|----------|-------------|-------------|
| `POSTGRES_PASSWORD` | 🔴 Critical | Database password |
| `JWT_SECRET` | 🔴 Critical | JWT signing key |
| `VK_CLIENT_SECRET` | 🔴 Critical | VK OAuth secret |
| `REDIS_PASSWORD` | 🟡 High | Redis password |
| `N8N_BASIC_AUTH_PASSWORD` | 🟡 High | N8N admin password |
| `N8N_API_KEY` | 🔴 Critical | N8N API access |
| `NOCODB_API_KEY` | 🔴 Critical | NocoDB access |

### Frontend `.env` (PHP/Laravel)

| Variable | Sensitivity | Description |
|----------|-------------|-------------|
| `DB_PASSWORD` | 🔴 Critical | Database password |
| `JWT_SECRET` | 🔴 Critical | Must match root .env |
| `VK_CLIENT_SECRET` | 🔴 Critical | VK OAuth secret |
| `VK_CLIENT_ID` | 🟡 High | VK App ID |

---

## 🛠 Automated Protection

### Git Hooks

The project uses `.gitignore` rules to automatically exclude:

```gitignore
# All .env files except examples
.env
!.env.example
frontend/php-app/.env
!frontend/php-app/.env.example

# SSL keys
*.key
*.pem
*.crt

# Logs and backups
*.log
backups/
*.backup

# Dependencies
vendor/
node_modules/

# Storage
storage/
bootstrap/cache/
```

### CI/CD Checks

Future improvements:
- [ ] Secret scanning in PRs
- [ ] Automated .env detection
- [ ] Dependency vulnerability scanning

---

## 📚 Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security](https://laravel.com/docs/11.x#security)
- [Docker Security Best Practices](https://docs.docker.com/security/)
- [VK ID Security Guide](docs/MOBILE_VK_ID_AUTH.md)

---

*Last Updated: 3 April 2026*
*Version: 1.0.0*
