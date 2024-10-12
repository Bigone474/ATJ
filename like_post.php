<?php
session_start();
require_once 'server.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่ได้รับอนุญาตหรือไม่มี post_id']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// ตรวจสอบว่าผู้ใช้เคยถูกใจโพสต์นี้หรือไม่
$check_sql = "SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$check_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$check_stmt->execute();
$result = $check_stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    // ถ้าเคยถูกใจแล้ว ให้ยกเลิกการถูกใจ
    $delete_sql = "DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $delete_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $delete_stmt->execute();
} else {
    // ถ้ายังไม่เคยถูกใจ ให้เพิ่มการถูกใจ
    $insert_sql = "INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $insert_stmt->execute();
}

// นับจำนวนการถูกใจทั้งหมดของโพสต์นี้
$count_sql = "SELECT COUNT(*) as likes_count FROM likes WHERE post_id = :post_id";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$count_stmt->execute();
$likes_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['likes_count'];

echo json_encode(['success' => true, 'likes_count' => $likes_count]);