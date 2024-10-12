<?php 
session_start();
require_once 'server.php'; // ดึงการเชื่อมต่อฐานข้อมูลจาก server.php

// ตรวจสอบการล็อกอินและสิทธิ์ของผู้ใช้
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] == 0) {
    header('Location: login.php');
    exit();
}

// ฟังก์ชันบันทึกการกระทำลง activity_log
function logActivity($pdo, $user_id, $action) {
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $action]);
}

// ฟังก์ชันสร้างโพสต์ใหม่
function createPost($pdo, $title, $content, $image_url, $user_id, $category) {
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, image_url, user_id, category, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$title, $content, $image_url, $user_id, $category]);
}

// ตรวจสอบการสร้างโพสต์ใหม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_url = $_POST['image_url'];
    $category = 'ประชาสัมพันธ์'; // ตั้งค่าประเภทเป็นประชาสัมพันธ์

    if (createPost($pdo, $title, $content, $image_url, $_SESSION['user_id'], $category)) {
        $_SESSION['success'] = "สร้างโพสต์สำเร็จ";
        logActivity($pdo, $_SESSION['user_id'], "สร้างโพสต์ใหม่: $title");
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการสร้างโพสต์";
    }

    header('Location: admin_posts.php');
    exit();
}

// ตรวจสอบว่ามีการส่งคำขอเพื่อลบโพสต์หรือไม่
if (isset($_GET['delete_post'])) {
    // ... (ส่วนที่คุณมีอยู่แล้ว) ...
}

// ฟังก์ชันดึงโพสต์ทั้งหมดที่สร้างโดยผู้ดูแลระบบ
function getAdminPosts($pdo) {
    $sql = "SELECT p.id, p.title, p.image_url, p.created_at, u.username, 
                   (SELECT COUNT(*) FROM reports WHERE reports.post_id = p.id) AS report_count 
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC";  // ลบเงื่อนไข WHERE ออกเพื่อแสดงโพสต์ทั้งหมด
    $stmt = $pdo->query($sql); // ใช้ query() แทน prepare() เพราะไม่มีพารามิเตอร์
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ดึงโพสต์ทั้งหมดที่สร้างโดยผู้ดูแลระบบ
$posts = getAdminPosts($pdo);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการโพสต์ - ผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css_admin/admin_posts.css">
</head>
<body>
    <div class="container mt-5">
        <h1>จัดการโพสต์</h1>

        <!-- แสดงข้อความสถานะ -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- ฟอร์มสำหรับสร้างโพสต์ใหม่ -->
        <div class="mb-4">
            <h3>สร้างโพสต์ประชาสัมพันธ์ใหม่</h3>
            <form action="admin_posts.php" method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">หัวข้อ</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">เนื้อหา</label>
                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="image_url" class="form-label">URL รูปภาพ</label>
                    <input type="text" class="form-control" id="image_url" name="image_url" required>
                </div>
                <button type="submit" name="create_post" class="btn btn-primary">สร้างโพสต์</button>
            </form>
        </div>

        <!-- ปุ่มกลับไปหน้าหลัก -->
        <div class="mb-3">
            <a href="admin_users.php" class="btn btn-secondary">กลับไปหน้าผู้ใช้</a>
        </div>

        <!-- ตารางแสดงโพสต์ทั้งหมด -->
        <div class="table-container">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-title">
                    <tr>
                        <th>ไอดี</th>
                        <th>หัวข้อ</th>
                        <th>ผู้โพสต์</th>
                        <th>วันที่สร้าง</th>
                        <th>จำนวนการรายงาน</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['username']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                        <td><?php echo $post['report_count']; ?></td>
                        <td>
                            <a href="view_post.php?post_id=<?php echo $post['id']; ?>" class="btn btn-info btn-sm">ตรวจสอบโพสต์</a>
                            <a href="admin_posts.php?delete_post=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบโพสต์นี้?');">ลบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="admin_users.php" class="btn btn-secondary">กลับไปหน้าผู้ใช้</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
