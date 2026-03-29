FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy source code
COPY public ./public
COPY nginx.conf ./nginx.conf

# Production stage - Nginx with SSL
FROM nginx:alpine

# Install OpenSSL
RUN apk add --no-cache openssl

# Create SSL directory
RUN mkdir -p /etc/nginx/ssl

# Copy SSL certificates (will be generated or mounted)
COPY ssl/server.crt /etc/nginx/ssl/server.crt
COPY ssl/server.key /etc/nginx/ssl/server.key

# Copy custom nginx config with SSL
COPY nginx-ssl.conf /etc/nginx/conf.d/default.conf

# Copy built application from builder
COPY --from=builder /app/public /usr/share/nginx/html

# Expose HTTPS port
EXPOSE 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider https://localhost:443/ || exit 1

# Set environment
ENV NGINX_HOST=localhost
ENV NGINX_PORT=443

CMD ["nginx", "-g", "daemon off;"]
