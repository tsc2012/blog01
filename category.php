<?php
/**
 * 分类页面
 */

// 检查分类ID是否存在
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: 404.php');
    exit();
}

$category_id = intval($_GET['id']);

// 获取分类数据
require_once 'includes/functions.php';
$category = get_category_by_id($category_id);

// 如果分类不存在，显示404页面
if (!$category) {
    header('Location: 404.php');
    exit();
}

// 设置页面标题
$page_title = $category['name'];
$page_description = $category['description'];

// 获取当前页码
$current_page = get_current_page();
$offset = ($current_page - 1) * POSTS_PER_PAGE;

// 获取分类下的文章总数
$total_posts = get_total_posts($category_id);

// 获取分类下的文章列表
$posts = get_posts_by_category($category_id, POSTS_PER_PAGE, $offset);

// 包含头部模板
require_once 'templates/header.php';
?>

    <main class="content">
        <div class="container">
            <div class="main-content">
                <h1 class="page-title"><?php echo htmlspecialspecialchars($category['name']); ?></h1>
                
                <?php if (!empty($category['description'])) : ?>
                    <div class="category-description">
                        <p><?php echo htmlspecialspecialchars($category['description']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php
                // 显示文章列表
                if (!empty($posts)) {
                    foreach ($posts as $post) {
                        ?>
                        <article class="post">
                            <h3 class="post-title">
                                <a href="<?php echo BASE_URL; ?>/article.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialspecialchars($post['title']); ?></a>
                            </h3>
                            
                            <div class="post-meta">
                                <span class="post-date"><i class="far fa-calendar-alt"></i> <?php echo format_datetime($post['created_at'], 'Y-m-d'); ?></span>
                                <span class="post-category"><i class="fas fa-folder"></i> <a href="<?php echo BASE_URL; ?>/category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialspecialchars($post['category_name']); ?></a></span>
                                <span class="post-comments"><i class="far fa-comment"></i> <a href="<?php echo BASE_URL; ?>/article.php?id=<?php echo $post['id']; ?>#comments"><?php echo get_total_comments($post['id']); ?> 评论</a></span>
                            </div>
                            
                            <?php if (!empty($post['featured_image'])) : ?>
                                <div class="post-image">
                                    <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" alt="<?php echo htmlspecialspecialchars($post['title']); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <?php echo substr(strip_tags($post['content']), 0, 300) . '...'; ?>
                            </div>
                            
                            <div class="read-more">
                                <a href="<?php echo BASE_URL; ?>/article.php?id=<?php echo $post['id']; ?>" class="btn">阅读更多</a>
                            </div>
                        </article>
                        <?php
                    }
                    
                    // 显示分页
                    $total_pages = ceil($total_posts / POSTS_PER_PAGE);
                    if ($total_pages > 1) {
                        echo get_pagination_links($total_posts, POSTS_PER_PAGE, $current_page, BASE_URL . '/category.php?id=' . $category_id);
                    }
                } else {
                    echo '<div class="no-posts">';
                    echo '<p>该分类下暂无文章</p>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <?php
            // 包含侧边栏模板
            require_once 'templates/sidebar.php';
            ?>
        </div>
    </main>

<?php
// 包含底部模板
require_once 'templates/footer.php';
?>
