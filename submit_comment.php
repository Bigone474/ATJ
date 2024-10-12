<?php
session_start();
require_once 'server.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];

    if (!empty($comment_content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$post_id, $user_id, $comment_content]);

        $newCommentId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT c.content, u.username, c.created_at FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
        $stmt->execute([$newCommentId]);
        $newComment = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'username' => $newComment['username'],
            'comment_content' => $newComment['content'],
            'created_at' => $newComment['created_at']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกความคิดเห็น']);
    }
}