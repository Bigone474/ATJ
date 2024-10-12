<?php 
session_start();
include('server.php');
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // ลบข้อความ error หลังจากแสดงแล้ว
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <div class="background-image"></div>
    <div class="login-container">
        <a href="welcome.php" class="logo-link">
            <div class="logo-text atj-logo">ATJ HUB</div>
        </a>
        <h2>เข้าสู่ระบบ</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        <!-- ฟอร์มเข้าสู่ระบบ -->
        <form action="login_db.php" method="POST">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="อีเมล" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="รหัสผ่าน" required>
            </div>
            <button type="submit" class="btn btn-login">เข้าสู่ระบบ</button>
        </form>

        <p class="text-center mt-3">ยังไม่มีบัญชี? <a href="register.php">สมัครเลย</a></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>