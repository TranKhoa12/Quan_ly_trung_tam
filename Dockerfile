# ============================================================
# Dockerfile - Quản lý trung tâm
# PHP 8.2 + Apache + Tesseract OCR + các extension cần thiết
# ============================================================
FROM php:8.2-apache

# Cài đặt dependencies hệ thống
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    tesseract-ocr \
    tesseract-ocr-vie \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        zip \
        mbstring \
        xml \
        opcache

# Cài Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Bật mod_rewrite cho Apache (dùng .htaccess)
RUN a2enmod rewrite

# Copy Apache vhost config
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Cấu hình PHP cho production
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 120" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www/html

# Copy toàn bộ source code
COPY . .

# Cài Composer dependencies (không có dev packages)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Tạo thư mục cần thiết & phân quyền
RUN mkdir -p public/uploads public/downloads storage/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 public/uploads public/downloads storage

EXPOSE 80
