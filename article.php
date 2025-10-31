<?php
/**
 * 文章详情页
 */

// 检查文章ID是否存在
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: 404.php');
    exit();
}

$post_id = intval($_GET['id']);

// 获取文章数据
require_once 'includes/functions.php';
require_once 'includes/comment_functions.php';
$post = get_post_by_id($post_id);

// 如果文章不存在，显示404页面
if (!$post) {
    header('Location: 404.php');
    exit();
}

// 设置页面标题
$page_title = $post['title'];
$page_description = substr(strip_tags($post['content']), 0, 160);

// 包含头部模板
require_once 'templates/header.php';
?>

    <main class="content">
        <div class="container">
            <div class="main-content">
                <article class="post">
                    <h1 class="post-title"><?php echo htmlspecialspecialchars($post['title']); ?></h1>
                    
                    <div class="post-meta">
                        <span class="post-date"><i class="far fa-calendar-alt"></i> <?php echo format_datetime($post['created_at'], 'Y-m-d H:i'); ?></span>
                        <span class="post-category"><i class="fas fa-folder"></i> <a href="<?php echo BASE_url; ?>/category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialspecialchars($post['category_name']); ?></a></span>
                        <span class="post-comments"><i class="far fa-comment"></i> <a href="#comments"><?php echo get_total_comments($post_id); ?> 评论</a></span>
                    </div>
                    
                    <?php if (!empty($post['featured_image'])) : ?>
                        <div class="post-image">
                            <img src="<?php echo UPLOAD_url . $post['featured_image']; ?>" alt="<?php echo htmlspecialspecialchars($post['title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <?php echo $post['content']; ?>
                    </div>
                    
                    <div class="post-tags">
                        <span class="tags-title">标签：</span>
                        <div class="tags-cloud">
                            <?php
                            // 模拟标签数据
                            $tags = ['PHP', 'MySQL', 'Web开发'];
                            foreach ($tags as $tag) {
                                echo '<a href="#">' . $tag . '</a>';
                            }
                            ?>
                        </div>
                    </div>
                </article>
                
                <div class="post-navigation">
                    <?php
                    // 获取上一篇文章
                    $db = get_db_connection();
                    $stmt = $db->prepare("SELECT id, title FROM posts WHERE id < :current_id AND status = 'published' ORDER BY id DESC LIMIT 1");
                    $stmt->bindParam(':current_id', $post_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $prev_post = $stmt->fetch();
                    
                    // 获取下一篇文章
                    $stmt = $db->prepare("SELECT id, title FROM posts WHERE id > :current_id AND status = 'published' ORDER BY id ASC LIMIT 1");
                    $stmt->bindParam(':current_id', $post_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $next_post = $stmt->fetch();
                    ?>
                    
                    <?php if ($prev_post) : ?>
                        <div class="prev-post">
                            <a href="<?php echo base_url; ?>/article.php?id=<?php echo $prev_post['id']; ?>">
                                <i class="fas fa-arrow-left"></i> 上一篇：<?php echo htmlspecialspecialchars($prev_post['title']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($next_post) : ?>
                        <div class="next-post">
                            <a href="<?php echo base_url; ?>/article.php?id=<?php echo $next_post['id']; ?>">
                                下一篇：<?php echo htmlspecialspecialchars($next_post['title']); ?> <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div id="comments" class="comments-section">
                    <h2 class="comments-title">评论 (<?php echo get_total_comments($post_id); ?>)</h2>
                    
                    <?php
                    // 获取评论
                    $comments = get_comments_by_post($post_id);
                    
                    if (!empty($comments)) {
                        foreach ($comments as $comment) {
                            ?>
                            <div class="comment">
                                <div class="comment-author">
                                    <div class="avatar">
                                        <?php
                                        // 使用Gravatar头像
                                        $email_hash = md5(strtolower(trim($comment['email'])));
                                        echo '<img src="https://www.gravatar.com/avatar/' . $email_hash . '?s=60&d=identicon" alt="' . htmlspecialspecialchars($comment['name']) . '">';
                                        ?>
                                    </div>
                                    <div class="author-info">
                                        <h4 class="author-name"><?php echo htmlspecialspecialchars($comment['name']); ?></h4>
                                        <span class="comment-date"><?php echo format_datetime($comment['created_at'], 'Y-m-d H:i'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialspecialchars($comment['content'])); ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="no-comments">暂无评论，欢迎发表评论！</p>';
                    }
                    ?>
                    
                    <div class="comment-form-section">
                        <h3 class="comment-form-title">发表评论</h3>
                        
                        <?php
                        // 处理评论提交
                        if (isset($_POST['submit_comment'])) {
                            $comment_data = [
                                'post_id' => $post_id,
                                'name' => $_POST['name'],
                                'email' => $_POST['email'],
                                'website' => $_POST['website'],
                                'content' => $_POST['content']
                            ];
                            
                            $comment_id = add_comment($comment_data);
                            
                            if ($comment_id) {
                                echo '<div class="alert alert-success">评论提交成功，等待审核！</div>';
                                // 清空表单
                                $_POST = [];
                            } else {
                                echo '<div class="alert alert-error">评论提交失败，请检查表单！</div>';
                            }
                        }
                        ?>
                        
                        <form action="#comments" method="post" class="comment-form">
                            <div class="form-group">
                                <label for="name">姓名 <span class="required">*</span></label>
                                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">邮箱 <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialspecialchars($_POST['email']) : ''; ?>">
                                <p class="form-note">您的邮箱不会被公开</p>
                            </div>
                            
                            <div class="form-group">
                                <label for="website">网站</label>
                                <input type="url" id="website" name="website" value="<?php echo isset($_POST['website']) ? htmlspecialspecialchars($_POST['website']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="content">评论内容 <span class="required">*</span></label>
                                <textarea id="content" name="content" rows="5" required><?php echo isset($_POST['content']) ? htmlspecialspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-submit">
                                <button type="submit" name="submit_comment" class="btn">提交评论</button>
                            </div>
                        </form>
                    </div>
                </div>
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
