<?php include('server.php'); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="background-image"></div>
    <div class="register-container">
        <a href="welcome.php" class="logo-link">
            <div class="logo-text atj-logo">ATJ HUB</div>
        </a>
        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px;">
            <img id="profilePreview" 
                src="http://26.120.114.83/test/ATJ/uploads/profile_images/default.png" 
                alt="รูปโปรไฟล์" 
                style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #ccc;">
        </div>

        <h2>ลงทะเบียน</h2>
        <?php if(isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <?php 
                    foreach($_SESSION['errors'] as $error) {
                        echo $error . "<br>";
                    }
                    unset($_SESSION['errors']);
                ?>
            </div>
        <?php endif; ?>
        <form class="register-form" action="register_db.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="firstname">ชื่อจริง:</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="lastname">นามสกุล:</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>
            <div class="form-group">
                <label for="email">อีเมล:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password_1">รหัสผ่าน:</label>
                <input type="password" id="password_1" name="password_1" required>
            </div>
            <div class="form-group">
                <label for="password_2">ยืนยันรหัสผ่าน:</label>
                <input type="password" id="password_2" name="password_2" required>
            </div>
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์( หากไม่ใส่หมายเลข ใส่ - ):</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="profile_image">รูปโปรไฟล์:</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)">
            </div>
            <button type="submit" name="reg_user" class="btn welcome-section__signup-btn">ลงทะเบียน</button>
        </form>
        <div class="login-link">
            <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('profilePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
</body>
</html>
