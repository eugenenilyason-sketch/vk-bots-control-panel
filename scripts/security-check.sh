#!/bin/bash
# ============================================
# Pre-commit Security Check
# ============================================
# Run this script before committing to ensure
# no sensitive data is exposed
# ============================================

# Don't exit on error - we want to run all checks
# set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0

echo "🔍 Running security checks..."
echo "================================"
echo ""

# Check 1: No .env files (except .env.example)
echo "1️⃣  Checking for .env files..."
ENV_FILES=$(find . -name ".env" -type f 2>/dev/null | grep -v node_modules | grep -v vendor | grep -v ".git")
if [ -n "$ENV_FILES" ]; then
    echo -e "   ${RED}❌ Found .env files:${NC}"
    echo "$ENV_FILES" | while read -r file; do
        echo "      - $file"
    done
    ERRORS=$((ERRORS + 1))
else
    echo -e "   ${GREEN}✅ No .env files found${NC}"
fi
echo ""

# Check 2: No SSL private keys
echo "2️⃣  Checking for SSL private keys..."
KEY_FILES=$(find . -name "*.key" -o -name "*.pem" 2>/dev/null | grep -v node_modules | grep -v vendor)
if [ -n "$KEY_FILES" ]; then
    echo -e "   ${RED}❌ Found SSL key files:${NC}"
    echo "$KEY_FILES" | while read -r file; do
        echo "      - $file"
    done
    ERRORS=$((ERRORS + 1))
else
    echo -e "   ${GREEN}✅ No SSL private keys found${NC}"
fi
echo ""

# Check 3: No database dumps
echo "3️⃣  Checking for database dumps..."
SQL_FILES=$(find . -name "*.sql" -o -name "*.dump" -o -name "*.backup" 2>/dev/null | grep -v node_modules | grep -v vendor)
if [ -n "$SQL_FILES" ]; then
    echo -e "   ${YELLOW}⚠️  Found SQL/backup files:${NC}"
    echo "$SQL_FILES" | while read -r file; do
        echo "      - $file"
    done
    echo -e "   ${YELLOW}   (Review these before committing)${NC}"
fi
echo ""

# Check 4: No hardcoded passwords in source
echo "4️⃣  Checking for hardcoded secrets..."
SECRET_PATTERNS=(
    "password\s*=\s*['\"][^'\"]{8,}"
    "secret\s*=\s*['\"][^'\"]{8,}"
    "API_KEY\s*=\s*['\"][^'\"]{8,}"
    "JWT_SECRET\s*=\s*['\"][^'\"]{8,}"
)

for pattern in "${SECRET_PATTERNS[@]}"; do
    FOUND=$(grep -r --include="*.ts" --include="*.js" --include="*.php" --include="*.json" -i "$pattern" . 2>/dev/null | grep -v node_modules | grep -v vendor | grep -v ".git" || true)
    if [ -n "$FOUND" ]; then
        echo -e "   ${YELLOW}⚠️  Possible secrets found (pattern: $pattern):${NC}"
        echo "$FOUND" | head -5 | while read -r line; do
            echo "      - $line"
        done
    fi
done
echo -e "   ${GREEN}✅ Secret check complete${NC}"
echo ""

# Check 5: .env.example files exist
echo "5️⃣  Checking .env.example files..."
MISSING_EXAMPLES=0
for dir in "." "backend" "frontend/php-app"; do
    if [ ! -f "$dir/.env.example" ]; then
        echo -e "   ${YELLOW}⚠️  Missing $dir/.env.example${NC}"
        MISSING_EXAMPLES=$((MISSING_EXAMPLES + 1))
    fi
done

if [ $MISSING_EXAMPLES -eq 0 ]; then
    echo -e "   ${GREEN}✅ All .env.example files present${NC}"
fi
echo ""

# Check 6: Git status
echo "6️⃣  Checking git status..."
UNTRACKED=$(git status --porcelain | grep "^??" || true)
if [ -n "$UNTRACKED" ]; then
    echo -e "   ${YELLOW}⚠️  Untracked files:${NC}"
    echo "$UNTRACKED" | while read -r line; do
        echo "      - $line"
    done
    echo ""
fi

# Summary
echo "================================"
if [ $ERRORS -gt 0 ]; then
    echo -e "${RED}❌ Security check FAILED with $ERRORS error(s)${NC}"
    echo -e "${YELLOW}Please fix the issues above before committing${NC}"
    exit 1
else
    echo -e "${GREEN}✅ All security checks passed!${NC}"
    echo ""
    echo -e "${YELLOW}📝 Final checklist:${NC}"
    echo "   [ ] No .env files with real secrets"
    echo "   [ ] No SSL private keys"
    echo "   [ ] No database dumps"
    echo "   [ ] .env.example files updated"
    echo "   [ ] README.md documents new variables"
    echo ""
    echo "Ready to commit! 🎉"
    exit 0
fi
