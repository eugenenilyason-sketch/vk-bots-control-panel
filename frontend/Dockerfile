# Frontend Dockerfile - VK Neuro-Agents UI
FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies (use npm install instead of ci)
RUN npm install

# Copy source code
COPY public ./public
COPY nginx.conf ./nginx.conf

# Production stage - Nginx
FROM nginx:alpine

# Copy custom nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy built application from builder
COPY --from=builder /app/public /usr/share/nginx/html

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:3000/ || exit 1

EXPOSE 3000

# Set environment
ENV NGINX_HOST=localhost
ENV NGINX_PORT=3000

CMD ["nginx", "-g", "daemon off;"]
