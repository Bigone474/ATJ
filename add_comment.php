<?php
session_start();
require_once 'server.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'ผู้ใช้ไม่ได้เข้าสู่ระบบ']);
    exit();
}

// ตรวจสอบว่ามีการส่ง post_id และเนื้อหาความคิดเห็นมาหรือไม่
if (!isset($_POST['post_id']) || empty(trim($_POST['content']))) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit();
}

$post_id = $_POST['post_id'];
$content = trim($_POST['content']);  // ใช้ trim เพื่อลบช่องว่างต้นและท้าย
$user_id = $_SESSION['user_id'];

// เพิ่มความคิดเห็นลงในฐานข้อมูล
$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())");
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':content', $content, PDO::PARAM_STR);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'เพิ่มความคิดเห็นสำเร็จ']);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเพิ่มความคิดเห็น']);
}
?>