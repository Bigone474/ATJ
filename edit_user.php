<?php
session_start();
include 'server.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] == 0) {
    header('Location: index.php');
    exit;
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "ไม่พบผู้ใช้";
        exit;
    }
}

// บันทึกการแก้ไขข้อมูลผู้ใช้
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $field = $_POST['field']; // ฟิลด์ที่ถูกแก้ไข
    $new_value = $_POST['new_value']; // ค่าที่ถูกแก้ไข

    // ตรวจสอบฟิลด์ที่แก้ไขและทำการอัปเดต
    if ($field == 'username' || $field == 'email' || $field == 'firstname' || $field == 'lastname' || $field == 'phone') {
        // ตรวจสอบ email และเบอร์โทรในกรณีที่เกี่ยวข้อง
        if ($field == 'email' && !filter_var($new_value, FILTER_VALIDATE_EMAIL)) {
            echo "รูปแบบอีเมลไม่ถูกต้อง";
        } elseif ($field == 'phone' && !preg_match('/^[0-9]{10}$/', $new_value)) { // ตรวจสอบเบอร์โทรศัพท์ 10 หลัก
            echo "เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก";
        } else {
            try {
                // อัปเดตข้อมูลในฟิลด์ที่ถูกแก้ไข
                $stmt = $pdo->prepare("UPDATE users SET $field = ? WHERE id = ?");
                $stmt->execute([$new_value, $user_id]);

                // บันทึกประวัติการกระทำ
                $action = "แก้ไข $field ของผู้ใช้: " . $user['username'] . " เป็น $new_value";
                $log_stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action) VALUES (?, ?)');
                $log_stmt->execute([$_SESSION['user_id'], $action]);

                // เมื่อบันทึกข้อมูลเสร็จแล้ว redirect พร้อมส่ง query string ว่าบันทึกสำเร็จ
                header('Location: edit_user.php?id=' . $user_id . '&status=success');
                exit;
            } catch (PDOException $e) {
                echo "เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลผู้ใช้</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css_admin/edit_user.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center text-primary">แก้ไขข้อมูลผู้ใช้</h2>

    <!-- ตารางแสดงข้อมูลผู้ใช้ พร้อมปุ่มแก้ไขในแต่ละฟิลด์ -->
    <div class="table-responsive"> <!-- เพิ่ม class table-responsive -->
        <table class="table table-bordered table-hover table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ฟิลด์</th>
                    <th scope="col">ข้อมูลปัจจุบัน</th>
                    <th scope="col">การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ชื่อผู้ใช้</td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openModal('username', '<?php echo htmlspecialchars($user['username']); ?>')">แก้ไข</button>
                    </td>
                </tr>
                <tr>
                    <td>อีเมล</td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openModal('email', '<?php echo htmlspecialchars($user['email']); ?>')">แก้ไข</button>
                    </td>
                </tr>
                <tr>
                    <td>ชื่อ</td>
                    <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openModal('firstname', '<?php echo htmlspecialchars($user['firstname']); ?>')">แก้ไข</button>
                    </td>
                </tr>
                <tr>
                    <td>นามสกุล</td>
                    <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openModal('lastname', '<?php echo htmlspecialchars($user['lastname']); ?>')">แก้ไข</button>
                    </td>
                </tr>
                <tr>
                    <td>เบอร์โทรศัพท์</td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openModal('phone', '<?php echo htmlspecialchars($user['phone']); ?>')">แก้ไข</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ลิงก์ไปที่หน้า reset_password.php สำหรับเปลี่ยนรหัสผ่าน -->
    <div class="d-flex justify-content-between">
        <a href="reset_password.php?id=<?php echo $user_id; ?>" class="btn btn-warning mt-3">เปลี่ยนรหัสผ่าน</a>
        <a href="admin_users.php" class="btn btn-secondary mt-3">กลับไปหน้าจัดการผู้ใช้</a>
    </div>
</div>

<!-- ป๊อปอัป -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h4>แก้ไขข้อมูล: <span id="fieldName"></span></h4>
        <p>ข้อมูลเดิม: <span id="oldValue"></span></p>
        <form method="POST">
            <input type="hidden" name="field" id="fieldInput">
            <div class="mb-3">
                <label for="new_value" class="form-label">ข้อมูลใหม่:</label>
                <input type="text" class="form-control" id="newValueInput" name="new_value" required
                       onfocus="this.value=''">
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
        </form>
    </div>
</div>

<!-- ป๊อปอัปแจ้งเตือนสำเร็จ -->
<div id="successPopup">บันทึกข้อมูลเรียบร้อยแล้ว!</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS สำหรับป๊อปอัป -->
<script>
    function openModal(field, oldValue) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('fieldName').innerText = field;
        document.getElementById('oldValue').innerText = oldValue;
        document.getElementById('fieldInput').value = field;
        document.getElementById('newValueInput').value = oldValue;
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // ปิด Modal เมื่อคลิกนอก Modal
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // ปิด Modal เมื่อกด Escape
    window.onkeydown = function(event) {
        if (event.key === "Escape") {
            closeModal();
        }
    }

    function showSuccessPopup() {
        const popup = document.getElementById('successPopup');
        popup.style.display = 'block';
        setTimeout(function () {
            popup.style.display = 'none';
        }, 3000); // ปิดป๊อปอัปหลัง 3 วินาที
    }

    // ตรวจสอบ query string ว่ามีสถานะการบันทึกสำเร็จหรือไม่
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        showSuccessPopup(); // แสดงป๊อปอัปแจ้งเตือน
    }
</script>
</body>
</html>