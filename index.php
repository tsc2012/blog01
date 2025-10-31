<?php
/**
 * 博客首页
 */

// 设置页面标题
$page_title = '首页';

// 包含头部模板
require_once 'includes/functions.php';
require_once 'templates/header.php';
?>

    <main class="content">
        <div class="container">
            <div class="main-content">
                <?php
                // 处理搜索
                if (isset($_GET['s']) && !empty($_GET['s'])) {
                    $search_term = clean_input($_GET['s']);
                    $page_title = '搜索结果: ' . $search_term;
                    
                    // 获取搜索结果
                    $db = get_db_connection();
                    $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                                        FROM posts p 
                                        LEFT JOIN categories c ON p.category_id = c.id 
                                        WHERE p.title LIKE :search OR p.content LIKE :search 
                                        ORDER BY p.created_at DESC");
                    $search_param = '%' . $search_term . '%';
                    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
                    $stmt->execute();
                    $posts = $stmt->fetchAll();
                    
                    echo '<h2 class="page-title">搜索结果: ' . htmlspecialspecialchars($search_term) . '</h2>';
                    echo '<p class="search-count">找到 ' . count($posts) . ' 篇相关文章</p>';
                } else {
                    // 获取当前页码
                    $current_page = get_current_page();
                    $offset = ($current_page - 1) * POSTS_PER_PAGE;
                    
                    // 获取文章总数
                    $total_posts = get_total_posts();
                    
                    // 获取文章列表
                    $posts = get_all_posts(POSTS_PER_PAGE, $offset);
                    
                    echo '<h2 class="page-title">最新文章</h2>';
                }
                
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
                                <span class="post-category"><i class="fas fa-folder"></i> <a href="<?php echo BASE_URL; ?>/category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialchars($post['category_name']); ?></a></span>
                                <span class="post-comments"><i class="far fa-comment"></i> <a href="<?php echo BASE_URL; ?>/article.php?id=<?php echo $post['id']; ?>#comments"><?php echo get_total_comments($post['id']); ?> 评论</a></span>
                            </div>
                            
                            <?php if (!empty($post['featured_image'])) : ?>
                                <div class="post-image">
                                    <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
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
                    if (!isset($_GET['s']) || empty($_GET['s'])) {
                        $total_pages = ceil($total_posts / POSTS_PER_PAGE);
                        if ($total_pages > 1) {
                            echo get_pagination_links($total_posts, POSTS_PER_PAGE, $current_page, BASE_URL . '/index.php');
                        }
                    }
                } else {
                    echo '<div class="no-posts">';
                    echo '<p>没有找到相关文章</p>';
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
