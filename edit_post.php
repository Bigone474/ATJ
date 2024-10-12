<?php
require_once 'server.php';

if (!isset($_POST['post_id']) || !isset($_POST['title']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$post_id = $_POST['post_id'];
$title = $_POST['title'];
$content = $_POST['content'];

// ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่หรือไม่
if (!empty($_FILES['image']['name'])) {
    $target_dir = "uploads/"; // โฟลเดอร์ที่เก็บรูปภาพ
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // ตรวจสอบประเภทไฟล์
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type']);
        exit();
    }

    // อัปโหลดรูปภาพ
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // อัปเดตโพสต์พร้อมกับ URL รูปภาพใหม่
        $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content, image_url = :image_url WHERE id = :post_id");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':image_url', $target_file);
        $stmt->bindParam(':post_id', $post_id);
        $result = $stmt->execute();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit();
    }
} else {
    // อัปเดตโพสต์โดยไม่มีการเปลี่ยนแปลงรูปภาพ
    $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :post_id");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':post_id', $post_id);
    $result = $stmt->execute();
}

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update post']);
}
?>