FROM php:8.2-apache

# تثبيت الامتدادات المطلوبة
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# تهيئة مجلد التطبيق
WORKDIR /var/www/html

# نسخ ملفات Laravel
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت حزم Laravel
RUN composer install

# إعداد صلاحيات Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# فتح المنفذ 80 (افتراضي لـ Apache)
EXPOSE 80

CMD ["apache2-foreground"]
