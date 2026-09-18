# syntax=docker/dockerfile:1

# 阶段一：构建前端资产
FROM node:22-alpine AS frontend
WORKDIR /app

# 先只拷 package*.json -- 依赖清单变了才重装，其余情况命中缓存（分层缓存）
COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# 阶段二：运行时镜像
FROM php:8.2-cli-alpine

# laravel 需要的 php 扩展
RUN docker-php-ext-install pdo_mysql opcache
WORKDIR /var/www/html

# composer 从专用镜像拷进来（不在 php 镜像中安装，保持镜像干净）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 先拷贝依赖清单再装（代码改动不触发依赖重装）
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-autoloader

# 拷贝全部源代码（.dockerignore 已排除）
COPY . .

# 补完 autoload(文件全到位后才能 dump, --no-scripts 避免执行框架的 post-autoload 钩子)
RUN composer dump-autoload --optimize --no-scripts

# 将阶段一中构建好的前端资产拷贝进来
COPY --from=frontend /app/public/build /var/www/html/public/build

# laravel 可写目录给 php 进程写权限
RUN chown -R www-data:www-data storage bootstrap/cache
USER www-data

# 暂时使用镜像内置开发服务器提供 http
EXPOSE 8000
CMD ["sh", "-c", "php artisan migrate --force && exec php artisan serve --host=0.0.0.0 --port=8000"]