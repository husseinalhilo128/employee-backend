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

# تثبيت الحزم
RUN composer install --no-dev --optimize-autoloader

# إعداد صلاحيات Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# إعداد Apache ليستخدم مجلد public
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# إعداد Laravel عند التشغيل
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    apache2-foreground
