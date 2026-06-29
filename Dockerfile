FROM richarvey/nginx-php-fpm:3.1.6

# Set working directory
WORKDIR /var/www/html

# Install system dependencies including Node.js, NPM, Chromium, and fonts
RUN apk update && \
    apk add --no-cache \
    nodejs \
    npm \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont \
    dos2unix

# Copy application files
COPY . .

# Adjust scripts line endings to Unix format and make executable
RUN dos2unix scripts/00_init.sh && \
    chmod +x scripts/00_init.sh

# Image and container config for richarvey/nginx-php-fpm
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1

# Environment variables for Puppeteer to use container-installed Chromium
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD true
ENV PUPPETEER_EXECUTABLE_PATH /usr/bin/chromium-browser
ENV PUPPETEER_HEADLESS true

# Run composer installation for production (excluding development packages)
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Run npm install and build frontend assets
RUN npm install && npm run build

# Adjust directory permissions for storage and bootstrap cache
RUN chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache
