# Imagem base com PHP 8.2 + extensões necessárias
FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir pasta de trabalho
WORKDIR /var/www/symfony

# Copiar arquivos do projeto
COPY . .

# Instalar dependências do Symfony
RUN composer install --no-interaction

# Expor porta
EXPOSE 9000

CMD ["php-fpm"]

