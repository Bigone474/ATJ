<?php
session_start();
require_once 'server.php';

// ตรวจสอบว่า comment_id และ content ถูกส่งมาและผู้ใช้ล็อกอินแล้ว
if (!isset($_POST['comment_id'], $_POST['content'], $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing data']);
    exit();
}

$comment_id = $_POST['comment_id'];
$new_content = $_POST['content'];
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

// ตรวจสอบสิทธิ์ในการแก้ไข (เป็นเจ้าของความคิดเห็นหรือเป็นแอดมิน)
if ($comment['user_id'] == $user_id || $is_admin) {
    // อัปเดตความคิดเห็น
    $updateStmt = $pdo->prepare("UPDATE comments SET content = :content WHERE id = :comment_id");
    $updateStmt->bindParam(':content', $new_content, PDO::PARAM_STR);
    $updateStmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'You are not authorized to edit this comment']);
}
?>