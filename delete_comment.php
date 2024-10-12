<?php
session_start();
require_once 'server.php';

// ตรวจสอบว่ามีการส่ง comment_id มาหรือไม่ และว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_POST['comment_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing comment ID']);
    exit();
}else {
    error_log('Comment ID: ' . $_POST['comment_id']); // ตรวจสอบว่า comment_id ถูกส่งมาหรือไม่
}

$comment_id = $_POST['comment_id'];
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// ตรวจสอบว่าความคิดเห็นนี้มีอยู่ในฐานข้อมูลหรือไม่
$stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = :comment_id");
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->execute();
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit();
}

// ตรวจสอบว่าผู้ใช้มีสิทธิ์ลบความคิดเห็นนี้ (เป็นเจ้าของหรือเป็นแอดมิน)
if ($comment['user_id'] == $user_id || $is_admin) {
    // ลบความคิดเห็น
    $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
    $deleteStmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this comment']);
}
?>