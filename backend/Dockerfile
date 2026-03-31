# Backend Dockerfile - VK Neuro-Agents API
FROM node:20-alpine

WORKDIR /app

# Install system dependencies
# openssl и curl уже есть в node:20-alpine, нужно только обновить
RUN apk update && \
    apk upgrade && \
    apk add --no-cache --upgrade openssl libcrypto3 curl

# Copy package files
COPY package*.json ./

# Install all dependencies (including dev for tsx)
RUN npm ci

# Copy source code
COPY . .

# Generate Prisma client
RUN npx prisma generate

# Create logs directory
RUN mkdir -p logs

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost:4000/health || exit 1

EXPOSE 4000

# Set NODE_ENV
ENV NODE_ENV=production

# Start application with tsx
CMD ["npx", "tsx", "src/index.ts"]
