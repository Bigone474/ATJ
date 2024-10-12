<?php
session_start();
include 'server.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] == 0) {
    header('Location: index.php');
    exit;
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "ไม่พบผู้ใช้";
        exit;
    }
}

// บันทึกการเปลี่ยนรหัสผ่าน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        echo "รหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 8) {
        echo "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
    } else {
        // เข้ารหัสรหัสผ่านและบันทึกลงในฐานข้อมูล
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hashed_password, $user_id]);

        // บันทึกประวัติการกระทำการเปลี่ยนรหัสผ่าน
        try {
            $action = 'เปลี่ยนรหัสผ่านสำหรับผู้ใช้: ' . $user['username'];
            $log_stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action) VALUES (?, ?)');
            $log_stmt->execute([$_SESSION['user_id'], $action]);
            echo "บันทึกการเปลี่ยนรหัสผ่านสำเร็จ"; // แสดงข้อความว่าบันทึกสำเร็จ
        } catch (PDOException $e) {
            echo "เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage();
        }

        // หลังจากเปลี่ยนรหัสผ่านสำเร็จ ย้ายกลับไปหน้าผู้ใช้ทั้งหมด
        header('Location: admin_users.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css_admin/reset_password.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center text-primary">เปลี่ยนรหัสผ่านสำหรับผู้ใช้: <?php echo htmlspecialchars($user['username']); ?></h2>

    <!-- ฟอร์มเปลี่ยนรหัสผ่าน -->
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่านใหม่:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่:</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">เปลี่ยนรหัสผ่าน</button>
    </form>

    <a href="admin_users.php" class="btn btn-secondary mt-3 w-100">กลับไปหน้าจัดการผู้ใช้</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>