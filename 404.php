<?php
/**
 * 404错误页面
 */

// 设置页面标题
$page_title = '页面未找到';

// 包含头部模板
require_once 'includes/functions.php';
require_once 'templates/header.php';
?>

    <main class="content">
        <div class="container">
            <div class="error-404">
                <h1>404</h1>
                <h2>页面未找到</h2>
                <p>抱歉，您请求的页面不存在或已被删除。</p>
                <div class="error-actions">
                    <a href="<?php echo BASE_URL; ?>" class="btn">返回首页</a>
                    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary">浏览文章</a>
                </div>
            </div>
        </div>
    </main>

<?php
// 包含底部模板
require_once 'templates/footer.php';
?>
