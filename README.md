# Laravel 博客（学习项目）

我使用 Laravel 实现的个人博客。

## 功能

### 文章
- CRUD：slug 路由绑定、创建/编辑共用表单 partial（`_form.blade.php`）
- 授权：PostPolicy——只有作者能编辑、删除自己的文章
- 分页：每页 10 篇，`created_at` + `id` 双键排序（避免同秒创建的文章顺序不确定）
- 验证：FormRequest（slug 唯一性 + `alpha_dash` 格式约束）

### 认证与会话（手写实现，非 Breeze 脚手架）
- 注册 / 登录 / 登出，登录失败限流（5 次/分钟，按邮箱+IP 命名 limiter）
- 密码重置：邮件令牌（60 秒重发冷却）、与旧密码相同则拒绝且令牌保留
- 已登录改密码：当前密码校验、页内反馈（独立成功/错误横条）

### 邮箱验证
- 注册后强制验证（`verified` 中间件挡住发文）
- 签名 URL 三要素校验：签名、hash 匹配、收件人身份（GitHub 同款逻辑）
- 重发冷却（60 秒，服务端 RateLimiter 为准，前端倒计时仅展示）+ 状态轮询自动跳转
- 修改邮箱：当前密码验明正身 + 同值拒绝（大小写不敏感）+ 换邮箱即降级重新验证（解决"注册时填错邮箱账号死亡"）
- 邮箱服务器配置界面化：管理员在设置页配置 SMTP（密码 `encrypted` cast 加密落库，运行时覆盖 config 即改即生效）

### 管理员体系
- 单角色 `is_admin`（不进 `$fillable`，防 mass assignment 提权）
- Gate `admin` + 路由 `can:admin` 中间件 + 设置页双 tab（普通用户只看到个人设置，`?tab=admin` 越权静默回退）
- 管理员由 seeder 按环境变量凭据引导创建（幂等：已存在不重建仅提权）；**生产默认不建管理员**，走注册后提权流程

### 其他
- 双语：中文/英文切换（`lang/zh.json` + `validation.php` 字段名映射），`__()` 键驱动
- 测试：64 个 Feature 用例 / 170 断言，覆盖权限墙、验证降级、加密落库、seeder 幂等等核心行为

## 技术栈

Laravel 12 · PHP 8.2 · MySQL 8 · Blade · Tailwind CSS 4（Vite）· PHPUnit 11

## 环境要求

- PHP >= 8.2，`php.ini` 需启用 `pdo_mysql` 和 `intl` 扩展
- MySQL 8
- Node.js + npm
- Composer

## 本地启动

```powershell
composer install
npm install

# 1. 先在 MySQL 中建库（utf8mb4 支持中文和 emoji）
#    mysql -u root -p -e "CREATE DATABASE blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. 准备环境配置（编辑 .env 填入你的 MySQL 账号）
copy .env.example .env
php artisan key:generate

# 3. 建表 + 填充示例数据（10 用户 / 20 文章）
php artisan migrate --seed

# 4. 构建前端资源（测试和生产都需要这份 manifest）
npm run build

# 5. 启动开发服务组（serve + queue + Vite 热更新）
composer dev
```

打开 <http://127.0.0.1:8000> 即可。

### 本地管理员与邮件

- **管理员**：`.env` 中取消注释 `ADMIN_EMAIL` / `ADMIN_PASSWORD` 并填值，再跑 `migrate --seed` 即建出管理员；不配则不建
- **邮件**：本地开发推荐 [Mailpit](https://github.com/axllent/mailpit)（`MAIL_MAILER=smtp`、`MAIL_HOST=localhost`、`MAIL_PORT=1025`，Web 界面 <http://127.0.0.1:8025>）；管理员也可登录后在 设置 → 管理员设置 → 邮箱服务器设置 中配置真实 SMTP（如 QQ 邮箱授权码）

## Docker 部署

镜像在本地构建、导出传到服务器运行，服务器不需要源码。线上为三容器架构：**nginx**（唯一对外端口，静态资源直出）+ **php-fpm**（Laravel）+ **MySQL**（数据在具名卷，容器重建不丢）。

### 服务器一次性准备（Debian）

```bash
apt update && apt upgrade -y
curl -fsSL https://get.docker.com | sh -s -- --mirror Aliyun   # 国内镜像源安装
# swap：内存尖峰保险（无 swap 时 OOM Killer 优先杀 mysqld）
fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
# 云控制台安全组放行 TCP 80

mkdir -p /opt/blog
nano /opt/blog/.env
```

`/opt/blog/.env` 四行（不进 git、不进镜像，只活在服务器）：

```dotenv
APP_KEY=base64:...      # 本地 .env 那份原样复制——换 key 则加密字段和会话全部解不开
DB_PASSWORD=强密码        # 别含 $，compose 会插值
MYSQL_ROOT_PASSWORD=另一个强密码
WEB_PORT=80
```

### 发布（本地 PowerShell）

```powershell
docker build -t blog-app .

# 三样物料：镜像 tar + 编排文件 + nginx 配置（tar 放临时目录，别留在项目根）
docker save blog-app -o "$env:TEMP\blog-app.tar"
scp "$env:TEMP\blog-app.tar" root@<服务器IP>:/root/
scp compose.yaml root@<服务器IP>:/opt/blog/compose.yaml
scp nginx.conf root@<服务器IP>:/opt/blog/nginx.conf
Remove-Item "$env:TEMP\blog-app.tar"
```

### 启动与验收（服务器）

```bash
docker load -i /root/blog-app.tar && rm /root/blog-app.tar
cd /opt/blog && docker compose up -d
docker compose exec web php artisan migrate --force   # 首次部署或本次含迁移时
curl -I http://127.0.0.1/posts                        # 200 = 服务器内部验收通过
```

本地从外部验收：`curl.exe -I http://<服务器IP>/posts` 应返回 `200` 且 `Server: nginx`。

### 日常更新

改完代码重复上面「发布 + 启动」两节即可。`docker compose up -d` 幂等：镜像变了只重建 web 容器，MySQL 连卷不动。注意 `nginx.conf` 挂载的是文件——**必须先传到服务器再启动容器**，路径不存在时 Docker 会自动建一个目录占位导致启动报错。

## 运行测试

```powershell
npm run build    # 首次必须——测试渲染的页面含 @vite，需要 manifest
composer test
```
