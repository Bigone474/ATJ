<?php
    session_start();
    include('server.php');

    // ตรวจสอบว่ามีการล็อกอินหรือไม่
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูลโดยใช้ PDO
    $user_id = $_SESSION['user_id'];

    // เตรียมคำสั่ง SQL
    $stmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // ดึงข้อมูลเป็น associative array
    $posts = getAllPosts($pdo);

    // ตรวจสอบว่าพบข้อมูลผู้ใช้หรือไม่
    if (!$user) {
        echo "ไม่พบข้อมูลผู้ใช้";
        exit();
    }

    // ดึงข้อมูลโพสต์จากฐานข้อมูลโดยใช้ PDO
    function getPostsByCategory($pdo, $category, $limit = 6) {
        $sql = "SELECT p.*, u.username, u.profile_image 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.category = :category 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // แก้ไขฟังก์ชัน getAllPosts ให้รับพารามิเตอร์ limit และกำหนดค่าเริ่มต้นเป็น 6
    function getAllPosts($pdo, $limit = 6) {
        $sql = "SELECT p.*, u.username, u.profile_image 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // เรียกใช้ฟังก์ชัน getAllPosts โดยกำหนด limit เป็น 6
    $allPosts = getAllPosts($pdo, 6);

    // ฟังก์ชันดึงโพสต์ยอดนิยม
    function getPopularPosts($pdo, $limit = 3) {
        $sql = "SELECT p.*, u.username, u.profile_image, 
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                ((SELECT COUNT(*) FROM likes WHERE post_id = p.id) + 
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id)) as popularity_score
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY popularity_score DESC 
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function getAnnouncementPosts($pdo, $limit = 5) {
        $sql = "SELECT p.id, p.title, p.content, p.image_url, p.created_at, u.username
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.category = 'ประชาสัมพันธ์'
                ORDER BY p.created_at DESC
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ดึงโพสต์ประชาสัมพันธ์
    $announcementPosts = getAnnouncementPosts($pdo, 5);
    
    // เรียกใช้ฟังก์ชันนี้ก่อนส่วน HTML
    $popularPosts = getPopularPosts($pdo);

    // ดึงโพสต์สำหรับแต่ละหมวดหมู่
    $newsPost = getPostsByCategory($pdo, 'news', 6);
    $sportsPost = getPostsByCategory($pdo, 'sports', 6);
    $gamesPost = getPostsByCategory($pdo, 'games', 6);
    $musicPost = getPostsByCategory($pdo, 'music', 6);
    $lifestylePost = getPostsByCategory($pdo, 'lifestyle', 6);

    // ตรวจสอบว่ามีรูปโปรไฟล์หรือไม่ ถ้าไม่มีให้ใช้รูปเริ่มต้น
    $profile_image = $user['profile_image'] ? 'uploads/profile_images/' . $user['profile_image'] : 'default_profile.png';
    $isAdmin = isset($_SESSION['user']) && $_SESSION['user']['is_admin'] === 1; // สมมุติว่า user เป็น array ที่เก็บข้อมูลของผู้ใช้

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
    <title>ATJ HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            font-family: 'Mitr', sans-serif;
            padding-top: 100px; /* เพิ่มระยะห่างด้านบนเพื่อชดเชย navbar ที่ fixed */
        }
        .main-content {
            padding-top: 80px; /* เพิ่มระยะห่างด้านบนของเนื้อหาหลัก */
        }
        .section-title {
            border-left: 5px solid #e4002b;
            padding-left: 15px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #e4002b;
        }
        .featured-post {
            height: 100%;
        }
        .featured-post .card-img-top {
            height: 300px;
            object-fit: cover;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card-title {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            /* จำกัดความสูงของหัวข้อ */
            height: 2.4rem; /* ประมาณ 2 บรรทัด */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .btn-sm {
            font-size: 0.8rem;
        }
        #announcementCarousel .carousel-item {
            height: 400px;
        }
        #announcementCarousel .card {
            height: 100%;
            border: none;
        }
        #announcementCarousel .card-img-top {
            height: 100%;
            object-fit: cover;
        }
        #announcementCarousel .card-img-overlay {
        
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1rem;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
        }
        #announcementCarousel .card-img-overlay {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1rem;
        }
        #announcementCarousel .card-text {
            font-size: 1.1rem;
        }
        #announcementCarousel .btn-primary {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        #announcementCarousel .carousel-control-prev,
        #announcementCarousel .carousel-control-next {
            background-color:transparent;
            width: 5%;
        }
        #announcementCarousel .carousel-control-prev-icon,
        #announcementCarousel .carousel-control-next-icon {
            background-color: #000;
            border-radius: 50%;
            padding: 10px;
        }
        .card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
            overflow: hidden;
            height: 350px; /* กำหนดความสูงคงที่สำหรับการ์ด */
            display: flex;
            flex-direction: column;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .card-img-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-img-top {
            width: 100%;
            height: 150px;
            object-fit: cover;
            object-position: center;
        }
        .card-text {
            color: #6c757d;
            font-size: 0.9rem;
            /* จำกัดความสูงของเนื้อหา */
            flex-grow: 2;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* แสดงไม่เกิน 3 บรรทัด */
            -webkit-box-orient: vertical;
        }
        .card-body {
            flex-grow: 1; /* ให้ card-body ขยายเต็มพื้นที่ที่เหลือ */
            display: flex;
            flex-direction: column;
            
        }
        .card-footer {
            background-color: transparent;
            border-top: 1px solid rgba(0,0,0,0.1);
            padding-top: 0.5rem;
        }

        .card-footer small {
            color: #6c757d;
        }
        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .btn-primary {
            background-color: red;
            border-color: #dc3545;
            margin-left: 0px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-primary:focus,
        .btn-primary:active {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5);
        }

        .btn-primary:not(:disabled):not(.disabled):active,
        .btn-primary:not(:disabled):not(.disabled).active {
            background-color: #bd2130;
            border-color: #b21f2d;
        }
        .btn-primary:hover {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .like-btn {
            color: #6c757d;
            padding: 0;
            text-decoration: none; /* เอาเส้นใต้ออก */
            border: none; /* เอาเส้นขอบออกหากมี */
            background: none; /* เอาพื้นหลังออกหากเป็นปุ่ม */
            cursor: pointer; /* ให้แสดงว่าเป็นปุ่มเมื่อ hover */
        }

        .like-btn:hover {
            color: #e4002b; /* เปลี่ยนเป็นสีแดงเมื่อ hover */
            text-decoration: none; /* เอาเส้นใต้ออกขณะ hover */
        }

        .like-btn.liked {
            color: #e4002b; /* สีแดงเต็มที่เมื่อถูกกด */
        }

        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css");

        .floating-menu {
          position: fixed;
          bottom: 20px;
          left: 20px;
          display: flex;
          flex-direction: column;
          align-items: center;
          z-index: 1000;
        }

        .menu-item {
          width: 50px;
          height: 50px;
          border-radius: 50%;
          background-color: #FF0033;
          color: white;
          border: none;
          margin-bottom: 10px;
          cursor: pointer;
          transition: background-color 0.3s, transform 0.3s;
          position: relative;
        }

        .menu-item:hover {
          background-color: #FF0033;
          transform: scale(1.1);
        }

        .menu-item i {
          font-size: 24px;
        }

        .menu-item::after {
          content: attr(data-tooltip);
          position: absolute;
          left: 100%;
          top: 50%;
          transform: translateY(-50%);
          background-color: #333;
          color: white;
          padding: 5px 10px;
          border-radius: 5px;
          white-space: nowrap;
          opacity: 0;
          pointer-events: none;
          transition: opacity 0.3s, transform 0.3s;
          margin-left: 10px;
        }

        .menu-item:hover::after {
          opacity: 1;
          transform: translateY(-50%) scale(1);
        }
        
        .navbar .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px; /* ปรับระยะห่างระหว่างรูปและชื่อ */
            padding: 0.25rem 0.5rem; /* ลดขนาด padding */
        }
        .navbar .dropdown-toggle:hover,
        .navbar .dropdown-toggle:focus {
            color: 	#FF0033;
        }
        .navbar .dropdown-toggle img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            margin-left: 10px;
        }
        .navbar .dropdown-menu {
            margin-top: 0.5rem;
        }
        .navbar .dropdown-item {
            padding: 0.5rem 1rem;
        }
        .navbar .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        .popular-posts .row {
            display: flex;
            flex-wrap: nowrap;
        }

        .popular-posts .col-md-8,
        .popular-posts .col-md-4 {
            flex: 0 0 auto;
        }

        .popular-posts .col-md-8 {
            width: 50%; /* ปรับขนาดของกระทู้อันดับ 1 */
        }

        .popular-posts .col-md-4 {
            width: 25%; /* ปรับขนาดของกระทู้อันดับ 2 และ 3 */
        }

        .popular-posts .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .popular-posts .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .popular-posts .featured-post .card-img-top {
            height: 250px;
        }

        .popular-posts .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .popular-posts .card-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .popular-posts .featured-post .card-title {
            font-size: 1.25rem;
        }

        .popular-posts .card-text {
            font-size: 0.8rem;
            flex-grow: 1;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .popular-posts .featured-post .card-text {
            font-size: 0.9rem;
            -webkit-line-clamp: 4;
        }

        .popular-posts .btn {
            align-self: flex-start;
            margin-top: auto;
        }

        .popular-posts .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .carousel-control-next, .carousel-control-prev {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;  /* ลดขนาดความกว้างของปุ่ม */
            height: 40px;  /* กำหนดความสูงของปุ่ม */
            padding: 0;
            color: #fff;
            text-align: center;
            background: rgba(0, 0, 0, 0.5);  /* เพิ่มพื้นหลังสีเทาโปร่งใส */
            border: 0;
            border-radius: 50%;  /* ทำให้ปุ่มเป็นวงกลม */
            opacity: 0.7;
            transition: opacity .15s ease;
        }

        .carousel-control-next:hover, .carousel-control-prev:hover {
            opacity: 1;
        }

        .carousel-control-next {
            right: 10px;  /* ปรับตำแหน่งปุ่มขวา */
        }

        .carousel-control-prev {
            left: 10px;  /* ปรับตำแหน่งปุ่มซ้าย */
        }

        .carousel-control-next-icon, .carousel-control-prev-icon {
            width: 20px;  /* ปรับขนาดไอคอน */
            height: 20px;
        }
        </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ATJ HUB</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#feed">ฟีดกระทู้</a></li>
                    <li class="nav-item"><a class="nav-link" href="#news">ข่าว</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sports">กีฬา</a></li>
                    <li class="nav-item"><a class="nav-link" href="#games">เกม</a></li>
                    <li class="nav-item"><a class="nav-link" href="#music">เพลง</a></li>
                    <li class="nav-item"><a class="nav-link" href="#lifestyle">ไลฟ์สไตล์</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            <i class="bi bi-plus-circle"></i> สร้างกระทู้
                        </button>
                    </div>
                    <div class="dropdown">
                        <a class="dropdown-toggle d-flex align-items-center hidden-arrow" href="#" id="navbarDropdownMenuAvatar" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

                            <!-- แสดงเฉพาะผู้ใช้ที่เป็นแอดมินระดับ 1 หรือ 2 -->
                            <?php if (isset($_SESSION['is_admin']) && ($_SESSION['is_admin'] == 1 || $_SESSION['is_admin'] == 2)): ?>
                                <li>
                                    <a class="dropdown-item" href="admin_users.php">
                                        <i class="bi bi-people-fill me-2"></i>จัดการผู้ใช้
                                    </a>
                                </li>
                            <?php endif; ?>

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
    </nav>

    <div class="container main-content">
    <h2 class="section-title">ประชาสัมพันธ์</h2>
    <div id="announcementCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php if (!empty($announcementPosts)): ?>
                <?php foreach ($announcementPosts as $index => $post): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="card">
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            <div class="card-img-overlay d-flex flex-column justify-content-end">
                                <h3 class="card-title text-white"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="card-text text-white"><?php echo htmlspecialchars(mb_substr($post['content'], 0, 150, 'UTF-8')) . '...'; ?></p>
                                <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-text">ไม่มีประกาศในขณะนี้</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (count($announcementPosts) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        <?php endif; ?>
    </div>


    <!-- ส่วนของกระทู้ยอดนิยม -->
    <div class="popular-posts mb-4">
        <h2 class="section-title">กระทู้ยอดนิยม</h2>
        <div class="row">
            <?php foreach ($popularPosts as $index => $post): ?>
                <?php if ($index === 0): ?>
                    <div class="col-md-8">
                        <div class="card featured-post">
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 200)) . '...'; ?></p>
                                <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                                <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container">
    <!-- ฟีดกระทู้ -->
    <div id="feed" class="category-section">
    <h2 class="section-title">ฟีดกระทู้</h2>
    <div class="row" id="feedPosts">
        <?php foreach ($allPosts as $post): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <?php if (!empty($post['image_url'])): ?>
                        <div class="card-img-container">
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                หมวดหมู่: <?php echo htmlspecialchars($post['category']); ?>
                                <?php if (!empty($post['subcategory'])): ?>
                                    , <?php echo htmlspecialchars($post['subcategory']); ?>
                                <?php endif; ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                โดย: <?php echo htmlspecialchars($post['username']); ?> | 
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                            </small>
                        </p>
                        <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                        <div class="post-actions">
                            <div class="dropdown">
                                <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                    <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                        
                                        <a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>">ลบ</a>
                                    <?php endif; ?>
                                    <a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="<?php echo $post['id']; ?>">รายงาน</a>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
        <a href="all_posts.php" class="btn btn-outline-primary">ดูทั้งหมด</a>
    </div>


    <!-- ข่าว -->
    <div id="news" class="category-section">
        <h2 class="section-title">ข่าว</h2>
        <div class="row" id="newsPosts">
            <?php foreach ($newsPost as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($post['image_url'])): ?>
                            <div class="card-img-container">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    หมวดหมู่: <?php echo htmlspecialchars($post['category']); ?>
                                    <?php if (!empty($post['subcategory'])): ?>
                                        , <?php echo htmlspecialchars($post['subcategory']); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                โดย: <?php echo isset($post['username']) ? htmlspecialchars($post['username']) : 'ไม่ระบุชื่อ'; ?> | 
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </p>
                            <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            <div class="post-actions">
                                <div class="dropdown">
                                    <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                        <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                    
                                            <a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>">ลบ</a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="<?php echo $post['id']; ?>">รายงาน</a>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="news.php?id=news" class="btn btn-outline-primary">ดูทั้งหมด</a>
        </div>
    </div>

        <!-- กีฬา -->
        <div id="sports" class="category-section">
        <h2 class="section-title">กีฬา</h2>
        <div class="row" id="sportsPosts">
            <?php foreach ($sportsPost as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($post['image_url'])): ?>
                            <div class="card-img-container">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    หมวดหมู่: <?php echo htmlspecialchars($post['category']); ?>
                                    <?php if (!empty($post['subcategory'])): ?>
                                        , <?php echo htmlspecialchars($post['subcategory']); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                โดย: <?php echo isset($post['username']) ? htmlspecialchars($post['username']) : 'ไม่ระบุชื่อ'; ?> | 
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </p>
                            <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            <div class="post-actions">
                                <div class="dropdown">
                                    <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                        <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                            
                                            <a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>">ลบ</a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="<?php echo $post['id']; ?>">รายงาน</a>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="sport.php?id=sports" class="btn btn-outline-primary">ดูทั้งหมด</a>
        </div>
    </div>

    <!-- เกม -->
    <div id="games" class="category-section">
        <h2 class="section-title">เกม</h2>
        <div class="row" id="gamesPosts">
            <?php foreach ($gamesPost as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($post['image_url'])): ?>
                            <div class="card-img-container">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    หมวดหมู่: <?php echo htmlspecialchars($post['category']); ?>
                                    <?php if (!empty($post['subcategory'])): ?>
                                        , <?php echo htmlspecialchars($post['subcategory']); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                โดย: <?php echo isset($post['username']) ? htmlspecialchars($post['username']) : 'ไม่ระบุชื่อ'; ?> | 
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </p>
                            <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            <div class="post-actions">
                                <div class="dropdown">
                                    <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                        <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                            
                                            <a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>">ลบ</a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="<?php echo $post['id']; ?>">รายงาน</a>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="games.php?id=games" class="btn btn-outline-primary">ดูทั้งหมด</a>
        </div>
    </div>

    <!-- เพลง -->
    <div id="music" class="category-section">
        <h2 class="section-title">เพลง</h2>
        <div class="row" id="musicPosts">
            <?php foreach ($musicPost as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($post['image_url'])): ?>
                            <div class="card-img-container">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    หมวดหมู่: <?php echo htmlspecialchars($post['category']); ?>
                                    <?php if (!empty($post['subcategory'])): ?>
                                        , <?php echo htmlspecialchars($post['subcategory']); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                    โดย: <?php echo isset($post['username']) ? htmlspecialchars($post['username']) : 'ไม่ระบุชื่อ'; ?> | 
                                    <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </p>
                            <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                            <div class="post-actions">
                                <div class="dropdown">
                                    <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                        <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                            
                                            <a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>">ลบ</a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="<?php echo $post['id']; ?>">รายงาน</a>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="music.php?id=music" class="btn btn-outline-primary">ดูทั้งหมด</a>
        </div>
    </div>

    <!-- ไลฟ์สไตล์ -->
<div id="lifestyle" class="category-section">
    <h2 class="section-title">ไลฟ์สไตล์</h2>
    <div class="row" id="lifestylePosts">
        <?php foreach ($lifestylePost as $post): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <?php if (!empty($post['image_url'])): ?>
                        <div class="card-img-container">
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                หมวดหมู่: <?php echo htmlspecialchars($post['category']); ?>
                                <?php if (!empty($post['subcategory'])): ?>
                                    , <?php echo htmlspecialchars($post['subcategory']); ?>
                                <?php endif; ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                            โดย: <?php echo isset($post['username']) ? htmlspecialchars($post['username']) : 'ไม่ระบุชื่อ'; ?> | 
                            <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                            </small>
                        </p>
                        <a href="view_post_user.php?post_id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
                        <div class="post-actions">
                            <div class="dropdown">
                                <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                    <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                        
                                        <a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="<?php echo $post['id']; ?>">ลบ</a>
                                    <?php endif; ?>
                                    <a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="<?php echo $post['id']; ?>">รายงาน</a>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
        <a href="lifestyle.php?id=lifestyle" class="btn btn-outline-primary">ดูทั้งหมด</a>
    </div>
</div>

    <!-- Modal สำหรับสร้างกระทู้ -->
    <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPostModalLabel">สร้างกระทู้ใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createPostForm">
                        <div class="mb-3">
                            <label for="postTitle" class="form-label">หัวข้อ</label>
                            <input type="text" class="form-control" id="postTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="postContent" class="form-label">เนื้อหา</label>
                            <textarea class="form-control" id="postContent" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="postImage" class="form-label">รูปภาพ</label>
                            <input type="file" class="form-control" id="postImage" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="postCategory" class="form-label">หมวดหมู่</label>
                            <select class="form-select" id="postCategory" required onchange="updateSubcategories()">
                                <option value="">เลือกหมวดหมู่</option>
                                <option value="news">ข่าว</option>
                                <option value="sports">กีฬา</option>
                                <option value="games">เกม</option>
                                <option value="music">เพลง</option>
                                <option value="lifestyle">ไลฟ์สไตล์</option>
                            </select>
                        </div>
                        <div class="mb-3" id="subcategoryContainer" style="display: none;">
                            <label for="postSubcategory" class="form-label">ประเภทย่อย</label>
                            <select class="form-select" id="postSubcategory">
                                <option value="">เลือกประเภทย่อย</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="submitPost()">สร้างกระทู้</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast สำหรับแสดงการแจ้งเตือน -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastMessage" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">การแจ้งเตือน</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div id="toastMessageBody" class="toast-body">
                สร้างกระทู้สำเร็จ!
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแสดงเนื้อหาโพสต์ -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postModalLabel">รายละเอียดโพสต์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="post-image-container">
                    <img id="postImage" src="" alt="รูปภาพกระทู้" style="max-width: 100%; height: auto;">
                </div>
                <div id="postContent">
                    <!-- เนื้อหาของโพสต์จะถูกโหลดที่นี่ -->
                </div>
                <hr>
                <div id="postComments">
                    <!-- ความคิดเห็นจะถูกโหลดที่นี่ -->
                </div>
                <form id="commentForm" class="mt-3">
                    <div class="mb-3">
                        <textarea class="form-control" id="commentContent" rows="3" placeholder="แสดงความคิดเห็น..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">ส่งความคิดเห็น</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    
<script>
const subcategories = {
    news: ['การเมือง', 'เศรษฐกิจ', 'สังคม', 'ต่างประเทศ'],
    sports: ['ฟุตบอล', 'บาสเกตบอล', 'วอลเลย์บอล', 'เทนนิส'],
    games: ['เกมคอมพิวเตอร์', 'เกมคอนโซล', 'เกมมือถือ', 'บอร์ดเกม'],
    music: ['ป๊อป', 'ร็อค', 'แจ๊ส', 'คลาสสิก'],
    lifestyle: ['แฟชั่น', 'อาหาร', 'ท่องเที่ยว', 'สุขภาพ', 'ความงาม']
};

function updateSubcategories() {
    const categorySelect = document.getElementById('postCategory');
    const subcategorySelect = document.getElementById('postSubcategory');
    const subcategoryContainer = document.getElementById('subcategoryContainer');
    const selectedCategory = categorySelect.value;

    subcategorySelect.innerHTML = '<option value="">เลือกประเภทย่อย</option>';

    if (selectedCategory && subcategories[selectedCategory]) {
        subcategories[selectedCategory].forEach(subcat => {
            const option = document.createElement('option');
            option.value = subcat;
            option.textContent = subcat;
            subcategorySelect.appendChild(option);
        });
        subcategoryContainer.style.display = 'block';
    } else {
        subcategoryContainer.style.display = 'none';
    }
}

function submitPost() {
    const title = document.getElementById('postTitle').value;
    const content = document.getElementById('postContent').value;
    const category = document.getElementById('postCategory').value;
    const subcategory = document.getElementById('postSubcategory').value;
    const image = document.getElementById('postImage').files[0];

    if (!title || !content || !category) {
        alert('กรุณากรอกข้อมูลให้ครบถ้วน');
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('content', content);
    formData.append('category', category);
    formData.append('subcategory', subcategory);
    if (image) {
        formData.append('image', image);
    }

    fetch('create_post.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('เกิดข้อผิดพลาดจากเซิร์ฟเวอร์');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('สร้างกระทู้สำเร็จ!');
            document.getElementById('createPostForm').reset();
            var modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
            modal.hide();
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการส่งข้อมูล: ' + error.message);
    });
}

function resizeImage(file, maxWidth, maxHeight, callback) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var width = img.width;
            var height = img.height;

            if (width > height) {
                if (width > maxWidth) {
                    height *= maxWidth / width;
                    width = maxWidth;
                }
            } else {
                if (height > maxHeight) {
                    width *= maxHeight / height;
                    height = maxHeight;
                }
            }

            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            callback(canvas.toDataURL(file.type));
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function createAndAddPost(title, content, category, subcategory, imageUrl, postId, isOwnPost) {
    if (!title || !content || !category) {
        alert('กรุณากรอกข้อมูลให้ครบถ้วน');
        return;
    }
    
    var newPost = createPostElement(title, content, category, subcategory, imageUrl, postId, isOwnPost);
    
    var feedPosts = document.getElementById('feedPosts');
    if (feedPosts) {
        feedPosts.insertBefore(newPost, feedPosts.firstChild);
    }
    
    var categoryPosts = document.getElementById(category + 'Posts');
    if (categoryPosts) {
        categoryPosts.insertBefore(newPost.cloneNode(true), categoryPosts.firstChild);
    }

    var toastMessage = 'สร้างกระทู้สำเร็จ! หัวข้อ: ' + title + ' หมวดหมู่: ' + category;
    if (subcategory) toastMessage += ' ประเภทย่อย: ' + subcategory;
    showToast(toastMessage);

    var modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
    if (modal) {
        modal.hide();
    }

    document.getElementById('createPostForm').reset();
    document.getElementById('subcategoryContainer').style.display = 'none';
}

function showToast(message) {
    var toastEl = document.getElementById('toastMessage');
    var toast = new bootstrap.Toast(toastEl);
    document.getElementById('toastMessageBody').innerText = message;
    toast.show();
}

function createPostElement(title, content, category, subcategory, imageUrl, postId, isOwnPost, author, createdAt) {
    var postElement = document.createElement('div');
    postElement.className = 'col-md-4 mb-4';
    postElement.innerHTML = `
        <div class="card">
            ${imageUrl ? `<div class="card-img-container"><img src="${imageUrl}" class="card-img-top" alt="${title}"></div>` : ''}
            <div class="card-body">
                <h5 class="card-title">${title}</h5>
                <p class="card-text">${content.substring(0, 100)}...</p>
                <p class="card-text">
                    <small class="text-muted">
                        หมวดหมู่: ${category}${subcategory ? ', ' + subcategory : ''}
                    </small>
                </p>
                <p class="card-text">
                    <small class="text-muted">
                        โดย: ${author} | ${new Date(createdAt).toLocaleString('th-TH')}
                    </small>
                </p>
                <a href="view_post_user.php?id=${postId}" class="btn btn-primary">อ่านเพิ่มเติม</a>
                <div class="post-actions">
                    <button class="like-btn" data-post-id="${postId}">
                        <span class="like-count">0</span> ถูกใจ
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-link" type="button" id="dropdownMenuButton${postId}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${postId}">
                            ${isOwnPost ? `<li><a href="javascript:void(0);" class="dropdown-item delete-post" data-post-id="${postId}">ลบ</a></li>` : ''}
                            <li><a href="javascript:void(0);" class="dropdown-item report-post" data-post-id="${postId}">รายงาน</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
    return postElement;
}

function toggleLike(postId) {
    console.log('Attempting to like post:', postId);
    fetch('like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
    .then(response => response.text())
   .then(text => {
       try {
           return JSON.parse(text);
       } catch (e) {
           console.error('Invalid JSON:', text);
           throw new Error('Invalid JSON response');
       }
   })
   .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
            if (likeBtn) {
                const likeCount = likeBtn.querySelector('.like-count');
                if (likeCount) {
                    likeCount.textContent = data.likes_count;
                }
                likeBtn.classList.toggle('liked');
            } else {
                console.warn(`ไม่พบปุ่มไลค์สำหรับโพสต์ ID: ${postId}`);
            }
        } else {
            throw new Error(data.message || 'เกิดข้อผิดพลาดในการถูกใจโพสต์');
        }
    })
   .catch(error => {
       console.error('Error:', error);
       alert('เกิดข้อผิดพลาดในการถูกใจโพสต์: ' + error.message);
   });
}

function loadComments(postId) {
    fetch('get_comments.php?post_id=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentsList = document.querySelector(`#comments-${postId} .comments-list`);
                
                commentsList.innerHTML = ''; 

                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment mb-2';
                    
                    commentElement.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${comment.username}</strong>: ${comment.content}
                                <small class="text-muted">${comment.created_at}</small>
                            </div>
                            ${(comment.user_id === currentUserId || isAdmin) ? `
                                <div class="dropdown">
                                    <button class="btn btn-link p-0" type="button" id="dropdownMenuButton${comment.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${comment.id}">
                                        <li><a class="dropdown-item" href="#" onclick="editComment(${comment.id}, '${comment.content}', ${postId})">แก้ไข</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="deleteComment(${comment.id}, ${postId})">ลบ</a></li>
                                    </ul>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    commentsList.appendChild(commentElement);
                });
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function handleCommentSubmit(postId, content) {
    const submitButton = document.querySelector(`.submit-comment[data-post-id="${postId}"]`);

    submitButton.disabled = true;

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
            document.querySelector(`#comment-input-${postId}`).value = '';
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการส่งความคิดเห็น');
    })
    .finally(() => {
        submitButton.disabled = false;
    });
}

function reportPost(postId) {
    const reason = prompt('กรุณาระบุเหตุผลในการรายงานโพสต์นี้:');
    if (reason) {
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

const currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
const isAdmin = <?php echo json_encode(isset($_SESSION['is_admin']) && $_SESSION['is_admin']); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const createPostBtn = document.getElementById('createPostBtn');
    const createPostModal = new bootstrap.Modal(document.getElementById('createPostModal'));
    const createPostForm = document.getElementById('createPostForm');
    const postCategorySelect = document.getElementById('postCategory');

    if (createPostBtn) {
        createPostBtn.addEventListener('click', function() {
            createPostModal.show();
        });
    }

    if (postCategorySelect) {
        postCategorySelect.addEventListener('change', updateSubcategories);
    }

    if (createPostForm) {
        createPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitPost();
        });
    }

    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-post')) {
            e.preventDefault();
            const postId = e.target.getAttribute('data-post-id');
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบกระทู้นี้?')) {
                deletePost(postId);
            }
        } else if (e.target.classList.contains('report-post')) {
            e.preventDefault();
            const postId = e.target.getAttribute('data-post-id');
            reportPost(postId);
        } else if (e.target.closest('.like-btn')) {
            e.preventDefault();
            const postId = e.target.closest('.like-btn').dataset.postId;
            toggleLike(postId);
        }
    });

    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            const action = this.getAttribute('data-tooltip');
            switch(action) {
                case 'หน้าหลัก':
                    window.location.href = 'index.php';
                    break;
                case 'สร้างกระทู้':
                    createPostModal.show();
                    break;
                case 'โปรไฟล์':
                    window.location.href = 'profile.php';
                    break;
            }
        });
    });
});

function deletePost(postId) {
    fetch('delete_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('ลบโพสต์สำเร็จ');
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการลบโพสต์');
    });
}

function editComment(commentId, content, postId) {
    const newContent = prompt('แก้ไขความคิดเห็น:', content);
    if (newContent !== null && newContent !== content) {
        fetch('edit_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}&content=${encodeURIComponent(newContent)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadComments(postId);
            } else {
                alert('เกิดข้อผิดพลาดในการแก้ไขความคิดเห็น: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการแก้ไขความคิดเห็น');
        });
    }
}

function deleteComment(commentId, postId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบความคิดเห็นนี้?')) {
        fetch('delete_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadComments(postId);
            } else {
                alert('เกิดข้อผิดพลาดในการลบความคิดเห็น: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการลบความคิดเห็น');
        });
    }
}
</script>
</body>
</html>