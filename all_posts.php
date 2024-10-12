<?php
    session_start();
    require_once 'server.php'; // ตรวจสอบให้แน่ใจว่า server.php ถูก include ไว้เพื่อเชื่อมต่อฐานข้อมูล

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        echo "ไม่พบ user_id ใน session";
        // จัดการกรณีที่ไม่มี user_id เช่น redirect ไปหน้า login
        header('Location: login.php');
        exit();
    }    

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT username, profile_image FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $posts = getAllPosts($pdo);

    // ตรวจสอบว่าพบข้อมูลผู้ใช้หรือไม่
    if (!$user) {
        echo "ไม่พบข้อมูลผู้ใช้";
        exit();
    }
    
    // ฟังก์ชันดึงโพสต์ทั้งหมด
    function getAllPosts($pdo, $limit = 10, $offset = 0) {
        $sql = "SELECT p.*, u.username, u.profile_image, 
                COALESCE((SELECT COUNT(*) FROM likes WHERE post_id = p.id), 0) AS likes_count,
                COALESCE((SELECT COUNT(*) FROM comments WHERE post_id = p.id), 0) AS comments_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงโพสต์
    $posts = getAllPosts($pdo);
    
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟีดกระทู้ทั้งหมด - ATJ HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
       body {
            padding-top: 100px;
            font-family: 'Mitr', sans-serif;
            background-color: #f0f0f0; /* เทาอ่อน */
        }
        .navbar {
            background-color: #ffffff; /* สีพื้นหลังเป็นสีขาว */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* เงา */
        }

        .navbar-brand {
            font-weight: bold; /* ตัวหนา */
            color: #e4002b; /* สีแดง */
        }

        .navbar-nav .nav-link {
            color: #555555; /* สีเทาเข้ม */
            font-weight: 500; /* น้ำหนักตัวอักษร */
        }

        .navbar-nav .nav-link:hover {
            color: #e4002b; /* สีแดงเมื่อ hover */
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

    .container {
        max-width: 750px;
    }

    .card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        background-color: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px); /* เอฟเฟกต์การลอย */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 20px;
    }

    .card-title {
        font-weight: bold;
        color: #343a40;
    }

    .post-image {
        display: block; /* ทำให้รูปอยู่กึ่งกลาง */
        margin: 0 auto; /* จัดกึ่งกลางรูปภาพในแนวนอน */
        max-width: 100%; /* ไม่ให้รูปเกินจากขนาดการ์ด */
        height: auto; /* รักษาอัตราส่วนของรูปภาพ */
        border-radius: 10px; /* มุมโค้ง */
        margin-bottom: 15px;
        max-height: 400px; /* กำหนดความสูงสูงสุดเพื่อไม่ให้รูปสูงเกินไป */
        object-fit: cover; /* ปรับขนาดรูปให้อยู่ในกรอบอย่างพอดี */
    }


    .category-info {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .btn-primary {
        background-color: #e63946;
        border-color: #e63946;
        transition: background-color 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #d62828;
        border-color: #d62828;
    }

    .btn-outline-secondary {
        color: #343a40;
        border-color: #e0e0e0;
    }

    .btn-outline-secondary:hover {
        background-color: #e0e0e0;
    }

    .page-title {
        border-left: 5px solid #e4002b;
        padding-left: 15px;
        margin-bottom: 20px;
        font-weight: bold;
        color: #e4002b;
    }

    .dropdown-menu {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-primary.rounded-circle {
        width: 60px;
        height: 60px;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .btn-primary.rounded-circle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }
    .btn-link .bi-three-dots-vertical {
    color: #6c757d;  /* สีเทาของ Bootstrap */
    }

    .btn-link:hover .bi-three-dots-vertical {
        color: #5a6268;  /* สีเทาเข้มขึ้นเมื่อ hover */
    }

    </style>
</head>
<body>
    <!-- ส่วนหัวของหน้า -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">ATJ HUB</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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

    <div class="container mt-4">
        <h1 class="page-title">ฟีดกระทู้ทั้งหมด</h1>
        
        <?php foreach ($posts as $post): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($post["title"]); ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-link" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                                <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']): ?>
                                    <li><a class="dropdown-item edit-post" href="#" data-post-id="<?php echo $post['id']; ?>" data-bs-toggle="modal" data-bs-target="#editPostModal">แก้ไข</a></li>
                                    <li><a class="dropdown-item delete-post" href="#" data-post-id="<?php echo $post['id']; ?>">ลบ</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item report-post" href="#" data-post-id="<?php echo $post['id']; ?>">รายงาน</a></li>
                            </ul>
                        </div>
                    </div>
                    <?php if (!empty($post["image_url"])): ?>
                        <img src="<?php echo htmlspecialchars($post["image_url"]); ?>" alt="รูปภาพกระทู้" class="post-image mb-3">
                    <?php endif; ?>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>
                    <p class="category-info">
                        หมวดหมู่: <?php echo htmlspecialchars($post["category"]); ?>
                        <?php if (!empty($post["subcategory"])): ?>
                            | ประเภท: <?php echo htmlspecialchars($post["subcategory"]); ?>
                        <?php endif; ?>
                    </p>
                    <p class="text-muted">
                        โดย: <?php echo htmlspecialchars($post["username"]); ?> | 
                        <?php echo date('d/m/Y H:i', strtotime($post["created_at"])); ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn btn-primary btn-sm like-btn" data-post-id="<?php echo $post['id']; ?>">
                            <i class="bi bi-hand-thumbs-up"></i> ถูกใจ <span class="like-count"><?php echo $post['likes_count']; ?></span>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm comment-btn" data-post-id="<?php echo $post['id']; ?>">
                            <i class="bi bi-chat-dots"></i> แสดงความคิดเห็น <span class="comment-count"><?php echo $post['comments_count']; ?></span>
                        </button>
                    </div>
                    <div class="comments-section mt-3" id="comments-<?php echo $post['id']; ?>">
                        <h6>ความคิดเห็น</h6>
                        <div class="comments-list" id="comment-list-<?php echo $post['id']; ?>">
                            <!-- ความคิดเห็นจะถูกโหลดที่นี่ -->
                        </div>
                        <form class="comment-form mt-2" onsubmit="return false;">
                            <div class="input-group">
                                <input type="text" class="form-control comment-input" id="comment-input-<?php echo $post['id']; ?>" placeholder="แสดงความคิดเห็น..." data-post-id="<?php echo $post['id']; ?>" onkeydown="if (event.key === 'Enter') submitComment(<?php echo $post['id']; ?>);">
                                <button class="btn btn-primary submit-comment" type="button" data-post-id="<?php echo $post['id']; ?>" onclick="submitComment(<?php echo $post['id']; ?>)">ส่ง</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ปุ่มสร้างกระทู้ -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <button type="button" class="btn btn-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#createPostModal" title="สร้างกระทู้">
            <i class="bi bi-pencil-square"></i>
        </button>
    </div>

    <!-- Modal สร้างกระทู้ -->
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
                        <div class="mb-3">
                            <label for="postSubcategory" class="form-label">ประเภทย่อย</label>
                            <select class="form-select" id="postSubcategory">
                                <option value="">เลือกประเภทย่อย</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="postImage" class="form-label">รูปภาพ (ถ้ามี)</label>
                            <input type="file" class="form-control" id="postImage" accept="image/*">
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

    <!-- Modal แก้ไขโพสต์ -->
    <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPostModalLabel">แก้ไขกระทู้</h5>
                    <button type="button" class="btn-close" id="closeButton" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPostForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="editPostTitle" class="form-label">หัวข้อ</label>
                            <input type="text" class="form-control" id="editPostTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPostContent" class="form-label">เนื้อหา</label>
                            <textarea class="form-control" id="editPostContent" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editPostImage" class="form-label">เปลี่ยนรูปภาพ (ถ้ามี)</label>
                            <input type="file" class="form-control" id="editPostImage" accept="image/*">
                        </div>
                        <div id="imageContainer" class="mb-3" style="display: none;">
                            <label for="currentImage" class="form-label">รูปภาพปัจจุบัน</label>
                            <img id="currentImage" src="" alt="Current Post Image" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                        <input type="hidden" id="editPostId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelButton" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="submitEditPost()">บันทึกการแก้ไข</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ข้อมูลประเภทย่อยสำหรับแต่ละหมวดหมู่
    const subcategories = {
        news: ['การเมือง', 'เศรษฐกิจ', 'สังคม', 'ต่างประเทศ', 'เทคโนโลยี'],
        sports: ['ฟุตบอล', 'บาสเกตบอล', 'วอลเลย์บอล', 'เทนนิส', 'กอล์ฟ'],
        games: ['เกมคอมพิวเตอร์', 'เกมคอนโซล', 'เกมมือถือ', 'บอร์ดเกม', 'เกมออนไลน์'],
        music: ['ป๊อป', 'ร็อค', 'แจ๊ส', 'ฮิปฮอป', 'คลาสสิก'],
        lifestyle: ['แฟชั่น', 'อาหาร', 'ท่องเที่ยว', 'สุขภาพ', 'ความงาม']
    };

    function updateSubcategories() {
        const category = document.getElementById('postCategory').value;
        const subcategorySelect = document.getElementById('postSubcategory');
        subcategorySelect.innerHTML = '<option value="">เลือกประเภทย่อย</option>';

        if (category in subcategories) {
            subcategories[category].forEach(sub => {
                const option = document.createElement('option');
                option.value = sub;
                option.textContent = sub;
                subcategorySelect.appendChild(option);
            });
            subcategorySelect.disabled = false;
        } else {
            subcategorySelect.disabled = true;
        }
    }

    function refreshPage() {
        location.reload();  // รีเฟรชหน้า
    }

    // ปิด Modal เมื่อกดปุ่ม "ยกเลิก"
    document.getElementById('cancelButton').addEventListener('click', function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('editPostModal'));
        modal.hide();  // ปิด Modal
        refreshPage();  // รีเฟรชหน้า
    });

    // ปิด Modal เมื่อกดปุ่มกากะบาท (x)
    document.getElementById('closeButton').addEventListener('click', function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('editPostModal'));
        modal.hide();  // ปิด Modal
        refreshPage();  // รีเฟรชหน้า
    });

    function submitPost() {
        const title = document.getElementById('postTitle').value;
        const content = document.getElementById('postContent').value;
        const category = document.getElementById('postCategory').value;
        const subcategory = document.getElementById('postSubcategory').value;
        const image = document.getElementById('postImage').files[0];

        // ตรวจสอบว่าข้อมูลที่จำเป็นถูกกรอกครบถ้วน
        if (!title || !content || !category) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }

        // สร้าง FormData object เพื่อส่งข้อมูลรวมถึงไฟล์
        const formData = new FormData();
        formData.append('title', title);
        formData.append('content', content);
        formData.append('category', category);
        formData.append('subcategory', subcategory);
        if (image) {
            formData.append('image', image);
        }

        // ส่งข้อมูลไปยังเซิร์ฟเวอร์ด้วย AJAX
        fetch('create_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('สร้างกระทู้สำเร็จ');
                location.reload(); // รีโหลดหน้าเพื่อแสดงกระทู้ใหม่
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
        });

        // ปิด Modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
        modal.hide();
    }

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
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการถูกใจโพสต์');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('โพสต์ถูกลบไปแล้ว: ' + error.message);
        });
    }

    // ส่งค่า user_id และ is_admin จาก PHP ไปยัง JavaScript
    const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const isAdmin = <?php echo json_encode(isset($_SESSION['is_admin']) && $_SESSION['is_admin']); ?>;

    // ฟังก์ชันสำหรับโหลดความคิดเห็นแบบเรียลไทม์
    function loadComments(postId) {
        fetch('get_comments.php?post_id=' + postId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const commentsList = document.querySelector(`#comments-${postId} .comments-list`);
                    
                    // ล้างคอมเมนต์เก่าออกก่อนเพิ่มใหม่
                    commentsList.innerHTML = ''; 

                    data.comments.forEach(comment => {
                        const commentElement = document.createElement('div');
                        commentElement.className = 'comment mb-2';
                        
                        // สร้าง HTML สำหรับความคิดเห็น และตรวจสอบว่าเป็นเจ้าของหรือแอดมิน
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

    // เพิ่ม Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Event Listener สำหรับปุ่มถูกใจ
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                likePost(postId);
            });
        });

        // ฟังก์ชันสำหรับการส่งความคิดเห็น ใช้เฉพาะ Event Listener
        async function handleCommentSubmit(postId, content) {
            try {
                const response = await fetch('add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&content=${encodeURIComponent(content)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadComments(postId); // โหลดความคิดเห็นใหม่หลังจากส่งสำเร็จ

                    // เพิ่มจำนวนความคิดเห็นทันทีใน DOM
                    const commentCountElement = document.querySelector(`.comment-btn[data-post-id="${postId}"] .comment-count`);
                    if (commentCountElement) {
                        // แปลงค่าจากข้อความเป็นตัวเลข แล้วเพิ่ม 1
                        const currentCount = parseInt(commentCountElement.textContent, 10);
                        commentCountElement.textContent = currentCount + 1;
                    }

                    // ล้างข้อความในกล่องอินพุตความคิดเห็น
                    const commentInput = document.querySelector(`#comment-input-${postId}`);
                    if (commentInput) {
                        commentInput.value = ''; // ล้างข้อความในกล่องอินพุต
                    }

                    return true;
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                    return false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการส่งความคิดเห็น');
                return false;
            }
        }

        // Event Listener สำหรับการคลิกปุ่ม "แสดงความคิดเห็น"
        document.addEventListener('click', function(e) {
            // ตรวจสอบว่าการคลิกเกิดขึ้นที่ปุ่มที่มี class "comment-btn"
            if (e.target && e.target.closest('.comment-btn')) {
                e.preventDefault();  // ป้องกันการ reload หน้า
                const button = e.target.closest('.comment-btn');
                const postId = button.getAttribute('data-post-id');
                
                // ค้นหา element ที่แสดงความคิดเห็น
                const commentSection = document.getElementById(`comments-${postId}`);
                
                // ถ้าแสดงอยู่ให้ซ่อน, ถ้าไม่แสดงให้โหลดและแสดงความคิดเห็น
                if (commentSection.style.display === 'block') {
                    commentSection.style.display = 'none';  // ซ่อนความคิดเห็น
                } else {
                    // ถ้ายังไม่ได้แสดงความคิดเห็นให้โหลดและแสดง
                    loadComments(postId);
                    commentSection.style.display = 'block';  // แสดงความคิดเห็น
                }
            }
        });

        // Event Listener สำหรับการคลิกปุ่มส่งความคิดเห็น
        document.querySelectorAll('.submit-comment').forEach(button => {
            button.addEventListener('click', async function () {
                const postId = this.getAttribute('data-post-id');
                const commentInput = document.querySelector(`.comment-input[data-post-id="${postId}"]`);
                const content = commentInput.value.trim();

                if (content) {
                    // ปิดการทำงานของปุ่มเพื่อป้องกันการส่งซ้ำ
                    button.disabled = true;

                    // ส่งความคิดเห็นไปยังเซิร์ฟเวอร์
                    const success = await handleCommentSubmit(postId, content);
                    
                    if (success) {
                        // ล้างข้อความในช่องกรอกความคิดเห็นหลังจากส่งสำเร็จ
                        commentInput.value = '';
                    }
                    
                    // เปิดใช้งานปุ่มอีกครั้งหลังจากเสร็จสิ้นการส่งหรือเกิดข้อผิดพลาด
                    button.disabled = false;

                } else {
                    alert('กรุณากรอกข้อความก่อนส่ง');
                }
            });
        });

        // Event Listener สำหรับการกด Enter
        document.querySelectorAll('.comment-input').forEach(input => {
            input.addEventListener('keydown', async function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    const postId = this.getAttribute('data-post-id');
                    const content = this.value.trim(); // ตัดช่องว่าง

                    if (content) {
                        // ส่งความคิดเห็นไปยังเซิร์ฟเวอร์
                        const success = await handleCommentSubmit(postId, content);

                        // ถ้าสำเร็จให้ล้างกล่องอินพุต
                        if (success) {
                            this.value = ''; // ล้างข้อความในกล่องอินพุต
                        }
                    }
                }
            });
        });

        // Event Listener สำหรับการคลิกปุ่มส่งความคิดเห็น
        document.querySelectorAll('.submit-comment').forEach(button => {
            button.addEventListener('click', function () {
                const postId = this.getAttribute('data-post-id');
                submitComment(postId); // เรียกใช้ฟังก์ชัน submitComment
            }, { once: true }); // ใช้ { once: true } เพื่อให้ Event Listener ทำงานเพียงครั้งเดียวต่อปุ่ม
        });


        // Event Listener สำหรับการแก้ไขกระทู้
        document.querySelectorAll('.edit-post').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const postId = this.getAttribute('data-post-id');
                editPost(postId);
            });
        });

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

    });

    function editPost(postId) {
        fetch(`get_post.php?post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // กรอกข้อมูลโพสต์ในฟอร์ม
                    document.getElementById('editPostId').value = data.post.id;
                    document.getElementById('editPostTitle').value = data.post.title;
                    document.getElementById('editPostContent').value = data.post.content;

                    // ตรวจสอบว่ามีรูปภาพหรือไม่
                    const imageContainer = document.getElementById('imageContainer');
                    const currentImageElement = document.getElementById('currentImage');
                    if (data.post.image_url) {
                        currentImageElement.src = data.post.image_url;
                        imageContainer.style.display = 'block'; // แสดงทั้ง label และ image
                    } else {
                        imageContainer.style.display = 'none'; // ซ่อนทั้ง label และ image
                    }

                    // เปิด Modal
                    var modal = new bootstrap.Modal(document.getElementById('editPostModal'));
                    modal.show();
                } else {
                    alert('เกิดข้อผิดพลาดในการโหลดข้อมูลโพสต์: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('ไม่สามารถโหลดข้อมูลได้');
            });
    }

    // เมื่อคลิกปุ่มแก้ไข ให้โหลดข้อมูลโพสต์เข้าไปใน Modal
    document.querySelectorAll('.edit-post').forEach(btn => {
        btn.addEventListener('click', function () {
            const postId = this.getAttribute('data-post-id');
            
            // เรียก API เพื่อดึงข้อมูลโพสต์
            fetch(`get_post.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    // กรอกข้อมูลโพสต์ใน Modal
                    document.getElementById('editPostId').value = data.id;
                    document.getElementById('editPostTitle').value = data.title;
                    document.getElementById('editPostContent').value = data.content;
                    
                    // ตรวจสอบว่ามีรูปภาพหรือไม่
                    const currentImageContainer = document.getElementById('currentImageContainer');
                    const currentImage = document.getElementById('currentImage');

                    if (data.image_url) {
                        // ถ้ามีรูปภาพให้แสดง
                        currentImage.src = data.image_url;
                        currentImageContainer.style.display = 'block'; // แสดง container ของรูปภาพ
                    } else {
                        // ถ้าไม่มีรูปภาพให้ซ่อน
                        currentImage.src = '';
                        currentImageContainer.style.display = 'none'; // ซ่อน container ของรูปภาพ
                    }

                    // เปิด Modal
                    var editPostModal = new bootstrap.Modal(document.getElementById('editPostModal'));
                    editPostModal.show();
                })
                .catch(error => console.error('Error:', error));
        });
    });


    // ส่งข้อมูลที่แก้ไขแล้วไปยังเซิร์ฟเวอร์
    function submitEditPost() {
        const postId = document.getElementById('editPostId').value;
        const title = document.getElementById('editPostTitle').value;
        const content = document.getElementById('editPostContent').value;
        const image = document.getElementById('editPostImage').files[0];

        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('title', title);
        formData.append('content', content);
        if (image) {
            formData.append('image', image);
        }

        fetch('edit_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('แก้ไขสำเร็จ');
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }


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
    function deleteComment(commentId, postId) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบความคิดเห็นนี้?')) {
            fetch('delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'comment_id=' + commentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadComments(postId); // โหลดความคิดเห็นใหม่หลังจากลบสำเร็จ
                    alert('ลบความคิดเห็นสำเร็จ');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการลบความคิดเห็น');
            });
        }
    }
    function editComment(commentId, currentContent, postId) {
        // สร้างกล่องข้อความเพื่อให้ผู้ใช้แก้ไขความคิดเห็น
        const newContent = prompt('แก้ไขความคิดเห็น:', currentContent);

        // ตรวจสอบว่าผู้ใช้กรอกข้อความใหม่แล้วหรือไม่
        if (newContent !== null && newContent.trim() !== '') {
            // ส่งข้อมูลการแก้ไขไปยังเซิร์ฟเวอร์
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
                    alert('แก้ไขความคิดเห็นสำเร็จ');
                    loadComments(postId);  // โหลดความคิดเห็นใหม่หลังจากแก้ไขสำเร็จ
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการแก้ไขความคิดเห็น');
            });
        }
    }

</script>
</body>
</html>