<?php
/**
 * PHP博客系统安装脚本
 * 初始化数据库和管理员账户
 */

// 检查是否已安装
if (file_exists('installed.txt')) {
    die("系统已安装，请勿重复安装！");
}

require_once 'includes/config.php';
require_once 'includes/db.php';

// 数据库连接
try {
    $db = get_db_connection();
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 创建数据库表
$tables = [
    // 管理员表
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        last_login DATETIME NULL
    )",
    
    // 权限表
    "CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT NULL
    )",
    
    // 管理员权限关联表
    "CREATE TABLE IF NOT EXISTS admin_permissions (
        admin_id INT NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY (admin_id, permission_id),
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
    )",
    
    // 分类表
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )",
    
    // 文章表
    "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        category_id INT NOT NULL,
        author_id INT NOT NULL,
        status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
        featured_image VARCHAR(255) NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
        FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE RESTRICT
    )",
    
    // 评论表
    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        website VARCHAR(255) NULL,
        content TEXT NOT NULL,
        status ENUM('pending', 'approved', 'spam') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )"
];

// 执行SQL语句创建表
foreach ($tables as $sql) {
    try {
        $db->exec($sql);
    } catch (PDOException $e) {
        die("创建表失败: " . $e->getMessage() . " SQL: " . $sql);
    }
}

// 插入默认权限
$permissions = [
    ['name' => 'manage_posts', 'description' => '管理文章'],
    ['name' => 'manage_categories', 'description' => '管理分类'],
    ['name' => 'manage_comments', 'description' => '管理评论'],
    ['name' => 'manage_users', 'description' => '管理用户'],
    ['name' => 'manage_settings', 'description' => '管理设置']
];

foreach ($permissions as $permission) {
    try {
        $stmt = $db->prepare("INSERT INTO permissions (name, description) VALUES (:name, :description)");
        $stmt->bindParam(':name', $permission['name'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $permission['description'], PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        // 忽略重复插入的错误
        if ($e->getCode() != 23000) {
            die("插入权限失败: " . $e->getMessage());
        }
    }
}

// 创建管理员账户
$admin_username = 'admin';
$admin_email = 'admin@example.com';
$admin_password = 'admin123'; // 默认密码，安装后应立即更改

try {
    // 检查管理员是否已存在
    $stmt = $db->prepare("SELECT id FROM admins WHERE username = :username");
    $stmt->bindParam(':username', $admin_username, PDO::PARAM_STR);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        // 创建管理员
        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = $db->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (:username, :email, :password, :created_at)");
        $stmt->bindParam(':username', $admin_username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $admin_email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
        $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);
        $stmt->execute();
        
        $admin_id = $db->lastInsertId();
        
        // 为管理员分配所有权限
        $stmt = $db->prepare("SELECT id FROM permissions");
        $stmt->execute();
        $permission_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($permission_ids as $permission_id) {
            $stmt = $db->prepare("INSERT INTO admin_permissions (admin_id, permission_id) VALUES (:admin_id, :permission_id)");
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
} catch (PDOException $e) {
    die("创建管理员失败: " . $e->getMessage());
}

// 创建默认分类
$categories = [
    ['name' => '技术', 'description' => '技术相关文章'],
    ['name' => '生活', 'description' => '生活相关文章'],
    ['name' => '随笔', 'description' => '随笔杂谈']
];

foreach ($categories as $category) {
    try {
        // 检查分类是否已存在
        $stmt = $db->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt->bindParam(':name', $category['name'], PDO::PARAM_STR);
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // 创建分类
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category['name'])));
            $created_at = date('Y-m-d H:i:s');
            $updated_at = $created_at;
            
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (:name, :slug, :description, :created_at, :updated_at)");
            $stmt->bindParam(':name', $category['name'], PDO::PARAM_STR);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->bindParam(':description', $category['description'], PDO::PARAM_STR);
            $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);
            $stmt->bindParam(':updated_at', $updated_at, PDO::PARAM_STR);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        die("创建分类失败: " . $e->getMessage());
    }
}

// 创建示例文章
try {
    // 获取第一个分类
    $stmt = $db->prepare("SELECT id FROM categories ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $category = $stmt->fetch();
    
    if ($category) {
        $category_id = $category['id'];
        
        // 获取管理员ID
        $stmt = $db->prepare("SELECT id FROM admins ORDER BY id ASC LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            $author_id = $admin['id'];
            
            // 检查示例文章是否已存在
            $stmt = $db->prepare("SELECT id FROM posts WHERE title = :title");
            $title = '欢迎使用PHP博客系统';
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                // 创建示例文章
                $slug = 'welcome-to-php-blog';
                $content = '<p>欢迎使用PHP博客系统！这是一个简单而强大的博客系统，基于PHP和MySQL开发。</p>
                            <p>系统特点：</p>
                            <ul>
                                <li>完整的文章管理功能</li>
                                <li>分类管理</li>
                                <li>评论系统</li>
                                <li>用户管理</li>
                                <li>响应式设计</li>
                            </ul>
                            <p>您可以通过以下步骤开始使用：</p>
                            <ol>
                                <li>登录管理后台（/admin/login.php）</li>
                                <li>创建新文章</li>
                                <li>添加分类</li>
                                <li>自定义网站设置</li>
                            </ol>
                            <p>默认管理员账号：admin，密码：admin123</p>
                            <p>请务必在登录后更改密码！</p>';
                $status = 'published';
                $created_at = date('Y-m-d H:i:s');
                $updated_at = $created_at;
                
                $stmt = $db->prepare("INSERT INTO posts (title, slug, content, category_id, author_id, status, created_at, updated_at) 
                                    VALUES (:title, :slug, :content, :category_id, :author_id, :status, :created_at, :updated_at)");
                
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
                $stmt->bindParam(':content', $content, PDO::PARAM_STR);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);
                $stmt->bindParam(':updated_at', $updated_at, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
} catch (PDOException $e) {
    die("创建示例文章失败: " . $e->getMessage());
}

// 创建安装完成标记文件
file_put_contents('installed.txt', 'PHP博客系统安装完成！' . "\n" . '安装时间: ' . date('Y-m-d H:i:s'));

echo "<!DOCTYPE html>
<html lang='zh-CN'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>PHP博客系统 - 安装完成</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .success {
            color: #4CAF50;
            font-size: 18px;
            text-align: center;
            margin: 20px 0;
        }
        .info {
            background-color: #f0f8ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0b7dda;
        }
        .btn-success {
            background-color: #4CAF50;
        }
        .btn-success:hover {
            background-color: #45a049;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>PHP博客系统安装完成！</h1>
        <div class='success'>
            <p>恭喜您，PHP博客系统已成功安装！</p>
        </div>
        <div class='info'>
            <h3>重要信息：</h3>
            <ul>
                <li>默认管理员账号：admin</li>
                <li>默认管理员密码：admin123</li>
                <li><strong>请务必在登录后更改密码！</strong></li>
            </ul>
        </div>
        <div class='center'>
            <a href='index.php' class='btn'>访问博客首页</a>
            <a href='admin/login.php' class='btn btn-success'>登录管理后台</a>
        </div>
    </div>
</body>
</html>";
?>
