# ប្រើ PHP 8.2 ជាមួយ Apache (សាកសមនឹង Laravel 12)
FROM php:8.2-apache

# ដំឡើងកម្មវិធីចាំបាច់ និង Extensions សម្រាប់ Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# បើក Rewrite Module របស់ Apache ដើម្បីឱ្យ Link ដើរ
RUN a2enmod rewrite

# កំណត់ Document Root ទៅកាន់ public folder របស់ Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# ដំឡើង Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# កំណត់ working directory
WORKDIR /var/www/html

# Copy កូដទាំងអស់ចូលក្នុង Image
COPY . .

# Run Composer Install ដើម្បីទាញ Library មកដាក់
RUN composer install --no-interaction --optimize-autoloader --no-dev

# កំណត់ Permission ឱ្យ Folder storage (សំខាន់ណាស់ បើអត់មាន Error)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# បើក Port 80
EXPOSE 80

# Run Apache
CMD ["apache2-foreground"]