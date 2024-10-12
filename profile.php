<?php
session_start();
require_once 'server.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

function getUserPosts($pdo, $user_id) {
    $sql = "SELECT p.*, 
            COALESCE((SELECT COUNT(*) FROM likes WHERE post_id = p.id), 0) AS likes_count,
            COALESCE((SELECT COUNT(*) FROM comments WHERE post_id = p.id), 0) AS comments_count
            FROM posts p 
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$user_posts = getUserPosts($pdo, $user_id);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // อัปเดตข้อมูลผู้ใช้ (ไม่รวมรหัสผ่านหากไม่ต้องการเปลี่ยน)
    $update_stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
    $update_stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':user_id' => $user_id
    ]);

    // ตรวจสอบการเปลี่ยนรหัสผ่าน
    if (!empty($password) && $password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน
        $update_password_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
        $update_password_stmt->execute([
            ':password' => $hashed_password,
            ':user_id' => $user_id
        ]);
    } elseif (!empty($password) && $password !== $confirm_password) {
        echo "รหัสผ่านไม่ตรงกัน";
    }

    // อัปโหลดรูปโปรไฟล์
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = uniqid() . '_' . $_FILES['profile_image']['name'];
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $update_image_stmt = $pdo->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :user_id");
            $update_image_stmt->execute([
                ':profile_image' => $upload_path,
                ':user_id' => $user_id
            ]);
        }
    }

    // รีเฟรชข้อมูลผู้ใช้
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}


function getNewsSubcategories($pdo) {
    $sql = "SELECT DISTINCT subcategory FROM posts WHERE category = 'news' AND subcategory IS NOT NULL";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ - ATJ HUB</title>
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
.profile-header {
    background-color: #e4002b;
    color: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
}
.profile-image {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #ffffff;
}
.profile-stats {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.post-card {
    margin-bottom: 20px;
    transition: transform 0.2s;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    padding: 15px;
}
.post-card:hover {
    transform: translateY(-5px);
}
.post-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

/* เพิ่มสไตล์ใหม่เพื่อให้รูปภาพในเนื้อหากระทู้อยู่ตรงกลาง */
.post-content img {
    display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    height: auto;
}

/* สไตล์สำหรับโมดัลแสดงรายละเอียดโพสต์ */
.modal-body img {
    display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    height: auto;
}

/* ปรับแต่งสไตล์เพิ่มเติมสำหรับหน้าโปรไฟล์ */
.profile-username {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.profile-bio {
    font-size: 20px;
    margin-bottom: 20px;
}

.edit-profile-btn {
    background-color: #ffffff;
    color: #e4002b;
    border: 2px solid #e4002b;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    transition: all 0.3s;
}

.edit-profile-btn:hover {
    background-color: #e4002b;
    color: #ffffff;
}

.user-posts-title {
    font-size: 22px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    border-left: 5px solid #e4002b;
    padding-left: 15px;
}
.btn-primary {
    color: #f8f9fa;
    background-color: #e4002b;
    border-color: #e4002b;
}
.btn.disabled, .btn:disabled, fieldset:disabled .btn {
    background-color: #fff;
    color: #e4002b;
    border-color: #dc3545;
}
.bi-three-dots-vertical::before {
    color: #BEBEBE;
   
}
</style>
</head>
<body>
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
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'default_profile.jpg'); ?>" alt="รูปโปรไฟล์" class="profile-image">
                </div>
                <div class="col-md-9">
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p></p>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#editProfileModal">แก้ไขโปรไฟล์</button>
                </div>
            </div>
        </div>

        <form action="" method="get" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                </div>
            </div>
        </form>

        <h2 class="mb-4">โพสต์ของฉัน</h2>
<div class="row">
    <?php if (empty($user_posts)): ?>
        <div class="alert alert-info">คุณยังไม่มีโพสต์</div>
    <?php else: ?>
        <?php foreach ($user_posts as $post): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card post-card h-100">
                    <?php if (!empty($post['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top post-image" alt="รูปภาพโพสต์">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text"><?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?></p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">โดย <?php echo htmlspecialchars($user['username']); ?>
                                <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                            </small>
                                <div>
                                <button class="btn btn-sm btn-outline-primary like-btn" data-post-id="<?php echo $post['id']; ?>" disabled>
                                    <i class="bi bi-hand-thumbs-up"></i> <span class="like-count"><?php echo $post['likes_count']; ?></span>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary comment-btn" data-post-id="<?php echo $post['id']; ?>" disabled>
                                    <i class="bi bi-chat-dots"></i> <span class="comment-count"><?php echo $post['comments_count']; ?></span>
                                </button>
                                </div>
                            </div>
                            <button class="btn btn-primary mt-2 read-more" data-post-id="<?php echo $post['id']; ?>" data-bs-toggle="modal" data-bs-target="#postModal">อ่านเพิ่มเติม</button>
                        </div>
                    </div>
                    <div class="dropdown dropup position-relative">
                        <button class="btn btn-link p-0 three-dots" type="button" id="dropdownMenuButton<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" style="position: absolute; right: 10px; bottom: 10px;">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $post['id']; ?>">
                            <li><a class="dropdown-item edit-post" href="#" data-post-id="<?php echo $post['id']; ?>">แก้ไข</a></li>
                            <li><a class="dropdown-item delete-post" href="#" data-post-id="<?php echo $post['id']; ?>">ลบ</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

    <!-- Modal สำหรับแก้ไขโปรไฟล์ -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">แก้ไขโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล (ไม่สามารถแก้ไขได้)</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">รูปโปรไฟล์</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่านใหม่ (ถ้าไม่เปลี่ยนไม่ต้องกรอก)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal สำหรับแสดงรายละเอียดโพสต์ -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">รายละเอียดโพสต์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="postImage" class="mb-3"></div>
                    <div id="postContent" class="mb-3"></div>
                    <hr>
                    <h6>ความคิดเห็น</h6>
                    <div id="postComments" class="mb-3"></div>
                    <!-- ฟอร์มสำหรับแสดงความคิดเห็น -->
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

    // เพิ่ม Event Listeners
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

        // Event Listener สำหรับการแก้ไขกระทู้
        document.querySelectorAll('.edit-post').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const postId = this.getAttribute('data-post-id');
                editPost(postId);
            });
        });

        function loadPostContent(postId) {
            fetch('get_post_content.php?post_id=' + postId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('postModalLabel').textContent = data.title;
                    const postImage = document.getElementById('postImage');
                    if (data.image_url) {
                        postImage.innerHTML = `<img src="${data.image_url}" class="img-fluid" alt="รูปภาพโพสต์">`;
                    } else {
                        postImage.innerHTML = '';
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

        function loadComments(postId) {
            fetch('get_comments.php?post_id=' + postId)
            .then(response => response.json())
            .then(data => {
                const commentsList = document.getElementById('postComments');
                commentsList.innerHTML = '';
                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment mb-2';
                    commentElement.innerHTML = `
                        <strong>${comment.username}</strong>: ${comment.content}
                        <small class="text-muted">${comment.created_at}</small>
                    `;
                    commentsList.appendChild(commentElement);
                });
            })
            .catch(error => console.error('Error:', error));
        }

        document.querySelectorAll('.read-more').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                loadPostContent(postId);
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
    });

    function submitComment(postId) {
        const content = document.getElementById('commentContent').value.trim();
        if (!content) {
            alert('กรุณาใส่ความคิดเห็นก่อนส่ง');
            return;
        }

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
                // รีเฟรชความคิดเห็นใหม่
                loadComments(postId);
                document.getElementById('commentContent').value = ''; // เคลียร์ฟิลด์ข้อความหลังส่งความคิดเห็นสำเร็จ
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('ไม่สามารถส่งความคิดเห็นได้');
        });
    }

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
    </script>
</body>
</html>