<?php
session_start();
include 'server.php'; // ดึงการเชื่อมต่อฐานข้อมูล

// ตรวจสอบสิทธิ์การเข้าถึงว่าผู้ใช้เป็นแอดมินหรือไม่
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] == 0) {
    header('Location: index.php');
    exit;
}

// ตรวจสอบระดับ admin ของผู้ใช้ปัจจุบัน
$current_admin_level = $_SESSION['is_admin']; // ใช้ is_admin ในการตรวจสอบระดับ

// ตรวจสอบว่ามีการส่งคำขอให้ลบรายการบันทึกหรือไม่
if (isset($_GET['delete_log']) && $current_admin_level == 1) { // Admin ระดับ 1 เท่านั้นที่ทำได้
    $log_id = $_GET['delete_log'];

    try {
        // ลบรายการบันทึกตาม ID
        $stmt = $pdo->prepare('DELETE FROM activity_log WHERE id = ?');
        $stmt->execute([$log_id]);

        // หลังจากลบแล้ว ย้ายกลับมาที่หน้า view_activity_log.php
        header('Location: view_activity_log.php');
        exit;
    } catch (PDOException $e) {
        die("เกิดข้อผิดพลาดในการลบ: " . $e->getMessage());
    }
}

// ตรวจสอบว่ามีการส่งคำขอให้ลบรายการบันทึกทั้งหมดหรือไม่
if (isset($_GET['delete_all']) && $current_admin_level == 1) { // Admin ระดับ 1 เท่านั้นที่ทำได้
    try {
        // ลบรายการบันทึกทั้งหมด
        $stmt = $pdo->prepare('DELETE FROM activity_log');
        $stmt->execute();

        // หลังจากลบแล้ว ย้ายกลับมาที่หน้า view_activity_log.php
        header('Location: view_activity_log.php');
        exit;
    } catch (PDOException $e) {
        die("เกิดข้อผิดพลาดในการลบทั้งหมด: " . $e->getMessage());
    }
}

// ดึงข้อมูลบันทึกการกระทำจากฐานข้อมูล
try {
    $stmt = $pdo->query('SELECT activity_log.*, users.username FROM activity_log JOIN users ON activity_log.user_id = users.id ORDER BY activity_log.created_at DESC');
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกการกระทำ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css_admin/view_activity_log.css">
</head>
<body>
    <div class="container mt-5">
        <h2>บันทึกการกระทำ</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-title">
                <tr>
                    <th>#</th>
                    <th>ผู้ดำเนินการ</th>
                    <th>การกระทำ</th>
                    <th>เวลา</th>
                    <?php if ($current_admin_level == 1): ?> <!-- เฉพาะแอดมินระดับ 1 ที่สามารถลบได้ -->
                        <th>การกระทำเพิ่มเติม</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                            <?php if ($current_admin_level == 1): ?> <!-- แสดงปุ่มลบเมื่อเป็นแอดมินระดับ 1 -->
                                <td>
                                    <button class="btn btn-danger" onclick="showDeleteModal(<?php echo $log['id']; ?>)">ลบ</button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">ไม่มีบันทึกการกระทำ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($current_admin_level == 1 && count($logs) > 0): ?> <!-- เฉพาะแอดมินระดับ 1 ที่สามารถลบได้ -->
            <button class="btn btn-danger mt-3" onclick="showDeleteAllModal()">ลบทั้งหมด</button>
        <?php endif; ?>

        <a href="admin_users.php" class="btn btn-secondary mt-3">กลับไปหน้าจัดการผู้ใช้</a>
    </div>

    <!-- Modal สำหรับยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณต้องการลบรายการนี้หรือไม่?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">ลบ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับยืนยันการลบทั้งหมด -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllModalLabel">ยืนยันการลบทั้งหมด</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณต้องการลบรายการทั้งหมดหรือไม่?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a id="confirmDeleteAllBtn" href="view_activity_log.php?delete_all=true" class="btn btn-danger">ลบทั้งหมด</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ฟังก์ชันเปิด Modal สำหรับยืนยันการลบ
        function showDeleteModal(logId) {
            const deleteUrl = 'view_activity_log.php?delete_log=' + logId;
            document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // ฟังก์ชันเปิด Modal สำหรับยืนยันการลบทั้งหมด
        function showDeleteAllModal() {
            const deleteAllModal = new bootstrap.Modal(document.getElementById('deleteAllModal'));
            deleteAllModal.show();
        }
    </script>
</body>
</html>
