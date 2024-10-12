<?php
    // ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'คุณต้องล็อกอินก่อนที่จะรายงานโพสต์']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // นำเข้าฟังก์ชันจาก server.php
        require_once 'server.php';

        $post_id = cleanInput($_POST['post_id']);
        $reason = cleanInput($_POST['reason']);
        $user_id = $_SESSION['user_id'];

        // บันทึกการรายงานโพสต์
        if (reportPost($post_id, $user_id, $reason)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถรายงานโพสต์ได้']);
        }
    }
?>