
FROM php:8.2-apache


RUN docker-php-ext-install mysqli pdo pdo_mysql


RUN chown -R www-data:www-data /var/www/html


# 日本語環境を追加
RUN apt-get update && apt-get install -y locales \
    && sed -i -e 's/# \(ja_JP.UTF-8\)/\1/' /etc/locale.gen \
    && locale-gen \
    && update-locale LANG=ja_JP.UTF-8

ENV LANG=ja_JP.UTF-8


# ECサイトのファイルをDockerコンテナ内のApacheディレクトリにコピー
COPY . /var/www/html


COPY ../apache-config/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite


COPY ../db/my.cnf /etc/mysql/conf.d/


# ポート80を公開（Webアクセス用）
EXPOSE 80