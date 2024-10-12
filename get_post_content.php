<?php
require_once 'server.php';

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    // ดึงข้อมูลโพสต์จากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        echo json_encode([
            'success' => true,
            'title' => $post['title'],
            'content' => $post['content'],
            'username' => $post['username'],
            'created_at' => $post['created_at'],
            'image_url' => $post['image_url']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบโพสต์นี้']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มี post_id']);
}