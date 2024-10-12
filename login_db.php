<?php
session_start();
include('server.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "รูปแบบอีเมลไม่ถูกต้อง กรุณาใส่อีเมลที่ถูกต้อง";
        header('location: login.php');
        exit();
    }

    try {
        // ตรวจสอบว่ามีผู้ใช้ที่ใช้อีเมลนี้หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (verifyPassword($password, $user['password'])) {
                // การเข้าสู่ระบบสำเร็จ
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'] ?? false;

                // บันทึกประวัติการเข้าสู่ระบบ
                recordLoginHistory($user['id'], $user['is_admin'] ?? false);

                header('location: index.php');
                exit();
            } else {
                $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $_SESSION['error'] = "ไม่พบอีเมลนี้ในระบบ";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }

    header('location: login.php');
    exit();
} else {
    // ถ้าไม่ใช่การ POST ให้กลับไปที่หน้า login
    header('location: login.php');
    exit();
}