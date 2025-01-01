FROM php:8.2-cli

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql

# タイムゾーンを設定
ENV TZ=Asia/Tokyo

WORKDIR /var/www/html
