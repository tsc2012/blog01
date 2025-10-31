# PHP博客系统

一个基于PHP和MySQL的简单而强大的博客系统，支持文章管理、分类管理、评论管理和用户管理等功能。

## 功能特点

- **文章管理**：创建、编辑、删除和发布文章
- **分类管理**：创建和管理文章分类
- **评论系统**：支持访客评论，管理员可以审核、批准或标记为垃圾评论
- **用户管理**：多管理员支持，可分配不同权限
- **响应式设计**：适配各种设备屏幕
- **搜索功能**：支持文章搜索
- **图片上传**：支持文章特色图片上传

## 技术栈

- PHP 7.0+
- MySQL 5.6+
- HTML5
- CSS3
- JavaScript
- Font Awesome 图标库
- TinyMCE 富文本编辑器

## 安装步骤

1. **克隆代码库**

```bash
git clone https://github.com/yourusername/php-blog.git
cd php-blog
```

2. **配置数据库**

创建一个MySQL数据库，然后修改 `includes/config.php` 文件中的数据库配置：

```php
define('DB_HOST', 'localhost');     // 数据库主机
define('DB_USER', 'root');          // 数据库用户名
define('DB_PASS', '');              // 数据库密码
define('DB_NAME', 'php_blog');      // 数据库名称
```

3. **运行安装脚本**

在浏览器中访问 `install.php` 文件：

```
http://yourdomain.com/php-blog/install.php
```

安装脚本将自动创建数据库表和默认管理员账户。

4. **登录管理后台**

使用默认管理员账户登录：
- 用户名：admin
- 密码：admin123

登录后请立即修改密码！

## 目录结构

```
php-blog/
├── admin/                 # 管理端目录
│   ├── index.php          # 管理后台首页
│   ├── login.php          # 管理员登录
│   ├── logout.php         # 退出登录
│   ├── articles.php       # 文章管理
│   ├── categories.php     # 分类管理
│   ├── users.php          # 用户管理
│   └── includes/
│       ├── auth.php       # 管理员认证（依赖全局配置）
│       └── functions.php  # 管理端专属函数（继承全局函数）
├── assets/                # 静态资源
│   ├── css/
│   ├── js/
│   └── images/            # 站点默认图片（如logo）
├── includes/
│   ├── config.php         # 全局配置（数据库、路径、站点信息）
│   ├── db.php             # 数据库连接（全局复用）
│   ├── functions.php      # 全局通用函数
│   └── comment_functions.php # 评论相关函数
├── templates/             # 公共模板片段
│   ├── header.php         # 头部导航
│   ├── footer.php         # 底部信息
│   └── sidebar.php        # 侧边栏（分类、热门文章）
├── uploads/               # 上传文件（权限0755）
├── user/                  # 前端用户功能（可选）
│   ├── login.php
│   ├── register.php
│   └── profile.php
├── index.php              # 博客首页
├── article.php            # 文章详情页（含评论区）
├── category.php           # 分类页面
├── comment.php            # 处理评论提交
├── 404.php                # 404错误页
└── install.php            # 安装脚本（初始化数据库、管理员）
```

## 使用说明

### 发布文章

1. 登录管理后台
2. 点击"文章管理" -> "新建文章"
3. 填写文章标题、选择分类、编写内容
4. 可选：上传特色图片
5. 选择状态（草稿或已发布）
6. 点击"保存"按钮

### 管理评论

1. 登录管理后台
2. 点击"评论管理"
3. 可以查看、编辑、批准、标记为垃圾或删除评论

### 添加新用户

1. 登录管理后台
2. 点击"用户管理" -> "新建用户"
3. 填写用户名、邮箱和密码
4. 点击"保存"按钮

## 安全建议

1. 安装完成后删除 `install.php` 文件
2. 定期备份数据库
3. 不要使用默认密码，登录后立即修改
4. 限制管理员账户数量
5. 定期更新系统和插件

## 许可证

MIT License
