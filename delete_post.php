<?php
session_start();
require_once 'server.php';

// ตรวจสอบการล็อกอินและสิทธิ์ของผู้ใช้
if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing post ID']);
    exit();
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// ตรวจสอบว่ามี post_id ส่งมาหรือไม่
if (empty($post_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
    exit();
}

// ดึงข้อมูลของผู้โพสต์
$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

// ตรวจสอบว่าผู้ใช้ที่ล็อกอินเป็นผู้โพสต์หรือเป็นแอดมิน
if ($post['user_id'] == $user_id || $is_admin) {
    // ลบโพสต์
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $result = $stmt->execute([$post_id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this post']);
}
?>