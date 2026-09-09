# Laravel 博客（学习项目）

我用来边做边学 Laravel 的个人博客。不为做成产品，只为把每个常见功能亲手写一遍：CRUD、认证、授权、分页、测试——包括所有踩过的坑。

## 功能

- 文章 CRUD：slug 路由绑定、创建/编辑共用表单 partial（`_form.blade.php`）
- 会话认证：注册 / 登录 / 登出（手写实现，非 Breeze 脚手架）
- 授权：PostPolicy——只有作者能编辑、删除自己的文章
- 分页：每页 10 篇，`created_at` + `id` 双键排序（避免同秒创建的文章顺序不确定）
- 验证：FormRequest（slug 唯一性 + `alpha_dash` 格式约束）
- 测试：PHPUnit Feature 测试覆盖成功路径、越权、未登录、空提交、重复 slug、非法 slug、分页

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

## 运行测试

```powershell
npm run build    # 首次必须——测试渲染的页面含 @vite，需要 manifest
composer test
```

## 学习笔记

- `latest()` 按 `created_at` 排序，同秒创建的文章顺序不确定，需要再加 `orderByDesc('id')` 作决胜键
- 字符串断言（`assertSee`）是子串匹配，测试数据的标记必须等长——`post-01` 与 `post-10` 互不为前缀，`post-01` 与 `post-011` 则会撞车
- Windows 没有 `pcntl` 扩展，Pail 日志工具跑不了，已从 `composer dev` 脚本移除，看日志直接读 `storage/logs/laravel.log`
- Blade 里 `{{ $post->tile }}` 这类拼错的属性名**返回 null 而不报错**（Eloquent 魔术属性），页面表现为"安静的空白"，只能靠肉眼或测试发现
- IDE（Intelephense）对 `auth()->id()` 报 `Undefined method 'id'` 是误报——`auth()` 的返回类型是 `Factory|Guard` 联合类型，静态分析看不到 `AuthManager` 转发；用 `Auth::id()` 既能通过又更明确
