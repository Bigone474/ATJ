<?php
require_once 'server.php';

// ตรวจสอบว่ามีการส่ง post_id และเป็นตัวเลขหรือไม่
if (!isset($_GET['post_id']) || !filter_var($_GET['post_id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
    exit();
}

$post_id = $_GET['post_id'];

// เตรียมและดำเนินการ SQL เพื่อดึงข้อมูลโพสต์จากฐานข้อมูล พร้อมดึงข้อมูลชื่อผู้ใช้ จำนวนไลค์ และจำนวนความคิดเห็น
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.content, p.image_url, p.created_at, u.username,
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = :post_id
");
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าพบโพสต์หรือไม่
if ($post) {
    $post['title'] = !empty($post['title']) ? $post['title'] : 'ไม่มีหัวข้อ';
    $post['content'] = !empty($post['content']) ? $post['content'] : 'ไม่มีเนื้อหา';
    $post['image_url'] = !empty($post['image_url']) ? $post['image_url'] : null; // กำหนดค่าเป็น null หากไม่มีรูปภาพ

    echo json_encode(['success' => true, 'post' => $post]);
} else {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
}