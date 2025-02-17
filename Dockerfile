FROM php:8.3-fpm

# Configure apt for better package download reliability
RUN set -eux; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*; \
    echo 'Acquire::ForceIPv4 "true";' > /etc/apt/apt.conf.d/99force-ipv4; \
    echo 'Acquire::http::Timeout "240";' > /etc/apt/apt.conf.d/99timeout; \
    echo 'Acquire::ftp::Timeout "240";' >> /etc/apt/apt.conf.d/99timeout; \
    echo 'APT::Get::Assume-Yes "true";' > /etc/apt/apt.conf.d/99assumeyes; \
    echo "deb http://mirror.yandex.ru/debian/ bookworm main" > /etc/apt/sources.list; \
    echo "deb http://mirror.yandex.ru/debian/ bookworm-updates main" >> /etc/apt/sources.list; \
    echo "deb http://mirror.yandex.ru/debian-security/ bookworm-security main" >> /etc/apt/sources.list
        
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    git \
    unzip \
    zip \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    libssl-dev \
    netcat-openbsd \
    libfcgi-bin \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        zip \
        intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js using package manager
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install PHP-FPM healthcheck script manually
RUN echo '#!/bin/sh' > /usr/local/bin/php-fpm-healthcheck \
    && echo 'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1' >> /usr/local/bin/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# Set Composer environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1

# Copy composer files first
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the application
COPY . .

# Generate autoload files
RUN composer dump-autoload --optimize

# Create necessary directories
RUN mkdir -p \
    storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    /var/run/php-fpm

# Set up directories and permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    /var/run/php-fpm \
    && chmod -R 775 \
    storage \
    bootstrap/cache \
    /var/run/php-fpm

# Configure PHP-FPM with optimized settings
RUN { \
    echo '[global]'; \
    echo 'error_log = /proc/self/fd/2'; \
    echo 'log_limit = 8192'; \
    echo 'daemonize = no'; \
    echo '[www]'; \
    echo 'access.log = /proc/self/fd/2'; \
    echo 'clear_env = no'; \
    echo 'catch_workers_output = yes'; \
    echo 'decorate_workers_output = no'; \
    echo 'listen = 0.0.0.0:9000'; \    
    echo 'listen.mode = 0666'; \
    echo 'listen.owner = www-data'; \
    echo 'listen.group = www-data'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 10'; \
    echo 'pm.start_servers = 2'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 4'; \
    echo 'pm.max_requests = 200'; \
    echo 'php_admin_value[error_log] = /proc/self/fd/2'; \
    echo 'php_admin_flag[log_errors] = on'; \
    echo 'php_admin_value[memory_limit] = 128M'; \
    echo 'php_admin_value[max_execution_time] = 60'; \
    echo 'php_admin_value[upload_max_filesize] = 32M'; \
    echo 'php_admin_value[post_max_size] = 32M'; \
    echo 'ping.path = /ping'; \
    echo 'ping.response = pong'; \
    } > /usr/local/etc/php-fpm.d/www.conf
    
# Configure PHP
RUN { \
    echo 'error_reporting = E_ALL'; \
    echo 'display_errors = Off'; \
    echo 'display_startup_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'error_log = /proc/self/fd/2'; \
    echo 'memory_limit = 128M'; \
    echo 'max_execution_time = 60'; \
    echo 'upload_max_filesize = 32M'; \
    echo 'post_max_size = 32M'; \
    } > /usr/local/etc/php/conf.d/docker-php-config.ini


# Copy entrypoint script
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Set healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

# Set entrypoint
ENTRYPOINT ["/docker-entrypoint.sh"]

# Default command
CMD ["php-fpm"]
