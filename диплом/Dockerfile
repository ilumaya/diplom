# Используем официальный образ PHP с Apache
FROM php:8.1-apache

# Установка зависимостей и расширений
RUN docker-php-ext-install mysqli pdo_mysql

# Копируем файлы приложения в контейнер
COPY . /var/www/html/

# Устанавливаем права доступа
RUN chown -R www-data:www-data /var/www/html

# Открываем порт 80
EXPOSE 80
