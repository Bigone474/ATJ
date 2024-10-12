<?php
session_start();
require_once 'server.php'; // ต้องใช้ $pdo แทน $conn เนื่องจากเราเปลี่ยนเป็น PDO แล้ว

if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่ได้รับอนุญาตหรือไม่มี post_id']);
    exit;
}

$post_id = $_GET['post_id'];

$sql = "SELECT c.*, u.username FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = :post_id
        ORDER BY c.created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formattedComments = [];
foreach ($comments as $row) {
    $formattedComments[] = [
        'id' => $row['id'],
        'content' => $row['content'],
        'username' => $row['username'],
        'created_at' => date('d/m/Y H:i', strtotime($row['created_at']))
    ];
}

echo json_encode(['success' => true, 'comments' => $formattedComments]);