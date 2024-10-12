<?php
session_start();
require_once 'server.php';

// ตรวจสอบว่ามีการ POST ข้อมูลมาจริง
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่าผู้ใช้ล็อกอินแล้ว
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนสร้างกระทู้']);
        exit;
    }

    // รับข้อมูลจาก POST และกรองข้อมูล
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $subcategory = filter_input(INPUT_POST, 'subcategory', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];

    // ตรวจสอบว่าข้อมูลที่จำเป็นถูกส่งมาครบ
    if (empty($title) || empty($content) || empty($category)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit;
    }

    // จัดการอัปโหลดรูปภาพ (ถ้ามี)
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // ตรวจสอบชนิดของไฟล์ที่อัปโหลด
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'ชนิดไฟล์ไม่ถูกต้อง']);
            exit;
        }

        // ตรวจสอบขนาดไฟล์ (กำหนดขนาดสูงสุด 5MB)
        $max_file_size = 5 * 1024 * 1024; // 5MB
        if ($_FILES['image']['size'] > $max_file_size) {
            echo json_encode(['success' => false, 'message' => 'ขนาดไฟล์ใหญ่เกินไป']);
            exit;
        }

        // อัปโหลดไฟล์
        $upload_dir = 'uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = $upload_path;
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ']);
            exit;
        }
    }

    // เพิ่มข้อมูลลงในฐานข้อมูลโดยใช้ transaction
    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO posts (user_id, title, content, category, subcategory, image_url) 
                VALUES (:user_id, :title, :content, :category, :subcategory, :image_url)";
        $stmt = $pdo->prepare($sql);

        // bindParam เพื่อกำหนดค่าพารามิเตอร์
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':subcategory', $subcategory, PDO::PARAM_STR);
        $stmt->bindParam(':image_url', $image_url, PDO::PARAM_STR);

        // ตรวจสอบการ execute
        if ($stmt->execute()) {
            $pdo->commit(); // ยืนยันการทำงาน
            echo json_encode(['success' => true, 'message' => 'สร้างกระทู้สำเร็จ']);
        } else {
            $pdo->rollBack(); // ยกเลิก transaction หากเกิดข้อผิดพลาด
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>