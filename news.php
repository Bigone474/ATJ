
<?php
session_start();
require_once 'server.php'; // ดึงการเชื่อมต่อฐานข้อมูลจาก server.php

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลโดยใช้ PDO
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, profile_image FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);



// ฟังก์ชันดึงกระทู้ข่าวทั้งหมด
function getNewsPosts($pdo, $subcategory = null) {
    $sql = "SELECT p.*, u.username, u.profile_image, 
            COALESCE((SELECT COUNT(*) FROM likes WHERE post_id = p.id), 0) AS likes_count,
            COALESCE((SELECT COUNT(*) FROM comments WHERE post_id = p.id), 0) AS comments_count
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.category = 'news'";

    if ($subcategory) {
        $sql .= " AND p.subcategory = :subcategory";
    }

    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    
    if ($subcategory) {
        $stmt->bindParam(':subcategory', $subcategory);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ฟังก์ชันดึงประเภทย่อยทั้งหมดของหมวดข่าว
function getNewsSubcategories($pdo) {
    $sql = "SELECT DISTINCT subcategory FROM posts WHERE category = 'news' AND subcategory IS NOT NULL";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ดึงประเภทย่อยทั้งหมด
$subcategories = getNewsSubcategories($pdo);

// ตรวจสอบว่ามีการเลือกประเภทย่อยหรือไม่
$selected_subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;

// ดึงกระทู้ข่าว
$news_posts = getNewsPosts($pdo, $selected_subcategory);

// แบ่งกระทู้ตามหมวดหมู่ย่อย
$categorized_posts = [];
foreach ($news_posts as $post) {
    $subcategory = $post['subcategory'] ?? 'อื่นๆ';
    $categorized_posts[$subcategory][] = $post;
}

// ฟังก์ชันดึงข่าวยอดนิยม 5 อันดับ
function getPopularNews($pdo, $limit = 5) {
    $sql = "SELECT p.*, u.username, 
            COALESCE((SELECT COUNT(*) FROM likes WHERE post_id = p.id), 0) AS likes_count,
            COALESCE((SELECT COUNT(*) FROM comments WHERE post_id = p.id), 0) AS comments_count
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.category = 'news'
            ORDER BY (likes_count + comments_count) DESC
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ดึงข่าวยอดนิยม
$popular_news = getPopularNews($pdo);

// ฟังก์ชันสำหรับดึงความคิดเห็น
function getComments($pdo, $post_id) {
    $sql = "SELECT c.*, u.username FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = :post_id
            ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กระทู้ข่าว - ATJ HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Mitr', sans-serif;
            background-color: #f8f9fa;
            padding-top: 60px;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #e4002b;
        }
        .navbar-nav .nav-link {
            color: #555555;
            font-weight: 500;
        }
        .navbar-nav .nav-link:hover {
            color: #e4002b;
        }
        .navbar .dropdown-toggle,
        .navbar .dropdown-toggle:hover,
        .navbar .dropdown-toggle:focus {
            color:#333;
            text-decoration: none !important;
        }
        .navbar .dropdown-toggle img {
            margin-right: var(--avatar-margin, 0.5rem);
        }
        .navbar .dropdown-toggle {
            padding: 8px 12px 8px 8px;
        }
        .form-select {
            color:#555555 ;
            border: 2px solid #e4002b;
            border-radius: 20px;
            padding: 10px 15px;
        }
        .mb-3 {
            margin-bottom: 1rem !important;
            border-left: 5px solid #e4002b;
            padding-left: 15px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #e4002b;
        }
        .category-title {
            background-color: #e4002b;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
        }
        .post-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .post-image, .post-card .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
        }
        .post-card .card-body {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .post-card .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }
        .post-card .card-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .post-card .btn {
            margin-top: auto;
            background-color: #e4002b;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
        }
        /* เปลี่ยนสีจุดสามจุด */
        .post-card .btn.three-dots {
            color: #BEBEBE; /* เปลี่ยนสีจุดสามจุดเป็นสีแดง */
            background-color: transparent !important; /* ใช้ !important เพื่อแทนที่การตั้งค่าที่มี */
            border: none; /* ลบขอบ */
        }
        .post-card .btn:hover {
            background-color: #c1001f;
        }
        .post-image-container {
            text-align: center;
            margin-bottom: 15px;
        }
        .post-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .post-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 15px;
        }
        .post-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .post-text {
            flex-grow: 1;
            overflow: hidden;
        }
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .popular-news {
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0;
        }
        .popular-news-item {
            display: inline-block;
            width: 200px;
            margin-right: 15px;
            vertical-align: top;
        }
        .popular-news-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        .popular-news-item .card {
            height: 100%;
            display: flex;
            flex-direction: column;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .popular-news-item .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }
        .popular-news-item .btn {
            margin-top: auto;
            background-color: #e4002b;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
        }
        .comment-section {
            max-height: 200px;
            overflow-y: auto;
        }
        .modal-body img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 15px;
            border-radius: 5px;
        }
        .post-image-container {
            text-align: center;
            margin-bottom: 15px;
        }
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .modal-header {
            border-bottom: none;
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-radius: 15px 15px 0 0;
        }
        .modal-title {
            font-weight: bold;
            color: #333;
        }
        .modal-body {
            padding: 30px;
        }
        #postImage {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            border-radius: 5px;
        }
        #postContent {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 20px;
        }
        #postComments {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .comment {
            background-color: #fff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        #commentForm textarea {
            border: 1px solid #ced4da;
            border-radius: 5px;
            resize: vertical;
        }
        #commentForm button {
            padding: 10px 20px;
            font-weight: bold;
            background-color: #e4002b;
            color:#fff;
            border: 2px solid #e4002b;
        }
        #postComments::-webkit-scrollbar {
            width: 8px;
        }
        #postComments::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        #postComments::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        #postComments::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .three-dots {
            color: red; /* เปลี่ยนสีข้อความเป็นสีแดง */
            border: none; /* ลบขอบของปุ่ม */
            background-color: transparent; /* ทำให้พื้นหลังโปร่งใส */
        }
       
        .btn-link {
            padding: 0;
            font-size: 1.25rem;  /* ปรับขนาดตามต้องการ */
        }
        .btn-link:hover,
        .btn-link:focus {
            text-decoration: none;
        }
        .card-header {
            background-color: transparent;
            border-bottom: none;
            padding-bottom: 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">ATJ HUB</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="all_posts.php">ฟีดกระทู้</a></li>
                <li class="nav-item"><a class="nav-link" href="news.php">ข่าว</a></li>
                <li class="nav-item"><a class="nav-link" href="sport.php">กีฬา</a></li>
                <li class="nav-item"><a class="nav-link" href="games.php">เกม</a></li>
                <li class="nav-item"><a class="nav-link" href="music.php">เพลง</a></li>
                <li class="nav-item"><a class="nav-link" href="lifestyle.php">ไลฟ์สไตล์</a></li>
            </ul>
            <div class="d-flex align-items-center">
            <div class="dropdown">
                    <a class="dropdown-toggle d-flex align-items-center hidden-arrow text-decoration-none" href="#" id="navbarDropdownMenuAvatar" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'default_profile.jpg'); ?>" alt="Profile Picture" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuAvatar">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person-circle me-2"></i>ข้อมูลส่วนตัว
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </nav>

    <div class="container mt-5">
        <h1 class="category-title">กระทู้ข่าว</h1>
        
        <!-- ส่วนแสดงข่าวยอดนิยม -->
        <h3 class="mb-3">ข่าวยอดนิยม</h3>
        <div class="popular-news mb-4">
            <?php foreach ($popular_news as $news): ?>
                <div class="popular-news-item">
                    <div class="card h-100">
                        <?php if (!empty($news['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($news['image_url']); ?>" class="card-img-top popular-news-image" alt="รูปภาพข่าว">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars(substr($news['title'], 0, 50)) . '...'; ?></h6>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-hand-thumbs-up"></i> <?php echo $news['likes_count']; ?>
                                        <i class="bi bi-chat-dots ms-2"></i> <?php echo $news['comments_count']; ?>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-primary mt-2 read-more" data-post-id="<?php echo $news['id']; ?>" data-bs-toggle="modal" data-bs-target="#postModal">อ่านเพิ่มเติม</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- เพิ่มส่วนเลือกประเภทย่อย -->
        <form action="" method="get" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <select name="subcategory" class="form-select" onchange="this.form.submit()">
                        <option value="">ทุกประเภท</option>
                        <?php foreach ($subcategories as $subcategory): ?>
                            <option value="<?php echo htmlspecialchars($subcategory['subcategory']); ?>"
                                <?php echo $selected_subcategory === $subcategory['subcategory'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subcategory['subcategory']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
        
        <?php if (empty($categorized_posts)): ?>
    <div class="alert alert-info">ไม่พบกระทู้ในประเภทที่เลือก</div>
<?php else: ?>
    <?php foreach ($categorized_posts as $subcategory => $posts): ?>
        <h2 class="mb-3"><?php echo htmlspecialchars($subcategory); ?></h2>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card post-card h-100">
                        <?php if (!empty($post['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="รูปภาพกระทู้">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?></p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">โดย <?php echo htmlspecialchars($post['username']); ?></small>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary like-btn" data-post-id="<?php echo $post['id']; ?>">
                                            <i class="bi bi-hand-thumbs-up"></i> <span class="like-count"><?php echo $post['likes_count']; ?></span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary comment-btn" data-post-id="<?php echo $post['id']; ?>">
                                            <i class="bi bi-chat-dots"></i> <span class="comment-count"><?php echo $post['comments_count']; ?></span>
                                        </button>
                                    </div>
                                </div>
                                <button class="btn btn-primary mt-2 read-more" data-post-id="<?php echo $post['id']; ?>" data-bs-toggle="modal" data-bs-target="#postModal">อ่านเพิ่มเติม</button>
                            </div>
                        </div>
                        <div class="dropdown position-relative">
                            <button class="btn btn-link p-0 three-dots" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" style="position: absolute; right: 10px; bottom: 10px;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                    <li><a class="dropdown-item delete-post" href="#" data-post-id="<?php echo $post['id']; ?>">ลบ</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item report-post" href="#" data-post-id="<?php echo $post['id']; ?>">รายงาน</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <hr>
    <?php endforeach; ?>
   <?php endif; ?>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">รายละเอียดกระทู้</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="post-image-container">
                        <img id="postImage" src="" alt="รูปภาพกระทู้" style="max-width: 100%; height: auto;">
                    </div>
                    <div id="postContent"></div>
                    <hr>
                    <div id="postComments"></div>
                    <form id="commentForm" class="mt-3">
                        <div class="mb-3">
                            <textarea class="form-control" id="commentContent" rows="3" placeholder="แสดงความคิดเห็น..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">ส่งความคิดเห็น</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ส่งค่า user_id และ is_admin จาก PHP ไปยัง JavaScript
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const isAdmin = <?php echo json_encode(isset($_SESSION['is_admin']) && $_SESSION['is_admin']); ?>;

        function deletePost(postId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบกระทู้นี้?')) {
                fetch('delete_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'post_id=' + postId
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response from server:', data); // ตรวจสอบข้อมูลการตอบกลับจากเซิร์ฟเวอร์
                    if (data.success) {
                        alert('ลบโพสต์สำเร็จ');
                        location.reload(); // รีเฟรชหน้าเพื่ออัปเดต
                    } else {
                        alert('เกิดข้อผิดพลาดในการลบโพสต์: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการลบโพสต์');
                });
            }
        }
        function reportPost(postId) {
            // ให้ผู้ใช้กรอกเหตุผลในการรายงาน
            const reason = prompt('กรุณาระบุเหตุผลในการรายงานโพสต์นี้:');
            if (reason) {
                // ส่งข้อมูลการรายงานไปยังเซิร์ฟเวอร์โดยใช้ fetch
                fetch('report_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&reason=${encodeURIComponent(reason)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('ขอบคุณสำหรับการรายงาน เราจะตรวจสอบกระทู้นี้โดยเร็วที่สุด');
                    } else {
                        alert('เกิดข้อผิดพลาดในการรายงาน: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการส่งคำร้อง');
                });
            }
        }
        

        document.addEventListener('DOMContentLoaded', function() {
            // Event Listener สำหรับการลบกระทู้
            document.querySelectorAll('.delete-post').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const postId = this.getAttribute('data-post-id');
                    console.log('Post ID:', postId); // ตรวจสอบว่า postId ถูกต้อง
                    deletePost(postId);
                });
            });
            // Event Listener สำหรับการรายงานกระทู้
            document.querySelectorAll('.report-post').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const postId = this.getAttribute('data-post-id');
                    reportPost(postId);
                });
            });
            
            // ฟังก์ชันสำหรับการถูกใจ
            function likePost(postId) {
                fetch('like_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'post_id=' + postId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
                        const likeCount = likeBtn.querySelector('.like-count');
                        likeCount.textContent = data.likes_count;
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            // ฟังก์ชันสำหรับโหลดความคิดเห็น
            function loadComments(postId) {
                fetch('get_comments.php?post_id=' + postId)
                .then(response => response.json())
                .then(data => {
                    const commentsList = document.getElementById('postComments');
                    commentsList.innerHTML = '<h6>ความคิดเห็น</h6>';
                    data.comments.forEach(comment => {
                        const commentElement = document.createElement('div');
                        commentElement.className = 'comment mb-2';
                        commentElement.innerHTML = `
                            <strong>${comment.username}</strong>: ${comment.content}
                            <small class="text-muted">${comment.created_at}</small>
                        `;
                        commentsList.appendChild(commentElement);
                    });
                    document.querySelector('.modal-body').setAttribute('data-post-id', postId);
                })
                .catch(error => console.error('Error:', error));
            }

            // ฟังก์ชันสำหรับส่งความคิดเห็น
            function submitComment(postId, content) {
                fetch('add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&content=${encodeURIComponent(content)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadComments(postId);
                        document.getElementById('commentContent').value = '';
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            // เพิ่ม Event Listeners
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.getAttribute('data-post-id');
                    likePost(postId);
                });
            });

            document.querySelectorAll('.comment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.getAttribute('data-post-id');
                    const commentSection = document.querySelector(`#comments-${postId}`);
                    if (commentSection.style.display === 'none') {
                        commentSection.style.display = 'block';
                        loadComments(postId);
                    } else {
                        commentSection.style.display = 'none';
                    }
                });
            });

            document.getElementById('commentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const postId = document.querySelector('.modal-body').getAttribute('data-post-id');
                const content = document.getElementById('commentContent').value.trim();
                if (content) {
                    submitComment(postId, content);
                }
            });

            // ฟังก์ชันสำหรับโหลดเนื้อหากระทู้
            function loadPostContent(postId) {
                fetch('get_post_content.php?post_id=' + postId)
                .then(response => response.json())
                .then(data => {
        if (data.success) {
            document.getElementById('postModalLabel').textContent = data.title;
            const postImage = document.getElementById('postImage');
            const imageContainer = document.querySelector('.post-image-container');
            if (data.image_url && data.image_url.trim() !== '') {
                postImage.src = data.image_url;
                postImage.style.display = 'block';
                imageContainer.style.display = 'block';
            } else {
                postImage.style.display = 'none';
                imageContainer.style.display = 'none';
            }
            document.getElementById('postContent').innerHTML = `
                <p>${data.content}</p>
                <small class="text-muted">โดย ${data.username} | ${data.created_at}</small>
            `;
            loadComments(postId);
        }
    })
    .catch(error => console.error('Error:', error));
}

            // เพิ่ม Event Listener สำหรับปุ่ม "อ่านเพิ่มเติม"
            document.querySelectorAll('.read-more').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.getAttribute('data-post-id');
                    loadPostContent(postId);
                });
            });

            var postModal = document.getElementById('postModal');
            postModal.addEventListener('hidden.bs.modal', function (event) {
                // รีเฟรชหน้าเว็บเมื่อ Modal ถูกปิด
                location.reload();
            });
        });
    </script>
</body>
</html>