<?php
    session_start();
    require_once 'server.php';

    // ตรวจสอบว่าได้ส่ง post_id มาหรือไม่
    if (!isset($_GET['post_id'])) {
        header('Location: admin_posts.php');
        exit();
    }

    $post_id = $_GET['post_id'];

    // ดึงข้อมูลโพสต์จากฐานข้อมูล พร้อมดึงรูปภาพ
    $stmt = $pdo->prepare("SELECT p.title, p.content, p.image_url, p.created_at, u.username FROM posts p
                        JOIN users u ON p.user_id = u.id
                        WHERE p.id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: admin_posts.php');
        exit();
    }

    // ดึงข้อมูลการรายงานที่เกี่ยวข้องกับโพสต์
    $report_stmt = $pdo->prepare("SELECT r.reason, r.report_time, u.username AS reporter
        FROM reports r
        JOIN users u ON r.user_id = u.id
        WHERE r.post_id = ?");
    $report_stmt->execute([$post_id]);
    $reports = $report_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดูโพสต์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css_admin/view_post.css"> <!-- ใช้ไฟล์ CSS ใหม่ -->
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="card-title mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="text-muted">โพสต์โดย: <strong><?php echo htmlspecialchars($post['username']); ?></strong> เมื่อ <strong><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></strong></p>
            
            <!-- ตรวจสอบว่ามีรูปภาพหรือไม่ -->
            <?php if (!empty($post['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="รูปภาพโพสต์" class="img-fluid mb-3 rounded" style="max-width: 100%; height: auto;">
            <?php endif; ?>

            <div class="content mb-3">
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            </div>

            <!-- การแสดงการรายงาน -->
            <?php if (!empty($reports)): ?>
                <div class="mt-4">
                    <h4>การรายงาน</h4>
                    <ul class="list-group">
                        <?php foreach ($reports as $report): ?>
                            <li class="list-group-item">
                                รายงานโดย: <strong><?php echo htmlspecialchars($report['reporter']); ?></strong><br>
                                เหตุผล: <?php echo htmlspecialchars($report['reason']); ?><br>
                                เมื่อ: <?php echo date('d/m/Y H:i', strtotime($report['report_time'])); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ปุ่มกลับ -->
            <a href="admin_posts.php" class="btn btn-secondary mt-3">กลับ</a>

            <!-- ปุ่มลบโพสต์ -->
            <form id="deletePostForm" method="POST">
                <input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>">
                <button type="submit" class="btn btn-danger mt-3">ลบโพสต์</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- เพิ่ม JavaScript เปลี่ยนเส้นทางหลังจากส่งฟอร์ม -->
<script>
    document.getElementById('deletePostForm').onsubmit = function(event) {
        event.preventDefault(); // ป้องกันไม่ให้ฟอร์มถูกส่งแบบปกติ

        if (confirm('คุณแน่ใจหรือไม่ที่จะลบโพสต์นี้?')) {
            var postId = document.getElementById('post_id').value;

            // ใช้ XMLHttpRequest เพื่อส่งคำขอ AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_post.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('ลบโพสต์สำเร็จ!');
                        window.location.href = 'admin_posts.php'; // เปลี่ยนเส้นทางหลังจากลบโพสต์สำเร็จ
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.message);
                    }
                }
            };
            xhr.send('post_id=' + postId); // ส่ง post_id ไปที่ delete_post.php
        }
    };
</script>
</body>
</html>
