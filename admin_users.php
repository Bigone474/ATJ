<?php
    session_start(); // เริ่มต้น session
    include 'server.php'; // ดึงไฟล์ server.php เข้ามาเพื่อใช้การเชื่อมต่อฐานข้อมูล

    // ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] == 0) {
        header('Location: index.php'); // ถ้าไม่ใช่ admin ให้ย้ายไปหน้าอื่น
        exit;
    }

    // ตรวจสอบระดับ admin ของผู้ใช้ปัจจุบัน
    $current_admin_level = $_SESSION['is_admin']; // ใช้ is_admin ในการตรวจสอบระดับ

    // ตรวจสอบว่ามีการส่งคำขอเพื่อลบผู้ใช้
    if (isset($_GET['delete_user']) && $current_admin_level == 1) { // Admin ระดับ 1 เท่านั้นที่ทำได้
        $user_id = $_GET['delete_user'];

        // ตรวจสอบว่าผู้ใช้นี้มีอยู่ในฐานข้อมูลหรือไม่
        $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            // ลบผู้ใช้จากฐานข้อมูล
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$user_id]);

            // ตรวจสอบว่ามีการตั้งค่า session user_id แล้วหรือไม่
            if (isset($_SESSION['user_id'])) {
                try {
                    // บันทึกการลบลงใน activity_log
                    $action = 'ลบผู้ใช้: ' . $user['username'];
                    $log_stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action) VALUES (?, ?)');
                    $log_stmt->execute([$_SESSION['user_id'], $action]);
                } catch (PDOException $e) {
                    echo "เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage();
                }
            }
        }

        // ย้ายกลับไปหน้าผู้ใช้ทั้งหมด
        header('Location: admin_users.php');
        exit;
    }

    // ตรวจสอบว่ามีการส่งคำขอเปลี่ยนสิทธิ์แอดมินหรือไม่
    if (isset($_GET['toggle_admin']) && $current_admin_level == 1) { // Admin ระดับ 1 เท่านั้นที่ทำได้
        $user_id = $_GET['toggle_admin'];

        // ตรวจสอบว่าผู้ใช้มีสิทธิ์เป็นแอดมินอยู่หรือไม่
        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            // สลับสถานะแอดมิน
            $new_admin_status = $user['is_admin'] == 0 ? 2 : 0; // เปลี่ยนจากธรรมดาเป็น admin หรือยกเลิกสิทธิ admin

            $stmt = $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?');
            $stmt->execute([$new_admin_status, $user_id]);

            // บันทึกการเปลี่ยนแปลงลงใน activity_log
            $action = $new_admin_status == 2 ? 'ให้สิทธิแอดมินแก่ผู้ใช้' : 'ยกเลิกสิทธิแอดมิน';
            $log_stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action) VALUES (?, ?)');
            $log_stmt->execute([$_SESSION['user_id'], $action]);
        }

        // ย้ายกลับไปหน้าผู้ใช้ทั้งหมด
        header('Location: admin_users.php');
        exit;
    }

    // ค้นหาผู้ใช้ (โค้ดค้นหาเหมือนเดิม)
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    if ($search) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC');
        $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    } else {
        $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
    }
    $users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css_admin/admin_users.css">
</head>
<body>
    <div class="container">
        <h1>จัดการผู้ใช้</h1>

        <!-- เพิ่มปุ่มไปยังหน้า admin_posts.php -->
        <div class="mb-3">
            <a href="admin_posts.php" class="btn btn-primary">จัดการโพสต์</a>
        </div>

        <!-- เพิ่มปุ่มดูบันทึกการกระทำ -->
        <div class="mb-3">
            <a href="view_activity_log.php" class="btn btn-info">ดูบันทึกการกระทำ</a>
        </div>

        <!-- ฟอร์มค้นหาผู้ใช้ -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="ค้นหาผู้ใช้...">
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </div>
        </form>

        <!-- ตารางแอดมิน -->
        <h2>ผู้ดูแลระบบ</h2>
        <div class="table-container">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-title">
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>อีเมล</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ระดับแอดมิน</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $admin_count = 0;
                    foreach ($users as $user):
                        if ($user['is_admin'] > 0):
                            $admin_count++;
                    ?>
                        <tr>
                            <td><?php echo $admin_count; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                            <td><?php echo $user['is_admin'] == 1 ? 'แอดมินระดับ 1' : 'แอดมินระดับ 2'; ?></td>
                            <td>
                                <?php if ($current_admin_level == 1 && $user['is_admin'] == 2): ?>
                                    <a href="admin_users.php?toggle_admin=<?php echo $user['id']; ?>" class="btn btn-warning">ยกเลิกสิทธิแอดมิน</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>

        <!-- ตารางผู้ใช้ -->
        <h2>ผู้ใช้ธรรมดา</h2>
        <div class="table-container">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-title">
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>อีเมล</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $user_count = 0;
                    foreach ($users as $user):
                        if ($user['is_admin'] == 0):
                            $user_count++;
                    ?>
                        <tr>
                            <td><?php echo $user_count; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                            <td>
                                <?php if ($current_admin_level == 1): ?>
                                    <a href="admin_users.php?toggle_admin=<?php echo $user['id']; ?>" class="btn btn-success">ให้สิทธิแอดมิน</a>
                                <?php endif; ?>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">แก้ไขข้อมูล</a>
                                <a href="admin_users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('คุณต้องการลบผู้ใช้นี้หรือไม่?')">ลบ</a>
                            </td>
                        </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="btn btn-secondary mt-3">กลับหน้าหลัก</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>