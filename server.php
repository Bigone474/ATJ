<?php
    // ตรวจสอบสถานะ session หากยังไม่มี session ให้เริ่มต้นใหม่
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // กำหนดค่าการเชื่อมต่อฐานข้อมูล (แทนที่ config.php)
    $host = 'localhost';
    $db = 'atjsytem_db';
    $user = 'root';
    $pass = '';
    
    // การตั้งค่า DSN และ PDO options
    $dsn = "mysql:host=" . $host . ";dbname=" . $db . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,   // โยนข้อยกเว้นเมื่อเกิดข้อผิดพลาด
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // ดึงข้อมูลออกมาเป็น associative array
    ];
    
    try {
        // สร้างการเชื่อมต่อฐานข้อมูล PDO
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // หากการเชื่อมต่อล้มเหลว ให้แสดงข้อความข้อผิดพลาดและหยุดการทำงาน
        die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
    }    

    // ตั้งค่าเขตเวลาของระบบ
    date_default_timezone_set('Asia/Bangkok');

    // ฟังก์ชันทำความสะอาดข้อมูล
    function cleanInput($data) {
        $data = trim($data); // ลบช่องว่างข้างหน้าและข้างหลัง
        $data = stripslashes($data); // ลบแบ็คสแลช
        $data = htmlspecialchars($data); // แปลงตัวอักษรพิเศษ
        return $data;
    }

    // ฟังก์ชันเพื่อตรวจสอบว่าผู้ใช้มีอยู่ในฐานข้อมูลหรือไม่
    function userExists($username, $email) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch(); // คืนค่าผู้ใช้ถ้ามีหรือ false ถ้าไม่มี
    }

    // ฟังก์ชันสำหรับเพิ่มผู้ใช้ลงฐานข้อมูล
    function addUser($username, $email, $password, $firstname, $lastname, $phone, $profile_image = 'default.png') {
        global $pdo;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, firstname, lastname, phone, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed_password, $firstname, $lastname, $phone, $profile_image]);
    }

    // ฟังก์ชันตรวจสอบรหัสผ่าน
    function verifyPassword($password, $hashed_password) {
        return password_verify($password, $hashed_password);
    }

    
    // ฟังก์ชันลบโพสต์
    function deletePost($pdo, $post_id, $user_id, $is_admin) {
        if ($is_admin) {
            // แอดมินสามารถลบโพสต์ใดก็ได้
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        } else {
            // ผู้ใช้ธรรมดาลบโพสต์ได้เฉพาะที่ตนเป็นเจ้าของ
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id AND user_id = :user_id");
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
    
        return $stmt->execute();
    }    
    

    // ฟังก์ชันแก้ไขโพสต์
    function editPost($pdo, $post_id, $user_id, $is_admin, $title, $content, $image_url) {
        // ตรวจสอบว่าผู้ใช้เป็นเจ้าของโพสต์หรือเป็นแอดมิน
        if ($is_admin) {
            // ถ้าเป็นแอดมิน ให้แก้ไขโพสต์โดยไม่สนว่าใครเป็นเจ้าของ
            $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content, image_url = :image_url WHERE id = :post_id");
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        } else {
            // ถ้าเป็นผู้ใช้ธรรมดา ให้แก้ไขเฉพาะโพสต์ที่ตัวเองเป็นเจ้าของ
            $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content, image_url = :image_url WHERE id = :post_id AND user_id = :user_id");
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':image_url', $image_url, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // ฟังก์ชันบันทึกการรายงานโพสต์
    function reportPost($post_id, $user_id, $reason) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO reports (post_id, user_id, reason, report_time) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$post_id, $user_id, $reason, date('Y-m-d H:i:s')]);
    }



    // ฟังก์ชันสำหรับอัปโหลดรูปภาพ
    function uploadProfileImage($file) {
        $target_dir = "uploads/profile_images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . uniqid() . '.' . strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        // ตรวจสอบว่าเป็นไฟล์รูปภาพจริงหรือไม่
        if (getimagesize($file["tmp_name"]) === false || $file["size"] > 500000) {
            return false;
        }

        // อนุญาตเฉพาะไฟล์บางประเภท
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), $allowed_types)) {
            return false;
        }

        return move_uploaded_file($file["tmp_name"], $target_file) ? $target_file : false;
    }

    // ฟังก์ชันสำหรับบันทึกประวัติการเข้าสู่ระบบ
    function recordLoginHistory($user_id, $is_admin) {
        global $pdo;
        $login_time = date("Y-m-d H:i:s");
        $stmt = $pdo->prepare("INSERT INTO login_history (user_id, is_admin, login_time) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $is_admin, $login_time]);
    }
?>