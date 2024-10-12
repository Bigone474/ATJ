<?php
session_start();
require_once 'server.php';

if (isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);

    try {
        $sql = "SELECT 
                    COALESCE((SELECT COUNT(*) FROM likes WHERE post_id = :post_id), 0) AS likes_count,
                    COALESCE((SELECT COUNT(*) FROM comments WHERE post_id = :post_id), 0) AS comments_count
                FROM posts WHERE id = :post_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                'success' => true,
                'likes_count' => $row['likes_count'],
                'comments_count' => $row['comments_count']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่พบโพสต์']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่ได้ระบุ ID ของโพสต์']);
}