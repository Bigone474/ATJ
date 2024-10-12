<?php
session_start();
require_once 'server.php'; // ดึงการเชื่อมต่อฐานข้อมูลจาก server.php

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// รับ ID ของโพสต์จาก URL
$post_id = isset($_GET['post_id']) ? $_GET['post_id'] : null;

if (!$post_id) {
    echo "ไม่พบโพสต์";
    exit();
}

// ดึงข้อมูลโพสต์จากฐานข้อมูล
$stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "ไม่พบโพสต์นี้";
    exit();
}

// ดึงจำนวนถูกใจ
$stmt = $pdo->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$like_data = $stmt->fetch(PDO::FETCH_ASSOC);
$like_count = $like_data['like_count'];

// ดึงคอมเมนต์จากฐานข้อมูล
function getComments($pdo, $post_id) {
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$comments = getComments($pdo, $post_id);

// ดึงจำนวนคอมเมนต์
$comment_count = count($comments);

// ถ้ามีการส่งคอมเมนต์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];

    if (!empty($comment_content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$post_id, $user_id, $comment_content]);
        header("Location: view_post_user.php?post_id=" . $post_id . "#comments"); // รีเฟรชหน้าเพื่อแสดงคอมเมนต์ใหม่และให้เลื่อนไปที่ส่วนคอมเมนต์
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      body {
                background-color: #f0f2f5;
                font-family: 'Mitr', sans-serif;
            }

            .container-box {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }

            .post-details {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .post-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }

            h1 {
                color: #CC3333;
                font-size: 28px;
                margin-bottom: 10px;
            }

            .text-muted {
                color: #65676b;
            }

            .post-image {
                max-width: 100%;
                height: auto;
                margin-bottom: 20px;
                border-radius: 8px;
            }

            p {
                font-size: 16px;
                line-height: 1.6;
                color: #333;
            }

            .like-btn {
                background: none;
                border: none;
                color: #CC3333;
                cursor: pointer;
                font-size: 14px;
                display: flex;
                align-items: center;
                padding: 5px 10px;
                border-radius: 5px;
                transition: background-color 0.3s;
            }

            .like-btn:hover {
                background-color: #f0f2f5;
            }

            .like-btn.liked {
                color: #CC3333;
            }

            .like-btn i {
                margin-right: 5px;
                font-size: 18px;
            }

            .comments-section {
                margin-top: 30px;
                border-top: 2px solid #CC3333;
                padding-top: 20px;
            }

            .comments-section h3 {
                color: #CC3333;
                font-size: 22px;
                margin-bottom: 15px;
            }

            .comment {
                background-color: #f9f9f9;
                border-left: 3px solid #CC3333;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 0 8px 8px 0;
            }

            .comment p {
                margin: 0 0 10px 0;
                color: #333;
            }

            .add-comment {
                margin-top: 30px;
                background-color: #f9f9f9;
                padding: 20px;
                border-radius: 8px;
            }

            .add-comment h4 {
                color: #CC3333;
                font-size: 20px;
                margin-bottom: 15px;
            }

            textarea.form-control {
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            .btn-primary {
                background-color: #CC3333;
                border-color: #CC3333;
                transition: background-color 0.3s;
            }

            .btn-primary:hover {
                background-color: #AA2222;
                border-color: #AA2222;
            }

            .bi {
                margin-right: 5px;
            }

            .back-button {
            position: fixed; /* ทำให้ปุ่มกากบาทอยู่ที่ตำแหน่งบนขวาของหน้าจอ */
            top: 10px;
            right: 10px;
            color: #000;
            font-size: 2rem; /* เพิ่มขนาดฟอนต์ */
            cursor: pointer;
            z-index: 1000; /* เพื่อให้ปุ่มอยู่เหนือทุกส่วนของหน้า */
            }

            .post-image-container {
            display: flex;
            justify-content: center; /* จัดแนวนอนให้รูปอยู่ตรงกลาง */
            align-items: center; /* จัดแนวตั้งให้รูปอยู่ตรงกลาง */
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- ปุ่มกากบาทเพื่อกลับไปยัง index.php -->
    <a href="index.php" class="back-button">
        <i class="bi bi-x-lg"></i> <!-- ไอคอนกากบาท -->
    </a>

    <div class="container-box mt-5">
        <div class="post-details">
            <div class="post-header">
                <div>
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    <small class="text-muted">โดย: <?php echo htmlspecialchars($post['username']); ?> | เมื่อ: <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></small>
                </div>
                <button class="like-btn <?php echo $like_count > 0 ? 'liked' : ''; ?>" id="likeBtn" data-liked="false">
                    <i class="bi bi-heart"></i> 
                    <span id="likeCount"><?php echo $like_count; ?></span>
                </button>
            </div>

            <!-- แสดงรูปภาพถ้ามี และจัดให้อยู่ตรงกลาง -->
            <?php if (!empty($post['image_url'])): ?>
                <div class="post-image-container">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post Image" class="post-image">
                </div>
            <?php endif; ?>

            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        </div>

        <hr>

        <div class="comments-section" id="comments">
            <h3>ความคิดเห็น (<?php echo $comment_count; ?>)</h3>

            <!-- แสดงคอมเมนต์ -->
            <div class="comments-container">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            <small class="text-muted">โดย: <?php echo htmlspecialchars($comment['username']); ?> | เมื่อ: <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>ยังไม่มีความคิดเห็น</p>
                <?php endif; ?>
            </div>

            <!-- ฟอร์มสำหรับเพิ่มคอมเมนต์ -->
            <div class="add-comment">
                <h4>แสดงความคิดเห็น</h4>
                <form id="commentForm" action="" method="POST">
                    <div class="mb-3">
                        <textarea name="comment_content" id="commentContent" class="form-control" rows="4" placeholder="แสดงความคิดเห็น..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> ส่งความคิดเห็น
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // จำลองการกดถูกใจ
        $('#likeBtn').click(function() {
            var $likeBtn = $(this);
            var liked = $likeBtn.data('liked') === 'true'; // ตรวจสอบสถานะว่าได้ถูกใจหรือไม่
            var likeCount = parseInt($('#likeCount').text());

            // ถ้าถูกใจก็ลดจำนวนลง และเปลี่ยนไอคอน, ถ้าไม่ถูกใจก็เพิ่มจำนวน
            if (liked) {
                likeCount--;
                $likeBtn.data('liked', 'false');
                $likeBtn.find('i').removeClass('bi-heart-fill').addClass('bi-heart');
            } else {
                likeCount++;
                $likeBtn.data('liked', 'true');
                $likeBtn.find('i').removeClass('bi-heart').addClass('bi-heart-fill');
            }

            // อัปเดตจำนวนถูกใจ
            $('#likeCount').text(likeCount);
        });

        // ส่งคอมเมนต์โดยการกด Enter หรือปุ่มส่ง
        $('#commentForm').on('submit', function(e) {
            e.preventDefault(); // ป้องกันการ submit form ปกติ
            this.submit(); // ส่งข้อมูลฟอร์ม
        });

        $('#commentContent').keydown(function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $('#commentForm').submit(); // ส่งข้อมูลฟอร์มเมื่อกด Enter
            }
        });
    </script>
</body>
</html>