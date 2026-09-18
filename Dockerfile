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
FROM php:8.2-fpm-alpine

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

# laravel 可写目录给 php 工作进程写权限
# 容器以 root 起 php-fpm 主进程：需要往共享卷写静态文件；PHP 请求本身由 www-data 工作进程执行（安全取舍，见 compose 的 command）
RUN chown -R www-data:www-data storage bootstrap/cache

# fpm 内部端口（仅容器间访问，不对宿主发布）
EXPOSE 9000
CMD ["php-fpm"]