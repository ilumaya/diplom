# Используем официальный образ PHP с Apache
FROM php:8.1-apache

# Установка зависимостей и расширений
RUN docker-php-ext-install mysqli

# Копируем файлы приложения в контейнер
COPY . /var/www/html/

# Устанавливаем права доступа
RUN chown -R www-data:www-data /var/www/html

# Настройка Apache для использования home.php как индексного файла
RUN echo "DirectoryIndex home.php index.php index.html" >> /etc/apache2/apache2.conf

# Открываем порт 80
EXPOSE 80
