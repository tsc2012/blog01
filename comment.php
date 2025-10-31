<?php
/**
 * 评论处理页面
 */

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: 404.php');
    exit();
}

// 检查评论数据是否完整
if (!isset($_POST['post_id'], $_POST['name'], $_POST['email'], $_POST['content'])) {
    header('Location: 404.php');
    exit();
}

// 处理评论提交
require_once 'includes/comment_functions.php';

$comment_data = [
    'post_id' => $_POST['post_id'],
    'name' => $_POST['name'],
    'email' => $_POST['email'],
    'website' => isset($_POST['website']) ? $_POST['website'] : '',
    'content' => $_POST['content']
];

$comment_id = add_comment($comment_data);

// 重定向回文章页面
$redirect_url = BASE_URL . '/article.php?id=' . $_POST['post_id'] . '#comments';

if ($comment_id) {
    // 评论提交成功
    $_SESSION['comment_success'] = '评论提交成功，等待审核！';
} else {
    // 评论提交失败
    $_SESSION['comment_error'] = '评论提交失败，请检查表单！';
}

header('Location: ' . $redirect_url);
exit();
?>
