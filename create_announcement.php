<?php
session_start();
require 'server.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์ของผู้ใช้
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['is_admin'] === 1;

if ($isAdmin) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // รับข้อมูลจากฟอร์ม
        $title = $_POST['title'];
        $content = $_POST['content'];
        $image_url = $_POST['image_url'];

        // เชื่อมต่อฐานข้อมูลและบันทึกข้อมูล
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $image_url);

        if ($stmt->execute()) {
            echo "ประกาศได้ถูกสร้างเรียบร้อยแล้ว!";
        } else {
            echo "เกิดข้อผิดพลาด: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    echo "คุณไม่มีสิทธิ์ในการสร้างประกาศประชาสัมพันธ์";
}
$conn->close();
?>
